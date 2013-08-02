<?php
$installer = $this->getInstaller();

// create config table
$installer->run("DROP TABLE IF EXISTS " . Yamp_Core_Helper_Tables::coreConfigData);
$installer->run("CREATE TABLE IF NOT EXISTS " . Yamp_Core_Helper_Tables::coreConfigData . " (
				`config_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`path` varchar(255) NOT NULL,
				`value` text,
				PRIMARY KEY (`config_id`),
				UNIQUE KEY `UNQ_CORE_CONFIG_DATA_PATH` (`path`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8;");
