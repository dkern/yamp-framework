<?php
class Yamp_Cache_Model_Cache extends Yamp_Core_Model_Abstract
{
	/**
	 * cache handler 
	 * @var Yamp_Cache_Model_File | Yamp_Cache_Model_Database
	 */
	private $handler;
	
	/**
	 * cache lifetime
	 * @var int
	 */
	private $cacheLifetime = 3600;
	
	/**
	 * cache key
	 * @var string
	 */
	private $cacheKey = "cache";
	
	
	
	/*
	** construct
	*/


	
	/**
	 * construct
	 */
	public function _construct()
	{
		Profiler::start("Yamp_Cache_Model_Cache::_construct");
		
		// check if database handler is available
		if( !config::cacheForceFile && $this->getHelper("database")->databaseAvailable() )
		{
			if( $this->getHelper("database")->tableExists(Yamp_Core_Helper_Tables::coreCache) )
			{
				$this->handler = $this->getModel("cache/database");
				
				if( $this->handler->init() )
				{
					return Profiler::stop("Yamp_Cache_Model_Cache::_construct");
				}
			}
		}
		
		// fallback to file handler
		$this->handler = $this->getModel("cache/file");
		$this->handler->init();

		return Profiler::stop("Yamp_Cache_Model_Cache::_construct");
	}



	/*
	** getter & setter
	*/
	
	
	
	/**
	 * set the cache lifetime
	 * @param int $lifetime
	 * @return Yamp_Cache_Model_Cache
	 */
	public function setCacheLifetime($lifetime)
	{
		if( is_numeric($lifetime) )
		{
			$this->cacheLifetime = $lifetime;
		}
		
		return $this;
	}
	
	/**
	 * get the current cache lifetime
	 * @return int
	 */
	public function getCacheLifetime()
	{
		return $this->cacheLifetime;
	}	
	
	/**
	 * set the cache key
	 * @param string $key
	 * @return Yamp_Cache_Model_Cache
	 */
	public function setCacheKey($key)
	{
		$this->cacheKey = $key;
		return $this;
	}
	
	/**
	 * get the current cache key
	 * @return string
	 */
	public function getCacheKey()
	{
		return $this->cacheKey;
	}
	
	
	
	/*
	** public
	*/
	
	
	
	/**
	 * set a cache with data
	 * @param string $identifier
	 * @param string $data
	 * @param boolean $override
	 * @return boolean
	 */
	public function setCache($identifier, $data, $override = false)
	{
		if( $this->handler )
		{
			$key = $this->getCacheKey();
			$lifetime = $this->getCacheLifetime();
			
			return $this->handler->setCache($key, $identifier, $lifetime, $data, $override);
		}
		
		return false;
	}
	
	/**
	 * get the cache data by key
	 * @param string $identifier
	 * @return mixed
	 */
	public function getCache($identifier)
	{
		if( $this->handler )
		{
			$key = $this->getCacheKey();
			return $this->handler->getCache($key, $identifier);
		}
		
		return false;
	}

	/**
	 * remove a specific cache
	 * @param string $key
	 * @param string $identifier
	 * @return bool
	 */
	public function remove($key = null, $identifier = null)
	{
		if( $this->handler )
		{
			if( is_null($key) )
			{
				$key = $this->getCacheKey();
			}

			return $this->handler->remove($key, $identifier);
		}

		return false;
	}

	/**
	 * cleanup cache
	 * @return boolean
	 */
	public function cleanup()
	{
		if( $this->handler )
		{
			return $this->handler->cleanup();
		}
		
		return false;
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
		if( $this->handler )
		{
			return call_user_func_array(array(&$this->handler, $name), $arguments);
		}
		
		return false;
	}
}
