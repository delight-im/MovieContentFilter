<?php

/*
 * MovieContentFilter (http://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App\Lib;

use App\Lib\Playlist\Playlist;
use App\Lib\Playlist\PlaylistItem;

/** Description of content that may be filtered in a video or audio file using an EDL playlist */
final class Edl extends Playlist {

	const ACTION_SKIP = 0;
	const ACTION_MUTE = 1;

	/**
	 * Converts this instance to a string representation
	 *
	 * @return string the string representation
	 */
	public function __toString() {
		$lines = [];

		foreach ($this->annotations as $annotation) {
			if ($annotation instanceof FilterableAnnotation xor $this->inverted) {
				$startStr = (string) $annotation->getTiming()->getStart();
				$endStr = (string) $annotation->getTiming()->getEnd();

				if ($annotation instanceof PlaylistItem) {
					if ($annotation->containsChannel('video') || $annotation->containsChannel('both')) {
						$action = self::ACTION_SKIP;
					}
					else {
						$action = self::ACTION_MUTE;
					}
				}
				else {
					$action = self::ACTION_SKIP;
				}

				$lines[] = $startStr . ' ' . $endStr . ' ' . $action;
			}
		}

		if (count($lines) > 0) {
			$lines[] = '';
		}

		return implode("\n", $lines);
	}

}
