<?php
class YampRegistry
{
	/**
	 * object registry
	 * @var array
	 */
	static protected $_registry = array();

	/**
	 * registry key buffer
	 * @var array
	 */
	static protected $_registryKeyBuffer = array();



	/*
	** public
	*/



	/**
	 * initial load registry
	 * @return void
	 */
	public static function init()
	{
		self::$_registry["_module"]["cache"]    = array("name" => "Yamp_Cache");
		self::$_registry["_module"]["core"]     = array("name" => "Yamp_Core");
		self::$_registry["_module"]["database"] = array("name" => "Yamp_Database");
		self::$_registry["_module"]["log"]      = array("name" => "Yamp_Log");
	}
	
	/**
	 * retrieve a value from registry by a key
	 * @param string $key
	 * @return mixed
	 */
	public static function registry($key = NULL)
	{
		// return whole registry if no key is set
		if( is_null($key) )
		{
			return self::$_registry;
		}

		// special behavior if someone call for modules
		if( $key == "_module" )
		{
			return self::$_registry[$key];
		}

		Profiler::start("YampRegistry::registry");
		$key = self::getReqistryKey($key);
		
		// return value if key is in registry
		if( self::isInRegistry($key) )
		{
			return Profiler::stop("YampRegistry::registry", self::$_registry[$key[0]][$key[1]]);
		}
		
		return Profiler::stop("YampRegistry::registry", NULL);
	}

	/**
	 * register a new enrty
	 * @param string $key
	 * @param mixed $value
	 * @param bool $graceful
	 * @throws Yamp_Core_Model_Exception
	 * @return boolean
	 */
	public static function register($key, $value, $graceful = false)
	{
		Profiler::start("YampRegistry::register");
		
		$key = self::getReqistryKey($key);

		// return error if allready exists
		if( self::isInRegistry($key) )
		{
			if( $graceful )
			{
				return Profiler::stop("YampRegistry::register", false);
			}
			
			yamp::throwException("registry key '" . $key[0] . "/" . $key[1] . "' already exists");
		}

		if( !isset(self::$_registry[$key[0]]) )
		{
			self::$_registry[$key[0]] = array();
		}
		
		self::$_registry[$key[0]][$key[1]] = $value;
		return Profiler::stop("YampRegistry::register", true);
	}

	/**
	 * Unregister a variable from register by key
	 * @param string $key
	 * @return boolean
	 */
	public static function unregister($key)
	{
		Profiler::start("YampRegistry::unregister");
		
		$key = self::getReqistryKey($key);
		
		if( self::isInRegistry($key) )
		{
			// if destructor is available call before remove from registry
			if( is_object(self::$_registry[$key[0]][$key[1]]) && (method_exists(self::$_registry[$key[0]][$key[1]], "_destruct")) )
			{
				self::$_registry[$key[0]][$key[1]]->_destruct();
			}
			
			unset(self::$_registry[$key[0]][$key[1]]);
			return Profiler::stop("YampRegistry::unregister", true);
		}

		return Profiler::stop("YampRegistry::unregister", false);
	}



	/*
	** protected
	*/



	/**
	 * check if a entry is in registry
	 * @param mixed $key
	 * @return bool
	 */
	protected static function isInRegistry($key)
	{
		if( !is_array($key) )
		{
			$key = self::getReqistryKey($key);
		}
		
		if( isset(self::$_registry[$key[0]][$key[1]]) )
		{
			return true;
		}
		
		return false;
	}

	/**
	 * @param $key
	 * @return array|bool
	 */
	protected static function getReqistryKey($key)
	{
		// don't handle arrays
		if( is_array($key) )
		{
			return $key;
		}
		
		// if in buffer return directly
		if( isset(self::$_registryKeyBuffer[$key]) )
		{
			return self::$_registryKeyBuffer[$key];
		}
		
		Profiler::start("YampRegistry::getReqistryKey");
		
		$call = array();
		
		if( ($pos = strpos($key, "/")) !== false )
		{
			$call[0] = substr($key, 0, $pos);
			$call[1] = substr($key, $pos + 1);
		}
		else
		{
			$call[0] = $key;
			$call[1] = $key;
		}
		
		self::$_registryKeyBuffer[$key] = $call;
		return Profiler::stop("YampRegistry::getReqistryKey", $call);
	}
}