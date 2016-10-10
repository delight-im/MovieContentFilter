<?php

/*
 * MovieContentFilter (http://www.moviecontentfilter.com/)
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

				$lines[] = '#EXTVLCOPT:start-time='.((string) $annotation->getTiming()->getStart());
				$lines[] = '#EXTVLCOPT:stop-time='.((string) $annotation->getTiming()->getEnd());
				$lines[] = $this->mediaFileUri;
			}
		}

		$lines[] = '';

		return implode("\n", $lines);
	}

}
