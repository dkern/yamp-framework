<?php
define("DS", DIRECTORY_SEPARATOR);
define("PS", PATH_SEPARATOR);
define("BP", dirname(dirname(__FILE__)));

final class Yamp extends YampRegistry
{
    /**
     * if debug mode is enabled
     * @var boolean
     */
    static private $_debug = false;

    /**
     * is autoloader enabled
     * @var boolean
     */
    static private $_autoloader = false;

	/**
	 * class identifier cache
	 * @var array
	 */
	static private $_identifier = array();

	/**
	 * class rewrite cache
	 * @var array
	 */
	static private $_rewrites = array();

	/**
	 * registered observers
	 * @var array
	 */
	static private $_observer = array();

	/**
	 * framework start time
	 * @var integer
	 */
	static private $_start = 0;

	/**
	 * framework end time
	 * @var integer
	 */
	static private $_end = 0;



    /*
    ** startup
    */



    /**
     * start framework
     * @return void
     */
    public static function run()
    {
	    // get starting time
	    self::$_start = microtime(true);
	    
        // set basic identifier data
        self::$_identifier["block"] = array();
	    self::$_identifier["controller"] = array();
        self::$_identifier["helper"] = array();
	    self::$_identifier["model"] = array();
	    
        // registered module names
        parent::init();
	    
        // start autoloader
        self::startAutoloader();
	    
        // debug and profiling
        self::load("Yamp_Core_Model_Profiler");
        if( self::isDebugMode() ) Profiler::enable();
	    Profiler::start("Yamp::run");
	    
	    // caching
	    if( !self::initCache() )
		
		// look for modules waiting for setup
		self::getHelper("setup")->startSetup();
	    
	    // start session and register session handler
	    session_set_save_handler(array(self::getSingleton("session/handler"), "open"),
								 array(self::getSingleton("session/handler"), "close"),
								 array(self::getSingleton("session/handler"), "read"),
								 array(self::getSingleton("session/handler"), "write"),
								 array(self::getSingleton("session/handler"), "destroy"),
								 array(self::getSingleton("session/handler"), "gc"));
	    
	    register_shutdown_function("session_write_close");
	    
	    session_save_path("var/session/");
	    session_name("Yamp_Framework");
	    session_start();
	    
	    // start translator before routing
	    self::getHelper("translator")->setSystemLanguage();
	    
	    // start controller routing
	    self::startRouting();

	    // get end time
	    self::$_end = microtime(true);
	    
        Profiler::stop("Yamp::run");
    }

	/**
	 * initialize core cache
	 * @return boolean
	 */
	private static function initCache()
	{
		// caching
		$cache = self::getHelper("cache");
		$core = self::getHelper("core");
		$coreCache = true;
		$modulesCache = true;

		// initialize core cache
		if( !$cache->initializeCoreCache() )
		{
			$coreCache = false;
			$core->readCoreConfig();
		}

		// initialize module cache
		if( !$cache->initializeModuleCache() )
		{
			$modulesCache = false;
			$core->readModulesConfig();
		}

		// initialize rewrite cache
		if( ! $cache->initializeRewriteCache() )
		{
			self::loadRewrites();
		}
		
		// initialize observer cache
		if( ! $cache->initializeEventCache() )
		{
			self::registerEvents();
		}
		
		return $coreCache && $modulesCache;
	}
	
    /**
     * set debug mode
     * @param boolean $debug
     * @return void
     */
    public static function setDebugMode($debug)
    {
        self::$_debug = $debug;
    }

    /**
     * get debug mode
     * @return boolean
     */
    public static function isDebugMode()
    {
        return (boolean)self::$_debug || config::debug;
    }



    /*
    ** autoloader
    */



    /**
     * register autoloader
     * @return void
     */
    public static function startAutoloader()
    {
        if( !self::$_autoloader )
        {
            spl_autoload_extensions(".php");
            spl_autoload_register("self::autoloader");
	        
            self::$_autoloader = true;
        }
    }

