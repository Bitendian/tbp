<?php

interface i_cache_connection {

	// opens a connection
	function open();

	// closes a connection
	function close();

	// retrieve existing keys (works with wildcards)
	function keys($pattern);

	// checks if key(s) exists (works with arrays)
	function exists($keys);

	// removes some key(s) (works with arrays)
	public function remove($keys);

	// removes all keys
	public function clear();

	// stores single value into key
	function store($key, $value);

	// gets a key value
	function get($key);

	// gets list cardinality
	function list_cardinality($key);

	// stores some value(s) into a list beginning (works with arrays)
	function list_prepend($key, $values);

	// stores some value(s) into a list end (works with arrays)
	function list_append($key, $values);

	// stores some value into a list at index
	function list_store($key, $index, $value);

	// gets all values from a list
	function list_get_all($key);

	// stores some value(s) into a set (works with arrays)
	function set_add($key, $values);

	// gets set cardianlity
	function set_cardinality($key);

	// removes some value(s) from a set
	function set_remove($key, $values);

	// gets all values from a set
	function set_get_all($key);

	// checks if a set contains some value(s) (work with arrays)
	function set_contains($key, $values);

	// gets diffrecence between sets
	function set_get_diff($keys);

	// stores difference between sets into a key
	function set_store_diff($key, $keys);

	// stores some value(s) into a sorted set with their scores (works with arrays)
	function sorted_set_add($key, $scores, $values);

	// gets all values from a sorted set
	function sorted_set_get_all($key);

	// stores some pairs key => values into a hash
	function hash_add($key, $data);

	// get a value from a given key in a hash
	function hash_get($key, $field);

	// get all key value pairs from hash
	function hash_get_all($key);
}
