<?php
class Yamp_Setup_Helper_Data extends Yamp_Core_Helper_Abstract
{
	/**
	 * start system setup
	 * @return void
	 */
	public function startSetup()
	{
		Profiler::start("Yamp_Setup_Helper_Data::startSetup");
		
		if( $this->getHelper("database")->databaseAvailable() )
		{
			$setup = $this->getModel("setup/setup");
			$sql = $this->getConnection()->getWrite();
	
			// get all registered modules
			$modules = yamp::registry("_module");
			
			// if nothing is to do, stop here
			if( !$this->isInstallWaiting($modules) )
			{
				Profiler::stop("Yamp_Setup_Helper_Data::startSetup");
				return;
			}
			
			// check if setup system is ready
			if( $setup->checkIsSystemReady() )
			{	
				$_updated = 0;
				$_core = array();
				$_modules = array();
				
				foreach( $modules as $module )
				{
					// check if module has a setup and is active
					if( isset($module["setup"]) && $module["setup"]["enabled"] && $module["name"] != "Yamp_Setup" )
					{
						// if an update is needed
						if( $module["setup"]["installed"] < $module["setup"]["version"] )
						{
							// run install for module
							yamp::log("start install module " . $module["name"]);
							
							if( $setup->installModule($module) )
							{
								// update version numbers
								$module["setup"]["installed"] = $module["setup"]["version"];
	
								$sql->insert("{DB}.{PRE}" . tables::coreResource)
									->fields("name", "setup_version")
									->values($module["name"], $module["setup"]["version"])
									->onDuplicate("setup_version = VALUES(setup_version)")
									->run();
							}
	
							++$_updated;
						}
					}
					
					if( substr($module["name"], 0, 5) == "Yamp_" )
					{
						if( $module["name"]== "Yamp_Setup" )
						{
							$result = $sql->select("setup_version")
										  ->from("{DB}.{PRE}" . tables::coreResource)
										  ->where("name = ?", "Yamp_Setup")
										  ->run()
										  ->fetch();
							
							if( count($result) == 1 )
							{
								$module["setup"]["installed"] = $result[0]["setup_version"];
							}
						}
	
						$_core[$module["alias"]] = $module;
					}
					else
					{
						$_modules[$module["alias"]] = $module;
					}
				}
				
				// renew cached data
				if( $_updated > 0 )
				{
					$this->getHelper("cache")->cacheCore($_core, true);
					$this->getHelper("cache")->cacheModules($_modules, true);
				}
			}
		}
		
		Profiler::stop("Yamp_Setup_Helper_Data::startSetup");
	}

	/**
	 * check if at least one module need an install
	 * @param array $modules
	 * @return boolean
	 */
	private function isInstallWaiting($modules)
	{
		Profiler::start("Yamp_Setup_Helper_Data::isInstallWaiting");
		
		foreach( $modules as $module )
		{
			if( isset($module["setup"]) )
			{
				if( $module["setup"]["enabled"] && $module["setup"]["installed"] < $module["setup"]["version"]  )
				{
					return Profiler::stop("Yamp_Setup_Helper_Data::isInstallWaiting", true);
				}
			}
		}
		
		return Profiler::stop("Yamp_Setup_Helper_Data::isInstallWaiting", false);
	}
}