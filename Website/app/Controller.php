<?php

/*
 * MovieContentFilter (http://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App;

use Delight\Foundation\App;

class Controller {

	protected static function ensureAuthenticated(App $app, $targetPath = null) {
		// if the user is not logged in
		if (!$app->auth()->check()) {
			// if the requested target path has not been provided
			if (empty($targetPath)) {
				// use the current route as the default
				$targetPath = $app->currentRoute();
			}

			// redirect back to the index and tell the user to sign in
			$app->flash()->warning('Please sign in to view the requested page. If you don\'t have an account yet, you may sign up for free.');
			$app->redirect('/?continue='.urlencode($targetPath));
			exit;
		}
	}

	public static function failNotFound(App $app) {
		// return the appropriate error code
		$app->setStatus(404);
		// return the view for the error page
		echo $app->view('404.html');
	}

}
