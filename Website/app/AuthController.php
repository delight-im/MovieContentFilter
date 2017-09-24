<?php

/*
 * MovieContentFilter (https://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App;

use Delight\Auth\ConfirmationRequestNotFound;
use Delight\Auth\EmailNotVerifiedException;
use Delight\Auth\InvalidEmailException;
use Delight\Auth\InvalidPasswordException;
use Delight\Auth\InvalidSelectorTokenPairException;
use Delight\Auth\ResetDisabledException;
use Delight\Auth\TokenExpiredException;
use Delight\Auth\TooManyRequestsException;
use Delight\Auth\UserAlreadyExistsException;
use Delight\Foundation\App;
use Delight\Str\Str;

class AuthController extends Controller {

	const MIN_PASSWORT_LENGTH = 8;

	public static function showSignUp(App $app) {
		echo $app->view(
			'sign-up.html',
			[
				'passwordMinLength' => self::MIN_PASSWORT_LENGTH
			]
		);
	}

	private static function sendEmail(App $app, $template, $subject, $toAddress, $toName = null, $params = null) {
		// since we’re sending an email, this request *may* take a bit longer in some rare cases
		\set_time_limit(60);

		// never stop execution before the email has been sent just because the client disconnected
		\ignore_user_abort(true);

		// if no parameters have been provided
		if ($params === null) {
			// initialize an empty array
			$params = [];
		}

		// add the general parameters that are passed to all email templates
		$params['projectName'] = $_ENV['COM_MOVIECONTENTFILTER_PROJECT_NAME'];
		$params['projectUrl'] = \parse_url($_ENV['APP_PUBLIC_URL'], \PHP_URL_HOST);
		$params['projectEmail'] = $_ENV['COM_MOVIECONTENTFILTER_MAIL_REPLY_TO'];
		$params['projectPostalAddress'] = $_ENV['COM_MOVIECONTENTFILTER_PROJECT_POSTAL'];
		$params['recipientEmailAddress'] = $toAddress;
		$params['recipientName'] = $toName;

		// combine the template and the parameters into the complete body text
		$body = $app->view($template, $params);

		// create a new message object
		/** @var \Swift_Mime_Message $obj */
		$obj = $app->mail()->createMessage();

		// configure the message
		$obj->setSubject($subject);
		$obj->setFrom(
			[ $_ENV['COM_MOVIECONTENTFILTER_MAIL_FROM'] => $_ENV['COM_MOVIECONTENTFILTER_PROJECT_NAME'] ]
		);
		if (empty($toName)) {
			$obj->setTo(
				[ $toAddress ]
			);
		}
		else {
			$obj->setTo(
				[ $toAddress => $toName ]
			);
		}
		$obj->setBody($body);

		// send the message and return whether this operation succeeded
		return $app->mail()->send($obj);
	}

	public static function saveSignUp(App $app) {
		$email = $app->input()->post('email', \TYPE_EMAIL);
		$password1 = $app->input()->post('password-1');
		$password2 = $app->input()->post('password-2');
		$displayName = $app->input()->post('display-name');

		if (!empty($email)) {
			if (!empty($password1) && \strlen($password1) >= self::MIN_PASSWORT_LENGTH) {
				if ($password1 === $password2) {
					try {
						$app->auth()->register($email, $password1, $displayName, function ($selector, $token) use ($app, $email) {
							// build the URL for the confirmation link
							$confirmationUrl = $app->url('/confirm/' . \urlencode($selector) . '/' . \urlencode($token));

							// send the link to the user
							self::sendEmail(
								$app,
								'mail/en-US/sign-up.txt',
								'Please confirm your email address',
								$email,

								// we can’t be sure just yet that the supplied name (if any) is acceptable to the owner of the email address
								null,

								[
									'requestedByIpAddress' => $app->getClientIp(),
									'reasonForEmailDelivery' => 'You’re receiving this email because you recently created a free account on our website. If that wasn’t you, please ignore this email and accept our excuses.',
									'confirmationUrl' => $confirmationUrl,
								]
							);
						});

						$app->flash()->success('Thanks for signing up! Please check your inbox for a confirmation email soon.');
						$app->redirect('/');
					}
					catch (InvalidEmailException $e) {
						$app->flash()->warning('Please check the email address that you’ve entered and try again. Thank you!');
						$app->redirect($app->currentRoute());
					}
					catch (InvalidPasswordException $e) {
						$app->flash()->warning('Please check the requirements for the password and try again. Thank you!');
						$app->redirect($app->currentRoute());
					}
					catch (UserAlreadyExistsException $e) {
						$app->flash()->warning('An account with that email address does already exist. Do you want to sign in instead?');
						$app->redirect($app->currentRoute());
					}
					catch (TooManyRequestsException $e) {
						$app->flash()->warning('Please try again later!');
						$app->redirect($app->currentRoute());
					}
				}
				else {
					$app->flash()->warning('The two passwords didn’t match. Please try again. Thank you!');
					$app->redirect($app->currentRoute());
				}
			}
			else {
				$app->flash()->warning('Please check the requirements for the password and try again. Thank you!');
				$app->redirect($app->currentRoute());
			}
		}
		else {
			$app->flash()->warning('Please check the email address that you’ve entered and try again. Thank you!');
			$app->redirect($app->currentRoute());
		}
	}

	public static function processLogin(App $app) {
		$email = $app->input()->post('email', \TYPE_STRING);
		$password = $app->input()->post('password');
		$continueToPath = $app->input()->post('continue');

		try {
			$app->auth()->login($email, $password, (int) (60 * 60 * 24 * 365.25));

			// if a desired target path to redirect to has been specified
			if (!empty($continueToPath)) {
				// if the target path is a local path
				if (Str::from($continueToPath)->matches('/^\\/[^\\/]/')) {
					// redirect to the requested path
					$app->redirect($continueToPath);
					exit;
				}
			}

			// otherwise redirect to the root path
			$app->redirect('/');
		}
		catch (InvalidEmailException $e) {
			$app->flash()->warning('Please check your email address and password and try again!');
			$app->redirect('/');
		}
		catch (InvalidPasswordException $e) {
			$app->flash()->warning('Please check your email address and password and try again!');
			$app->redirect('/');
		}
		catch (EmailNotVerifiedException $e) {
			$app->flash()->warning('Please verify your email address before being able to sign in. You should have received an email containing the activation link. Thank you!');
			$app->redirect('/');
		}
		catch (TooManyRequestsException $e) {
			$app->flash()->warning('Please try again later!');
			$app->redirect('/');
		}
	}

	public static function confirmEmail(App $app, $selector, $token) {
		try {
			$app->auth()->confirmEmailAndSignIn($selector, $token, (int) (60 * 60 * 24 * 365.25));

			$app->flash()->success('Your email address has been verified successfully. Thank you!');
			$app->redirect('/');
		}
		catch (InvalidSelectorTokenPairException $e) {
			$app->flash()->warning('The confirmation link that you followed was invalid. Did you already use up your link? Otherwise, please try again!');
			$app->redirect('/');
		}
		catch (TokenExpiredException $e) {
			$app->flash()->warning('Your confirmation link has already expired. Please request a new confirmation email.');
			$app->redirect('/');
		}
		catch (TooManyRequestsException $e) {
			$app->flash()->warning('Please try again later!');
			$app->redirect('/');
		}
	}

	public static function logout(App $app) {
		$app->auth()->logout();

		$app->flash()->success('You have been successfully logged out. See you next time!');
		$app->redirect('/');
	}

	public static function getForgotPassword(App $app) {
		echo $app->view('forgot_password.html');
	}

	public static function postForgotPassword(App $app) {
		$email = $app->input()->post('email', \TYPE_STRING);

		try {
			$app->auth()->forgotPassword($email, function ($selector, $token) use ($app, $email) {
				// build the URL for the reset link
				$resetUrl = $app->url('/reset/' . \urlencode($selector) . '/' . \urlencode($token));

				// get the user’s display name (if verified)
				$displayName = $app->db()->selectValue(
					'SELECT username FROM users WHERE email = ? AND verified = 1',
					[ $email ]
				);

				// send the link to the user
				self::sendEmail(
					$app,
					'mail/en-US/forgot_password.txt',
					'Resetting your password',
					$email,
					$displayName,
					[
						'requestedByIpAddress' => $app->getClientIp(),
						'reasonForEmailDelivery' => 'You’re receiving this email because you recently requested your password to be reset on our website. If that wasn’t you, please ignore this email and accept our excuses.',
						'resetUrl' => $resetUrl,
					]
				);
			});

			$app->flash()->success('Thank you! Please check your inbox soon for further instructions.');
			$app->redirect('/');
		}
		catch (InvalidEmailException $e) {
			$app->flash()->warning('Please check the email address that you’ve entered and try again. Thank you!');
			$app->redirect($app->currentRoute());
		}
		catch (EmailNotVerifiedException $e) {
			$app->flash()->warning('Please verify your email address first. You should have received an email containing the activation link. Thank you!');
			$app->redirect('/');
		}
		catch (ResetDisabledException $e) {
			$app->flash()->warning('Password resets have been explicitly disabled for your account. There’s nothing we can do about it. Sorry!');
			$app->redirect('/');
		}
		catch (TooManyRequestsException $e) {
			$app->flash()->warning('Please try again later!');
			$app->redirect($app->currentRoute());
		}
	}

	public static function getResetPassword(App $app, $selector, $token) {
		if ($app->auth()->canResetPassword($selector, $token)) {
			echo $app->view(
				'reset_password.html',
				[
					'passwordMinLength' => self::MIN_PASSWORT_LENGTH
				]
			);
		}
		else {
			$app->flash()->warning('The reset link that you followed was invalid or has already expired. Please try again!');
			$app->redirect('/');
		}
	}

	public static function postResetPassword(App $app, $selector, $token) {
		$password1 = $app->input()->post('password-1');
		$password2 = $app->input()->post('password-2');

		if (!empty($password1) && \strlen($password1) >= self::MIN_PASSWORT_LENGTH) {
			if ($password1 === $password2) {
				try {
					$app->auth()->resetPassword($selector, $token, $password1);

					$app->flash()->success('Your password has been successfully reset. Thank you!');
					$app->redirect('/');
				}
				catch (InvalidSelectorTokenPairException $e) {
					$app->flash()->warning('The reset link that you followed was invalid. Did you already use up your link? Otherwise, please try again!');
					$app->redirect('/');
				}
				catch (TokenExpiredException $e) {
					$app->flash()->warning('Your reset link has already expired. Please request to reset your password once again.');
					$app->redirect('/');
				}
				catch (ResetDisabledException $e) {
					$app->flash()->warning('Password resets have been explicitly disabled for your account. There’s nothing we can do about it. Sorry!');
					$app->redirect('/');
				}
				catch (InvalidPasswordException $e) {
					$app->flash()->warning('Please check the requirements for the password and try again. Thank you!');
					$app->redirect($app->currentRoute());
				}
				catch (TooManyRequestsException $e) {
					$app->flash()->warning('Please try again later!');
					$app->redirect($app->currentRoute());
				}
			}
			else {
				$app->flash()->warning('The two passwords didn’t match. Please try again. Thank you!');
				$app->redirect($app->currentRoute());
			}
		}
		else {
			$app->flash()->warning('Please check the requirements for the password and try again. Thank you!');
			$app->redirect($app->currentRoute());
		}
	}

	public static function getResendConfirmation(App $app) {
		echo $app->view('resend_confirmation.html');
	}

	public static function postResendConfirmation(App $app) {
		$email = $app->input()->post('email', \TYPE_STRING);

		try {
			$app->auth()->resendConfirmationForEmail($email, function ($selector, $token) use ($app, $email) {
				// build the URL for the confirmation link
				$confirmationUrl = $app->url('/confirm/' . \urlencode($selector) . '/' . \urlencode($token));

				// get the user’s display name (if verified)
				$displayName = $app->db()->selectValue(
					'SELECT username FROM users WHERE email = ? AND verified = 1',
					[ $email ]
				);

				// send the link to the user
				self::sendEmail(
					$app,
					'mail/en-US/confirm_email.txt',
					'Confirming your email address',
					$email,
					$displayName,
					[
						'requestedByIpAddress' => $app->getClientIp(),
						'reasonForEmailDelivery' => 'You’re receiving this email because you recently requested a new confirmation email on our website. If that wasn’t you, please ignore this email and accept our excuses.',
						'confirmationUrl' => $confirmationUrl,
					]
				);
			});

			$app->flash()->success('Thank you! Please check your inbox soon for further instructions.');
			$app->redirect('/');
		}
		catch (ConfirmationRequestNotFound $e) {
			$app->flash()->warning('We couldn’t find any earlier confirmation request for that address. Please try again!');
			$app->redirect($app->currentRoute());
		}
		catch (TooManyRequestsException $e) {
			$app->flash()->warning('Please try again later!');
			$app->redirect($app->currentRoute());
		}
	}

}
