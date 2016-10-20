<?php

/*
 * MovieContentFilter (https://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App;

use Delight\Foundation\App;

class PrefsController extends Controller {

	public static function showOverview(App $app) {
		self::ensureAuthenticated($app);

		$numCategories = $app->db()->selectValue('SELECT COUNT(*) FROM categories');
		$numTopics = $app->db()->selectValue('SELECT COUNT(*) FROM topics');

		$topics = $app->db()->select(
			'SELECT id, label, (SELECT COUNT(*) FROM preferences WHERE user_id = ? AND category_id IN (SELECT id FROM categories WHERE topic_id = topics.id)) AS categories_enabled FROM topics ORDER BY label ASC',
			[ $app->auth()->id() ]
		);

		echo $app->view('preferences.html', [
			'numCategories' => $numCategories,
			'numTopics' => $numTopics,
			'topics' => $topics
		]);
	}

	public static function showTopic(App $app, $topicId) {
		self::ensureAuthenticated($app);

		$topicId = $app->ids()->decode(trim($topicId));

		$topicName = $app->db()->selectValue(
			'SELECT label FROM topics WHERE id = ?',
			[ $topicId ]
		);

		$categories = $app->db()->select(
			'SELECT id, label, is_general FROM categories AS a WHERE a.topic_id = ? ORDER BY a.is_general DESC, a.label ASC',
			[ $topicId ]
		);

		$severities = $app->db()->select(
			'SELECT id, name, label_in_preferences FROM severities WHERE available_as_preference = 1 ORDER BY inclusiveness ASC'
		);

		$prefs = $app->db()->select(
			'SELECT category_id, severity_id FROM preferences WHERE user_id = ?',
			[ $app->auth()->id() ]
		);

		$initialValues = [];

		foreach ($prefs as $pref) {
			// set the initial value for the preference
			$initialValues[$pref['category_id']] = $pref['severity_id'];
		}

		echo $app->view('preferences_by_topic.html', [
			'topicName' => $topicName,
			'categories' => $categories,
			'severities' => $severities,
			'initialValues' => $initialValues
		]);
	}

	public static function saveTopic(App $app, $topicId) {
		self::ensureAuthenticated($app);

		$topicId = $app->ids()->decode(trim($topicId));

		if (isset($_POST['category']) && is_array($_POST['category'])) {
			$validSeverityIds = $app->db()->selectColumn(
				'SELECT id FROM severities WHERE available_as_annotation = 1'
			);

			$categoryIdsToRemove = [];
			$categoriesToUpdate = [];

			foreach ($_POST['category'] as $categoryId => $severityId) {
				$categoryId = (int) $categoryId;
				$severityId = (int) $severityId;

				if (in_array($severityId, $validSeverityIds)) {
					$categoriesToUpdate[$categoryId] = $severityId;
				}
				else {
					$categoryIdsToRemove[] = $categoryId;
				}
			}

			if (count($categoryIdsToRemove) > 0) {
				$app->db()->exec(
					'DELETE FROM preferences WHERE user_id = ? AND category_id IN ('.implode(',', $categoryIdsToRemove).')',
					[ $app->auth()->id() ]
				);
			}

			if (count($categoriesToUpdate) > 0) {
				$categoryUpdateValues = [];

				foreach ($categoriesToUpdate as $categoryId => $severityId) {
					$categoryUpdateValues[] = '('.$app->auth()->id().', '.$categoryId.', '.$severityId.')';
				}

				$app->db()->exec(
					'INSERT INTO preferences (user_id, category_id, severity_id) VALUES '.implode(',', $categoryUpdateValues).' ON DUPLICATE KEY UPDATE severity_id = VALUES(severity_id)'
				);
			}

			// redirect back to the preferences overview
			$app->flash()->success('Your preferences have been saved!');
			$app->redirect('/preferences');
			exit;
		}

		// redirect back to the page where these preferences can be viewed (and edited)
		$app->flash()->warning('Your preferences could not be saved. Please try again!');
		$app->redirect('/preferences/' . $app->ids()->encode($topicId));
	}

}
