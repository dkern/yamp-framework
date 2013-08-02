<?php
$installer = $this->getInstaller();

// create config table
$installer->run("DROP TABLE IF EXISTS " . Yamp_Core_Helper_Tables::coreSession);
$installer->run("CREATE TABLE IF NOT EXISTS " . Yamp_Core_Helper_Tables::coreSession . " (
				`session_id` varchar(255) NOT NULL,
				`session_time` int(10) unsigned NOT NULL DEFAULT '0',
				`session_data` mediumblob NOT NULL,
				PRIMARY KEY (`session_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
