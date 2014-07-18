<?php

    namespace Maestro\Tests\Controllers;

    use Maestro\Controller;

    /**
     * Class Test
     * @package Maestro\Tests\Controllers
     */
    class Test extends Controller
    {
        protected function _setup()
        {
            $this->_layoutPath = '_layouts/sweg';
        }

        public function index()
        {
            $this->render();
        }
    }