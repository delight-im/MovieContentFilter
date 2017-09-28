<?php

/*
 * MovieContentFilter (https://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App;

use App\Lib\ContentualAnnotation;
use App\Lib\Filter;
use App\Lib\Timestamp;
use App\Lib\Timing;
use Delight\Db\Throwable\Error;
use Delight\Db\Throwable\IntegrityConstraintViolationException;
use Delight\Foundation\App;

class AnnotationController extends Controller {

	use AnnotationViewerTrait;

	public static function launchEditor(App $app, $id) {
		$id = $app->ids()->decode(\trim($id));

		$work = $app->db()->selectRow(
			'SELECT title, year FROM works WHERE id = ?',
			[ $id ]
		);

		$topics = $app->db()->select(
			'SELECT id, label FROM topics ORDER BY label ASC'
		);
		$categories = $app->db()->select(
			'SELECT id, label, is_general, topic_id FROM categories ORDER BY is_general DESC, label ASC'
		);
		$severities = $app->db()->select(
			'SELECT id, label FROM severities WHERE available_as_annotation = 1 ORDER BY id ASC'
		);
		$channels = $app->db()->select(
			'SELECT id, label, is_default FROM channels ORDER BY id ASC'
		);

		echo $app->view('contribute.html', [
			'id' => $id,
			'title' => $work['title'],
			'year' => $work['year'],
			'topics' => $topics,
			'categories' => $categories,
			'severities' => $severities,
			'channels' => $channels
		]);
	}

	public static function receiveFromEditor(App $app, $id) {
		// do not lose user input when the connection is dropped by the client
		\ignore_user_abort(true);

		// if the user is logged in
		if ($app->auth()->check()) {
			$id = $app->ids()->decode(\trim($id));

			if (isset($_POST['start']) && isset($_POST['end'])) {
				if (isset($_POST['annotations']) && \is_array($_POST['annotations']) && !empty($_POST['annotations'])) {
					$fileStartTime = \floatval(\trim($_POST['start']));
					$fileEndTime = \floatval(\trim($_POST['end']));

					// update the canonical timing and duration of the work
					$app->db()->exec(
						'UPDATE works SET canonical_start_time = ?, canonical_end_time = ? WHERE id = ? AND (canonical_start_time IS NULL OR canonical_end_time IS NULL)',
						[
							$fileStartTime,
							$fileEndTime,
							$id
						]
					);

					$filter = new Filter(
						Timestamp::fromSeconds($fileStartTime),
						Timestamp::fromSeconds($fileEndTime)
					);

					foreach ($_POST['annotations'] as $annotation) {
						$filter->addAnnotation(
							new ContentualAnnotation(
								new Timing(
									Timestamp::fromSeconds($annotation['start']),
									Timestamp::fromSeconds($annotation['end'])
								),
								$annotation['category'],
								$annotation['severity'],
								$annotation['channel']
							)
						);
					}

					// normalize the timings and durations of all annotations
					$filter->normalizeTime();

					// iterate over the normalized annotations
					foreach ($filter->getAnnotations() as $annotation) {
						// and insert them into the database
						try {
							$app->db()->insert(
								'annotations',
								[
									'work_id' => $id,
									'start_position' => $annotation->getTiming()->getStart()->toSeconds(),
									'end_position' => $annotation->getTiming()->getEnd()->toSeconds(),
									'category_id' => $annotation->getCategory(),
									'severity_id' => $annotation->getSeverity(),
									'channel_id' => $annotation->getChannel(),
									'author_user_id' => $app->auth()->id()
								]
							);
						}
						catch (IntegrityConstraintViolationException $ignored) { }
						catch (Error $e) {
							// fail with a proper HTTP response code
							$app->setStatus(500);
						}
					}

					// make hidden drafts that are intended for public display (including any potential parents) visible to everyone now that annotations have been added
					$app->db()->exec(
						'UPDATE works SET is_public = 1 WHERE (id = ? OR id IN (SELECT parent_work_id FROM works_relations WHERE child_work_id = ?)) AND is_public IS NULL',
						[
							$id,
							$id
						]
					);

					// save a message to be displayed on the next page
					$app->flash()->success('Thank you so much! Your contributions have been saved!');
				}

				// return the URL to proceed to after this successful (or empty) contribution
				echo $app->url('/works/' . $app->ids()->encode($id));
			}
			else {
				// fail with a proper HTTP response code
				$app->setStatus(400);
			}
		}
		// if the user is not logged in
		else {
			// fail with a proper HTTP response code
			$app->setStatus(401);
			exit;
		}
	}

	public static function showAnnotation(App $app, $id) {
		$id = $app->ids()->decode(\trim($id));

		$params = [];

		$params['annotation'] = $app->db()->selectRow(
			'SELECT a.work_id, a.start_position, a.end_position, a.author_user_id, a.voting_score, b.label AS category_label, b.is_general AS category_is_general, b.topic_id, e.label AS topic_label, c.name AS severity, d.label AS channel_label FROM annotations AS a JOIN categories AS b ON b.id = a.category_id JOIN severities AS c ON c.id = a.severity_id JOIN channels AS d ON d.id = a.channel_id JOIN topics AS e ON e.id = b.topic_id WHERE a.id = ?',
			[ $id ]
		);

		// add the ID of this annotation to the view parameters
		$params['id'] = $id;

		// if the user is currently signed in
		if ($app->auth()->check()) {
			// if the current user is the author of this annotation
			if ($app->auth()->id() === $params['annotation']['author_user_id']) {
				// the author’s vote has implicitly been cast when creating the annotation
				$params['voted'] = true;
			}
			// if the current user is not the author of this annotation
			else {
				// check if the user’s vote has already been cast
				$castVotesFound = $app->db()->selectValue(
					'SELECT COUNT(*) FROM annotations_votes WHERE annotation_id = ? AND user_id = ?',
					[
						$id,
						$app->auth()->id()
					]
				);

				// the database result tells us whether the user has already voted
				$params['voted'] = $castVotesFound === 1;
			}
		}
		// if the user is not signed in yet
		else {
			// let the voting appear to be available
			$params['voted'] = false;
		}

		// drop author’s user ID which is not needed anymore
		unset($params['annotation']['author_user_id']);

		$params['work'] = $app->db()->selectRow(
			'SELECT type, title, canonical_start_time, canonical_end_time FROM works WHERE id = ?',
			[ $params['annotation']['work_id'] ]
		);

		// move the work’s ID from the annotation’s record to the work’s record itself
		$params['work']['id'] = $params['annotation']['work_id'];
		unset($params['annotation']['work_id']);

		if ($params['work']['type'] === 'episode') {
			$params['series'] = $app->db()->selectRow(
				'SELECT b.id AS parent_id, b.title AS parent_title, a.season, a.episode_in_season FROM works_relations AS a JOIN works AS b ON a.parent_work_id = b.id WHERE a.child_work_id = ? LIMIT 0, 1',
				[ $params['work']['id'] ]
			);
		}

		// prepare the annotation for display
		$params['annotation'] = self::prepareAnnotationForDisplay($params['annotation'], $params['work']['canonical_start_time'], $params['work']['canonical_end_time']);

		// drop timings for overall work which are not needed anymore
		unset($params['work']['canonical_start_time']);
		unset($params['work']['canonical_end_time']);

		echo $app->view('annotation.html', $params);
	}

	public static function voteForAnnotation(App $app, $id, $direction) {
		// if the user is logged in
		if ($app->auth()->check()) {
			$id = $app->ids()->decode(\trim($id));

			// determine in what way to alter the voting score
			if ($direction === 'up') {
				$addend = 1;
			}
			elseif ($direction === 'down') {
				$addend = -1;
			}
			else {
				// fail with a proper HTTP response code
				$app->setStatus(400);
				exit;
			}

			// prevent the user from voting again
			try {
				$app->db()->insert(
					'annotations_votes',
					[
						'annotation_id' => $id,
						'user_id' => $app->auth()->id(),
						'addend' => $addend
					]
				);
			}
			catch (IntegrityConstraintViolationException $e) {
				// fail with a proper HTTP response code
				$app->setStatus(403);
				exit;
			}

			// and record the vote
			$app->db()->exec(
				'UPDATE annotations SET voting_score = voting_score + ? WHERE id = ?',
				[
					$addend,
					$id
				]
			);
		}
		// if the user is not logged in
		else {
			// fail with a proper HTTP response code
			$app->setStatus(401);
			exit;
		}
	}

}
