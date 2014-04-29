<?php

    namespace Maestro;

    use Maestro\HTTP\Request;
    use Maestro\HTTP\Response;
    use Maestro\Renderer\RenderTypes\PHPRenderer;

    /**
     * Class Controller
     * @package Maestro
     */
    abstract class Controller
    {
        /** @var array - Controller data for templates */
        private $_data;
        /** @var array - Handler hash for before filters */
        private $_beforeFilters;
        /** @var array - Handler hash for after filters */
        private $_afterFilters;

        /** @var string - Layout file path */
        protected $_layoutPath;

        /** @var Response - HTTP Response */
        public $res;
        /** @var Request - HTTP Request */
        public $req;
        /** @var Maestro - Maestro app */
        public $app;

        /**
         * CTOR
         */
        final public function __construct()
        {
            $this->_data          = array();
            $this->_afterFilters  = array();
            $this->_beforeFilters = array();
            $this->_layoutPath    = null;
            $this->_setup();
        }

        /**
         * @param string $action
         * @param mixed  $params
         * @return mixed
         */
        final public function invoke($action, $params)
        {
            $this->_callBefore('*');
            $this->_callBefore($action);
            $ret = call_user_func_array(array($this, $action), $params);
            $this->_callAfter('*');
            $this->_callAfter($action);
            return $ret;
        }


        /**
         * Can be overriden to setup before and/or after filters
         */
        protected function _setup() {}

        /**
         * @param string $action
         */
        private function _callBefore($action)
        {
            if (!isset($this->_beforeFilters[$action]))
                return;

            foreach ($this->_beforeFilters[$action] as $c)
                $c($this->req, $this->res);
        }

        /**
         * @param string $action
         */
        private function _callAfter($action)
        {
            if (!isset($this->_afterFilters[$action]))
                return;

            foreach ($this->_afterFilters[$action] as $c)
                $c($this->req, $this->res);
        }

        /**
         * Adds a $handler to before filters on designated $action
         * @param string   $action
         * @param callable $handler
         * @return $this
         */
        final public function before($action, \Closure $handler)
        {
            if (!isset($this->_beforeFilters[$action]))
                $this->_beforeFilters[$action] = array();

            if (!in_array($handler, $this->_beforeFilters[$action]))
                $this->_beforeFilters[$action][] = $handler;

            return $this;
        }

        /**
         * Adds a $handler to after filters on designated $action
         * @param string   $action
         * @param callable $handler
         * @return $this
         */
        final public function after($action, \Closure $handler)
        {
            if (!isset($this->_afterFilters[$action]))
                $this->_afterFilters[$action] = array();

            if (!in_array($handler, $this->_afterFilters[$action]))
                $this->_afterFilters[$action][] = $handler;

            return $this;
        }

        /**
         * @param string     $name
         * @param null|mixed $default
         * @return mixed
         */
        final public function param($name, $default = null)
        {
            return $this->req->param($name, $default);
        }

        /**
         * Inits req/res/app data
         */
        public function init()
        {
            $this->res->renderer('php');
        }

        /**
         * Initiates default controller rendering
         */
        final public function render()
        {
            $renderer = $this->res->renderer();
            if ($renderer instanceof PHPRenderer)
                $renderer->setLayoutPath($this->_layoutPath);

            $this->res->render($this->_data);
        }

        /**
         * Magic getter for $this->_data
         * @param $name
         * @return null|mixed
         */
        final public function __get($name)
        {
            return isset($this->_data[$name]) ? $this->_data[$name] : null;
        }

        /**
         * Magic setter for $this->_data
         * @param $name
         * @param $value
         */
        final public function __set($name, $value)
        {
            $this->_data[$name] = $value;
        }
    }