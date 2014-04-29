<?php

    namespace Maestro\Renderer\RenderTypes;

    use Maestro\Renderer\Renderer;

    /**
     * Class JSONPRenderer
     * @package Maestro\Renderer\RenderTypes
     */
    class JSONPRenderer extends Renderer
    {
        public $callback;

        /**
         * Render function.
         * @param mixed $vars New variables to merge just in time
         */
        public function render($vars = array())
        {
            $this->_data = array_merge($this->_data, $vars);
            echo $this->callback.'('.json_encode($this->_data).');';
        }
    }