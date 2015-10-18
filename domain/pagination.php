<?php

class pagination {

	var $current_page;
	var $total_items = 0;
	var $total_pages = 0;
	var $items_per_page;

	function __construct($current_page = 1, $items_per_page = 1) {

		if ($current_page < 1) $current_page = 1;
		if ($items_per_page < 1) $items_per_page = 1;
		$this->current_page = $current_page;
		$this->items_per_page = $items_per_page;
	}

	function get_current_page() { return $this->current_page; }

	function get_items_per_page() { return $this->items_per_page; }

	function get_total_pages() { return $this->total_pages; }

	function get_total_items() { return $this->total_items; }

	function get_current_offset() { return (($this->current_page - 1) * $this->items_per_page); }

	function set_total_items($value) {

		$this->total_items = $value;
		if ($this->items_per_page > 0) {
			$this->total_pages = ceil($this->total_items / $this->items_per_page);
		}
	}
}
