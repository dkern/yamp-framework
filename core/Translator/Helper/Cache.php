<?php
class Yamp_Translator_Helper_Cache extends Yamp_Core_Helper_Abstract
{
	/**
	 * get cache instance with default options
	 * @return Yamp_Cache_Model_Cache
	 */
	public function getCacheInstance()
	{
		Profiler::start("Yamp_Translator_Helper_Cache::getCacheInstance");

		$cache = $this->getSingleton("cache/cache");

		// set basic options
		$cache->setCacheDirectory("var/cache/");
		$cache->setUseDivider(false);
		$cache->setCacheKey("language");
		$cache->setCacheLifetime(config::systemCacheLifetime);

		return Profiler::stop("Yamp_Translator_Helper_Cache::getCacheInstance", $cache);
	}
}
