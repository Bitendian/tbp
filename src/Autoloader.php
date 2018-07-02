<?php

/**
 * This file is part of the TBP package.
 *
 * (c) Bitendian <info@bitendian.com>
 *
 * For the full copyright and license information, please view the LICENSE file that
 * was distributed with this source code.
 *
 * @category Framework
 * @package  TBP
 * @author   Bitendian S.L. <info@bitendian.com>
 * @license  Apache License
 * @link     https://github.com/Bitendian/tbp
 */

namespace Bitendian\TBP;

/**
 * Implements a lightweight PSR-4 compliant autoloader for TBP.
 *
 * @category Framework
 * @package  TBP
 * @author   Bitendian S.L. <info@bitendian.com>
 * @license  Apache License
 * @link     https://github.com/Bitendian/tbp
 */
class Autoloader
{
    /**
     * @var string
     */
    private $directory;
    /**
     * @var string
     */
    private $prefix;
    /**
     * @var int
     */
    private $length;

    /**
     * Autoloader constructor.
     * @param string $base
     */
    public function __construct($base = __DIR__)
    {
        $this->directory = $base;
        $this->prefix = __NAMESPACE__ . '\\';
        $this->length = strlen($this->prefix);
    }

    /**
     * @param bool $prepend
     */
    public static function register($prepend = false)
    {
        spl_autoload_register(array(new self, 'autoload'), true, $prepend);
    }

    /**
     * @param string $className
     */
    public function autoload($className)
    {
        if (strpos($className, $this->prefix) === 0) {
            $parts = explode('\\', substr($className, $this->length));
            $filePath = $this->directory . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts) . '.php';
            if (is_file($filePath)) {
                require $filePath;
            }
        }
    }
}
