<?php

/**
 * Clase singleton que lee la configuracion de la aplicacion.
 */
class config {

	const SEPARATOR = '=';
	const COMMENT = '#';

	// variable estatica usada para cachear el contenido del fichero de configuracion durante un unica ejecucion
	static $instance = null;

	var $config;

	function __construct($dir) {
		$this->config = new stdClass();
		$this->load_dir($dir);
	}

	public function load_dir($dir) {
		if (is_dir($dir)) {
			$link = opendir($dir);
			while (($file = readdir($link))) {
				$name = preg_split('/[.]/', $file);
				$dot_counter = count($name);
				if ($dot_counter > 1) {
					//  extension ".config"
					if (isset($name[($dot_counter - 1)]) && $name[($dot_counter - 1)] == 'config') {
						$config_array_name = Array();
						for ($i = 0; $i < ($dot_counter - 1); $i++) {
							$config_array_name[] = $name[$i];
						}
						$config_name = implode('.', $config_array_name);

						$this->config->$config_name = $this->create_config_object($dir . DIRECTORY_SEPARATOR . $file);
					}
				}
//				if (is_dir($dir . $file) && substr($file, 0, 1) != '.') {
//					$this->load_dir($dir . $file);
//				}
			}
			closedir($link);
		}
	}

	public static function create_config_object($filename) {
		$tmp = new stdClass();
		$link = fopen($filename, 'r');
		while ($data = fgetcsv($link, 0, self::SEPARATOR)) {
			if (count($data) > 1) {
				$data[0] = trim($data[0]);
				if (strlen($data[0]) > 0 && $data[0][0] != self::COMMENT) {
					$value = trim($data[0]);
					$tmp->$value = trim(implode(self::SEPARATOR, array_slice($data, 1)));
					if (strtolower($tmp->$value) == "true") $tmp->$value = true;
					elseif (strtolower($tmp->$value) == "false") $tmp->$value = false;
					else $tmp->$value = str_replace('BASEPATH', BASEPATH, $tmp->$value);
				}
			}
		}
		fclose($link);
		return $tmp;
	}

	public static function get_config($key = null) {
		if ($key == null) return Config::$instance->config;
		else if (isset(Config::$instance->config->$key)) return Config::$instance->config->$key;
		return null;
	}
}

Config::$instance = new Config(BASEPATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR);

