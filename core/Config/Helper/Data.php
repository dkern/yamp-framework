<?php
class Yamp_Config_Helper_Data extends Yamp_Core_Model_Object
{
	/**
	 * sql connection instance
	 * @var Yamp_Database_Model_Database
	 */
	private $sql;
	
	
	
	/*
	** config getter / setter
	*/
	
	
	
	/**
	 * retrive a config entry from the database
	 * @param string $code
	 * @return string
	 */
	public function getConfig($code)
	{
		Profiler::start("Yamp_Config_Helper_Data::getConfig");
		
		$code = strtolower($code);
		$db = $this->getHelper("database");
		
		// return from buffer
		if( $this->hasData($code) )
		{
			return Profiler::stop("Yamp_Config_Helper_Data::getConfig", $this->getData($code));
		}
		
		// get value from database
		if( $db->isDatabaseAvailable() )
		{
			$result = $this->_getConnection()
						   ->select("value")
						   ->from("{DB}.{PRE}" . tables::coreConfigData)
						   ->where("code = ?", $code)
						   ->limit(1)
						   ->run()
						   ->fetch();
			
			if( count($result) == 1 )
			{
				$this->setData($code, $result[0]["value"]);
				return Profiler::stop("Yamp_Config_Helper_Data::getConfig", $this->getData($code));
			}
		}

		return Profiler::stop("Yamp_Config_Helper_Data::getConfig", NULL);
	}
	
	/**
	 * set a configuration value
	 * @param string $code
	 * @param mixed $value
	 * @return bool
	 */
	public function setConfig($code, $value)
	{
		Profiler::start("Yamp_Config_Helper_Data::setConfig");
		
		$code = strtolower($code);
		$db = $this->getHelper("database");
		
		if( $db->isDatabaseAvailable() )
		{
			$result = $this->_getConnection()
						   ->insert("{DB}.{PRE}" . tables::coreConfigData)
						   ->fields("code", "value")
						   ->values($code, $value)
						   ->onDuplicate("value = VALUES(value)")
						   ->run(true);
			
			if( $result )
			{
				$this->setData($code, $value);
				return Profiler::stop("Yamp_Config_Helper_Data::setConfig", true);
			}
		}

		return Profiler::stop("Yamp_Config_Helper_Data::setConfig", false);
	}


	
	/*
	** object events
	*/
	
	
	
	/**
	 * event to check if value is available
	 * @param string $code
	 * @return bool
	 */
	protected function beforeGet($code)
	{
		if( !$this->hasData($code) )
		{
			$this->getConfig($code);
		}
		
		return true;
	}

	/**
	 * event to set value even to database
	 * @param string $code
	 * @param mixed $value
	 * @return boolean
	 */
	protected function beforeSet($code, $value)
	{
		return $this->setConfig($code, $value);
	}
	
	
	
	/*
	** module config
	*/
	
	
	
	/**
	 * check if a module has configuration data
	 * @param string $module
	 * @return boolean
	 */
	public function hasModulConfig($module)
	{
		Profiler::start("Yamp_Config_Helper_Data::hasModulConfig");
		
		$module = strtolower($module);
		
		if( ($data = yamp::registry("_module/" . $module)) !== NULL )
		{
			// module has config
 			if( isset($data["configuration"]) )
			{
				return Profiler::stop("Yamp_Config_Helper_Data::hasModulConfig", true);
			}
		}
		
		return Profiler::stop("Yamp_Config_Helper_Data::hasModulConfig", false);
	}
	
	/**
	 * get a configuration value from a module
	 * @param string $module
	 * @param string $name
	 * @return mixed
	 */
	public function getModulConfig($module, $name)
	{
		Profiler::start("Yamp_Config_Helper_Data::getModulConfig");
		
		$module = strtolower($module);
		
		if( ($data = yamp::registry("_module/" . $module)) !== NULL )
		{
			// module has config with given name
 			if( isset($data["configuration"][$name]) )
			{
				return Profiler::stop("Yamp_Config_Helper_Data::getModulConfig", $data["configuration"][$name]);
			}
		}

		return Profiler::stop("Yamp_Config_Helper_Data::getModulConfig", NULL);
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