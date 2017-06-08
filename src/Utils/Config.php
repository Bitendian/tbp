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

    // static cache for already loaded folders
    private static $folders = array();

    // current instance folder
    private $folder;

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
            self::$folders[$this->folder] = self::loadFolder($this->folder);
        }
    }

    /**
     * @param string $folder
     * @return array
     */
    private static function loadFolder($folder)
    {
        $configs = array();
        $link = opendir($folder);
        while (($file = readdir($link))) {
            $name = preg_split('/[.]/', $file);
            $dot_counter = count($name);
            if ($dot_counter > 1) {
                //  extension ".config"
                if (isset($name[($dot_counter - 1)]) && $name[($dot_counter - 1)] == 'config') {
                    $config_array_name = array();
                    for ($i = 0; $i < ($dot_counter - 1); $i++) {
                        $config_array_name[] = $name[$i];
                    }
                    $config_name = implode('.', $config_array_name);

                    $configs[$config_name] = self::createConfigObject($folder . DIRECTORY_SEPARATOR . $file);
                }
            }
        }

        return $configs;
    }

    /**
     * @param string $filename
     * @return \stdClass
     */
    private static function createConfigObject($filename)
    {
        $tmp = new \stdClass();
        $link = fopen($filename, 'r');
        while ($data = fgetcsv($link, 0, self::SEPARATOR)) {
            if (count($data) > 1) {
                $data[0] = trim($data[0]);
                if (strlen($data[0]) > 0 && $data[0][0] != self::COMMENT) {
                    $value = trim($data[0]);
                    $tmp->$value = trim(implode(self::SEPARATOR, array_slice($data, 1)));
                    if (strtolower($tmp->$value) == "true") {
                        $tmp->$value = true;
                    } elseif (strtolower($tmp->$value) == "false") {
                        $tmp->$value = false;
                    }
                }
            }
        }
        fclose($link);
        return $tmp;
    }

    /**
     * Get configuration object or null if config file not found.
     * @param string $file
     * @return null|\stdClass
     */
    public function getConfig($file)
    {
        if (isset(self::$folders[$this->folder][$file])) {
            return self::$folders[$this->folder][$file];
        }

        return null;
    }
}
