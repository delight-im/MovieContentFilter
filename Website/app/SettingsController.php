<?php

/*
 * MovieContentFilter (https://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App;

use Delight\Auth\EmailNotVerifiedException;
use Delight\Auth\InvalidEmailException;
use Delight\Auth\InvalidPasswordException;
use Delight\Auth\NotLoggedInException;
use Delight\Auth\TooManyRequestsException;
use Delight\Auth\UserAlreadyExistsException;
use Delight\Foundation\App;

class SettingsController extends Controller {

	use EmailSenderTrait;

	public static function getSettings(App $app) {
		self::ensureAuthenticated($app);

		echo $app->view(
			'settings.html',
			[
				'passwordMinLength' => AuthController::MIN_PASSWORD_LENGTH,
				'passwordResetEnabled' => $app->auth()->isPasswordResetEnabled()
			]
		);
	}

	public static function postChangePassword(App $app) {
		self::ensureAuthenticated($app);

		$oldPassword = $app->input()->post('old-password', \TYPE_STRING);
		$newPassword = $app->input()->post('password-1', \TYPE_STRING);
		$newPasswordRepeated = $app->input()->post('password-2', \TYPE_STRING);

		if ($oldPassword !== null && $newPassword !== null && $newPasswordRepeated !== null) {
			if (\strlen($newPassword) >= AuthController::MIN_PASSWORD_LENGTH) {
				if ($newPasswordRepeated === $newPassword) {
					try {
						$app->auth()->changePassword($oldPassword, $newPassword);

						// inform the user about this critical change via email
						self::sendEmail(
							$app,
							'mail/en-US/password-changed.txt',
							'Your password has been changed',
							$app->auth()->getEmail(),
							$app->auth()->getUsername(),
							[
								'requestedByIpAddress' => $app->getClientIp(),
								'reasonForEmailDelivery' => 'You’re receiving this email because the password for your account has recently been changed. Your email address is the address associated with that account.'
							]
						);

						$app->flash()->success('Your password has been changed successfully.');
						$app->redirect('/settings');
					}
					catch (NotLoggedInException $e) {
						self::failNotSignedIn($app);
					}
					catch (InvalidPasswordException $e) {
						$app->flash()->warning('It seems the old password that you entered was not correct. Please try again!');
						$app->redirect('/settings');
					}
					catch (TooManyRequestsException $e) {
						$app->flash()->warning('Please try again later!');
						$app->redirect('/settings');
					}
				}
				else {
					$app->flash()->warning('The two new passwords didn’t match. Please try again!');
					$app->redirect('/settings');
				}
			}
			else {
				$app->flash()->warning('Please check the requirements for the new password and try again.');
				$app->redirect('/settings');
			}
		}
		else {
			$app->flash()->warning('Please enter your old password once and twice your new password.');
			$app->redirect('/settings');
		}
	}

	public static function postChangeEmail(App $app) {
		self::ensureAuthenticated($app);

		$password = $app->input()->post('email-password', \TYPE_STRING);
		$email = $app->input()->post('email-1', \TYPE_EMAIL);
		$emailRepeated = $app->input()->post('email-2', \TYPE_EMAIL);

		if ($email !== null && $emailRepeated !== null) {
			if ($emailRepeated === $email) {
				try {
					if ($app->auth()->reconfirmPassword($password)) {
						$app->auth()->changeEmail($email, function ($selector, $token) use ($app, $email) {
							// build the URL for the confirmation link
							$confirmationUrl = $app->url('/confirm/' . \urlencode($selector) . '/' . \urlencode($token));

							// send the link to the user
							self::sendEmail(
								$app,
								'mail/en-US/confirm-email.txt',
								'Confirming your email address',
								$email,

								// we can’t be sure just yet that the supplied name (if any) is acceptable to the owner of the *new* email address
								null,

								[
									'requestedByIpAddress' => $app->getClientIp(),
									'reasonForEmailDelivery' => 'You’re receiving this email because this email address has been designated as the new address for your account on our website. If that wasn’t you, please ignore this email and accept our excuses.',
									'confirmationUrl' => $confirmationUrl,
								]
							);
						});

						$app->flash()->success('Please check your inbox for a confirmation email soon. As soon as confirmed, your new email address will be active.');
						$app->redirect('/settings');
					}
					else {
						$app->flash()->warning('It seems the password that you entered was not correct. Please try again!');
						$app->redirect('/settings');
					}
				}
				catch (InvalidEmailException $e) {
					$app->flash()->warning('Please check the email addresses that you’ve entered and try again.');
					$app->redirect('/settings');
				}
				catch (UserAlreadyExistsException $e) {
					$app->flash()->warning('An account with that email address does already exist. Do you own another account?');
					$app->redirect('/settings');
				}
				catch (EmailNotVerifiedException $e) {
					$app->flash()->warning('Please verify your old email address first. You should have received an email containing the activation link.');
					$app->redirect('/settings');
				}
				catch (NotLoggedInException $e) {
					self::failNotSignedIn($app);
				}
				catch (TooManyRequestsException $e) {
					$app->flash()->warning('Please try again later!');
					$app->redirect('/settings');
				}
			}
			else {
				$app->flash()->warning('The two new email addresses didn’t match. Please try again!');
				$app->redirect('/settings');
			}
		}
		else {
			$app->flash()->warning('Please check the email addresses that you’ve entered and try again.');
			$app->redirect('/settings');
		}
	}

	public static function postControlPasswordReset(App $app) {
		self::ensureAuthenticated($app);

		$password = $app->input()->post('password-reset-password', \TYPE_STRING);
		$enabled = $app->input()->post('password-reset-enabled', \TYPE_INT) === 1;

		try {
			if ($app->auth()->reconfirmPassword($password)) {
				$app->auth()->setPasswordResetEnabled($enabled);

				$app->flash()->success('Your settings for the password reset have been changed successfully.');
				$app->redirect('/settings');
			}
			else {
				$app->flash()->warning('It seems the password that you entered was not correct. Please try again!');
				$app->redirect('/settings');
			}
		}
		catch (NotLoggedInException $e) {
			self::failNotSignedIn($app);
		}
		catch (TooManyRequestsException $e) {
			$app->flash()->warning('Please try again later!');
			$app->redirect('/settings');
		}
	}

	public static function postLogOutEverywhere(App $app) {
		self::ensureAuthenticated($app, '/settings');

		$includeCurrentDevice = $app->input()->post('current-device', \TYPE_INT);

		try {
			if ($includeCurrentDevice === 1) {
				$app->auth()->logOutEverywhere();
			}
			else {
				$app->auth()->logOutEverywhereElse();
			}

			$app->flash()->success('You will be logged out from all sessions within the next five minutes.');
			$app->redirect('/');
		}
		catch (NotLoggedInException $e) {
			self::failNotSignedIn($app, '/settings');
		}
	}

}
