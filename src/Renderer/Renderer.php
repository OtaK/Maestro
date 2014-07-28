<?php

    namespace Maestro\Renderer;

    use Maestro\Maestro;

    /**
     * Class Renderer
     * @package Maestro\Renderer
     */
    abstract class Renderer implements \ArrayAccess
    {
        protected $_data;
        protected $_controller;
        protected $_action;
        public $raw;

        /**
         * CTOR
         * @param array $data
         */
        public function __construct($data = array())
        {
            $this->_data = $data;
            $this->raw = true;
        }

        /**
         * Renderer factory
         * @param $type
         * @throws \Exception
         * @return null|Renderer
         */
        static public function Factory($type)
        {
            $class = self::ClassFactory($type);
            if ($class === null)
                throw new \Exception('Renderer class '.$type.' not found!');

            return new $class();
        }

        /**
         * Renderer class name factory
         * @param string $type
         * @param string $namespace
         * @return null|string
         */
        static public function ClassFactory($type, $namespace = 'Maestro\\Renderer\\RenderTypes')
        {
            $class = $namespace.'\\'.ucfirst($type).'Renderer';
            if (class_exists($class))
                return $class;

            $class = $namespace.'\\'.strtoupper($type).'Renderer';
            if (class_exists($class))
                return $class;

            return null;
        }

        /**
         * @param $controller
         * @param $action
         * @return self
         */
        public function curRoute($controller, $action)
        {
            $tmp = ltrim(str_replace(Maestro::gi()->get('controller namespace'), '', $controller), '\\');
            $this->_controller = $tmp;
            $this->_action     = $action;

            return $this;
        }

        /**
         * @param mixed $offset
         * @return bool
         */
        public function offsetExists($offset)
        {
            return isset($this->_data[$offset]);
        }

        /**
         * @param mixed $offset
         * @return mixed
         */
        public function offsetGet($offset)
        {
            return $this->_data[$offset];
        }

        /**
         * @param mixed $offset
         * @param mixed $value
         */
        public function offsetSet($offset, $value)
        {
            $this->_data[$offset] = $value;
        }

        /**
         * @param mixed $offset
         */
        public function offsetUnset($offset)
        {
            if (isset($this->_data[$offset]))
                unset($this->_data[$offset]);
        }

        /**
         * Render function.
         * @param array|mixed $vars New variables to merge just in time
         * @return int - Length of content just echoed
         */
        public function render($vars = array())
        {
            ob_start();
            $this->_render($vars);
            $buf = ob_get_clean();
            $len = strlen($buf);
            echo $buf;
            return $len;
        }

        /**
         * Private function to override by renderers
         * @param array $vars
         */
        abstract protected function _render($vars = array());
    }