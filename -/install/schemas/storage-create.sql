SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE TABLE `ewma_storage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `module_namespace` varchar(255) NOT NULL,
  `node_path` varchar(255) NOT NULL,
  `node_instance` varchar(255) NOT NULL,
  `data` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
