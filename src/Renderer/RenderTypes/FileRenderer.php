<?php

    namespace Maestro\Renderer\RenderTypes;

    use Maestro\Renderer\Renderer;

    /**
     * Class FileRenderer
     * @package Maestro\Renderer\RenderTypes
     */
    class FileRenderer extends Renderer
    {
        public function render($path = '')
        {
            return $this->_render($path);
        }

        /**
         * Render function.
         * @param string $path file path to render
         * @return int
         */
        protected function _render($path = '')
        {
            if (!empty($path))
            {
                readfile($path);
                return filesize($path);
            }
        }
    }