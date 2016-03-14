<?php

class utils {

	// yes, yes, everybody knows fgetcsv, but...
	// what happens with comment lines?
	// and how can you trim an end line character like semicolon?
	static function getcsv($handle, $separator = ',', $comment = '#', $eol = ';') {
		do {
			$line = fgets($handle);
		} while ($line !== false && substr($line, 0, 1) == $comment);

		if ($line === false)
			return false;
		else
			return explode($separator, trim($line, "\n\r" . $eol));
	}

}

