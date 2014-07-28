<?php

    namespace Maestro\Renderer\RenderTypes;
    use Maestro\Renderer\Renderer;


    /**
     * Class JSONRenderer
     * @package Maestro\Renderer\RenderTypes
     */
    class JSONRenderer extends Renderer
    {
        /**
         * Render function.
         * @param array $vars New variables to merge just in time
         */
        protected function _render($vars = array())
        {
            $this->_data = is_array($vars) ? array_merge($this->_data, $vars) : $vars;
            echo json_encode($this->_data);
        }
    }