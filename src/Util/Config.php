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

use TBP\TBPException as TBPException;

/*
 * Class to manage app config property files.
 *
 * Once loaded a config dir, any file with 'config' extension can be access as object where any property of config file
 * can be accessed as object property.
*/

class Config
{
    private const SEPARATOR = '=';
    private const COMMENT = '#';

    // cache for already loaded files
    private static $instance = null;

    private $config;

    public function __construct($dir)
    {
        $this->config = new stdClass();
        $this->loadDir($dir);
    }

    private function loadDir($dir)
    {
        if (is_dir($dir)) {
            $link = opendir($dir);
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

                        $this->config->$config_name = $this->create_config_object($dir . DIRECTORY_SEPARATOR . $file);
                    }
                }
            }
            closedir($link);
        }
    }

    private function createConfigObject($filename)
    {
        $tmp = new stdClass();
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

    public function getConfig($key = null)
    {
        if ($key === null) {
            return config::$instance->config;
        } elseif (isset(config::$instance->config->$key)) {
            return config::$instance->config->$key;
        }

        return null;
    }
}
