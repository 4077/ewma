SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE TABLE `ewma_handlers_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `cat_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `source_id` int(11) NOT NULL,
  `source_used` tinyint(1) NOT NULL,
  `position` int(11) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `type` enum('OUTPUT','HANDLER','VALUE','VAR','INPUT') NOT NULL,
  `required` tinyint(1) NOT NULL,
  `cache_enabled` tinyint(1) NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ewma_handlers_cats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ewma_handlers_cats_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
