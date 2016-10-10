<?php

/*
 * MovieContentFilter (http://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App\Lib;

use App\Lib\Throwables\EmptyTimingException;

/** Indication of a range in playback time */
class Timing {

	/** @var Timestamp the start of this time range */
	private $start;
	/** @var Timestamp the end of this time range */
	private $end;

	/**
	 * Constructor
	 *
	 * @param Timestamp $start the start of this time range
	 * @param Timestamp $end the end of this time range
	 * @throws EmptyTimingException
	 */
	public function __construct(Timestamp $start, Timestamp $end) {
		if ($end->compareTo($start) <= 0) {
			throw new EmptyTimingException();
		}

		$this->start = $start;
		$this->end = $end;
	}

	/**
	 * Returns the start of this time range
	 *
	 * @return Timestamp the start
	 */
	public function getStart() {
		return $this->start;
	}

	/**
	 * Returns the end of this time range
	 *
	 * @return Timestamp the end
	 */
	public function getEnd() {
		return $this->end;
	}

	/**
	 * Returns the duration of this time range
	 *
	 * @return float the duration in seconds
	 */
	public function getDuration() {
		return $this->end->toSeconds() - $this->start->toSeconds();
	}

	/**
	 * Adds the specified number of seconds to this instance
	 *
	 * @param float $seconds the seconds to add
	 */
	public function addSeconds($seconds) {
		$this->start->addSeconds($seconds);
		$this->end->addSeconds($seconds);
	}

	/**
	 * Subtracts the specified number of seconds from this instance
	 *
	 * @param float $seconds the seconds to subtract
	 */
	public function subtractSeconds($seconds) {
		$this->start->subtractSeconds($seconds);
		$this->end->subtractSeconds($seconds);
	}

	/**
	 * Multiplies this instance with the specified factor
	 *
	 * @param float $factor the factor to apply
	 */
	public function multiply($factor) {
		$this->start->multiply($factor);
		$this->end->multiply($factor);
	}

	/**
	 * Divides this instance by the specified divisor
	 *
	 * @param float $divisor the divisor to apply
	 */
	public function divide($divisor) {
		$this->start->divide($divisor);
		$this->end->divide($divisor);
	}

}
