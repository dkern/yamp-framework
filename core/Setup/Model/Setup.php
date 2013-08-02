<?php
class Yamp_Setup_Model_Setup extends Yamp_Core_Model_Abstract
{
	/**
	 * sql connection instance
	 * @var Yamp_Database_Model_Database
	 */
	private $sql;
	
	/**
	 * check if setup instance is ready and database is available
	 * @return boolean
	 */
	public function checkIsSystemReady()
	{
		Profiler::start("Yamp_Setup_Model_Setup::checkIsSystemReady");
		
		$this->sql = $this->getConnection()->getWrite();
		
		// check if database is available
		if( $this->getHelper("database")->databaseAvailable() )
		{
			$config = yamp::registry("_module/setup");
			
			// check if setup system is installed allready
			if( $this->getHelper("database")->tableExists(Yamp_Core_Helper_Tables::coreResource) )
			{
				// if setup is enabled
				if( isset($config["setup"]["enabled"]) && $config["setup"]["enabled"] )
				{
					$result = $this->sql->select("setup_version")
										->from("{DB}.{PRE}" . Yamp_Core_Helper_Tables::coreResource)
										->where("name = ?", $config["name"])
										->limit(1)
										->run()
										->fetch();
					
					if( count($result) == 1 )
					{
						if( $result[0]["setup_version"] >= $config["setup"]["version"] )
						{
							return Profiler::stop("Yamp_Setup_Model_Setup::checkIsSystemReady", true);
						}
					}
				}
			}
			
			// install setup module
			yamp::log("start install " . $config["name"]);
			
			if( $this->installModule($config) )
			{
				$this->sql->insert("{DB}.{PRE}" . Yamp_Core_Helper_Tables::coreResource)
						  ->fields("name", "setup_version")
					      ->values($config["name"], $config["setup"]["version"])
					      ->onDuplicate("setup_version = VALUES(setup_version)")
					      ->run();
				
				return Profiler::stop("Yamp_Setup_Model_Setup::checkIsSystemReady", true);
			}
		}

		return Profiler::stop("Yamp_Setup_Model_Setup::checkIsSystemReady", false);
	}
	
	/**
	 * run all available setup files for a module
	 * @param array $data
	 * @return boolean
	 */
	public function installModule($data)
	{
		Profiler::start("Yamp_Setup_Model_Setup::installModule");
		
		// check if module has setup information and is enabled
		if( !empty($data) && isset($data["setup"]["version"]) && isset($data["setup"]["installed"]) && $data["setup"]["enabled"] )
		{
			// if install is needed
			if( $data["setup"]["version"] > $data["setup"]["installed"] )
			{
				$actual = $data["setup"]["installed"];
				$until = $data["setup"]["version"];
				$versions = array();
			
				$folder = yamp::getModulDir($data["alias"], "setup");
				
				// get all available install scripts
				foreach( glob($folder . "install_*.*.php") as $file )
				{
					if( preg_match("/install_([0-9]+\.[0-9]+).php/Uis", $file, $matched) )
					{
						$versions[] = $matched[1];
					}
				}
				
				sort($versions);
				
				$installer = $this->getModel("setup/installer");
				$installed = true;
				
				foreach( $versions as $version )
				{
					// run only needed versions
					if( $version > $actual && $version <= $until )
					{
						$file = $folder . "install_" . $version .".php";
						
						yamp::log("run install script: " . $file);
						$check = $installer->start($file);
						
						// if everything went fine update database entry
						if( $check )
						{
							$actual = $version;
							$this->sql->insert("{DB}.{PRE}" . Yamp_Core_Helper_Tables::coreResource)
								      ->fields("name", "setup_version", "installed_version")
								      ->values($data["name"], $version, $version)
								      ->onDuplicate("setup_version = VALUES(setup_version), installed_version = VALUES(installed_version)")
								      ->run();
						}
						else
						{
							$installed = false;
							break;
						}
					}
				}
				
				return Profiler::stop("Yamp_Setup_Model_Setup::installModule", $installed);
			}
		}

		return Profiler::stop("Yamp_Setup_Model_Setup::installModule", false);
	}
}
