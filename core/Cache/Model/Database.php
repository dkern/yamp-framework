<?php
class Yamp_Cache_Model_Database extends Yamp_Core_Model_Abstract implements Yamp_Cache_Model_Handler
{
	/**
	 * sql instance
	 * @var Yamp_Database_Model_Database
	 */
	private $sql;



	/*
	** public
	*/
	
	
	
	/**
	 * initialize cache handler
	 * @return boolean
	 */
	public function init()
	{
		Profiler::start("Yamp_Cache_Model_Database::init");
		
		$this->sql = $this->getConnection()->getWrite();
		return Profiler::stop("Yamp_Cache_Model_Database::init", true);
	}
	
	/**
	 * set data to the cache by key and identidier
	 * @param string $key
	 * @param string $identifier
	 * @param integer $lifetime
	 * @param mixed $data
	 * @param boolean $override
	 * @return mixed
	 */
	public function setCache($key, $identifier, $lifetime, $data, $override = false)
	{
		Profiler::start("Yamp_Cache_Model_Database::setCache");
		
		// make database friendly value
		$serialized = serialize($data);
		$serialized = base64_encode($serialized);
		
		// select existing cache entry
		$result = $this->sql->select()
							->from("{DB}.{PRE}" . Yamp_Core_Helper_Tables::coreCache)
							->where("cache_key = ?", $key)
							->where("ident = ?", $identifier)
							->limit(1)
							->run()
							->fetch();
		
		$query = $this->sql->insert("{DB}.{PRE}" . Yamp_Core_Helper_Tables::coreCache)
						   ->ignore()
						   ->fields("cache_key", "ident", "timestamp", "lifetime", "content")
						   ->values($key, $identifier, time(), $lifetime, $serialized);
		
		// no cache in database found
		if( count($result) == 0 )
		{
			if( $query->run(true) )
			{
				return Profiler::stop("Yamp_Cache_Model_Database::setCache", true);
			}
		}
		
		// cache found
		else if( count($result) == 1 )
		{
			$query->onDuplicate("timestamp = VALUES(timestamp), lifetime = VALUES(lifetime), content = VALUES(content)");
			
			// cache actual
			if( $this->activeCache($result[0]["timestamp"], $result[0]["lifetime"]) )
			{
				// override cached data
				if( $override )
				{
					if( $query->run(true) )
					{
						return Profiler::stop("Yamp_Cache_Model_Database::setCache", true);
					}
				}
			}
			
			// cached data too old
			else
			{
				if( $query->run(true) )
				{
					return Profiler::stop("Yamp_Cache_Model_Database::setCache", true);
				}
			}
		}
		
		return Profiler::stop("Yamp_Cache_Model_Database::setCache", false);
	}

	/**
	 * get a cache entry by key and identifier
	 * @param string $key
	 * @param string $identifier
	 * @return mixed
	 */
	public function getCache($key, $identifier)
	{
		Profiler::start("Yamp_Cache_Model_Database::getCache");
		
		// select cache entry
		$result = $this->sql->select()
							->from("{DB}.{PRE}" . Yamp_Core_Helper_Tables::coreCache)
							->where("cache_key = ?", $key)
							->where("ident = ?", $identifier)
							->run()
							->fetch();
		
		// entry found
		if( count($result) == 1 )
		{
			// is active
			if( $this->activeCache($result[0]["timestamp"], $result[0]["lifetime"]) )
			{
				// get data from value
				$data = base64_decode($result[0]["content"]);
				$data = unserialize($data);
				
				return Profiler::stop("Yamp_Cache_Model_Database::getCache", $data);
			}
		}
		
		return Profiler::stop("Yamp_Cache_Model_Database::getCache", false);
	}

	/**
	 * remove a whole cache by key or a single one by identifier
	 * @param string $key
	 * @param string $identifier
	 * @return mixed
	 */
	public function removeCache($key, $identifier = null)
	{
		Profiler::start("Yamp_Cache_Model_Database::removeCache");
		
		$query = $this->sql->delete("{DB}.{PRE}" . Yamp_Core_Helper_Tables::coreCache)
						   ->where("cache_key = ?", $key);
		
		if( !is_null($identifier) )
		{
			$query->where("ident = ?", $identifier);
		}
		
		if( $query->run(true) )
		{
			return Profiler::stop("Yamp_Cache_Model_Database::removeCache", true);
		}
		
		return Profiler::stop("Yamp_Cache_Model_Database::removeCache", false);
	}

	/**
	 * check if the cache is active with the lifetime setting
	 * @param integer $timestamp
	 * @param integer $lifetime
	 * @return boolean
	 */
	public function activeCache($timestamp, $lifetime)
	{
		if( (time() - $timestamp) <= $lifetime )
		{
			return true;
		}

		return false;
	}

	/**
	 * database cleanup
	 * @return boolean
	 */
	public function cleanup()
	{
		Profiler::start("Yamp_Cache_Model_Database::cleanup");

		$query = $this->sql->delete("{DB}.{PRE}" . Yamp_Core_Helper_Tables::coreCache)
						   ->where(time() . " - timestamp > lifetime");
		
		if( $query->run(true) )
		{
			return Profiler::stop("Yamp_Cache_Model_Database::cleanup", true);
		}

		return Profiler::stop("Yamp_Cache_Model_Database::cleanup", false);
	}



	/*
	** magic
	*/



	/**
	 * redirect unkown functions to handler
	 * @param string $name
	 * @param array $arguments
	 * @return mixed
	 */
	public function __call($name, $arguments)
	{
		if( $name == "setCacheDirectory" || $name == "setUseDivider" )
		{
			return true;
		}
		
		return false;
	}
}
