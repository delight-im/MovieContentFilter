<?php

/*
 * MovieContentFilter (https://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App\Lib\Playlist;

/** Indication of a single point in playback time for a playlist */
class Timestamp extends \App\Lib\Timestamp {

	/**
	 * Converts this instance to a string representation
	 *
	 * @return string the string representation
	 */
	public function __toString() {
		return number_format($this->seconds, 3, '.', '');
	}

}
