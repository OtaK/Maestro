<?php

    namespace Maestro\Renderer\RenderTypes;

    use Maestro\Renderer\Renderer;

    /**
     * Class FileRenderer
     * @package Maestro\Renderer\RenderTypes
     */
    class FileRenderer extends Renderer
    {
        /**
         * Render function.
         * @param string $path file path to render
         */
        protected function _render($path = '')
        {
            if (!empty($path))
                readfile($path);
        }
    }