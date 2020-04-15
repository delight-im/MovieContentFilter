<?php

/*
 * MovieContentFilter (https://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App;

use App\Lib\Imdb;
use Delight\Auth\TooManyRequestsException;
use Delight\Db\Throwable\IntegrityConstraintViolationException;
use Delight\Foundation\App;
use Delight\Str\Str;

class WorkController extends Controller {

	use AnnotationViewerTrait;

	const YEAR_MIN = 1900;
	const YEAR_MAX = 2099;

	public static function showWork(App $app, $id) {
		$id = $app->ids()->decode(\trim($id));

		$work = $app->db()->selectRow(
			'SELECT type, title, year, imdb_url, author_user_id FROM works WHERE id = ?',
			[ $id ]
		);

		if (empty($work)) {
			self::failNotFound($app);
			exit;
		}

		$params = [
			'id' => $id,
			'type' => $work['type'],
			'title' => $work['title'],
			'year' => $work['year'],
			'imdbUrl' => $work['imdb_url'],
			'authorUserId' => $work['author_user_id']
		];

		if ($work['type'] === 'movie' || $work['type'] === 'episode') {
			if ($work['type'] === 'episode') {
				$params['series'] = $app->db()->selectRow(
					'SELECT b.id AS parent_id, b.title AS parent_title, a.season, a.episode_in_season FROM works_relations AS a JOIN works AS b ON a.parent_work_id = b.id WHERE a.child_work_id = ? LIMIT 0, 1',
					[ $id ]
				);
			}

			$topicsAndSeverities = $app->db()->select(
				'SELECT c.topic_id AS id, SUM(a.end_position - a.start_position) AS share, d.label, b.name AS severity FROM annotations AS a JOIN severities AS b ON a.severity_id = b.id JOIN categories AS c ON a.category_id = c.id JOIN topics AS d ON c.topic_id = d.id WHERE a.work_id = ? GROUP BY c.topic_id, a.severity_id ORDER BY d.label ASC, b.inclusiveness DESC',
				[ $id ]
			);

			$params['topics'] = [];
			$params['max_total_share'] = 0;

			if ($topicsAndSeverities !== null) {
				// for each pair of topic and severity level along with corresponding data
				foreach ($topicsAndSeverities as $topicAndSeverity) {
					// if there is no record for the current topic yet
					if (!isset($params['topics'][$topicAndSeverity['id']])) {
						// create a (still incomplete) record
						$params['topics'][$topicAndSeverity['id']] = [
							'sharesBySeverity' => []
						];
					}

					// add the topic’s label to the record (repeatedly)
					$params['topics'][$topicAndSeverity['id']]['label'] = $topicAndSeverity['label'];
					// add the mapping from the severity to the runtime share to the list
					$params['topics'][$topicAndSeverity['id']]['sharesBySeverity'][$topicAndSeverity['severity']] = $topicAndSeverity['share'];
				}

				// for each unique topic with its corresponding data
				foreach ($params['topics'] as $topicId => $topic) {
					// calculate the total runtime share of the topic (across severity levels)
					$params['topics'][$topicId]['total_share'] = \array_sum($topic['sharesBySeverity']);

					// if the runtime share of the current topic is the highest overall so far
					if ($params['topics'][$topicId]['total_share'] > $params['max_total_share']) {
						// update the highest recorded runtime share
						$params['max_total_share'] = $params['topics'][$topicId]['total_share'];
					}
				}
			}

			echo $app->view('view_single.html', $params);
		}
		else {
			$params['episodes'] = $app->db()->select(
				'SELECT b.id, a.season, a.episode_in_season, b.title, b.year FROM works_relations AS a JOIN works AS b ON a.child_work_id = b.id WHERE a.parent_work_id = ? AND (b.is_public = 1 OR b.author_user_id = ?) ORDER BY a.season ASC, a.episode_in_season ASC LIMIT 0, 100',
				[
					$id,
					$app->auth()->getUserId()
				]
			);

			echo $app->view('view_multiple.html', $params);
		}
	}

	public static function showTopicInWork(App $app, $workId, $topicId) {
		$workId = $app->ids()->decode(\trim($workId));
		$topicId = $app->ids()->decode(\trim($topicId));

		$params = [];

		$params['work'] = $app->db()->selectRow(
			'SELECT type, title, canonical_start_time, canonical_end_time, author_user_id AS authorUserId FROM works WHERE id = ?',
			[ $workId ]
		);

		$params['work']['id'] = $workId;

		if ($params['work']['type'] === 'episode') {
			$params['series'] = $app->db()->selectRow(
				'SELECT b.id AS parent_id, b.title AS parent_title, a.season, a.episode_in_season FROM works_relations AS a JOIN works AS b ON a.parent_work_id = b.id WHERE a.child_work_id = ? LIMIT 0, 1',
				[ $workId ]
			);
		}

		$params['topic'] = [];

		$params['topic']['label'] = $app->db()->selectValue(
			'SELECT label FROM topics WhERE id = ?',
			[ $topicId ]
		);

		if (empty($params['topic']['label'])) {
			self::failNotFound($app);
			exit;
		}

		$params['annotations'] = $app->db()->select(
			'SELECT a.id, a.start_position, a.end_position, b.label AS category_label, b.is_general AS category_is_general, c.name AS severity, d.label AS channel_label FROM annotations AS a JOIN categories AS b ON b.id = a.category_id JOIN severities AS c ON c.id = a.severity_id JOIN channels AS d ON d.id = a.channel_id WHERE a.work_id = ? AND b.topic_id = ? ORDER BY a.start_position ASC LIMIT 0, 500',
			[
				$workId,
				$topicId
			]
		);

		if (empty($params['annotations'])) {
			self::failNotFound($app);
			exit;
		}

		// if annotations have been found for this topic
		if ($params['annotations'] !== null) {
			// iterate over all annotations
			$params['annotations'] = \array_map(function ($each) use ($params) {
				// and prepare them for display
				return self::prepareAnnotationForDisplay($each, $params['work']['canonical_start_time'], $params['work']['canonical_end_time']);
			}, $params['annotations']);
		}

		// drop timings for overall work which are not needed anymore
		unset($params['work']['canonical_start_time']);
		unset($params['work']['canonical_end_time']);

		echo $app->view('topic_in_work.html', $params);
	}

	public static function prepareWork(App $app) {
		self::ensureAuthenticated($app);

		$primaryType = $app->input()->get('primary-type');

		if ($primaryType === null) {
			echo $app->view('works_add_step_1.html');
		}
		else {
			if ($primaryType === 'movie' || $primaryType === 'series') {
				$params = [
					'primaryType' => $primaryType
				];

				if ($primaryType === 'series') {
					$params['seriesParents'] = $app->db()->select(
						'SELECT id, year, title FROM works WHERE type = ? ORDER BY year ASC, title ASC LIMIT 0, 1000',
						[ 'series' ]
					);

					// add an optional suggestion on which parent should be selected by default
					if (isset($_GET['parent-work-id'])) {
						$params['seriesParentDefault'] = $app->ids()->decode(\trim($_GET['parent-work-id']));
					}
				}

				echo $app->view('works_add_step_2.html', $params);
			}
			else {
				self::failNotFound($app);
				exit;
			}
		}
	}

	public static function saveWork(App $app) {
		self::ensureAuthenticated($app);

		$primaryType = $app->input()->post('primary-type');

		if ($primaryType === 'movie' || $primaryType === 'series') {
			$secondaryType = $app->input()->post('secondary-type');
			$title = $app->input()->post('title');
			$year = $app->input()->post('year', \TYPE_INT);
			$imdbUrl = $app->input()->post('imdb-url');

			if (!empty($year) && !empty($imdbUrl)) {
				if ($year >= self::YEAR_MIN && $year <= self::YEAR_MAX) {
					if (Str::from($imdbUrl)->matches(Imdb::WORK_URL_REGEX, $imdbUrlParts)) {
						if (!empty($title) || $secondaryType === 'episode') {
							try {
								$app->auth()->throttle([ 'createWork', 'user', $app->auth()->id() ], 1, (60 * 60 * 4), 6);
								$app->auth()->throttle([ 'createWork', 'ipAddress', $_SERVER['REMOTE_ADDR'] ], 1, (60 * 60 * 4), 8);
							}
							catch (TooManyRequestsException $e) {
								\http_response_code(429);
								$app->flash()->warning('Please try again later!');
								$app->redirect('/add?primary-type=' . \urlencode($primaryType));
								exit;
							}

							try {
								$app->db()->insert(
									'works',
									[
										'type' => empty($secondaryType) ? $primaryType : $secondaryType,
										'title' => $title,
										'year' => $year,
										'imdb_url' => $imdbUrlParts[1],
										'author_user_id' => $app->auth()->id(),

										// initially create as hidden draft and make public only when annotations have been added
										'is_public' => null
									]
								);
							}
							catch (IntegrityConstraintViolationException $e) {
								$duplicateOfId = $app->db()->selectValue(
									'SELECT id FROM works WHERE imdb_url = ?',
									[ $imdbUrlParts[1] ]
								);

								if ($duplicateOfId !== null) {
									$app->redirect('/works/' . $app->ids()->encode($duplicateOfId));
									exit;
								}
								else {
									$app->flash()->warning('An error occurred. Please try again later. Sorry for the inconvenience!');
									$app->redirect('/add?primary-type=' . \urlencode($primaryType));
									exit;
								}
							}
						}
						else {
							$app->flash()->warning('The data you entered was invalid. Please try again!');
							$app->redirect('/add?primary-type=' . \urlencode($primaryType));
							exit;
						}

						$newWorkId = (int) $app->db()->getLastInsertId();

						if ($primaryType === 'series' && $secondaryType === 'episode') {
							$parent = $app->ids()->decode(\trim($_POST['parent']));
							$season = $app->input()->post('season', \TYPE_INT);
							$episode = $app->input()->post('episode', \TYPE_INT);

							$app->db()->insert(
								'works_relations',
								[
									'parent_work_id' => $parent,
									'season' => $season,
									'episode_in_season' => $episode,
									'child_work_id' => $newWorkId
								]
							);
						}

						$app->flash()->success('The new entry has been successfully created. Thank you!');
						$app->redirect('/works/' . $app->ids()->encode($newWorkId));
					}
				}
			}
		}

		$app->flash()->warning('The data you entered was invalid. Please try again!');
		$app->redirect('/add');
	}

}