    /**
     * autoloader function
     * @param string $className
     * @return void
     */
    public static function autoloader($className)
    {
	    if( $className == "Profiler" ) return;
	    
        $file = str_replace("_", DS, $className) . ".php";
	    
        if( substr($file, 0, 4) !== "Yamp" )
        {
            $file = "modules" . DS . $file;
        }
	    else
	    {
		    $file = str_replace("Yamp", "core", $file);
	    }
	    
        if( file_exists($file) )
        {
            include_once($file);
	        
            if( !class_exists($className) && !interface_exists($className) )
            {
				self::throwException("file loaded, but class '" . $className . "' not found");
            }
        }
        else
        {
            self::throwException("file '" . $file . "' not found");
        }
    }

    /**
     * include a class manually by name
     * @param string $className
     * @return void
     */
    public static function load($className)
    {
        self::autoloader($className);
    }

	/**
	 * load rewrite informations from modules
	 * @return void
	 */
	private static function loadRewrites()
	{
		Profiler::start("Yamp::loadRewrites");
		
		self::$_rewrites = array();
		
		foreach( self::$_registry["_module"] as $modul )
			if( isset($modul["rewrites"]) )
				foreach( $modul["rewrites"] as $source => $destination )
					self::$_rewrites[$source] = $destination;
		
		self::getHelper("cache")->cacheRewrite(self::$_rewrites);
		Profiler::stop("Yamp::loadRewrites");
		return;
	}

	/**
	 * set rewrite informations
	 * @param array $rewrites
	 * @return boolean
	 */
	public static function setRewrites($rewrites)
	{
		if( is_array($rewrites) && count(self::$_rewrites) == 0 )
		{
			foreach( $rewrites as $source => $destination )
				if( !empty($source) && !empty($destination) )
					self::$_rewrites[$source] = $destination;
			
			return true;
		}
		
		return false;
	}
	
	
	
    /*
    ** getter
    */


	/**
	 * get current framework runtime
	 * @return float
	 */
	public static function getCurrentRuntime()
    {
	    if( self::$_end > 0 )
	    {
		    return round((self::$_end - self::$_start), 4);
	    }
	    
	    return round((microtime(true) - self::$_start), 4);
    }
	
	/**
     * get a new model instance by identifier
     * @param string $identifier
     * @param mixed $argument
     * @return object
     */
    public static function getModel($identifier, $argument = NULL)
    {
        $className = self::getModelClassName($identifier);

        if( !is_null($argument) )
        {
            $class = new $className($argument);

            if( method_exists($class, "_construct") )
            {
                $class->_construct($argument);
            }

            return $class;
        }
        else
        {
            $class = new $className();

            if( method_exists($class, "_construct") )
            {
                $class->_construct();
            }

            return $class;
        }
    }

    /**
     * get a single instance of a model
     * @param string $identifier
     * @return object
     */
    public static function getSingleton($identifier)
    {
        $className = self::getModelClassName($identifier);
        $registryKey = "_singleton/" . $className;

        if( !self::registry($registryKey) )
        {
            self::register($registryKey, self::getModel($identifier));
        }
	    
	    return self::registry($registryKey);
    }

    /**
     * retrieve model class name
     * @param string $identifier
     * @return string
     */
    public static function getModelClassName($identifier)
    {
        return self::getClassName($identifier, "model");
    }

    /**
     * get a single helper instance by identifier
     * @param string $identifier
     * @return object
     */
    public static function getHelper($identifier)
    {
        $className = self::getHelperClassName($identifier);
        $registryKey = "_singleton/" . $className;
	    
        if( !self::registry($registryKey) )
        {
	        $class = new $className();
	        
	        if( method_exists($class, "_construct") )
	        {
		        $class->_construct();
	        }
	        
            self::register($registryKey, $class);
        }

        return self::registry($registryKey);
    }

    /**
     * retrieve helper class name
     * @param string $identifier
     * @return string
     */
    public static function getHelperClassName($identifier)
    {
        return self::getClassName($identifier, "helper");
    }

	/**
	 * get a new block instance by identifier
	 * @param string $identifier
	 * @return object
	 */
	public static function getBlock($identifier)
	{
		$className = self::getBlockClassName($identifier);
		$class = new $className();

		$identifier = trim($identifier);
		$identifier = strtolower($identifier);
		$identifier = explode("/", $identifier);

		$alias = $identifier[0];
		$block = $identifier[1];

		$class->setBlockModule($alias);
		$class->setBlockName($block);

		if( method_exists($class, "_construct") )
		{
			$class->_construct();
		}
		
		return $class;
	}

