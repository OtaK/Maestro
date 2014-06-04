<?php

    namespace FastRoute\DataGenerator;

    /**
     * Class GroupPosBased
     * @package FastRoute\DataGenerator
     */
    class GroupPosBased extends RegexBasedAbstract
    {
        const APPROX_CHUNK_SIZE = 10;

        /**
         * @return array
         */
        public function getData()
        {
            if (empty($this->regexToRoutesMap))
            {
                return array($this->staticRoutes, array());
            }

            return array($this->staticRoutes, $this->generateVariableRouteData());
        }

        /**
         * @return array
         */
        private function generateVariableRouteData()
        {
            $chunkSize = $this->computeChunkSize(count($this->regexToRoutesMap));
            $chunks    = array_chunk($this->regexToRoutesMap, $chunkSize, true);

            return array_map(array($this, 'processChunk'), $chunks);
        }

        /**
         * @param $count
         * @return float
         */
        private function computeChunkSize($count)
        {
            $numParts = max(1, round($count / self::APPROX_CHUNK_SIZE));

            return ceil($count / $numParts);
        }

        /**
         * @param $regexToRoutesMap
         * @return array
         */
        private function processChunk($regexToRoutesMap)
        {
            $routeMap = array();
            $regexes  = array();
            $offset   = 1;
            foreach ($regexToRoutesMap as $regex => $routes)
            {
                foreach ($routes as $route)
                {
                    $routeMap[$offset][$route->httpMethod]
                        = array($route->handler, $route->variables);
                }

                $regexes[] = $regex;
                $offset += count(reset($routes)->variables);
            }

            $regex = '~^(?:' . implode('|', $regexes) . ')$~';

            return array('regex' => $regex, 'routeMap' => $routeMap);
        }
    }

