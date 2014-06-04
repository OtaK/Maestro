<?php

    namespace Maestro\Utils;

    /**
     * Class Enum
     * @package Maestro\Utils
     */
    class Enum
    {
        protected static $_consts = null;

        const __default = null;

        /**
         * Returns const name from value
         * @param $val
         * @return string
         */
        public static function Text($val)
        {
            return array_search($val, static::ConstList(), true);
        }

        /**
         * Returns assoc array of constants
         * @return array
         */
        public static function ConstList()
        {
            if (static::$_consts === null)
            {
                $reflection = new \ReflectionClass(get_called_class());
                static::$_consts = $reflection->getConstants();
                unset(static::$_consts['__default']);
            }

            return static::$_consts;
        }

        /**
         *
         * @param null $val
         * @return int
         */
        public function __invoke($val = null)
        {
            static::ConstList();
            if ($val !== null
            && isset(static::$_consts[$val]))
                return $val;

            return static::__default;
        }
    }