<?php

class util {

	static function get_ssl_page($url) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}

	static function post_ssl_page($url, $post = array(), $headers = array()) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}

	static function action_encode($action) {
		return urlencode(base64_encode($action));
	}

	static function action_decode($action) {
		return base64_decode(urldecode($action));
	}

	static function generate_token($prefix = "") {
		return uniqid($prefix);
	}

	static function set_user_autologin(&$user) {
		$user->autologin = uniqid();
		$user = user_service::update($user);
		setcookie("tauth", $user->autologin, time() + 15552000, "/", "", false, true);
	}

	static function unset_user_autologin(&$user = null) {
		if ($user != null) $user->autologin = '*' . uniqid();
		setcookie("tauth", "", time() - 3600, "/", "", false, true);
	}

	static function check_flood_protection() {

		$config = config::get_config('flood_protection');

		$flood_protection_enabled = ($config != null && isset($config->flood_protection_enabled) && $config->flood_protection_enabled == 'true');
		if ($flood_protection_enabled != true) {
			return false;
		}

		$flood_protection_key = 'FLOOD_PROTECTION_DATA';
		$flood_protection_window_max_size = ($config != null && isset($config->flood_protection_window_max_size) ? $config->flood_protection_window_max_size : 5);
		$flood_protection_time_limit = ($config != null && isset($config->flood_protection_time_limit) ? $config->flood_protection_time_limit : 20);

		if (!isset($_SESSION[$flood_protection_key])) {
			$_SESSION[$flood_protection_key] = array();
		}

		$_SESSION[$flood_protection_key][] = time();

		$flood_window_current_size = count($_SESSION[$flood_protection_key]);
		if ($flood_window_current_size > $flood_protection_window_max_size) {
			$_SESSION[$flood_protection_key] = array_slice($_SESSION[$flood_protection_key], $flood_window_current_size - $flood_protection_window_max_size, $flood_protection_window_max_size);
		} else {
			return false;
		}

		$flood_window_current_size = count($_SESSION[$flood_protection_key]);

		if ($flood_window_current_size == $flood_window_current_size) {
			$difference = $_SESSION[$flood_protection_key][$flood_window_current_size - 1] - $_SESSION[$flood_protection_key][0];
			if ($difference < $flood_protection_time_limit) {
				return true;
			}
		}
		return false;
	}

	static function translate($string) {
		return gettext($string);
	}

	static function get_locale($lang) {
		switch ($lang) {
			case "es": return locale_constants::ES_ES;
			case "ca": return locale_constants::CA_ES;
			default: return locale_constants::EN_US;
		}
	}

}
