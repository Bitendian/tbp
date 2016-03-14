<?php

interface i_cache_connection {

	// abre la conexion
	function open();

	// cierra la conexion
	function close();

	// comprueba que existen una serie de claves (clave unica o array)
	function exists($keys);

	// elimina una serie de claves (clave unica o array)
	public function remove($keys);

	// almacena un valor simple en una clave
	function store($key, $value);

	// recupera el valor simple de una clave
	function get($key);

	// almacena una serie de valores (valor unico o array) al principio de una lista
	function list_prepend($key, $values);

	// almacena una serie de valores (valor unico o array) al final de una lista
	function list_append($key, $values);

	// recupera todos los valores de una lista
	function list_get_all($key);

	// almacena una serie de valores (valor unico o array) en un conjunto
	function set_add($key, $values);

	// retorna la cardinalidad de un conjunto
	function set_cardinality($key);

	// elimina una serie de valores (valor unico o array) en un conjunto
	function set_remove($key, $values);

	// recupera todos los valores de un conjunto
	function set_get_all($key);

	// comprueba si un conjunto contiene un valor
	function set_contains($key, $value);

	// recupera la diferencia entre conjuntos
	function set_get_diff($keys);

	// almacena la diferencia entre conjuntos
	function set_store_diff($key, $keys);
}
