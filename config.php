<?php
class config
{
	/*
	** MySQL Configuration
	*/



	/**
	 * if true the framework tries to access database
	 * @var boolean
	 */
	const useDatabase = true;

	/**
	 * use a persistent connection
	 * @var boolean
	 */
	const persistent = true;

	/**
	 * use mysqli instead of mysql
	 * @var boolean
	 */
	const mysqli = false;

	/**
	 * database hostname
	 * @var string
	 */
	const hostname = "localhost";

	/**
	 * database hostname
	 * @var string
	 */
	const port = "3306";

	/**
	 * database username
	 * @var string
	 */
	const username = "root";

	/**
	 * password to access the database
	 * @var string
	 */
	const password = "";

	/**
	 * database to select
	 * @var string
	 */
	const database = "yamp";

	/**
	 * table name prefix
	 * only necessary when you want to use prefix replace
	 * @var string
	 */
	const prefix   = "";

	/**
	 * verbose on mysql error
	 * @var boolean
	 */
	const verbose = false;



	/*
	** General Configuration
	*/



	/**
	 * enable debug mode
	 * will overwrite yamp::setDebugMode(false) on true
	 * @var boolean
	 */
	const debug = false;

	/**
	 * base system url
	 * @var string
	 */
	const baseUrl = "/";

	/**
	 * default system logging file in /var/log
	 * @var string
	 */
	const defaultLogFile = "system.log";

	/**
	 * enable module caching
	 * @var boolean
	 */
	const useSystemCache = true;

	/**
	 * lifetime of the module cache in seconds (by default one day)
	 * @var integer
	 */
	const systemCacheLifetime = 86400;

	/**
	 * if enabled session data will not stored in database
	 * @var boolean
	 */
	const sessionForceFile = false;

	/**
	 * if enabled cache data will not stored in database
	 * @var boolean
	 */
	const cacheForceFile = false;
}
