<?php

/*
 * MovieContentFilter (https://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App\Lib\Mcf;

use App\Lib\Timestamp;
use App\Lib\Mcf\Throwables\InvalidWebvttTimestampException;

/** Indication of a single point in playback time according to the WebVTT (Web Video Text Tracks) standard */
final class WebvttTimestamp extends Timestamp {

	/** The regular expression that is used to parse instances from strings */
	const REGEX = '/^([0-9]{2,}?):([0-9]{2}?):([0-9]{2}?).([0-9]{3}?)/';
	/** The character that is used for padding of numbers */
	const PAD_CHAR = '0';

	/**
	 * Converts this instance to a string representation
	 *
	 * @return string the string representation
	 */
	public function __toString() {
		$secondsFloat = $this->seconds;

		// calculate the hours portion
		$hours = \floor($secondsFloat / 3600);
		$hours = \min($hours, 99);

		// consume the hours
		$secondsFloat = \fmod($secondsFloat, 3600);

		// calculate the minutes portion
		$minutes = \floor($secondsFloat / 60);

		// consume the minutes
		$secondsFloat = \fmod($secondsFloat, 60);

		// calculate the seconds portion
		$seconds = \floor($secondsFloat);

		// consume the seconds
		$secondsFloat = \fmod($secondsFloat, 1);

		// calculate the milliseconds portion
		$milliseconds = (int) \round($secondsFloat * 1000);

		// return the formatted composite string
		return self::pad($hours, 2) . ':' . self::pad($minutes, 2) . ':' . self::pad($seconds, 2) . '.' . self::pad($milliseconds, 3);
	}

	/**
	 * Parses an instance from the specified string
	 *
	 * @param string $str the string to parse
	 * @return static a new instance of this class
	 * @throws InvalidWebvttTimestampException
	 */
	public static function parse($str) {
		if (\preg_match(self::REGEX, $str, $parts)) {
			$hour = (int) $parts[1];
			$minute = (int) $parts[2];
			$second = (int) $parts[3];
			$millisecond = (int) $parts[4];

			return self::fromComponents($hour, $minute, $second, $millisecond);
		}
		else {
			throw new InvalidWebvttTimestampException();
		}
	}

	/**
	 * Pads the specified number with zeros until it reaches the desired length
	 *
	 * @param int $number the number to pad
	 * @param int $length the number of digits to pad to
	 * @return string the padded number as a string
	 */
	private static function pad($number, $length) {
		return \str_pad($number, $length, self::PAD_CHAR, \STR_PAD_LEFT);
	}

}
