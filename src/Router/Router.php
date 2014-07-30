<?php

    namespace Maestro\Router;

    require_once __DIR__ . '/FastRoute/bootstrap.php';

    use Maestro\Controller;
    use Maestro\HTTP\Request;
    use Maestro\HTTP\Response;
    use Maestro\Maestro;
    use Maestro\Utils\HttpCommons;
    use Maestro\Utils\HttpStatusCode;
    use FastRoute\BadRouteException;
    use FastRoute\Dispatcher;
    use FastRoute\RouteCollector;
    use Jeremeamia\SuperClosure\SerializableClosure;

    /**
     * Class Router
     * @package Maestro\Router
     */
    class Router extends HttpCommons
    {
        const CACHE_FILE_LOCATION = '/tmp/maestro.routing.cache';

        static private $RESOURCE_ROUTES = array(
            'get'   => array(
                'index' => '',
                'show' => '/{id[0-9]+}'
            ),
            'post'  => array('create' => ''),
            'put'   => array('update' => '/{id[0-9]+}' ),
            'del'   => array('destroy' => '/{id[0-9]+}')
        );

        /** @var array - Controller instanciation cache array */
        static private $__controllerCache = array();

        /** @var string - Default response renderer */
        protected $_defaultRenderer;
        /** @var string - Controllers base namespace */
        protected $_controllerNamespace;
        /** @var string - Controller name */
        protected $_controller;
        /** @var string - Action name */
        protected $_action;
        /** @var Dispatcher - Router handling instance */
        protected $_dispatcher;
        /** @var array - Routes Array */
        protected $_routes;
        /** @var string - Route prefix when using folders/namespaces */
        protected $_prefix;
        /** @var Request - Request object */
        protected $_req;
        /** @var Response - Response object */
        protected $_res;


        /**
         * CTOR
         */
        public function __construct()
        {
            $this->_routes              = array();
            $this->_dispatcher          = null;
            $this->_controller          = null;
            $this->_action              = null;
            $this->_prefix              = null;
            $this->_controllerNamespace = null;
            $this->_defaultRenderer     = null;
        }

        /**
         * Sets default renderer for all responses throughout app
         * @param string $renderer
         * @return self
         */
        public function defaultRenderer($renderer)
        {
            $this->_defaultRenderer = $renderer;

            return $this;
        }

        /**
         * Assigns incoming request
         * @param Request $req
         * @return self
         */
        public function assignRequest(Request $req)
        {
            $this->_req = $req;

            return $this;
        }

        /**
         * Assigns controller base namespace
         * @param $ns
         * @return self
         */
        public function assignControllerNamespace($ns)
        {
            $this->_controllerNamespace = $ns;

            return $this;
        }

        /**
         * Inits dispatcher according to routes.
         *
         * @chainable
         * @return self
         */
        public function init()
        {
            $routes = $this->_routes;

            $this->_dispatcher = \FastRoute\cachedDispatcher(function (RouteCollector $routeCollector) use ($routes) {
                foreach ($routes as &$r)
                    foreach ($r['verbs'] as $verb => $handler)
                        $routeCollector->addRoute($verb, $r['pattern'], $handler);
            }, array(
                'cacheFile'     => self::CACHE_FILE_LOCATION,
                'cacheDisabled' => 'development' === Maestro::gi()->get('env')
            ));

            return $this;
        }

        /**
         * @throws \Exception
         * @return bool|Response
         */
        public function drive()
        {
            if ($this->_dispatcher === null)
                throw new \Exception('Maestro::Router - Dispatcher not initialized!');

            $result     = $this->_dispatcher->dispatch($this->_req->method, $this->_req->path);
            $this->_res = new Response();
            
            if ($this->_defaultRenderer !== null)
            {
                $this->_res->renderer($this->_defaultRenderer);
                $rendererClass = get_class($this->_res->renderer());
                $ct = $rendererClass::CONTENT_TYPE;
                if ($ct !== null)
                    $this->_res->set('content-type', $ct);
            }

            switch ($result[0])
            {
                case Dispatcher::NOT_FOUND:
                    $this->_res->send(HttpStatusCode::NOT_FOUND);
                    break;
                case Dispatcher::METHOD_NOT_ALLOWED:
                    $this->_res->set('allow', implode(', ', $result[1]));
                    $this->_res->send(HttpStatusCode::METHOD_NOT_ALLOWED);
                    break;
                case Dispatcher::FOUND:
                    list(, $handler, $vars) = $result;

                    $this->_req->params = array_merge($this->_req->params, $vars);

                    if ($handler instanceof \Closure 
                    || $handler instanceof \Jeremeamia\SuperClosure\SerializableClosure) // Simple closure
                    {
                        $this->_res->locals = self::_invokeClosureWithParams($this, $handler);
                        break;
                    }

                    $tmp = $this->_parseHandler($handler);
                    if ($tmp === null)
                        $tmp = $this->_parseHandler($handler, '::');

                    if ($tmp === null)
                        throw new BadRouteException("Impossible to parse given handler string [$handler]");

                    list($class, $method) = $tmp;

                    if (!class_exists($class))
                        require_once Maestro::gi()->get('app path').'/controllers/'.($this->_prefix !== null ? $this->_prefix.'/' : '').ucfirst($class).'.php';

                    $class = $this->_controllerNamespace .
                        ($this->_prefix ? '\\'.str_replace(array('/', '.'), array('\\', '_'), $this->_prefix) : '') .
                        '\\' . ucfirst($class);

                    if (!class_exists($class))
                    {
                        $this->_res->send(HttpStatusCode::INTERNAL_SERVER_ERROR);
                        throw new \Exception("Controller [$class] not found!");
                    }

                    $controller      = self::_cachedController($class);
                    $controller->req = &$this->_req;
                    $controller->res = &$this->_res;
                    $controller->app = Maestro::gi();
                    $controller->init();

                    if (!method_exists($controller, $method))
                    {
                        $this->_res->send(HttpStatusCode::INTERNAL_SERVER_ERROR);
                        throw new \Exception("Method $class::[$method] not found");
                    }

                    $this->_controller = $class;
                    $this->_action     = $method;

                    self::_invokeMethodWithParams($controller, $method, $vars);
                    $this->_res->renderer()->curRoute($this->_controller, $this->_action);
                    break;
            }

            return $this->_res;
        }

        /**
         * Invokes a closure with request and response given by reference
         * @param Router   $ctx
         * @param callable $closure
         * @return array
         */
        static protected function _invokeClosureWithParams(Router $ctx, $closure)
        {
            // Inject req and res
            return $closure($ctx->_req, $ctx->_res);
        }

        /**
         * @param string $handler
         * @param string $sep
         * @return array|null
         */
        private function _parseHandler($handler, $sep = '#')
        {
            $prefix = explode('/', $handler);
            if ($prefix[0] !== $handler)
            {
                $this->_prefix = $prefix[1];
                $handler = $prefix[2];
            }

            $tmp = explode($sep, $handler);
            if ($tmp[0] !== $handler)
                return $tmp;

            return null;
        }

        /**
         * @param $class
         * @return mixed
         * @throws \Exception
         */
        static protected function &_cachedController($class)
        {
            if (!isset(self::$__controllerCache[$class])) // Init controller and put it in cache for future usages
            {
                $controller                      = new $class();
                $controller->app                 = Maestro::gi();
                self::$__controllerCache[$class] = array(
                    '__controller' => $controller
                );
            }
            else
                $controller = & self::$__controllerCache[$class]['__controller'];

            return $controller;
        }

        /**
         * @param Controller $controller
         * @param            $method
         * @param            $params
         * @return array
         */
        static protected function _invokeMethodWithParams($controller, $method, $params)
        {
            $class = get_class($controller);
            if (!isset(self::$__controllerCache[$class][$method])) // Cache reflections because those are *very* costly
                self::$__controllerCache[$class][$method] = new \ReflectionMethod($controller, $method);

            /** @var \ReflectionMethod $reflection */
            $reflection   = & self::$__controllerCache[$class][$method];
            $actionParams = array();
            foreach ($reflection->getParameters() as $p)
            {
                $actionParams[] = (isset($params[$p->getName()])
                    ? $params[$p->getName()]
                    : $p->getDefaultValue());
            }

            return $controller->invoke($method, $actionParams);
        }

        /**
         * Returns current controller name
         * @return null|string
         */
        public function getController()
        {
            return $this->_controller;
        }

        /**
         * Returns current action name
         * @return null|string
         */
        public function getAction()
        {
            return $this->_action;
        }

        /**
         * Use with a closure that takes this router as a param
         * @param          $path
         * @param callable $folderDef
         */
        public function ns($path, \Closure $folderDef)
        {
            $this->_prefix = $path;
            $folderDef($this);
            $this->_prefix = null;
        }

        /**
         * REST resource pattern generator
         * @param string $pattern    - Base pattern for the resource (eg: /users)
         * @param string $controller - Controller name for the resource (eg: user)
         * @param array  $without    - List of methods to exclude amongst index, create, update, show, destroy
         * @return self
         */
        public function resource($pattern, $controller, array $without = array())
        {
            foreach (self::$RESOURCE_ROUTES as $verb => $handlers)
            {
                foreach ($handlers as $method => $path)
                {
                    if (in_array($method, $without, true)) continue;
                    $this->{$verb}($pattern . $path, $controller . '#' . $method);
                }
            }

            return $this;
        }

        /**
         * Servs static files in folder $path
         * @param string $path - Root of static assets
         * @return self
         */
        public function assets($path)
        {
            return $this->get($path.'/{path:.*}', function(Request $req, Response $res) {
                $path = Maestro::gi()->get('base path') . str_replace('..', '', $req->path);
                $res->sendfile($path);
            });
        }

        /**
         * @param $pattern
         * @param $handler
         * @return self
         */
        public function get($pattern, $handler)
        {
            return $this->match($pattern, $handler, array(self::HTTP_VERB_GET));
        }

        /**
         * @param       $pattern
         * @param       $handler
         * @param array $verbs
         * @chainable
         * @return self
         */
        public function match($pattern, $handler, $verbs = array(self::HTTP_VERB_GET))
        {
            if ($this->_prefix !== null)
            {
                $pattern = $this->_prefix . $pattern;
                if (is_string($handler))
                    $handler = $this->_prefix . '/' . $handler;
            }

            if ($handler instanceof \Closure) // Serializable closures support
                $handler = new SerializableClosure($handler);

            $verbs = array_flip($verbs);
            foreach ($verbs as &$v)
                $v = $handler;

            $this->_routes[$pattern] = array(
                'verbs'   => array_unique(array_merge(
                    isset($this->_routes[$pattern]) ? $this->_routes[$pattern]['verbs'] : array(),
                    $verbs
                ), SORT_REGULAR),
                'pattern' => $pattern
            );

            return $this;
        }

        /**
         * @param array $routes
         * @return self
         */
        public function batchMatch(array $routes)
        {
            foreach ($routes as $r)
                $this->match($r['pattern'], $r['handler'], $r['verbs']);

            return $this;
        }

        /**
         * @param $pattern
         * @param $handler
         * @return self
         */
        public function post($pattern, $handler)
        {
            return $this->match($pattern, $handler, array(self::HTTP_VERB_POST));
        }

        /**
         * @param $pattern
         * @param $handler
         * @return self
         */
        public function put($pattern, $handler)
        {
            return $this->match($pattern, $handler, array(self::HTTP_VERB_PUT));
        }

        /**
         * @param $pattern
         * @param $handler
         * @return self
         */
        public function del($pattern, $handler)
        {
            return $this->match($pattern, $handler, array(self::HTTP_VERB_DELETE));
        }

        /**
         * @param $pattern
         * @param $handler
         * @return self
         */
        public function head($pattern, $handler)
        {
            return $this->match($pattern, $handler, array(self::HTTP_VERB_HEAD));
        }

        /**
         * @param $pattern
         * @param $handler
         * @return self
         */
        public function all($pattern, $handler)
        {
            return $this->match($pattern, $handler, array(
                self::HTTP_VERB_GET,
                self::HTTP_VERB_POST,
                self::HTTP_VERB_DELETE,
                self::HTTP_VERB_PUT,
                self::HTTP_VERB_HEAD
            ));
        }
    }
