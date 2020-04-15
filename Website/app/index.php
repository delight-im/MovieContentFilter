<?php

/*
 * MovieContentFilter (https://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

// define and execute the routes
$app->get('/', '\App\MetaController::welcome');
$app->get('/works/:id', '\App\WorkController::showWork');
$app->get('/browse', '\App\BrowsingController::showOverview');
$app->get('/browse/:type', '\App\BrowsingController::showCategory');
$app->get('/works/:id/download', '\App\FilterController::customizeDownload');
$app->post('/works/:id/download', '\App\FilterController::sendDownload');
$app->get('/works/:id/contribute', '\App\AnnotationController::launchEditor');
$app->post('/works/:id/contribute', '\App\AnnotationController::receiveFromEditor');
$app->get('/works/:id/delete', '\App\WorkController::getDelete');
$app->get('/preferences', '\App\PrefsController::showOverview');
$app->get('/preferences/:topicId', '\App\PrefsController::showTopic');
$app->post('/preferences/:topicId', '\App\PrefsController::saveTopic');
$app->get('/sign-up', '\App\AuthController::showSignUp');
$app->post('/sign-up', '\App\AuthController::saveSignUp');
$app->get('/confirm/:selector/:token', '\App\AuthController::confirmEmail');
$app->get('/resend-confirmation', '\App\AuthController::getResendConfirmation');
$app->post('/resend-confirmation', '\App\AuthController::postResendConfirmation');
$app->get('/add', '\App\WorkController::prepareWork');
$app->post('/add', '\App\WorkController::saveWork');
$app->post('/login', '\App\AuthController::processLogin');
$app->get('/annotations/:id', '\App\AnnotationController::showAnnotation');
$app->post('/annotations/:id/vote/:direction', '\App\AnnotationController::voteForAnnotation');
$app->get('/works/:workId/topics/:topicId', '\App\WorkController::showTopicInWork');
$app->get('/logout', '\App\AuthController::logout');
$app->get('/settings', '\App\SettingsController::getSettings');
$app->post('/settings/change-password', '\App\SettingsController::postChangePassword');
$app->post('/settings/change-email', '\App\SettingsController::postChangeEmail');
$app->post('/settings/control-password-reset', '\App\SettingsController::postControlPasswordReset');
$app->post('/settings/sign-out-everywhere', '\App\SettingsController::postLogOutEverywhere');
$app->get('/forgot-password', '\App\AuthController::getForgotPassword');
$app->post('/forgot-password', '\App\AuthController::postForgotPassword');
$app->get('/reset/:selector/:token', '\App\AuthController::getResetPassword');
$app->post('/reset/:selector/:token', '\App\AuthController::postResetPassword');
$app->get('/specification', '\App\MetaController::showSpecification');
$app->get('/help', '\App\MetaController::getHelp');
$app->get('/privacy', '\App\MetaController::showPrivacyPolicy');
$app->get('/contact', '\App\MetaController::showContactInformation');

// otherwise fail with "not found"
\App\Controller::failNotFound($app);
