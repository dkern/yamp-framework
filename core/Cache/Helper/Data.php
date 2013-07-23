<?php
class Yamp_Cache_Helper_Data extends Yamp_Core_Helper_Abstract
{
	/**
	 * initialize module cache
	 * @return boolean
	 */
	public function initializeCoreCache()
	{
		Profiler::start("Yamp_Cache_Helper_Data::initializeCoreCache");
		
		// only if cache is active
		if( config::useSystemCache )
		{
			// try to get data from cache
			if( ($core = $this->getCacheInstance()->getCache("core")) !== false )
			{
				// register core mocules
				foreach( $core as $name => $data )
				{
					yamp::unregister("_module/" . $name);
					yamp::register("_module/" . $name, $data, true);
				}
				
				return Profiler::stop("Yamp_Cache_Helper_Data::initializeCoreCache", true);
			}
		}
		
		return Profiler::stop("Yamp_Cache_Helper_Data::initializeCoreCache", false);
	}

	/**
	 * renew core modules cache
	 * @param array $modules
	 * @param boolean $override
	 * @return void
	 */
	public function cacheCore($modules, $override = false)
	{
		Profiler::start("Yamp_Cache_Helper_Data::cacheCore");

		if( config::useSystemCache )
		{
			$this->getCacheInstance()->setCache("core", $modules, $override);
		}

		Profiler::stop("Yamp_Cache_Helper_Data::cacheCore");
		return;
	}
	
	/**
	 * initialize module cache
	 * @return boolean
	 */
	public function initializeModuleCache()
	{
		Profiler::start("Yamp_Cache_Helper_Data::initializeModuleCache");
		
		// only if cache is active
		if( config::useSystemCache )
		{
			// try to get data from cache
			if( ($modules = $this->getCacheInstance()->getCache("modules")) !== false )
			{
				// register modules
				foreach( $modules as $name => $data )
				{
					yamp::register("_module/" . $name, $data, true);
				}
				
				return Profiler::stop("Yamp_Cache_Helper_Data::initializeModuleCache", true);
			}
		}
		
		return Profiler::stop("Yamp_Cache_Helper_Data::initializeModuleCache", false);
	}
	
	/**
	 * renew modules cache
	 * @param array $modules
	 * @param boolean $override
	 * @return void
	 */
	public function cacheModules($modules, $override = false)
	{
		Profiler::start("Yamp_Cache_Helper_Data::cacheModules");
		
		if( config::useSystemCache )
		{
			$this->getCacheInstance()->setCache("modules", $modules, $override);
		}
		
		Profiler::stop("Yamp_Cache_Helper_Data::cacheModules");
		return;
	}

	/**
	 * initialize rewrite cache
	 * @return boolean
	 */
	public function initializeRewriteCache()
	{
		Profiler::start("Yamp_Cache_Helper_Data::initializeRewriteCache");

		// only if cache is active
		if( config::useSystemCache )
		{
			// try to get data from cache
			if( ($rewrites = $this->getCacheInstance()->getCache("rewrites")) !== false )
			{
				yamp::setRewrites($rewrites);
				return Profiler::stop("Yamp_Cache_Helper_Data::initializeRewriteCache", true);
			}
		}

		return Profiler::stop("Yamp_Cache_Helper_Data::initializeRewriteCache", false);
	}

	/**
	 * renew rewrite cache
	 * @param array $rewrites
	 * @param boolean $override
	 * @return void
	 */
	public function cacheRewrite($rewrites, $override = false)
	{
		Profiler::start("Yamp_Cache_Helper_Data::cacheRewrite");

		if( config::useSystemCache )
		{
			$this->getCacheInstance()->setCache("rewrites", $rewrites, $override);
		}

		Profiler::stop("Yamp_Cache_Helper_Data::cacheRewrite");
		return;
	}

	/**
	 * initialize event cache
	 * @return boolean
	 */
	public function initializeEventCache()
	{
		Profiler::start("Yamp_Cache_Helper_Data::initializeEventCache");

		// only if cache is active
		if( config::useSystemCache )
		{
			// try to get data from cache
			if( ($events = $this->getCacheInstance()->getCache("events")) !== false )
			{
				yamp::setEvents($events);
				return Profiler::stop("Yamp_Cache_Helper_Data::initializeEventCache", true);
			}
		}

		return Profiler::stop("Yamp_Cache_Helper_Data::initializeEventCache", false);
	}

	/**
	 * renew event cache
	 * @param array $events
	 * @param boolean $override
	 * @return void
	 */
	public function cacheEvents($events, $override = false)
	{
		Profiler::start("Yamp_Cache_Helper_Data::cacheEvents");

		if( config::useSystemCache )
		{
			$this->getCacheInstance()->setCache("events", $events, $override);
		}

		Profiler::stop("Yamp_Cache_Helper_Data::cacheEvents");
		return;
	}
	
	
	
	/*
	** private
	*/
	
	
	
	/**
	 * get cache instance with default options
	 * @return Yamp_Cache_Model_Cache
	 */
	private function getCacheInstance()
	{
		Profiler::start("Yamp_Cache_Helper_Data::getCacheInstance");
		
		$cache = $this->getSingleton("cache/cache");
		
		// set basic options
		$cache->setCacheDirectory("var/cache/");
		$cache->setUseDivider(false);
		$cache->setCacheKey("core");
		$cache->setCacheLifetime(config::systemCacheLifetime);
		
		return Profiler::stop("Yamp_Cache_Helper_Data::getCacheInstance", $cache);
	}
}