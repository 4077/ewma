SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE TABLE `ewma_routers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `position` int(11) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ewma_routers_routes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `router_id` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  `listen` tinyint(1) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `name` varchar(255) NOT NULL,
  `pattern` varchar(255) NOT NULL,
  `target_type` enum('METHOD','HANDLERS_OUTPUT') NOT NULL,
  `target_method_path` varchar(255) NOT NULL,
  `target_method_data` text NOT NULL,
  `target_handlers_output_id` int(11) NOT NULL,
  `response_wrapper` enum('NONE','EWMA_HTML') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
