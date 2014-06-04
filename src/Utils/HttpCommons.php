<?php

    namespace Maestro\Utils;

    /**
     * Class HttpCommons
     * @package Maestro\Utils
     */
    class HttpCommons
    {
        const
            HTTP_VERB_GET    = 'GET',
            HTTP_VERB_POST   = 'POST',
            HTTP_VERB_PUT    = 'PUT',
            HTTP_VERB_DELETE = 'DELETE',
            HTTP_VERB_HEAD   = 'HEAD';

        const
            PROTOCOL_HTTP  = 'http',
            PROTOCOL_HTTPS = 'https',
            PROTOCOL_FTP   = 'ftp';

        const
            MIME_JSON = 'application/json',
            MIME_HTML = 'text/html',
            MIME_FORM = 'application/x-www-form-urlencoded';


        /**
         * @param $string
         * @param $contentType
         * @return mixed|null
         */
        static protected function _parseString($string, $contentType)
        {
            $tmp = explode('; ', $contentType);
            $type = $tmp[0];
            if (count($tmp) > 1)
                list(, $charset) = explode('=', $tmp[1]);
            else
                $charset = 'UTF-8';

            $inferred = mb_detect_encoding($string, null, true);
            $string = mb_convert_encoding($string, 'UTF-8', $inferred !== false ? $inferred : $charset);

            switch ($type)
            {
                case self::MIME_JSON:
                    return json_decode($string, true);
                case self::MIME_HTML:
                    return htmlspecialchars_decode($string);
                case self::MIME_FORM:
                    $ret = null;
                    parse_str($string, $ret);
                    return $ret;
                default:
                    return $string;
            }
        }
    }