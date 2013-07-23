<?php
interface Yamp_Cache_Model_Handler
{
	/**
	 * initialize cache handler
	 * @return boolean
	 */
	public function init();
	
	/**
	 * set data to the cache by key and identidier
	 * @param string $key
	 * @param string $identifier
	 * @param integer $lifetime
	 * @param mixed $data
	 * @param boolean $override
	 * @return boolean
	 */
	public function setCache($key, $identifier, $lifetime, $data, $override = false);

	/**
	 * get a cache entry by key and identifier
	 * @param string $key
	 * @param string $identifier
	 * @return mixed
	 */
	public function getCache($key, $identifier);

	/**
	 * remove a whole cache by key or a single one by identifier
	 * @param string $key
	 * @param string $identifier
	 * @return boolean
	 */
	public function removeCache($key, $identifier = null);
	
	/**
	 * check if the cache is active with the lifetime setting
	 * @param integer $timestamp
	 * @param integer $lifetime
	 * @return boolean
	 */
	public function activeCache($timestamp, $lifetime);

	/**
	 * cleanup cache
	 * @return boolean
	 */
	public function cleanup();
}