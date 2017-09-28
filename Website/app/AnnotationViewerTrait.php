<?php

/*
 * MovieContentFilter (https://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App;

use App\Lib\Mcf\WebvttTimestamp;

trait AnnotationViewerTrait {

	private static function prepareAnnotationForDisplay(array $data, $workStartTime, $workEndTime) {
		// add approximate timings in addition to relative start and end positions
		$data['start_time'] = (string) WebvttTimestamp::fromPositionInRuntime($workStartTime, $workEndTime, $data['start_position']);
		$data['end_time'] = (string) WebvttTimestamp::fromPositionInRuntime($workStartTime, $workEndTime, $data['end_position']);

		// calculate the sizes of partitions symbolizing the runtime shares
		$data['partitions'] = [];
		$data['partitions']['before'] = (int) \round($data['start_position'] * 100);
		$data['partitions']['self'] = (int) \round(($data['end_position'] - $data['start_position']) * 100);
		$data['partitions']['self'] += 2;
		$data['partitions']['before'] -= 1;
		$data['partitions']['after'] = 100 - $data['partitions']['before'] - $data['partitions']['self'];

		// drop relative start and end positions which are not needed anymore
		unset($data['start_position']);
		unset($data['end_position']);

		return $data;
	}

}
