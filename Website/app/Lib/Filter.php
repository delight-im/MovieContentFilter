<?php

/*
 * MovieContentFilter (https://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App\Lib;

/** Description of content that may be filtered in a video or audio file */
class Filter {

	/** The default start time to use for this instance when no time has been provided explicitly */
	const START_TIME_DEFAULT = 0;
	/** The default end time to use for this instance when no time has been provided explicitly */
	const END_TIME_DEFAULT = 1;

	/** @var Timestamp the start time of the annotated source as a reference point */
	protected $startTime;
	/** @var Timestamp the end time of the annotated source as a reference point */
	protected $endTime;
	/** @var Annotation[] the list of annotations that describe the content that may be filtered */
	protected $annotations;

	/**
	 * Constructor
	 *
	 * @param Timestamp|null $startTime the start time of the annotated source as a reference point
	 * @param Timestamp|null $endTime the end time of the annotated source as a reference point
	 */
	public function __construct(Timestamp $startTime = null, Timestamp $endTime = null) {
		if ($startTime === null) {
			$startTime = static::createTimestampFromSeconds(self::START_TIME_DEFAULT);
		}

		if ($endTime === null) {
			$endTime = static::createTimestampFromSeconds(self::END_TIME_DEFAULT);
		}

		$this->startTime = $startTime;
		$this->endTime = $endTime;
		$this->annotations = [];
	}

	/**
	 * Returns the start time of this instance
	 *
	 * @return Timestamp the time
	 */
	public function getStartTime() {
		return $this->startTime;
	}

	/**
	 * Returns the end time of this instance
	 *
	 * @return Timestamp the time
	 */
	public function getEndTime() {
		return $this->endTime;
	}

	/**
	 * Returns the list of annotations from this instance
	 *
	 * @return Annotation[]|ContentualAnnotation[]|\App\Lib\Mcf\Annotation[] the list of annotations
	 */
	public function getAnnotations() {
		return $this->annotations;
	}

	/**
	 * Adds a new annotation to this instance
	 *
	 * @param Annotation $annotation the annotation to add
	 */
	public function addAnnotation(Annotation $annotation) {
		$this->annotations[] = $annotation;
	}

	/**
	 * Changes the start and end time of this instance and re-scales all other time references accordingly
	 *
	 * @param Timestamp $start the new start time of this instance
	 * @param Timestamp $end the new end time of this instance
	 */
	public function changeTime(Timestamp $start, Timestamp $end) {
		// retrieve the old and new offset (i.e. the start times)
		$oldOffset = $this->startTime->toSeconds();
		$newOffset = $start->toSeconds();

		// calculate the old and new length (i.e. the total durations)
		$oldLength = $this->endTime->toSeconds() - $oldOffset;
		$newLength = $end->toSeconds() - $newOffset;

		// for each annotation in this instance
		foreach ($this->annotations as $annotation) {
			// revert the old offset
			$annotation->getTiming()->subtractSeconds($oldOffset);

			// revert the old duration
			$annotation->getTiming()->divide($oldLength);

			// apply the new duration
			$annotation->getTiming()->multiply($newLength);

			// apply the new offset
			$annotation->getTiming()->addSeconds($newOffset);
		}

		// update the start and end time
		$this->startTime = $start;
		$this->endTime = $end;
	}

	/** Normalizes the start and end time of this instance and re-scales all other time references accordingly */
	public function normalizeTime() {
		$this->changeTime(
			static::createTimestampFromSeconds(self::START_TIME_DEFAULT),
			static::createTimestampFromSeconds(self::END_TIME_DEFAULT)
		);
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
