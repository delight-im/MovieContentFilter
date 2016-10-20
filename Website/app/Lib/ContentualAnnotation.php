<?php

/*
 * MovieContentFilter (https://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App\Lib;

/** Annotated time range that describes a single piece of content */
final class ContentualAnnotation extends Annotation {

	/** @var int the category ID */
	private $category;
	/** @var int the severity ID */
	private $severity;
	/** @var int the channel ID */
	private $channel;

	/**
	 * Constructor
	 *
	 * @param Timing $timing the time range
	 * @param int $category the category ID
	 * @param int $severity the severity ID
	 * @param int|null $channel (optional) the channel ID
	 */
	public function __construct(Timing $timing, $category, $severity, $channel = null) {
		parent::__construct($timing);

		$this->category = (int) $category;
		$this->severity = (int) $severity;

		if ($channel !== null) {
			$this->channel = (int) $channel;
		}
	}

	/**
	 * Returns the category ID of this entry
	 *
	 * @return int
	 */
	public function getCategory() {
		return $this->category;
	}

	/**
	 * Returns the severity ID of this entry
	 *
	 * @return int
	 */
	public function getSeverity() {
		return $this->severity;
	}

	/**
	 * Returns the channel ID of this entry
	 *
	 * @return int
	 */
	public function getChannel() {
		return $this->channel;
	}

}
