<?php

    namespace Maestro\Utils;

    /**
     * Class Settings
     * @package Maestro\Utils
     */
    class Settings implements \ArrayAccess
    {
        /** @var array app configuration */
        protected static $_settings;

        /**
         * Whether a offset exists
         * @link http://php.net/manual/en/arrayaccess.offsetexists.php
         * @param mixed $offset An offset to check for.
         * @return boolean true on success or false on failure.
         *
         * The return value will be casted to boolean if non-boolean was returned.
         */
        public function offsetExists($offset)
        {
            return isset(self::$_settings, self::$_settings[$offset]);
        }

        /**
         * Offset to retrieve
         * @link http://php.net/manual/en/arrayaccess.offsetget.php
         * @param mixed $offset The offset to retrieve.
         * @return mixed Can return all value types.
         */
        public function offsetGet($offset)
        {
            return isset(self::$_settings[$offset]) ? self::$_settings[$offset] : null;
        }

        /**
         * Offset to set
         * @link http://php.net/manual/en/arrayaccess.offsetset.php
         * @param mixed $offset The offset to assign the value to.
         * @param mixed $value  The value to set.
         * @return void
         */
        public function offsetSet($offset, $value)
        {
            self::$_settings[$offset] = $value;
        }

        /**
         * Offset to unset
         * @link http://php.net/manual/en/arrayaccess.offsetunset.php
         * @param mixed $offset The offset to unset.
         * @return void
         */
        public function offsetUnset($offset)
        {
            unset(self::$_settings[$offset]);
        }
    }