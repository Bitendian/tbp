<?php

/*
 * This file is part of the TBP package.
 *
 * (c) Bitendian <info@bitendian.com>
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source
 * code.
 */

namespace Bitendian\TBP;

/**
 * Implements a lightweight PSR-4 compliant autoloader for TBP.
 */
class Autoloader
{
    private $directory;
    private $prefix;
    private $length;

    public function __construct($base = __DIR__)
    {
        $this->directory = $base;
        $this->prefix = __NAMESPACE__ . '\\';
        $this->length = strlen($this->prefix);
    }

    public static function register($prepend = false)
    {
        spl_autoload_register(array(new self, 'autoload'), true, $prepend);
    }

    public function autoload($className)
    {
        if (strpos($className, $this->prefix) === 0) {
            $parts = explode('\\', substr($className, $this->length));
            $filepath = $this->directory . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts).'.php';

            if (is_file($filepath)) {
                require($filepath);
            }
        }
    }
}
