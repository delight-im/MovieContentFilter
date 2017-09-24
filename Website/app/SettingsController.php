<?php

/*
 * MovieContentFilter (https://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App;

use Delight\Foundation\App;

class SettingsController extends Controller {

	public static function getSettings(App $app) {
		self::ensureAuthenticated($app);

		echo $app->view(
			'settings.html',
			[
			]
		);
	}

}