	/**
	 * retrieve block class name
	 * @param string $identifier
	 * @return string
	 */
	public static function getBlockClassName($identifier)
	{
		return self::getClassName($identifier, "block");
	}
	
    /**
     * retrieve helper class name
     * @param string $identifier
     * @param string $type
     * @return string
     */
    public static function getClassName($identifier, $type)
    {
        $identifier = trim($identifier);
        $identifier = strtolower($identifier);

        // check identifier cache
        if( isset(self::$_identifier[$type][$identifier]) )
        {
            return self::$_identifier[$type][$identifier];
        }

        // validate identifier
        if( $type == "helper" )
        {
            if( strpos($identifier, "/") === false && strpos($identifier, "_") !== false )
            {
                self::throwException("invalid class identifier '" . $identifier . "'");
            }

            if( strpos($identifier, "/") === false )
            {
                $identifier .= "/data";
            }
        }
        else
        {
            if( strpos($identifier, "/") === false )
            {
                self::throwException("invalid class identifier '" . $identifier . "'");
            }
        }

        $identifierParts = explode("/", $identifier);
	    
        if( !parent::isInRegistry("_module/" . $identifierParts[0]) )
        {
	        self::throwException("unkown alias name '" . $identifierParts[0] . "'");
        }
	    
        // build class name
	    $registry = self::registry("_module/" . $identifierParts[0]);
        self::$_identifier[$type][$identifier]  = $registry["name"];
        self::$_identifier[$type][$identifier] .= "_" . ucfirst($type);

        foreach( explode("_", $identifierParts[1]) as $helperName )
        {
            self::$_identifier[$type][$identifier] .= "_";
            self::$_identifier[$type][$identifier] .= ucfirst($helperName);
        }
	    
	    // check for existing class rewrite
	    if( isset(self::$_rewrites[self::$_identifier[$type][$identifier]]) )
	    {
		    self::$_identifier[$type][$identifier] = self::$_rewrites[self::$_identifier[$type][$identifier]];
	    }
	    
        return self::$_identifier[$type][$identifier];
    }



	/*
	** routing
	*/
	
	
	
	/**
	 * try to rout to correct controller
	 * @return void
	 */
	public static function startRouting()
	{
		Profiler::start("Yamp::startRouting");

		$request = self::getSingleton("core/request");
		
		if( ($alias = $request->getRouterModule()) !== false )
		{
			$call = $alias . "/" . $request->getControllerName();
			$controller = self::getControllerClassName($call);
			$action = $request->getActionName() . "Action";
			
			// controller file exists
			if( file_exists(self::getModulDir($alias, "Controller") . ucfirst($request->getControllerName()) . ".php") )
			{
				// load controller
				$controller = new $controller();
	
				if( method_exists($controller, "_construct") )
				{
					$controller->_construct();
				}
				
				if( method_exists($controller, $action) )
				{
					$data = array("class" => $call, "router" => $request->getRouterName(), "controller" => $request->getControllerName(), "action" => $request->getActionName());
					
					self::dispatch("before_prepare_layout", $data);
					$controller->prepareLayout();
					self::dispatch("after_prepare_layout", $data);

					self::dispatch("before_action", $data);
					$controller->beforeAction($request->getActionName());
					$controller->$action();
					$controller->afterAction($request->getActionName());
					self::dispatch("after_action", $data);
					
					Profiler::stop("Yamp::startRouting");
	
					return;
				}
			}
		}

		self::showHttpErrorPage(404);
		Profiler::stop("Yamp::startRouting");
	}

	/**
	 * retrieve controller class name
	 * @param string $identifier
	 * @return string
	 */
	public static function getControllerClassName($identifier)
	{
		return self::getClassName($identifier, "controller");
	}
	
	
	
	/*
	** events / observer
	*/

	

