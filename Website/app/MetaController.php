<?php

/*
 * MovieContentFilter (https://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

namespace App;

use Delight\Foundation\App;
use Delight\PrivacyPolicy\Data\DataBasis;
use Delight\PrivacyPolicy\Data\DataGroup;
use Delight\PrivacyPolicy\Data\DataPurpose;
use Delight\PrivacyPolicy\Data\DataRequirement;
use Delight\PrivacyPolicy\Data\DataType;
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

		$policy->setPublishedAt(1550698066);
		$policy->setCanonicalUrl($app->url('/privacy'));
		$policy->setContactUrl($app->url('/contact'));

		$policy->setUserDataTraded(false);
		$policy->setDataMinimizationGoal(true);
		$policy->setChildrenMinimumAge(16);
		$policy->setPromotionalEmailOptOut(false);
		$policy->setFirstPartyCookies(true);
		$policy->setThirdPartyCookies(false);
		$policy->setAccountDeletable(false);
		$policy->setPreservationInBackups(true);
		$policy->setThirdPartyServiceProviders(true);
		$policy->setInternationalTransfers(true);
		$policy->setTransferUponMergerOrAcquisition(true);
		$policy->setTlsEverywhere(true);
		$policy->setCompetentSupervisoryAuthority($_ENV['COM_MOVIECONTENTFILTER_DATA_PROTECTION_SUPERVISORY_AUTHORITY_NAME'], $_ENV['COM_MOVIECONTENTFILTER_DATA_PROTECTION_SUPERVISORY_AUTHORITY_URL']);
		$policy->setNotificationPeriod(30);
		$policy->setRightOfAccess(true);
		$policy->setRightToRectification(true);
		$policy->setRightToErasure(true);
		$policy->setRightToRestrictProcessing(true);
		$policy->setRightToDataPortability(true);
		$policy->setRightToObject(true);
		$policy->setRightsRelatedToAutomatedDecisions(true);

		$policy->addDataGroup(
			'Account information',
			'When you create an account by signing up, and whenever you use that account by signing in afterwards, we collect the data that you provide to us voluntarily in the course of that process.',
			[
				DataBasis::CONTRACT,
				DataBasis::LEGITIMATE_INTERESTS,
			],
			null,
			[
				DataPurpose::ADMINISTRATION,
				DataPurpose::FULFILLMENT,
				DataPurpose::PERSONALIZATION
			],
			DataRequirement::OPT_IN,

			function (DataGroup $group) {
				$group->addElement(
					DataType::USER_EMAIL,
					DataRequirement::ALWAYS,
					null
				);

				$group->addElement(
					DataType::USER_PASSWORD_HASHED_STRONG,
					DataRequirement::ALWAYS,
					null
				);

				$group->addElement(
					DataType::USER_NAME_ALIAS,
					DataRequirement::OPT_IN,
					null
				);

				$group->addElement(
					DataType::USER_ACCESS_PRIVILEGES,
					DataRequirement::ALWAYS,
					null
				);

				$group->addElement(
					DataType::USER_EMAIL_VERIFIED,
					DataRequirement::ALWAYS,
					null
				);

				$group->addElement(
					DataType::USER_PASSWORD_RESETTABLE,
					DataRequirement::ALWAYS,
					null
				);

				$group->addElement(
					DataType::USER_REGISTRATION_DATETIME,
					DataRequirement::ALWAYS,
					null
				);

				$group->addElement(
					DataType::USER_LOGIN_DATETIME,
					DataRequirement::ALWAYS,
					null
				);
			}
		);

		$policy->addDataGroup(
			'Server logs',
			'Whenever you access our services, including your access of any individual part or section of our services, we record certain information about the nature of your access. That information is never combined with information from other data sources and will not be associated with the identity of any account. However, we reserve the right to review the data retrospectively if there is specific evidence supporting the suspicion of a case of fraud or any other illegal activity or illegal use of our services.',
			[ DataBasis::LEGITIMATE_INTERESTS ],
			null,
			[ DataPurpose::ADMINISTRATION ],
			DataRequirement::ALWAYS,

			function (DataGroup $group) {
				$retentionTimeHours = 24 * 14;

				$group->addElement(
					DataType::ACCESS_HTTP_METHOD,
					DataRequirement::ALWAYS,
					$retentionTimeHours
				);

				$group->addElement(
					DataType::ACCESS_HTTP_STATUS,
					DataRequirement::ALWAYS,
					$retentionTimeHours
				);

				$group->addElement(
					DataType::ACCESS_IP_ADDRESS,
					DataRequirement::ALWAYS,
					$retentionTimeHours
				);

				$group->addElement(
					DataType::ACCESS_REFERER,
					DataRequirement::ALWAYS,
					$retentionTimeHours
				);

				$group->addElement(
					DataType::ACCESS_SIZE,
					DataRequirement::ALWAYS,
					$retentionTimeHours
				);

				$group->addElement(
					DataType::ACCESS_DATETIME,
					DataRequirement::ALWAYS,
					$retentionTimeHours
				);

				$group->addElement(
					DataType::ACCESS_URL,
					DataRequirement::ALWAYS,
					$retentionTimeHours
				);

				$group->addElement(
					DataType::ACCESS_USERAGENT_STRING,
					DataRequirement::ALWAYS,
					$retentionTimeHours
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

	public static function showContactInformation(App $app) {
		echo $app->view(
			'contact.html',
			[
				'imageUrlFull' => $_ENV['COM_MOVIECONTENTFILTER_CONTACT_IMAGE_URL_FULL'],
				'imageWidthFull' => $_ENV['COM_MOVIECONTENTFILTER_CONTACT_IMAGE_WIDTH_FULL'],
				'imageAlt' => $_ENV['COM_MOVIECONTENTFILTER_CONTACT_IMAGE_ALT'],
				'audioUrlFull' => $_ENV['COM_MOVIECONTENTFILTER_CONTACT_AUDIO_URL_FULL']
			]
		);
	}

	public static function getHelp(App $app) {
		echo $app->view('help.html');
	}

}
