<?php

/*
 * MovieContentFilter (http://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App\Lib;

/** Utilities for reading and writing XML */
final class Xml {

	// do not allow instantiation
	private function __construct() { }

	public static function escape($text) {
		// ampersand
		$text = str_replace('&', '&amp;', $text);
		// less-than
		$text = str_replace('<', '&lt;', $text);
		// greater-than
		$text = str_replace('>', '&gt;', $text);
		// quotation mark
		$text = str_replace('"', '&quot;', $text);
		// apostrophe
		$text = str_replace('\'', '&apos;', $text);

		return $text;
	}

}