	/**
	 * dispatch an event to all registered listener
	 * @param string $event
	 * @param mixed $data
	 * @return void
	 */
	public static function dispatch($event, $data = NULL)
	{
		if( isset(self::$_observer[$event]) && is_array(self::$_observer[$event]) )
		{
			foreach( self::$_observer[$event] as $observer )
			{
				Profiler::start("DISPATCH EVENT " . $event . ": " . $observer["class"]);
				
				$observerClass = self::getSingleton($observer["class"]);
				$observerClass->$observer["call"]($event, $data);
				
				Profiler::stop("DISPATCH EVENT " . $event . ": " . $observer["class"]);
			}
		}

		return;
	}
	
	/**
	 * register all event listener from modules
	 * @return void
	 */
	public static function registerEvents()
	{
		foreach( self::$_registry["_module"] as $alias => $module )
			if( isset($module["events"]) && is_array($module["events"]) )
				foreach( $module["events"] as $event => $observer )
					if( isset($observer["model"]) && isset($observer["call"]) )
					{
						$class = $alias . "/" . strtolower($observer["model"]);
						self::$_observer[$event][] = array("class" => $class, "call" => $observer["call"]);
					}
		
		self::getHelper("cache")->cacheEvents(self::$_observer);
	}

	/**
	 * set event informations
	 * @param array $events
	 * @return boolean
	 */
	public static function setEvents($events)
	{
		if( is_array($events) && count(self::$_observer) == 0 )
		{
			self::$_observer = $events;
			return true;
		}

		return false;
	}



	/*
    ** path & url
    */

	
	
	/**
     * retrieve application root absolute path
     * @param string $sub
     * @return string
     */
    public static function getBaseDir($sub = NULL)
    {
        $base = "";

        if( !is_null($sub) )
        {
            $sub = trim($sub, DS);
            $sub = trim($sub, "/");

            $base .= $sub . DS;
        }
	    
        return YAMP_ROOT . DS . $base;
    }

	/**
	 * retrieve module root folder
	 * @param string $alias
	 * @param string $sub
	 * @return string
	 */
	public static function getModulDir($alias, $sub = NULL)
	{
		$module = parent::registry("_module/" . $alias);
		$path = NULL;
		
		if( !is_null($module) )
		{
			$path .= YAMP_ROOT . DS;
			$name = $module["name"];
			
			if( substr($name, 0, 4) === "Yamp" )
			{
				$path .= "core" . DS;
				$path .= substr($name, 5) . DS;
			}
			else
			{
				$path .= "modules" . DS;
				
				foreach( explode("_", $name) as $part )
				{
					$path .= $part . DS;
				}
			}
			
			if( !is_null($sub) )
			{
				$path .= $sub;
				
				if( substr($path, -1) != "/" && substr($path, -1) != DS )
				{
					$path .= DS;
				}
			}
		}
		
		return $path;
	}

	/**
	 * retrieve module root url
	 * @param string $alias
	 * @param string $sub
	 * @return string
	 */
	public static function getModulUrl($alias, $sub = NULL)
	{
		$module = parent::registry("_module/" . $alias);
		$url = self::getBaseUrl();
		
		if( !is_null($module) )
		{
			$name = $module["name"];
			
			if( substr($name, 0, 4) === "Yamp" )
			{
				$url .= "core" . "/";
				$url .= substr($name, 5) . "/";
			}
			else
			{
				$url .= "modules" . "/";

				foreach( explode("_", $name) as $part )
				{
					$url .= $part . "/";
				}
			}
			
			if( !is_null($sub) )
			{
				$url .= $sub;
			}
		}
		
		return $url;
	}
	
	/**
	 * get base url
	 * @param string $sub
	 * @return string
	 */
	public static function getBaseUrl($sub = NULL)
	{
		if( !is_null($sub) )
		{
			$sub = trim($sub, "/");
		}
		
		return config::baseUrl . $sub;
	}

	/**
	 * get image url
	 * @param string $image
	 * @param string $module
	 * @return string
	 */
	public static function getImageUrl($image, $module = NULL)
	{
		$url = "";
		
		if( !is_null($module) )
		{
			$url = self::getModulUrl($module, "template/images");
			
			if( $url === config::baseUrl )
			{
				$url = self::getBaseUrl("template/images");
			}
		}
		else
		{
			$url = self::getBaseUrl("template/images");
		}
		
		$url .= "/" . $image;
		
		return $url;
	}
	
