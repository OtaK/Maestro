<?php

    namespace FastRoute;

    /**
     * Class RouteCollector
     * @package FastRoute
     */
    class RouteCollector
    {
        private $routeParser;
        private $dataGenerator;

        /**
         * Constructs a route collector.
         *
         * @param RouteParser   $routeParser
         * @param DataGenerator $dataGenerator
         */
        public function __construct(RouteParser $routeParser, DataGenerator $dataGenerator)
        {
            $this->routeParser   = $routeParser;
            $this->dataGenerator = $dataGenerator;
        }

        /**
         * Adds a route to the collection.
         *
         * The syntax used in the $route string depends on the used route parser.
         *
         * @param string $httpMethod
         * @param string $route
         * @param mixed  $handler
         */
        public function addRoute($httpMethod, $route, $handler)
        {
            $routeData = $this->routeParser->parse($route);
            $this->dataGenerator->addRoute($httpMethod, $routeData, $handler);
        }

        /**
         * Returns the collected route data, as provided by the data generator.
         *
         * @return array
         */
        public function getData()
        {
            return $this->dataGenerator->getData();
        }
    }
