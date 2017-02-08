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

/*
 * Class to manage and centralize html headers to be sended to user.
 *
 * Class methods must be static and HtmlHeaders follows Singleton pattern.
 */

class HtmlHeaders
{
    protected static $title = null;
    protected static $scripts = array();
    protected static $styleSheets = array();

    public static function hasTitle()
    {
        return self::$title != null;
    }

    public static function hasScripts()
    {
        return count(self::$scripts);
    }

    public static function hasStyleSheets()
    {
        return count(self::$styleSheets);
    }

    public static function setTitle($title)
    {
        self::$title = $title;
    }

    public static function addScript($script)
    {
        self::$scripts[count(self::$scripts) + count(self::$styleSheets)] = $script;
    }

    public static function addStyleSheet($styleSheet)
    {
        self::$styleSheets[count(self::$scripts) + count(self::$styleSheets)] = $styleSheet;
    }

    public static function getTitle()
    {
        return self::$title;
    }

    public static function getScripts()
    {
        return self::$scripts;
    }

    public static function getStyleSheets()
    {
        return self::$styleSheets;
    }
}
