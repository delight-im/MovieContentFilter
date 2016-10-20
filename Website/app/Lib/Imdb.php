<?php

/*
 * MovieContentFilter (https://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App\Lib;

/** Utilities for handling links to IMDb.com */
final class Imdb {

	/** The regular expression that is used to parse links to pieces of work */
	const WORK_URL_REGEX = '/(?:http(?:s)?:\\/\\/)?(?:www\\.)?imdb\\.com\\/(title\\/[^\\/]+)\\//i';

	// do not allow instantiation
	private function __construct() { }

}
