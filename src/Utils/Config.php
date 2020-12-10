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

use Bitendian\TBP\TBPException as TBPException;
use stdClass;

/**
 * Class Config to manage app config property files.
 *
 * Properties are pairs separated by equal sign '='.
 *
 * property=value
 *
 * Comment lines and inline comments are allowed with hash character '#' syntax.
 *
 * Once loaded a config folder, any file with 'config' extension can be access as object where any property of config
 * file can be accessed as object property.
 *
 * @package Bitendian\TBP\Utils
 */
class Config
{
    const SEPARATOR = '=';
    const COMMENT = '#';
    const ENVIRONMENT_PREFIX = '$';
    const ENVIRONMENT_DEFAULT_VALUE_SEPARATOR = ':';

    // static cache for already loaded folders
    private static $folders = array();

    // current instance folder
    private $folder;

    // config file extension
    private $extension = '.config';

    /**
     * Config constructor.
     * @param string $folder
     * @throws TBPException
     */
    public function __construct($folder)
    {
        if (($this->folder = realpath($folder)) === false || !is_dir($this->folder)) {
            throw new TBPException("folder " . $folder . " not found", -1);
        } elseif (!isset(self::$folders[$this->folder])) {
            self::$folders[$this->folder] = self::loadFolder($this->folder, $this->extension);
        }
    }

    /**
     * @param string $folder
     * @param string $extension
     * @return array
     */
    private static function loadFolder($folder, $extension)
    {
        $configs = array();
        $link = opendir($folder);
        while (($fileName = readdir($link))) {
            if (self::endsWidth($fileName, $extension)) {
                $configName = substr($fileName, 0, strlen($fileName) - strlen($extension));
                $configs[$configName] = self::createConfigObject($folder . DIRECTORY_SEPARATOR . $fileName);
            }
        }
        return $configs;
    }

    /**
     * @param string $fileName
     * @param string $extension
     * @return bool
     */
    private static function endsWidth($fileName, $extension)
    {
        $fileNameLength = strlen($fileName);
        $extensionLength = strlen($extension);
        if ($extensionLength >= $fileNameLength) {
            return false;
        }
        return \substr_compare($fileName, $extension, $fileNameLength - $extensionLength, $extensionLength) === 0;
    }

    /**
     * @param string $filename
     * @return stdClass
     */
    private static function createConfigObject($filename)
    {
        $tmp = new stdClass();
        $link = fopen($filename, 'r');
        while ($data = fgetcsv($link, 0, self::SEPARATOR)) {
            if (count($data) > 1) {
                $data[0] = trim($data[0]);
                if (strlen($data[0]) > 0 && $data[0][0] != self::COMMENT) {
                    $attributeName = trim($data[0]);
                    $attributeValue = trim(implode(self::SEPARATOR, array_slice($data, 1)));
                    if (self::isEnvironmentVariable($attributeValue)) {
                        $attributeValue = self::getEnvironmentVariableValue($attributeValue);
                    }
                    $tmp->$attributeName = $attributeValue;
                    if (strtolower($tmp->$attributeName) == "true") {
                        $tmp->$attributeName = true;
                    } elseif (strtolower($tmp->$attributeName) == "false") {
                        $tmp->$attributeName = false;
                    }
                }
            }
        }
        fclose($link);
        return $tmp;
    }

    private static function isEnvironmentVariable($value)
    {
        if (strlen($value) < 2) {
            return false;
        }

        return $value[0] === self::ENVIRONMENT_PREFIX;
    }

    private static function getEnvironmentVariableName($rawName)
    {
        $data = explode(self::ENVIRONMENT_DEFAULT_VALUE_SEPARATOR, $rawName);
        return substr(trim($data[0]), 1);
    }

    private static function hasEnvironmentVariableDefaultValue($rawName)
    {
        $name = trim($rawName);
        $position = stripos($name, self::ENVIRONMENT_DEFAULT_VALUE_SEPARATOR, 0);
        return ($position > 0 && $position < strlen($name));
    }

    private static function getEnvironmentVariableDefaultValue($rawName)
    {
        if (!self::hasEnvironmentVariableDefaultValue($rawName)) {
            return '';
        }

        $data = explode(self::ENVIRONMENT_DEFAULT_VALUE_SEPARATOR, $rawName);
        if (count($data) < 2) {
            return '';
        }

        return trim(implode(self::ENVIRONMENT_DEFAULT_VALUE_SEPARATOR, array_slice($data, 1)));;
    }

    private static function getEnvironmentVariableValue($attributeValue)
    {
        $envName = self::getEnvironmentVariableName($attributeValue);
        $value = getenv($envName, false);
        if ($value === false) {
            return self::getEnvironmentVariableDefaultValue($attributeValue);
        }
        return $value;
    }

    /**
     * Get configuration object or null if config file not found.
     * @param string $file
     * @return null|stdClass
     */
    public function getConfig($file)
    {
        if (isset(self::$folders[$this->folder][$file])) {
            return self::$folders[$this->folder][$file];
        }

        return null;
    }

    public function setConfigFileExtension($extension)
    {
        $this->extension = $extension;
        self::$folders[$this->folder] = self::loadFolder($this->folder, $this->extension);
    }
}
