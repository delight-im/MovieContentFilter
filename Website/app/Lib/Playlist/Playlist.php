<?php

/*
 * MovieContentFilter (http://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App\Lib\Playlist;

use App\Lib\Filter;
use App\Lib\Timing;
use App\Lib\UnfilterableAnnotation;

/** Description of content that may be filtered in a video or audio file using a playlist */
class Playlist extends Filter {

	/** @var string the public URL where more information about this instance is available */
	protected $publicInfoUrl;
	/** @var bool whether this instance shall be inverted to keep only what would otherwise be filtered */
	protected $inverted;

	/**
	 * Constructor
	 *
	 * @param string $publicInfoUrl the public URL where more information about this instance is available
	 * @param Timestamp|null $fileStartTime the start time of the annotated source as a reference point
	 * @param Timestamp|null $fileEndTime the end time of the annotated source as a reference point
	 */
	public function __construct($publicInfoUrl, Timestamp $fileStartTime = null, Timestamp $fileEndTime = null) {
		parent::__construct($fileStartTime, $fileEndTime);

		$this->publicInfoUrl = $publicInfoUrl;
		$this->inverted = false;
	}

	/**
	 * Sets whether this instance shall be inverted to keep only what would otherwise be filtered
	 *
	 * @param bool $inverted
	 */
	public function setInverted($inverted) {
		$this->inverted = (bool) $inverted;
	}

	/**
	 * Returns whether this instance is inverted to keep only what would otherwise be filtered
	 *
	 * @return bool
	 */
	public function isInverted() {
		return $this->inverted;
	}

	/** Fills all gaps between individual annotations so that a continuous description is available */
	public function fillUp() {
		$count = count($this->annotations);

		$newAnnotations = [];

		$previousEndTime = static::createTimestampFromSeconds(0);

		for ($i = 0; $i < $count; $i++) {
			// get the start time of the current annotation
			$currentStart = $this->annotations[$i]->getTiming()->getStart();
			// if there is a gap between this and the previous annotation
			if ($currentStart->compareTo($previousEndTime) > 0) {
				// add an unfilterable annotation in between to fill the gap
				$newAnnotations[] = new UnfilterableAnnotation(
					new Timing(
						$previousEndTime,
						$currentStart
					)
				);
			}

			// add the current annotation
			$newAnnotations[] = $this->annotations[$i];

			// update the previous end time
			$previousEndTime = $this->annotations[$i]->getTiming()->getEnd();
		}

		$fileEndTime = static::createTimestampFromSeconds($this->endTime->toSeconds());

		// if there is a gap at the end of the file
		if ($previousEndTime->compareTo($fileEndTime) < 0) {
			// add an unfilterable annotation spanning from the end of the last annotation to the end of the file
			$newAnnotations[] = new UnfilterableAnnotation(
				new Timing(
					$previousEndTime,
					$fileEndTime
				)
			);
		}

		// replace the old annotations with the new
		$this->annotations = $newAnnotations;
	}

	/**
	 * Creates a new timestamp from the specified time in seconds
	 *
	 * @param float $secondsFloat the time in seconds
	 * @return Timestamp the new instance
	 */
	protected static function createTimestampFromSeconds($secondsFloat) {
		return Timestamp::fromSeconds($secondsFloat);
	}

}
