<?php

/*
 * MovieContentFilter (http://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App;

use Delight\Foundation\App;

class BrowsingController extends Controller {

	public static function showOverview(App $app) {
		$query = 'SELECT title, year FROM works WHERE type = ? ORDER BY RAND() LIMIT 0, 5';

		$movies = $app->db()->select(
			$query,
			[ 'movie' ]
		);
		$series = $app->db()->select(
			$query,
			[ 'series' ]
		);

		echo $app->view('browse_which.html', [
			'examples' => [
				'movies' => $movies,
				'series' => $series
			]
		]);
	}

	public static function showCategory(App $app, $type) {
		$type = $app->input()->value($type);

		if ($type === 'movies') {
			$typeSingular = 'movie';
		}
		else {
			$typeSingular = $type;
		}

		$numWorks = $app->db()->selectValue(
			'SELECT COUNT(*) FROM works WHERE type = ?',
			[ $typeSingular ]
		);

		$works = $app->db()->select(
			'SELECT id, title, year FROM works WHERE type = ? ORDER BY year DESC, title ASC LIMIT 0, 50',
			[ $typeSingular ]
		);

		echo $app->view('browse_list.html', [
			'typeSingular' => $typeSingular,
			'numWorks' => $numWorks,
			'works' => $works
		]);
	}

}
