<?php

/*
 * MovieContentFilter (http://www.moviecontentfilter.com/)
 * Copyright (c) delight.im (https://www.delight.im/)
 * Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)
 */

// define and execute the routes
$app->get('/', '\App\MetaController::welcome');
$app->get('/view/:id', '\App\WorkController::showWork');
$app->get('/browse', '\App\BrowsingController::showOverview');
$app->get('/browse/:type', '\App\BrowsingController::showCategory');
$app->get('/download/:id', '\App\FilterController::customizeDownload');
$app->post('/download/:id', '\App\FilterController::sendDownload');
$app->get('/contribute/:id', '\App\AnnotationController::launchEditor');
$app->post('/contribute/:id', '\App\AnnotationController::receiveFromEditor');
$app->get('/preferences', '\App\PrefsController::showOverview');
$app->get('/preferences/:topicId', '\App\PrefsController::showTopic');
$app->post('/preferences/:topicId', '\App\PrefsController::saveTopic');
$app->get('/sign-up', '\App\AuthController::showSignUp');
$app->post('/sign-up', '\App\AuthController::saveSignUp');
$app->get('/add', '\App\WorkController::prepareWork');
$app->post('/add', '\App\WorkController::saveWork');
$app->post('/login', '\App\AuthController::processLogin');
$app->get('/logout', '\App\AuthController::logout');
$app->get('/specification', '\App\MetaController::showSpecification');

// otherwise fail with "not found"
\App\Controller::failNotFound($app);
