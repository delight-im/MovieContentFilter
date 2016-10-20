<?php

/*
 * MovieContentFilter (https://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App;

use Delight\Foundation\App;

class MetaController extends Controller {

	public static function welcome(App $app) {
		echo $app->view('welcome.html');
	}

	public static function showSpecification(App $app) {
		$categories = $app->db()->select(
			'SELECT a.label AS topic, b.name, b.label, b.is_general FROM topics AS a JOIN categories AS b ON a.id = b.topic_id ORDER BY a.label ASC, b.is_general DESC, b.name ASC'
		);

		$categoriesByTopic = [];

		foreach ($categories as $category) {
			$topic = $category['topic'];
			unset($category['topic']);

			if (!isset($categoriesByTopic[$topic])) {
				$categoriesByTopic[$topic] = [];
			}

			$categoriesByTopic[$topic][] = $category;
		}

		$severities = $app->db()->select(
			'SELECT name, label FROM severities WHERE available_as_annotation = 1 ORDER BY id ASC'
		);
		$channels = $app->db()->select(
			'SELECT name, label, is_default FROM channels ORDER BY id ASC'
		);

		echo $app->view('specification.html', [
			'topics' => $categoriesByTopic,
			'severities' => $severities,
			'channels' => $channels
		]);
	}

}
