<?php

/*
 * MovieContentFilter (https://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App;

use Delight\Foundation\App;

trait EmailSenderTrait {

	protected static function sendEmail(App $app, $template, $subject, $toAddress, $toName = null, $params = null) {
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

}
