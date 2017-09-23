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
					$fileStartTime = \floatval(trim($_POST['start']));
					$fileEndTime = \floatval(trim($_POST['end']));

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

}
