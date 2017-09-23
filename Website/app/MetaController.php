<?php

/*
 * MovieContentFilter (https://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App;

use Delight\Foundation\App;
use Delight\PrivacyPolicy\Data\DataGroup;
use Delight\PrivacyPolicy\Data\DataPurpose;
use Delight\PrivacyPolicy\Data\DataRequirement;
use Delight\PrivacyPolicy\Language\EnglishPrivacyPolicy;

class MetaController extends Controller {

	public static function welcome(App $app) {
		echo $app->view('welcome.html');
	}

	public static function showSpecification(App $app) {
		$availableVersions = [
			'1.0.0',
			'1.1.0'
		];

		// if a specific version has been requested explicitly
		if (isset($_GET['v'])) {
			// if the requested version is valid
			if (\in_array($_GET['v'], $availableVersions)) {
				// use the specified version
				$selectedVersion = \trim($_GET['v']);
			}
			else {
				$app->setStatus(404);
				exit;
			}
		}
		// if no specific version has been requested
		else {
			// select the most recent version available
			$selectedVersion = \array_values(\array_slice($availableVersions, -1))[0];
		}

		$categories = $app->db()->select(
			'SELECT a.label AS topic, b.name, b.label, b.is_general FROM topics AS a JOIN categories AS b ON a.id = b.topic_id ORDER BY a.label ASC, b.is_general DESC, b.name ASC'
		);

		$categoriesByTopic = [];

		foreach ($categories as $category) {
			$topic = $category['topic'];
			unset($category['topic']);

			if (!isset($categoriesByTopic[$topic])) {
				$categoriesByTopic[$topic] = [];
			}

			$categoriesByTopic[$topic][] = $category;
		}

		$severities = $app->db()->select(
			'SELECT name, label FROM severities WHERE available_as_annotation = 1 ORDER BY id ASC'
		);
		$channels = $app->db()->select(
			'SELECT name, label, is_default FROM channels ORDER BY id ASC'
		);

		echo $app->view('specification.html', [
			'version' => $selectedVersion,
			'versions' => $availableVersions,
			'topics' => $categoriesByTopic,
			'severities' => $severities,
			'channels' => $channels
		]);
	}

	public static function showPrivacyPolicy(App $app) {
		$policy = new EnglishPrivacyPolicy();

		$policy->setLastUpdated(1493301040);
		$policy->setCanonicalUrl($app->url('/privacy'));
		$policy->setContactUrl('https://www.delight.im/contact');

		$policy->setUserDataTraded(false);
		$policy->setDataMinimizationGoal(true);
		$policy->setChildrenMinimumAge(13);
		$policy->setPromotionalEmailOptOut(false);
		$policy->setFirstPartyCookies(true);
		$policy->setThirdPartyCookies(false);
		$policy->setAccountDeletable(false);
		$policy->setPreservationInBackups(true);
		$policy->setThirdPartyServiceProviders(true);
		$policy->setTransferUponMergerOrAcquisition(true);
		$policy->setTlsEverywhere(true);
		$policy->setRightToInformation(true);
		$policy->setNotificationPeriod(30);

		$policy->addDataGroup(
			'Account information',
			'When you create an account by signing up, and whenever you use that account by signing in afterwards, we collect the data that you provide to us voluntarily in the course of that process.',
			[
				DataPurpose::ADMINISTRATION,
				DataPurpose::FULFILLMENT,
				DataPurpose::PERSONALIZATION
			],
			DataRequirement::OPT_IN,

			function (DataGroup $group) {
				$group->addElement(
					\Delight\PrivacyPolicy\Data\DataType::USER_EMAIL,
					DataRequirement::ALWAYS,
					null,
					true,
					false
				);

				$group->addElement(
					\Delight\PrivacyPolicy\Data\DataType::USER_PASSWORD_HASHED_STRONG,
					DataRequirement::ALWAYS,
					null,
					false,
					false
				);

				$group->addElement(
					\Delight\PrivacyPolicy\Data\DataType::USER_NAME_ALIAS,
					DataRequirement::OPT_IN,
					null,
					true,
					false
				);

				$group->addElement(
					\Delight\PrivacyPolicy\Data\DataType::USER_REGISTRATION_DATETIME,
					DataRequirement::ALWAYS,
					null,
					false,
					false
				);

				$group->addElement(
					\Delight\PrivacyPolicy\Data\DataType::USER_LOGIN_DATETIME,
					DataRequirement::ALWAYS,
					null,
					false,
					false
				);
			}
		);

		$policy->addDataGroup(
			'Server logs',
			'Whenever you access our services, including your access of any individual part or section of our services, we record certain information about the nature of your access. That information is never combined with information from other data sources and will not be associated with the identity of any account. However, we reserve the right to review the data retrospectively if there is specific evidence supporting the suspicion of a case of fraud or any other illegal activity or illegal use of our services.',
			[ DataPurpose::ADMINISTRATION ],
			DataRequirement::ALWAYS,

			function (DataGroup $group) {
				$retentionTimeHours = 24 * 14;

				$group->addElement(
					\Delight\PrivacyPolicy\Data\DataType::ACCESS_HTTP_METHOD,
					DataRequirement::ALWAYS,
					$retentionTimeHours,
					false,
					false
				);

				$group->addElement(
					\Delight\PrivacyPolicy\Data\DataType::ACCESS_HTTP_STATUS,
					DataRequirement::ALWAYS,
					$retentionTimeHours,
					false,
					false
				);

				$group->addElement(
					\Delight\PrivacyPolicy\Data\DataType::ACCESS_IP_ADDRESS,
					DataRequirement::ALWAYS,
					$retentionTimeHours,
					false,
					false
				);

				$group->addElement(
					\Delight\PrivacyPolicy\Data\DataType::ACCESS_REFERER,
					DataRequirement::ALWAYS,
					$retentionTimeHours,
					false,
					false
				);

				$group->addElement(
					\Delight\PrivacyPolicy\Data\DataType::ACCESS_SIZE,
					DataRequirement::ALWAYS,
					$retentionTimeHours,
					false,
					false
				);

				$group->addElement(
					\Delight\PrivacyPolicy\Data\DataType::ACCESS_DATETIME,
					DataRequirement::ALWAYS,
					$retentionTimeHours,
					false,
					false
				);

				$group->addElement(
					\Delight\PrivacyPolicy\Data\DataType::ACCESS_URL,
					DataRequirement::ALWAYS,
					$retentionTimeHours,
					false,
					false
				);

				$group->addElement(
					\Delight\PrivacyPolicy\Data\DataType::ACCESS_USERAGENT_STRING,
					DataRequirement::ALWAYS,
					$retentionTimeHours,
					false,
					false
				);
			}
		);

		echo $app->view(
			'privacy_policy.html',
			[
				'htmlSource' => $policy->toHtml()
			]
		);
	}

	public static function getHelp(App $app) {
		echo $app->view('help.html');
	}

}
