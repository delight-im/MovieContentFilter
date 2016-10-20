<?php

/*
 * MovieContentFilter (https://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App\Lib;

/** Indication of a single point in playback time */
class Timestamp {

	const MILLISECONDS_PER_SECOND = 1000;
	const NANOSECONDS_PER_SECOND = 1000000000;
	const SECONDS_PER_HOUR = 3600;
	const SECONDS_PER_MINUTE = 60;

	/** @var float the time in seconds */
	protected $seconds;

	/**
	 * Constructor
	 *
	 * @param float $seconds the time in seconds
	 */
	protected function __construct($seconds) {
		$this->seconds = (float) $seconds;
	}

	/**
	 * Adds the specified number of seconds to this instance
	 *
	 * @param float $seconds the seconds to add
	 */
	public function addSeconds($seconds) {
		$this->seconds += (float) $seconds;
	}

	/**
	 * Subtracts the specified number of seconds from this instance
	 *
	 * @param float $seconds the seconds to subtract
	 */
	public function subtractSeconds($seconds) {
		$this->seconds -= (float) $seconds;
	}

	/**
	 * Multiplies this instance with the specified factor
	 *
	 * @param float $factor the factor to apply
	 */
	public function multiply($factor) {
		$this->seconds *= (float) $factor;
	}

	/**
	 * Divides this instance by the specified divisor
	 *
	 * @param float $divisor the divisor to apply
	 */
	public function divide($divisor) {
		$this->seconds /= (float) $divisor;
	}

	/**
	 * Converts the time to seconds
	 *
	 * @return float the time in seconds
	 */
	public function toSeconds() {
		return $this->seconds;
	}

	/**
	 * Compares this instance against the other specified instance
	 *
	 * @param Timestamp $other another instance of this class to compare with
	 * @return int whether this instance is less than (result < 0), equal to (result = 0) or greater than (result > 0)
	 */
	public function compareTo(Timestamp $other) {
		$delta = $this->seconds - $other->seconds;

		if (abs($delta) <= 1) {
			$delta *= self::NANOSECONDS_PER_SECOND;
		}
		return (int) $delta;
	}

	/**
	 * Creates a new instance from the specified time in seconds
	 *
	 * @param float $secondsFloat the time in seconds
	 * @return static a new instance of this class
	 */
	public static function fromSeconds($secondsFloat) {
		return new static($secondsFloat);
	}

	/**
	 * Creates a new instance from the specified individual components
	 *
	 * @param int $hour the hour (0-99)
	 * @param int $minute the minute (0-59)
	 * @param int $second the second (0-59)
	 * @param int $millisecond the millisecond (0-999)
	 * @return static a new instance of this class
	 */
	public static function fromComponents($hour, $minute, $second, $millisecond) {
		$secondsFloat = 0;

		$secondsFloat += $hour * self::SECONDS_PER_HOUR;
		$secondsFloat += $minute * self::SECONDS_PER_MINUTE;
		$secondsFloat += $second;
		$secondsFloat += $millisecond / self::MILLISECONDS_PER_SECOND;

		return static::fromSeconds($secondsFloat);
	}

}
