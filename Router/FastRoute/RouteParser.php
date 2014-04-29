<?php

    namespace FastRoute;

    /**
     * Interface RouteParser
     * @package FastRoute
     */
    interface RouteParser
    {
        /**
         * Returns an array of the following form:
         *
         * [
         *     "/fixedRoutePart/",
         *     ["varName", "[^/]+"],
         *     "/moreFixed/",
         *     ["varName2", [0-9]+"],
         * ]
         *
         * @param string $route Route to parse
         *
         * @return array Parsed route data
         */
        public function parse($route);
    }
