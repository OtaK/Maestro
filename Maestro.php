<?php

    namespace Maestro;

    use Maestro\HTTP\Request;
    use Maestro\Renderer\Renderer;
    use Maestro\Router\Router;
    use Maestro\Utils\HttpCommons;
    use Maestro\Utils\Settings;

    /**
     * Class Maestro
     *
     * PHP MVC's choirmaster.
     *
     * Manages routing, dispatching and middleware injection.
     *
     * @package Maestro
     */
    class Maestro
    {
        const DEBUG = true;

        /** @var Maestro|null - Singleton */
        static private $_instance = null;

        /** @var Settings - Settings object */
        static protected $_settings = null;

        /** @var Router - Router handling instance */
        protected $_router;
        /** @var array - Routes array */
        protected $_routes;
        /** @var array - Controllers array */
        protected $_controllers;
        /** @var array - Middlewares stack */
        protected $_middlewares;
        /** @var Renderer - Renderer class used to render views */
        protected $_renderer;

        /**
         * CTOR
         */
        public function __construct()
        {
            $this->_router      = new Router();
            $this->_controllers = array();
            $this->_middlewares = array();
            $this->_routes      = array();
        }

        /**
         * Singleton getter
         * @return Maestro
         */
        public static function gi()
        {
            if (self::$_instance === null)
                self::$_instance = new Maestro();

            return self::$_instance;
        }

        /**
         * Settings object automatic instanciation
         */
        private static function __sinst()
        {
            if (self::$_settings === null)
            {
                self::$_settings                         = new Settings();
                self::$_settings['view engine']          = 'php';
                self::$_settings['app path']             = __DIR__ . '/Tests/';
                self::$_settings['controller namespace'] = '\\';
            }
        }

        /**
         * @param $field
         * @return $this
         */
        public function enable($field)
        {
            self::__sinst();
            self::$_settings[$field] = true;

            return $this;
        }

        /**
         * @param $field
         * @return $this
         */
        public function disable($field)
        {
            self::__sinst();
            self::$_settings[$field] = false;

            return $this;
        }

        /**
         * @param $field
         * @return bool
         */
        public function enabled($field)
        {
            self::__sinst();

            return isset(self::$_settings[$field]) && !!self::$_settings[$field];
        }

        /**
         * @param $field
         * @return bool
         */
        public function disabled($field)
        {
            self::__sinst();

            return !isset(self::$_settings[$field]) || !self::$_settings[$field];
        }

        /**
         * @param $field
         * @return mixed
         */
        public function get($field)
        {
            self::__sinst();

            return self::$_settings[$field];
        }

        /**
         * @param $field
         * @param $value
         * @return $this
         */
        public function set($field, $value)
        {
            self::__sinst();
            self::$_settings[$field] = $value;

            return $this;
        }

        /**
         * @param      $path
         * @param null $middleware
         * @return $this
         */
        public function mount($path, $middleware = null)
        {
            if ($path instanceof \Closure)
            {
                $middleware = $path;
                $path       = '/*';
            }

            $this->_middlewares[] = & $middleware;
            $this->_routes[]      = array(
                'pattern' => $path,
                'verbs'   => array(
                    HttpCommons::HTTP_VERB_GET,
                    HttpCommons::HTTP_VERB_PUT,
                    HttpCommons::HTTP_VERB_POST,
                    HttpCommons::HTTP_VERB_DELETE,
                    HttpCommons::HTTP_VERB_HEAD
                ),
                'handler' => &$middleware
            );

            return $this;
        }

        /**
         * Starts conducting our orchestra
         * - Boots the router
         * - Injects the middlewares
         * - Instanciates the controller in memory
         */
        public function conduct()
        {
            self::__sinst();

            $this->_router
                ->batchMatch($this->_routes)
                ->init()
                ->assignRequest(new Request(true))
                ->assignControllerNamespace($this->get('controller namespace'))
                ->drive();
        }

        /**
         * @return Router
         */
        public function route()
        {
            return $this->_router;
        }
    }