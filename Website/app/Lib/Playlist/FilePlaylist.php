<?php

/*
 * MovieContentFilter (https://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App\Lib\Playlist;

/** Description of content that may be filtered in a video or audio file using a file-specific playlist */
class FilePlaylist extends Playlist {

	/** @var string the URI of the media file that should be filtered with this instance ("media resource locator") */
	protected $mediaFileUri;

	/**
	 * Constructor
	 *
	 * @param string $mediaFileUri the URI of the media file that should be filtered with this instance
	 * @param string $publicInfoUrl the public URL where more information about this instance is available
	 * @param Timestamp|null $fileStartTime the start time of the annotated source as a reference point
	 * @param Timestamp|null $fileEndTime the end time of the annotated source as a reference point
	 */
	public function __construct($mediaFileUri, $publicInfoUrl, Timestamp $fileStartTime = null, Timestamp $fileEndTime = null) {
		parent::__construct($publicInfoUrl, $fileStartTime, $fileEndTime);

		$this->mediaFileUri = $mediaFileUri;
	}

}
