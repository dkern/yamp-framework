<?php
class Yamp_Core_Helper_Data extends Yamp_Core_Helper_Abstract
{
	/**
	 * sql connection instance
	 * @var Yamp_Database_Model_Database
	 */
	private $sql;



	/*
	** public
	*/
	
	
	
	/**
	 * load all base configurations and register names
	 * @return void
	 */
	public function readCoreConfig()
	{
		Profiler::start("Yamp_Core_Helper_Data::readCoreConfig");
		
		// hard load classes
		yamp::load("Yamp_Database_Model_Database");
		yamp::load("Yamp_Database_Helper_Data");
		
		// check if database is available
		$helper = new Yamp_Database_Helper_Data();
		$available = $helper->databaseAvailable();
		
		// open single sql connection for core modules
		$sql = false;
		$tableExists = false;
		
		if( $available )
		{
			$sql = new Yamp_Database_Model_Database();
			$sql->connect();

			// check if table exists
			$tableExists = $helper->tableExists(tables::coreResource, $sql);
		}
		
		// loop models
		$loaded = array();
		$folder = yamp::getBaseDir("core");
		
		$dir = dir($folder);
		
		// loop to all core modules
		while( $obj = $dir->read() ) 
		{
			$current = $folder . $obj;
			
			if( $obj != "." && $obj != ".." && is_dir($current) ) 
			{
				$config = $current . DS . "etc" . DS . "config.xml";
				
				// module has a config.xml
				if( file_exists($config) )
				{
					// read config.xml
					$xml = file_get_contents($config);
					$xml = yamp::getModel("core/xml")->createByXml($xml)->asObject();
					
					if( $xml->hasAlias() )
					{
						$data = $xml->getData();
						
						// allocate installed version
						if( isset($data["setup"]) )
						{
							$data["setup"]["installed"] = 0;
							
							if( $tableExists )
							{
								$result = $sql->select("setup_version")
											   ->from("{DB}.{PRE}" . tables::coreResource)
											   ->where("name = ?", $xml->getName())
											   ->limit(1)
											   ->run(false)
											   ->fetch();
								
								if( count($result) == 1 )
								{
									$data["setup"]["installed"] = $result[0]["setup_version"];
								}
							}
						}
						
						// unregister core modules before renew entry
						yamp::unregister("_module/" . $xml->getAlias());
						
						// register module
						yamp::register("_module/" . $xml->getAlias(), $data, true);
						$loaded[$xml->getAlias()] = $data;
					}
				}
			}
		}
		
		// cache data
		yamp::getHelper("cache")->cacheCore($loaded);
		
		// close sql
		if( $sql !== false )
		{
			$sql->close();
		}
		
		unset($sql);
		unset($helper);
		
		Profiler::stop("Yamp_Core_Helper_Data::readCoreConfig");
		return;
	}
	
	/**
	 * load all active modules
	 * @return void
	 */
	public function readModulesConfig()
	{
		Profiler::start("Yamp_Core_Helper_Data::readModulesConfig");
		
		
		// check if table exists
		$available = $this->getHelper("database")->databaseAvailable();
		$tableExists = false;
		
		if( $available )
		{
			$tableExists = $this->getHelper("database")->tableExists(tables::coreResource);
		}
		
		$loaded = array();
		$folder = yamp::getBaseDir("modules");
		
		$dir = dir($folder);
		
		// loop to all developers
		while( $obj = $dir->read() ) 
		{
			$current = $folder . $obj;
			
			if( $obj != "." && $obj != ".." && is_dir($current) ) 
			{
				$name = dir($current);
				
				// loop to all developer modules
				while( $modules = $name->read() ) 
				{
					$modul = $current . DS . $modules;
					
					if( $modules != "." && $modules != ".." && is_dir($modul) ) 
					{
						$config = $modul . DS . "etc" . DS . "config.xml";
						
						// module has a config.xml
						if( file_exists($config) )
						{
							// get module config.xml
							$xml = file_get_contents($config);
							$xml = yamp::getModel("core/xml")->createByXml($xml)->asObject();
							
							// module is active
							if( $xml->hasAlias() && $xml->hasActive() && (bool)$xml->getActive() )
							{
								$data = $xml->getData();
								
								// module need a setup
								if( isset($data["setup"]) )
								{
									$data["setup"]["installed"] = 0;
									
									if( $tableExists )
									{
										$result = $this->_getConnection()
													   ->select("setup_version")
													   ->from("{DB}.{PRE}" . tables::coreResource)
													   ->where("name = ?", $xml->getName())
													   ->limit(1)
													   ->run(false)
													   ->fetch();
										
										if( count($result) == 1 )
										{
											$data["setup"]["installed"] = (float)$result[0]["setup_version"];
										}
									}
								}
								
								// reguister module
								yamp::register("_module/" . $xml->getAlias(), $data);
								$loaded[$xml->getAlias()] = $data;
							}
						}
					}
				}
			}
		}
		
		// cache data
		yamp::getHelper("cache")->cacheModules($loaded);
		
		Profiler::stop("Yamp_Core_Helper_Data::readModulesConfig");
		return;
	}

	

	/*
	** private
	*/



	/**
	 * get database instance
	 * @return Yamp_Database_Model_Database
	 */
	private function _getConnection()
	{
		if( !$this->sql )
		{
			$this->sql = $this->getConnection()->getWrite();
		}

		return $this->sql;
	}
}