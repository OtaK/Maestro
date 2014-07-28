<?php

    namespace Maestro\Utils;

    /**
     * Class MimeType
     * @package Maestro\Utils
     */
    class MimeType
    {
        private static $_mapExtensions = array(
            'css'  => 'text/css',
            'js'   => 'application/javascript',
            'html' => 'text/html',
            'csv'  => 'text/csv'
        );

        public static function FileType($path)
        {
            $info = pathinfo($path);
            if (isset(self::$_mapExtensions[$info['extension']]))
                return self::$_mapExtensions[$info['extension']];

            if (!function_exists('finfo_open'))
                return 'text/plain';

            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            return $finfo->file($path);
        }
    }