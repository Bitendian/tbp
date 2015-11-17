<?php

require_once(TBP_BASE_PATH . "/abstract_renderizable.php");
require_once(TBP_BASE_PATH . "/config.php");
require_once(TBP_BASE_PATH . "/util.php");

class templater extends abstract_renderizable {

	var $source;
	var $context;
	var $result;
	var $replaced_array_tags = array();

	const SEPARATOR = '@@';
	const ARRAY_SEPARATOR = '@@@';
	const GETTEXT_SEPARATOR = '##';

	function __construct($source, $context = null) {
		$this->source = $source;
		if (is_object($context)) $this->context = clone($context);
		else $this->context = $context;
	}

	function render_js() {}

	function render() {
		$this->result = $this->load_content();
		$context = $this->context;
		if (!is_array($context))	{
			$this->context = array();
			$this->context[] = $context;
		}
		$this->replace();
		echo($this->result);
	}

	private function load_content() {
		if ($this->source === null) return '';
		return file_get_contents($this->source);
	}

	protected function replace() {
		if ($this->context != null) {
			$this->replace_array_tags();
			$this->replace_tags();
		}
		$this->replace_gettext();
	}

	protected function replace_gettext() {
		while (preg_match($this->get_gettext_regexp(), $this->result, $groups) > 0) {
			$key = $groups[1];
			$value = util::translate($key);
			$this->result = str_replace($groups[0], $value, $this->result);
		}
	}

	private function r_replace_property($context, $property, &$value) {
		// get context properties in lowercase
		if (isset($context) && is_object($context))
			$context_vars = array_change_key_case(get_object_vars($context));
		elseif (isset($context) && is_array($context))
			$context_vars = array_change_key_case($context);
		else
			return false;

		if ($p = strpos($property, '.')) {
			// must replace with subobject property or associative subarray value
			$property1 = substr($property, 0, $p);
			$property2 = substr($property, $p + 1);
			// if exists property1 as context var $property1 is context and property2 is property
			if (array_key_exists($property1, $context_vars))
				return $this->r_replace_property($context_vars[$property1], $property2, $value);
		} elseif (array_key_exists($property, $context_vars)) {
			// is context property
			if (!is_array($value) && is_object($context_vars[$property])) {
				$value = $context_vars[$property]->__toString();
				return true;
			} elseif (is_array($value) && is_object($context_vars[$property])) {
				return false;
			} elseif (!is_array($value) && is_array($context_vars[$property])) {
				return false;
			} elseif (is_array($value) && is_array($context_vars[$property])) {
				// if property is an array and we expect an array is matching
				foreach ($context_vars[$property] as $item) {
						if (is_object($item)) $value[] = $item->__toString();
						else $value[] = $item;
				}
				return true;
			}

			if (is_array($value))
				return false;

			$value = $context_vars[$property];
			return true;
		}

		return false;
	}

	protected function replace_tags() {
		while (preg_match($this->get_tags_regexp(), $this->result, $groups) > 0) {
			$property = strtolower($groups[1]);
			$value = null;
			foreach ($this->context as &$context)
				if ($this->r_replace_property($context, $property, $value))
					break;

			$this->result = str_replace($groups[0], $value, $this->result);
		}
	}

	protected function replace_array_tags() {
		while (preg_match($this->get_array_tags_regexp(), $this->result, $groups) > 0) {
			$property = strtolower($groups[1]);
			$value = array();
			foreach ($this->context as $context) {
				if ($this->r_replace_property($context, $property, $value, true))
					break;
			}

			$this->result = str_replace($groups[0], implode($value), $this->result);
		}
	}

	private function get_array_tags_regexp() {
		return '/' . self::ARRAY_SEPARATOR . '(.+?)' . self::ARRAY_SEPARATOR . '/';
	}

	private function get_tags_regexp() {
		return '/' . self::SEPARATOR . '(.+?)' . self::SEPARATOR . '/';
	}

	private function get_gettext_regexp() {
		return '/' . self::GETTEXT_SEPARATOR . '(.+?)' . self::GETTEXT_SEPARATOR . '/';
	}

	public function get_tags() {
		$content = $this->load_content();
		$tags = Array();
		// remove array tags (dirty style)
		while (preg_match($this->get_array_tags_regexp(), $content, $groups) > 0) {
			$tags []= strtolower($groups[1]);
			$content = str_replace($groups[0], '', $content);
		}
		// pull tags
		while (preg_match($this->get_tags_regexp(), $content, $groups) > 0) {
			$tags []= strtolower($groups[1]);
			$content = str_replace($groups[0], '', $content);
		}
		return $tags;
	}
}
