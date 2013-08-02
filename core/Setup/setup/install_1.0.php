<?php
$installer = $this->getInstaller();

// create config table
$installer->run("DROP TABLE IF EXISTS " . Yamp_Core_Helper_Tables::coreResource);
$installer->run("CREATE TABLE IF NOT EXISTS " . Yamp_Core_Helper_Tables::coreResource . " (
				`name` varchar(50) NOT NULL,
				`setup_version` varchar(50) DEFAULT NULL,
				`installed_version` varchar(50) DEFAULT NULL,
				PRIMARY KEY (`name`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
