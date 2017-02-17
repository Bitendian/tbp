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

    private static function containsScript($script)
    {
        foreach (self::$scripts as $k => $v) {
            if ($v == $script) {
                return true;
            }
        }
        return false;
    }

    private static function containsStyleSheet($styleSheet)
    {
        foreach (self::$styleSheets as $k => $v) {
            if ($v == $styleSheet) {
                return true;
            }
        }
        return false;
    }

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
        if (!self::containsScript($script)) {
            self::$scripts[count(self::$scripts) + count(self::$styleSheets)] = $script;
        }
    }

    public static function addStyleSheet($styleSheet)
    {
        if (!self::containsStyleSheet($styleSheet)) {
            self::$styleSheets[count(self::$scripts) + count(self::$styleSheets)] = $styleSheet;
        }
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
