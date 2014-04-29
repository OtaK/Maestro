<?php

    namespace FastRoute\DataGenerator;

    use FastRoute\DataGenerator;
    use FastRoute\BadRouteException;
    use FastRoute\Route;

    /**
     * Class RegexBasedAbstract
     * @package FastRoute\DataGenerator
     */
    abstract class RegexBasedAbstract implements DataGenerator
    {
        protected $staticRoutes = array();
        protected $regexToRoutesMap = array();

        /**
         * @param string $httpMethod
         * @param array  $routeData
         * @param mixed  $handler
         */
        public function addRoute($httpMethod, $routeData, $handler)
        {
            if ($this->isStaticRoute($routeData))
            {
                $this->addStaticRoute($httpMethod, $routeData, $handler);
            }
            else
            {
                $this->addVariableRoute($httpMethod, $routeData, $handler);
            }
        }

        /**
         * @param $routeData
         * @return bool
         */
        private function isStaticRoute($routeData)
        {
            return count($routeData) == 1 && is_string($routeData[0]);
        }

        /**
         * @param $httpMethod
         * @param $routeData
         * @param $handler
         * @throws \FastRoute\BadRouteException
         */
        private function addStaticRoute($httpMethod, $routeData, $handler)
        {
            $routeStr = $routeData[0];

            if (isset($this->staticRoutes[$routeStr][$httpMethod]))
            {
                throw new BadRouteException(sprintf(
                    'Cannot register two routes matching "%s" for method "%s"',
                    $routeStr, $httpMethod
                ));
            }

            foreach ($this->regexToRoutesMap as $routes)
            {
                if (!isset($routes[$httpMethod])) continue;

                $route = $routes[$httpMethod];
                /** @noinspection PhpUndefinedMethodInspection */
                if ($route->matches($routeStr))
                {
                    throw new BadRouteException(sprintf(
                        'Static route "%s" is shadowed by previously defined variable route "%s" for method "%s"',
                        $routeStr, $route->regex, $httpMethod
                    ));
                }
            }

            $this->staticRoutes[$routeStr][$httpMethod] = $handler;
        }

        /**
         * @param $httpMethod
         * @param $routeData
         * @param $handler
         * @throws \FastRoute\BadRouteException
         */
        private function addVariableRoute($httpMethod, $routeData, $handler)
        {
            list($regex, $variables) = $this->buildRegexForRoute($routeData);

            if (isset($this->regexToRoutesMap[$regex][$httpMethod]))
            {
                throw new BadRouteException(sprintf(
                    'Cannot register two routes matching "%s" for method "%s"',
                    $regex, $httpMethod
                ));
            }

            $this->regexToRoutesMap[$regex][$httpMethod] = new Route(
                $httpMethod, $handler, $regex, $variables
            );
        }

        /**
         * @param $routeData
         * @return array
         * @throws \FastRoute\BadRouteException
         */
        private function buildRegexForRoute($routeData)
        {
            $regex     = '';
            $variables = array();
            foreach ($routeData as $part)
            {
                if (is_string($part))
                {
                    $regex .= preg_quote($part, '~');
                    continue;
                }

                list($varName, $regexPart) = $part;

                if (isset($variables[$varName]))
                {
                    throw new BadRouteException(sprintf(
                        'Cannot use the same placeholder "%s" twice', $varName
                    ));
                }

                $variables[$varName] = $varName;
                $regex .= '(' . $regexPart . ')';
            }

            return array($regex, $variables);
        }
    }