<?php

/*
 * MovieContentFilter (https://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App\Lib;

/** Annotated time range */
class Annotation {

	/** @var Timing the time range */
	protected $timing;

	/**
	 * Constructor
	 *
	 * @param Timing $timing the time range
	 */
	public function __construct(Timing $timing) {
		$this->timing = $timing;
	}

	/**
	 * Returns the time range of this instance
	 *
	 * @return Timing the time range
	 */
	public function getTiming() {
		return $this->timing;
	}

}