	/**
	 * get an url with additional parts
	 * @param string $identifier
	 * @param array $parameter
	 * @return string
	 */
	public static function getUrl($identifier, $parameter = array())
	{
		$identifier = self::parseUrlIdentifier($identifier);
		$_parameter = "";
		
		foreach( $parameter as $name => $value )
		{
			$_parameter .= "/" . $name . "/" . $value;
		}
		
		return config::baseUrl . $identifier . $_parameter;
	}

	/**
	 * parse an url identifier and get full path
	 * @param string $identifier
	 * @param boolean $asArray
	 * @return string|array
	 */
	public static function parseUrlIdentifier($identifier, $asArray = false)
	{
		// return from buffer if available
		if( isset(self::$_identifier["url"][$identifier]) )
		{
			return self::$_identifier["url"][$identifier];
		}
		
		$request = self::getSingleton("core/request");
		
		$router = Yamp_Core_Model_Request::DEFAULT_ROUTER;
		$controller = "index";
		$action = "index";
		
		if( strpos($identifier, "/") !== false )
		{
			$parts = explode("/", $identifier, 3);
			$_router = $parts[0];
			$_controller = $parts[1];
			$_action = isset($parts[2]) ? $parts[2] : NULL;
			
			// router
			if( !empty($_router) )
				$router = $_router == "*" ? $request->getRouterName() : $_router;
			
			// controller
			if( !empty($_controller) )
				$controller = ($_controller == "*") ? $request->getControllerName() : $_controller;
			
			// action
			if( !empty($_action) )
				$action = ($_action == "*") ? $request->getActionName() : $_action;
		}
		else if( !empty($identifier) )
		{
			$router = $identifier == "*" ? $request->getRouterName() : $identifier;
		}
		
		$url = array("router" => $router, "controller" => $controller, "action" => $action);
		self::$_identifier["url"][$identifier] = $url;
		
		if( $asArray )
		{
			return $url;
		}
		
		return $url["router"] . "/" . $url["controller"] . "/". $url["action"];
	}
	
	
	
    /*
    ** logging & exceptions
    */

	

	/**
	 * print out an error page by http response code
	 * @param integer $responseCode
	 * @return void
	 */
	public static function showHttpErrorPage($responseCode = 0)
	{
		$controller = self::getControllerClassName("core/error");
		$action = "http" . $responseCode . "Action";
		
		// load controller
		$controller = new $controller();
		
		if( method_exists($controller, "_construct") )
		{
			$controller->_construct();
		}

		$controller->prepareLayout();
		$controller->$action();
		
		return;
	}
	
	/**
     * add a new entry to log file
     * @param string $message
     * @param integer $level
     * @param string $file
     * @return void
     */
    public static function log($message, $level = NULL, $file = NULL)
    {
        $model = self::getSingleton("log/log");
        $model::log($message, $level, $file);
    }

    /**
     * return new exception by module to be thrown
     * @param string $message
     * @param string $module
     * @return Yamp_Core_Model_Exception
     */
    public static function exception($message, $module = "core")
    {
        $className = $module . "/exception";
        return self::getModel($className, $message);
    }

    /**
     * throw a new exception and log message
     * @param string $message
     * @param string $module
     * @throws Yamp_Core_Model_Exception
     * @return void
     */
    public static function throwException($message, $module = "core")
    {
        $e = self::exception($message, $module);

        self::logException($e);
        self::printException($e);

        throw $e;
    }

    /**
     * write exception to single report log file
     * @param Exception $ex
     * @return Yamp_Core_Model_Exception
     */
    public static function logException(Exception $ex)
    {
        $model = self::getSingleton("log/log");
        $model::logException($ex);

        return $ex;
    }

    /**
     * display exception
     * @param Exception $ex
     * @return void
     */
    public static function printException(Exception $ex)
    {
        if( self::isDebugMode() )
        {
            echo "<pre>";
            echo $ex->getMessage();
            echo "\n\n";
            echo $ex->getTraceAsString();
            echo "</pre>";

            die();
        }
    }
}
