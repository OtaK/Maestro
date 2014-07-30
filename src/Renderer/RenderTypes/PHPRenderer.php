<?php

    namespace Maestro\Renderer\RenderTypes;

    use Exception;
    use Maestro\Maestro;
    use Maestro\Renderer\Renderer;

    class PHPRendererLayoutNotFound extends \Exception
    {
        public function __construct($path)
        {
            parent::__construct("Template file not found at $path when constructing response");
        }
    }

    class PHPRendererActionNotFound extends PHPRendererLayoutNotFound {}

    /**
     * Class PHPRenderer
     * @package Maestro\Renderer\RenderTypes
     */
    class PHPRenderer extends Renderer
    {
        const CONTENT_TYPE = 'text/html';
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
            self::$_viewsPath = Maestro::gi()->get('app path').'/views';
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
         * @throws PHPRendererLayoutNotFound
         * @throws PHPRendererActionNotFound
         */
        protected function _render($vars = array())
        {
            $this->_data = is_array($vars) ? array_merge($this->_data, $vars) : $vars;

            if ($this->raw)
            {
                echo $this->_data;
                return;
            }

            extract(array_merge(self::$_commons, $this->_data), EXTR_OVERWRITE|EXTR_REFS);
            $actionFile = self::$_viewsPath.'/'.strtolower($this->_controller).'/'.$this->_action.'.'.$this->_extension;
            if (file_exists($actionFile))
            {
                ob_start();
                include $actionFile;
                $this->__action = ob_get_clean();
            }
            else
                throw new PHPRendererActionNotFound($actionFile);

            $layoutFile = self::$_viewsPath.'/'.$this->_layout.'.'.$this->_extension;
            if (!file_exists($layoutFile))
                throw new PHPRendererLayoutNotFound($layoutFile);

            include $layoutFile;
        }

        /**
         * @return mixed
         */
        private function _yield()
        {
            return $this->__action;
        }
    }