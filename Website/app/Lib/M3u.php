<?php

/*
 * MovieContentFilter (https://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App\Lib;

use App\Lib\Playlist\FilePlaylist;
use App\Lib\Playlist\PlaylistItem;

/** Description of content that may be filtered in a video or audio file using an M3U playlist */
final class M3u extends FilePlaylist {

	/**
	 * Converts this instance to a string representation
	 *
	 * @return string the string representation
	 */
	public function __toString() {
		$lines = [];

		$lines[] = '# MovieContentFilter';
		$lines[] = '# '.$this->publicInfoUrl;

		foreach ($this->annotations as $annotation) {
			if ($annotation instanceof FilterableAnnotation xor $this->inverted) {
				if ($annotation instanceof PlaylistItem) {
					$lines[] = '';
					$lines[] = '# '.$annotation->getDetailsUrl();
				}
			}
			else {
				$lines[] = '';

				if ($annotation instanceof PlaylistItem) {
					$lines[] = '# '.$annotation->getDetailsUrl();
				}

				// if the end of this annotation extends up to the very end of the file
				if ($annotation->getTiming()->getEnd()->equals($this->endTime)) {
					// let this annotation end at zero seconds to symbolize the very end of the file
					$endTimestamp = static::createTimestampFromSeconds(0);
				}
				// if this annotation ends somewhere in the middle of the file
				else {
					// do not modify this annotation
					$endTimestamp = $annotation->getTiming()->getEnd();
				}

				$lines[] = '#EXTVLCOPT:start-time='.((string) $annotation->getTiming()->getStart());
				$lines[] = '#EXTVLCOPT:stop-time='.((string) $endTimestamp);
				$lines[] = $this->mediaFileUri;
			}
		}

		$lines[] = '';

		return implode("\n", $lines);
	}

}
