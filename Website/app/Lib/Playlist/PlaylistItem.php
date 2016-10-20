<?php

/*
 * MovieContentFilter (https://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App\Lib\Playlist;

use App\Lib\FilterableAnnotation;
use App\Lib\Timing;

/** Filtered playlist item for the XSPF format */
class PlaylistItem extends FilterableAnnotation {

	/** @var string the URL where more information about this instance is available */
	private $detailsUrl;
	/** @var string[] the channels that this instance affects */
	private $channels;

	/**
	 * Constructor
	 *
	 * @param string $detailsUrl the URL where more information about this instance is available
	 * @param Timing $timing the time range
	 */
	public function __construct($detailsUrl, Timing $timing) {
		parent::__construct($timing);

		$this->detailsUrl = $detailsUrl;
		$this->channels = [];
	}

	/**
	 * Returns the URL where more information about this instance is available
	 *
	 * @return string the URL
	 */
	public function getDetailsUrl() {
		return $this->detailsUrl;
	}

	/**
	 * Adds a channel that this instance affects
	 *
	 * @param string $channel the channel
	 */
	public function addChannel($channel) {
		$this->channels[$channel] = true;
	}

	/**
	 * Returns whether this instance affects the specified channel
	 *
	 * @param string $channel the channel to check
	 * @return bool whether the channel is affected
	 */
	public function containsChannel($channel) {
		return isset($this->channels[$channel]) && $this->channels[$channel];
	}

}
