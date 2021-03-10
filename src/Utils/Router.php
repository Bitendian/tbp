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

use Bitendian\TBP\TBPException;

class Router
{
    /**
     * @var array
     */
    protected static $routes;

    /**
     * @param array $routes
     */
    public static function load($routes)
    {
        self::$routes = $routes;
    }

    /**
     * @param string $uri
     * @param mixed $role
     * @return bool|object
     */
    public static function getRouteData($uri, $role)
    {
        foreach (self::$routes as $route => $routeObject) {
            foreach ($routeObject->urls as $lang => $url) {
                $pregUrl = $url;
                $vars = [];
                preg_match_all('*\{([^\}]+)\}*', $url, $vars);
                foreach ($vars[0] as $var) {
                    $pregUrl = str_replace($var, '([^/]+)', $pregUrl);
                }

                $matches = [];
                $urlVars = [];

                if (preg_match('*^' . $pregUrl . '$*i', $uri, $matches) == 1) {
                    for ($i = 0; $i < count($vars[1]); $i++) {
                        $urlVars[$vars[1][$i]] = $matches[$i + 1];
                    }

                    if (!isset($routeObject->roles) || in_array($role, $routeObject->roles)) {
                        return (object)[
                            "lang" => $lang,
                            "layout" => $routeObject->layout,
                            "module" => $routeObject->module,
                            "vars" => $urlVars,
                            "urlKey" => $route
                        ];
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param string $name
     * @param string $lang
     * @return string
     * @throws TBPException
     */
    public static function getRoute($name, $lang)
    {
        if (!isset(self::$routes[$name])) {
            throw new TBPException(_('route not found'));
        }

        $route = self::$routes[$name];
        if (!isset($route->urls[$lang])) {
            throw new TBPException(sprintf(_('no route for language %s found with name %s'), $lang, $name));
        }

        $url = $route->urls[$lang];
        $vars = [];
        preg_match_all('*\{([^\}]+)\}*', $url, $vars);
        if (count($vars[0]) != func_num_args() - 2) {
            throw new TBPException(sprintf(
                _('invalid URL params, expected %s but got %s'),
                count($vars[0]),
                (func_num_args() - 2)
            ));
        }

        for ($i = 0; $i < count($vars[0]); $i++) {
            $url = str_replace($vars[0][$i], func_get_arg($i + 2), $url);
        }
        return $url;
    }

    /**
     * @param $name
     * @return array
     * @throws TBPException
     */
    public static function getRouteUris($name)
    {
        if (!isset(self::$routes[$name])) {
            throw new TBPException(_('route not found'));
        }

        $route = self::$routes[$name];
        return $route->urls;
    }

    /**
     * @param string $name
     * @param string $lang
     * @param array $params
     * @return string
     * @throws TBPException
     */
    public static function getRouteWithParams($name, $lang, &$params)
    {
        if (!isset(self::$routes[$name])) {
            throw new TBPException('route not found');
        }

        $route = self::$routes[$name];
        if (!isset($route->urls[$lang])) {
            throw new TBPException(sprintf(_('no route for language %s found with name %s'), $lang, $name));
        }

        $url = $route->urls[$lang];
        $vars = [];
        preg_match_all('*\{([^\}]+)\}*', $url, $vars);

        for ($i = 0; $i < count($vars[0]); $i++) {
            if (!isset($params[$vars[1][$i]])) {
                throw new TBPException('param ' . $vars[1][$i] . ' not found');
            }
            $url = str_replace($vars[0][$i], $params[$vars[1][$i]], $url);
        }

        return $url;
    }
}
