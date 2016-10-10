<?php

/*
 * MovieContentFilter (http://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App\Lib\Mcf;

use App\Lib\Timing;
use App\Lib\Mcf\Throwables\InvalidWebvttTimingException;

/** Indication of a range in playback time according to the WebVTT (Web Video Text Tracks) standard */
final class WebvttTiming extends Timing {

	/** The regular expression that is used to parse instances from strings */
	const REGEX = '/^((?:[0-9]{2,}?):(?:[0-9]{2}?):(?:[0-9]{2}?).(?:[0-9]{3}?)) --> ((?:[0-9]{2,}?):(?:[0-9]{2}?):(?:[0-9]{2}?).(?:[0-9]{3}?))/';
	/** The string to use as a delimiter in string representations */
	const DELIMITER_STRING = ' --> ';

	/**
	 * Converts this instance to a string representation
	 *
	 * @return string the string representation
	 */
	public function __toString() {
		return ((string) $this->getStart()) . self::DELIMITER_STRING . ((string) $this->getEnd());
	}

	/**
	 * Parses an instance from the specified string
	 *
	 * @param string $str the string to parse
	 * @return static a new instance of this class
	 * @throws InvalidWebvttTimingException
	 */
	public static function parse($str) {
		if (preg_match(self::REGEX, $str, $parts)) {
			$start = WebvttTimestamp::parse($parts[1]);
			$end = WebvttTimestamp::parse($parts[2]);

			return new static($start, $end);
		}
		else {
			throw new InvalidWebvttTimingException();
		}
	}

}
