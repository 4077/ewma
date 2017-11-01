SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE TABLE `ewma_access_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `system` tinyint(1) NOT NULL,
  `system_type` enum('GUESTS','REGISTERED') COLLATE utf8_unicode_ci DEFAULT NULL,
  `name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `ewma_access_groups_permissions` (
  `group_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `ewma_access_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `module_namespace` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `path_segment` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `path` text COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `ewma_access_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `login` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `phone` char(11) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `pass` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('NONE','REGISTRATION','RESTORING') COLLATE utf8_unicode_ci NOT NULL,
  `sent_pass` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sent_pass_datetime` datetime NOT NULL,
  `token` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `session_key` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `restore_key` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `restore_key_time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login_phone` (`login`,`phone`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `ewma_access_users_groups` (
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `ewma_access_users_permissions` (
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `mode` enum('MERGE','DIFF') COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
