<?php
$installer = $this->getInstaller();

// create config table
$installer->run("DROP TABLE IF EXISTS " . Yamp_Core_Helper_Tables::coreCache);
$installer->run("CREATE TABLE IF NOT EXISTS " . Yamp_Core_Helper_Tables::coreCache . " (
				`cache_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`cache_key` varchar(255) NOT NULL,
				`ident` varchar(255) NOT NULL,
				`timestamp` int(10) unsigned NOT NULL,
				`lifetime` int(10) unsigned NOT NULL,
				`content` text,
				PRIMARY KEY (`cache_id`),
				UNIQUE KEY `UNQ_CORE_CACHE_KEY_IDENT` (`cache_key`, `ident`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");
