<?php

    namespace Maestro\Renderer\RenderTypes;

    use Maestro\Renderer\Renderer;

    /**
     * Class JSONPRenderer
     * @package Maestro\Renderer\RenderTypes
     */
    class JSONPRenderer extends Renderer
    {
        const CONTENT_TYPE = 'text/javascript';
        public $callback;

        /**
         * Render function.
         * @param mixed $vars New variables to merge just in time
         */
        protected function _render($vars = array())
        {
            $this->_data = is_array($vars) ? array_merge($this->_data, $vars) : $vars;
            echo $this->callback.'('.json_encode($this->_data).');';
        }
    }