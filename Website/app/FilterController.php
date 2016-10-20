<?php

/*
 * MovieContentFilter (https://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App;

use App\Lib\Edl;
use App\Lib\M3u;
use App\Lib\Mcf\Annotation;
use App\Lib\Mcf\Content;
use App\Lib\Mcf\Mcf;
use App\Lib\Mcf\WebvttTiming;
use App\Lib\Playlist\Playlist;
use App\Lib\Playlist\PlaylistItem;
use App\Lib\Playlist\Timestamp;
use App\Lib\Timing;
use App\Lib\Xspf;
use App\Lib\Mcf\WebvttTimestamp;
use Delight\Foundation\App;

class FilterController extends Controller {

	public static function customizeDownload(App $app, $id) {
		self::ensureAuthenticated($app);

		$id = $app->ids()->decode(trim($id));

		$work = $app->db()->selectRow(
			'SELECT type, title, year, canonical_start_time, canonical_end_time FROM works WHERE id = ?',
			[ $id ]
		);

		$params = $work;

		$params['id'] = $id;

		if ($params['canonical_start_time'] !== null) {
			$params['canonical_start_time'] = (string) WebvttTimestamp::fromSeconds($params['canonical_start_time']);
		}

		if ($params['canonical_end_time'] !== null) {
			$params['canonical_end_time'] = (string) WebvttTimestamp::fromSeconds($params['canonical_end_time']);
		}

		if ($work['type'] === 'episode') {
			$params['series'] = $app->db()->selectRow(
				'SELECT b.id AS parent_id, b.title AS parent_title, a.season, a.episode_in_season FROM works_relations AS a JOIN works AS b ON a.parent_work_id = b.id WHERE a.child_work_id = ? LIMIT 0, 1',
				[ $id ]
			);
		}

		echo $app->view('download.html', $params);
	}

	public static function sendDownload(App $app, $id) {
		self::ensureAuthenticated($app);

		$id = $app->ids()->decode(trim($id));

		$format = $app->input()->post('format');
		$syncStartTime = $app->input()->post('synchronization-start-time');
		$syncEndTime = $app->input()->post('synchronization-end-time');
		$videoFileUri = $app->input()->post('video-source');

		$syncStartTimestamp = WebvttTimestamp::parse($syncStartTime);
		$syncEndTimestamp = WebvttTimestamp::parse($syncEndTime);

		if ($format === 'mcf') {
			$downloadSuggestedFilename = 'Filter (MCF).mcf';
			$downloadMimeType = 'text/plain';

			$annotations = $app->db()->select(
				"SELECT a.start_position, a.end_position, GROUP_CONCAT(b.name, '=', c.name, '=', d.name, ?, a.id ORDER BY b.name ASC, a.id ASC SEPARATOR ',') AS content_list FROM annotations AS a JOIN categories AS b ON b.id = a.category_id JOIN severities AS c ON c.id = a.severity_id JOIN channels AS d ON d.id = a.channel_id WHERE a.work_id = ? GROUP BY a.start_position, a.end_position ORDER BY a.start_position ASC LIMIT 0, 500",
				[
					Content::COMMENT_SEPARATOR . $app->url('/annotation/'),
					$id
				]
			);

			$out = new Mcf();

			if ($annotations !== null) {
				foreach ($annotations as $annotation) {
					// encode annotation IDs in content
					$annotation['content_list'] = preg_replace_callback('/(?<=\/)([0-9]+)(?=,|$)/', function ($match) use ($app) {
						return $app->ids()->encode($match[1]);
					}, $annotation['content_list']);

					$annotationObj = new Annotation(
						new WebvttTiming(
							WebvttTimestamp::fromSeconds($annotation['start_position']),
							WebvttTimestamp::fromSeconds($annotation['end_position'])
						)
					);

					$contentEntries = explode(',', $annotation['content_list']);

					foreach ($contentEntries as $contentEntry) {
						$annotationObj->addContent(
							Content::parse($contentEntry)
						);
					}

					$out->addAnnotation($annotationObj);
				}
			}

			// scale to the maximum duration so that all fixed-point numbers in the output have maximum precision
			$out->changeTime(
				WebvttTimestamp::fromSeconds(0),
				WebvttTimestamp::fromComponents(99, 59, 59, 999)
			);
		}
		else {
			$mode = $app->input()->post('mode');

			$annotations = $app->db()->select(
				"SELECT a.id, a.start_position, a.end_position, GROUP_CONCAT(b.name SEPARATOR ',') AS channel_list FROM annotations AS a JOIN channels AS b ON b.id = a.channel_id JOIN preferences AS c ON c.user_id = ? AND c.category_id = a.category_id AND c.severity_id <= a.severity_id WHERE a.work_id = ? GROUP BY a.start_position, a.end_position ORDER BY a.start_position ASC LIMIT 0, 500",
				[
					$app->auth()->id(),
					$id
				]
			);

			if ($format === 'xspf') {
				$downloadSuggestedFilename = 'Filter (XSPF).xspf';
				$downloadMimeType = 'application/xspf+xml';

				$out = new Xspf($videoFileUri, $app->url('/view/' . $app->ids()->encode($id)));
			}
			elseif ($format === 'm3u') {
				$downloadSuggestedFilename = 'Filter (M3U).m3u';
				$downloadMimeType = 'audio/x-mpegurl';

				$out = new M3u($videoFileUri, $app->url('/view/' . $app->ids()->encode($id)));
			}
			elseif ($format === 'edl') {
				$downloadSuggestedFilename = 'Filter (EDL).edl';
				$downloadMimeType = 'text/plain';

				$out = new Edl($app->url('/view/' . $app->ids()->encode($id)));
			}
			else {
				throw new \RuntimeException('Unknown format `'.$format.'`');
			}

			if ($annotations !== null) {
				foreach ($annotations as $annotation) {
					$annotationObj = new PlaylistItem(
						$app->url('/annotation/' . $app->ids()->encode($annotation['id'])),
						new Timing(
							Timestamp::fromSeconds($annotation['start_position']),
							Timestamp::fromSeconds($annotation['end_position'])
						)
					);

					$channels = explode(',', $annotation['channel_list']);

					foreach ($channels as $channel) {
						$annotationObj->addChannel($channel);
					}

					$out->addAnnotation($annotationObj);
				}
			}

			// scale the time to match the desired playback time
			$out->changeTime($syncStartTimestamp, $syncEndTimestamp);

			// if the filter is a playlist
			if ($out instanceof Playlist) {
				// fill all gaps between annotations
				$out->fillUp();
			}

			// if the preview mode has been selected
			if ($mode === 'preview') {
				// invert the filter
				$out->setInverted(true);
			}
		}

		$app->downloadContent((string) $out, $downloadSuggestedFilename, $downloadMimeType);
	}

}
