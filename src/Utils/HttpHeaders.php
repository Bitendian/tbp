<?php

/*
 * This file is part of the TBP package.
 *
 * (c) Bitendian <info@bitendian.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source
 * code.
 */

namespace Bitendian\TBP\Utils;

/**
 * Class to manage and centralize http headers to be sent to user.
 *
 * Class methods must be static and HttpHeaders follows Singleton pattern.
 */
class HttpHeaders
{
    protected static $headers = array();

    private static function containsHeader($header)
    {
        foreach (self::$headers as $k => $v) {
            if ($v == $header) {
                return true;
            }
        }
        return false;
    }

    public static function hasHeaders()
    {
        return count(self::$headers);
    }

    public static function addHeader($header)
    {
        if (!self::containsHeader($header)) {
            self::$headers[count(self::$headers)] = $header;
        }
    }

    public static function getHeaders()
    {
        return self::$headers;
    }
}
