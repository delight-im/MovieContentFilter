<?php

/*
 * MovieContentFilter (https://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App;

use Delight\Auth\InvalidPasswordException;
use Delight\Auth\NotLoggedInException;
use Delight\Auth\TooManyRequestsException;
use Delight\Foundation\App;

class SettingsController extends Controller {

	use EmailSenderTrait;

	public static function getSettings(App $app) {
		self::ensureAuthenticated($app);

		echo $app->view(
			'settings.html',
			[
				'passwordMinLength' => AuthController::MIN_PASSWORT_LENGTH
			]
		);
	}

	public static function postChangePassword(App $app) {
		self::ensureAuthenticated($app);

		$oldPassword = $app->input()->post('old-password', \TYPE_STRING);
		$newPassword = $app->input()->post('password-1', \TYPE_STRING);
		$newPasswordRepeated = $app->input()->post('password-2', \TYPE_STRING);

		if ($oldPassword !== null && $newPassword !== null && $newPasswordRepeated !== null) {
			if (\strlen($newPassword) >= AuthController::MIN_PASSWORT_LENGTH) {
				if ($newPasswordRepeated === $newPassword) {
					try {
						$app->auth()->changePassword($oldPassword, $newPassword);

						// inform the user about this critical change via email
						self::sendEmail(
							$app,
							'mail/en-US/password_changed.txt',
							'Your password has been changed',
							$app->auth()->getEmail(),
							$app->auth()->getUsername(),
							[
								'requestedByIpAddress' => $app->getClientIp(),
								'reasonForEmailDelivery' => 'You’re receiving this email because the password for your account has recently been changed. Your email address is the address associated with that account.'
							]
						);

						$app->flash()->success('Your password has been changed successfully. Thank you!');
						$app->redirect('/settings');
					}
					catch (NotLoggedInException $e) {
						self::failNotSignedIn($app);
					}
					catch (InvalidPasswordException $e) {
						$app->flash()->warning('It seems the old password that you entered was not correct. Please try again. Thank you!');
						$app->redirect('/settings');
					}
					catch (TooManyRequestsException $e) {
						$app->flash()->warning('Please try again later!');
						$app->redirect('/settings');
					}
				}
				else {
					$app->flash()->warning('The two new passwords didn’t match. Please try again. Thank you!');
					$app->redirect('/settings');
				}
			}
			else {
				$app->flash()->warning('Please check the requirements for the new password and try again. Thank you!');
				$app->redirect('/settings');
			}
		}
		else {
			$app->flash()->warning('Please enter your old password once and your new password twice. Thank you!');
			$app->redirect('/settings');
		}
	}

}
