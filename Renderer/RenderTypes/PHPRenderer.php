<?php

    namespace Maestro\Renderer\RenderTypes;

    use Maestro\Maestro;
    use Maestro\Renderer\Renderer;

    /**
     * Class PHPRenderer
     * @package Maestro\Renderer\RenderTypes
     */
    class PHPRenderer extends Renderer
    {
        static protected $_commons = array();
        static protected $_viewsPath;
        protected $_layout;
        protected $_extension;
        private  $__action;

        /**
         * CTOR
         * @param array $data
         */
        function __construct($data = array())
        {
            parent::__construct($data);
            $this->_extension = 'phtml';
            self::$_viewsPath = Maestro::gi()->get('app path').'/views/';
            $this->raw = false;
        }

        /**
         * Sets layout file path
         * @param $path
         */
        public function setLayoutPath($path)
        {
            $this->_layout = $path;
        }

        /**
         * Render function.
         * Renders using PHP's default template engine: PHP itself.
         * Override if you need something else.
         * @param array $vars New variables to merge just in time
         */
        public function render($vars = array())
        {
            if (is_array($vars))
                $this->_data = array_merge($this->_data, $vars);

            if ($this->raw)
            {
                echo $vars;
                return;
            }

            extract(array_merge(self::$_commons, $this->_data), EXTR_OVERWRITE|EXTR_REFS);
            ob_start();
            include self::$_viewsPath.'/'.$this->_controller.'/'.$this->_action.'.'.$this->_extension;
            $this->__action = ob_get_clean();
            include self::$_viewsPath.'/'.$this->_layout.'.'.$this->_extension;
        }

        /**
         * @return mixed
         */
        /** @noinspection PhpUnusedPrivateMethodInspection */
        private function _yield()
        {
            return $this->__action;
        }
    }