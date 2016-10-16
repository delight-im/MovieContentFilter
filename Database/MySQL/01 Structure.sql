-- MovieContentFilter (http://www.moviecontentfilter.com/)
-- Copyright (c) delight.im (https://www.delight.im/)
-- Licensed under the GNU AGPL v3 (https://www.gnu.org/licenses/agpl-3.0.txt)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE `annotations` (
  `id` int(10) UNSIGNED NOT NULL,
  `work_id` int(10) UNSIGNED NOT NULL,
  `start_position` decimal(12,11) UNSIGNED NOT NULL,
  `end_position` decimal(12,11) UNSIGNED NOT NULL,
  `category_id` smallint(5) UNSIGNED NOT NULL,
  `severity_id` tinyint(3) UNSIGNED NOT NULL,
  `channel_id` tinyint(3) UNSIGNED NOT NULL,
  `author_user_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categories` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `name` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(248) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_general` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `topic_id` tinyint(3) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `channels` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_default` tinyint(1) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `preferences` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `category_id` smallint(5) UNSIGNED NOT NULL,
  `severity_id` tinyint(3) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `severities` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `name` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `available_as_annotation` tinyint(1) UNSIGNED NOT NULL,
  `available_as_preference` tinyint(1) UNSIGNED NOT NULL,
  `label_in_preferences` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `inclusiveness` tinyint(3) UNSIGNED NOT NULL,
  `coefficient` decimal(4,3) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `topics` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `label` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(249) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verified` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `registered` int(10) UNSIGNED NOT NULL,
  `last_login` int(10) UNSIGNED DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users_confirmations` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(249) COLLATE utf8mb4_unicode_ci NOT NULL,
  `selector` varchar(16) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `token` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `expires` int(10) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users_remembered` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user` int(10) UNSIGNED NOT NULL,
  `selector` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `token` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `expires` int(10) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users_resets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user` int(10) UNSIGNED NOT NULL,
  `selector` varchar(20) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `token` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `expires` int(10) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users_throttling` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `action_type` enum('login','register','confirm_email') COLLATE utf8mb4_unicode_ci NOT NULL,
  `selector` varchar(44) CHARACTER SET latin1 COLLATE latin1_general_cs DEFAULT NULL,
  `time_bucket` int(10) UNSIGNED NOT NULL,
  `attempts` mediumint(8) UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `works` (
  `id` int(10) UNSIGNED NOT NULL,
  `type` enum('movie','series','episode') COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(248) COLLATE utf8mb4_unicode_ci NOT NULL,
  `year` smallint(4) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `imdb_url` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_user_id` int(10) UNSIGNED NOT NULL,
  `canonical_start_time` float UNSIGNED DEFAULT NULL,
  `canonical_end_time` float UNSIGNED DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `works_relations` (
  `id` int(10) UNSIGNED NOT NULL,
  `parent_work_id` int(10) UNSIGNED NOT NULL,
  `season` tinyint(2) UNSIGNED NOT NULL,
  `episode_in_season` tinyint(2) UNSIGNED NOT NULL,
  `child_work_id` int(10) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `annotations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `work_id_start_position` (`work_id`,`start_position`) USING BTREE;

ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `topic_id_is_general_label` (`topic_id`,`is_general`,`label`) USING BTREE;

ALTER TABLE `channels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

ALTER TABLE `preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id_category_id` (`user_id`,`category_id`) USING BTREE;

ALTER TABLE `severities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `available_as_annotation_id` (`available_as_annotation`,`id`),
  ADD KEY `available_as_preference_inclusiveness` (`available_as_preference`,`inclusiveness`);

ALTER TABLE `topics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `label` (`label`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `users_confirmations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `selector` (`selector`),
  ADD KEY `email_expires` (`email`,`expires`);

ALTER TABLE `users_remembered`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `selector` (`selector`),
  ADD KEY `user` (`user`);

ALTER TABLE `users_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `selector` (`selector`),
  ADD KEY `user_expires` (`user`,`expires`);

ALTER TABLE `users_throttling`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `action_type_selector_time_bucket` (`action_type`,`selector`,`time_bucket`);

ALTER TABLE `works`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `imdb_url` (`imdb_url`),
  ADD KEY `type_year_title` (`type`,`year`,`title`) USING BTREE;
ALTER TABLE `works` ADD FULLTEXT KEY `title` (`title`);

ALTER TABLE `works_relations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `child_work_id_parent_work_id` (`child_work_id`,`parent_work_id`) USING BTREE,
  ADD KEY `parent_work_id_season_episode_in_season` (`parent_work_id`,`season`,`episode_in_season`);

ALTER TABLE `annotations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `categories`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `channels`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `preferences`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `severities`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `topics`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `users_confirmations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `users_remembered`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `users_resets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `users_throttling`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `works`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `works_relations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
