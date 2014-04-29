<?php

    namespace Maestro\Utils;

    /**
     * Class CookieSignature
     * @package Maestro\Utils
     */
    class CookieSignature
    {
        private static $_cookieSecret;

        /**
         * GetSetter for Cookie Secret
         * @param null $val
         * @return null
         */
        public static function CookieSecret($val = null)
        {
            if ($val !== null)
                self::$_cookieSecret = $val;

            return self::$_cookieSecret;
        }

        /**
         * @param $val
         * @return string
         */
        public function sign($val)
        {
            $hmac = str_replace(array('=', '$', '+'), '', base64_encode(hash('sha256', self::$_cookieSecret.$val)));
            return $val.'.'.$hmac;
        }

        /**
         * @param $val
         * @return bool
         */
        public function unsign($val)
        {
            list($str,) = explode('.', $val);
            $mac = $this->sign($str, self::$_cookieSecret);
            return $this->sign($mac, self::$_cookieSecret) === $this->sign($val, self::$_cookieSecret) ? $str : false;
        }

        /**
         * @param array $cookies
         * @return array
         */
        public function parse(array $cookies)
        {
            $res = array();

            foreach ($cookies as $k => $c)
            {
                $unsigned = $this->unsign($c, self::$_cookieSecret);
                if ($unsigned !== false)
                    $res[$k] = $unsigned;
            }

            return $res;
        }
    }