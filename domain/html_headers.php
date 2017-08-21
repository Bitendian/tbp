<?php

class html_headers {
	
	static $metas = array();
	static $links = array();
	static $scripts = array();
	static $title = '';

	public static function has_metatags() {

		return count(self::$metas);
	}

	public static function has_links() {

		return count(self::$links);
	}

	public static function has_scripts() {

		return count(self::$scripts);
	}

	public static function has_title() {

		return !empty($title);
	}

	public static function add_metatag($name, $value) {

		self::$metas[$name] = $value;
	}

	public static function add_link($href, $type = 'text/css', $rel = 'stylesheet') {

		if (!empty($href) && !empty($type) && !empty($rel)) {
			self::$links[] = array('href' => $href, 'type' => $type, 'rel' => $rel);
		}
	}

	public static function add_script($src, $type = 'text/javascript') {

		if (!empty($src) && !empty($type)) {
			self::$links[] = array('src' => $src, 'type' => $type);
		}
	}
}
