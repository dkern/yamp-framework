<?php
class Yamp_Core_Model_Request
{
	/**
	 * name of the router GET parameter
	 * @var string
	 */
	private $routerName = "__router";

	/**
	 * name of the controller GET parameter
	 * @var string
	 */
	private $controllerName = "__controller";

	/**
	 * name of the action GET parameter
	 * @var string
	 */
	private $actionName = "__action";

	/**
	 * name of the parameter GET parameter
	 * @var string
	 */
	private $parameterName = "__parameter";

	/**
	 * module router information
	 * @var array
	 */
	private $routerInformation = NULL;
	
	/**
	 * object of all get parameters
	 * @var Yamp_Core_Model_Object
	 */
	protected $_getData = NULL;
	
	/**
	 * object of all post parameters
	 * @var Yamp_Core_Model_Object
	 */
	protected $_postData = array();

	/**
	 * object of all server parameters
	 * @var Yamp_Core_Model_Object
	 */
	protected $_serverData = array();

	/**
	 * object of all routing informations
	 * @var Yamp_Core_Model_Object
	 */
	protected $_routingData = array();

	/**
	 * router to module buffer
	 * @var array
	 */
	private $_routerModule = array();
	
	
	
	/*
	** construct
	*/
	
	
	
	/**
	 * construct
	 */
	public function _construct()
	{
		Profiler::start("Yamp_Core_Model_Request::_construct");
		
		// create parameter array
		$registeredParameter = array("router"     => $this->routerName, 
		                             "controller" => $this->controllerName, 
		                             "action"     => $this->actionName, 
		                             "parameter"  => $this->parameterName);
		
		// add raw data to object
		$this->_getData = yamp::getModel("core/object")->setData($_GET);
		$this->_postData = yamp::getModel("core/object")->setData($_POST);
		$this->_serverData = yamp::getModel("core/object")->setData($_SERVER);
		$this->_routingData = yamp::getModel("core/object");
		
		// enable "magic" on all values
		foreach( $_GET as $key => $value )
		{
			if( !in_array($key, $registeredParameter) )
			{
				$this->_getData->setData(strtolower($key), $value);
			}
			else
			{
				$this->_getData->unsetData($key);
				$this->_routingData->setData($key, $value);
				
				// add url parameter to object
				if( $key == $this->parameterName )
				{
					$value = trim($value, "/");
					$parameter = explode("/", $value);
					$count = count($parameter);
					
					for( $i = 0; $i < $count; $i = $i + 2)
					{
						if( $i + 1 < $count )
							$this->_getData->setData($parameter[$i], $parameter[($i + 1)]);
					}
				}
			}
		}
		
		foreach( $_POST as $key => $value )
		{
			$this->_postData->setData(strtolower($key), $value);
		}
		
		foreach( $_SERVER as $key => $value )
		{
			$this->_serverData->setData(strtolower($key), $value);
		}
		
		// unset browser data
		$_GET = array();
		$_POST = array();
		
		Profiler::stop("Yamp_Core_Model_Request::_construct");
	}
	
	
	
    /*
	** get
	*/
	
	
	
	/**
	 * get parameter object or value of the object
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function getParam($key = NULL, $default = NULL)
	{
		if( is_null($key) )
		{
			return $this->_getData;
		}
		
		if( $this->_getData->hasData($key) )
		{
			return $this->_getData->getData($key);
		}
		
		return $default;
	}
	
	/**
	 * alias of getParam()
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function getParameter($key = NULL, $default = NULL)
	{
		return $this->getParam($key, $default);
	}
	
	
	
	/*
	** post
	*/
	
	
	
	/**
	 * check if current request is a post request
	 * @return boolean
	 */
	public function isPost()
	{
		if( $this->getServer("REQUEST_METHOD", "GET") == "POST" )
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * get post object or value of the object
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function getPost($key = NULL, $default = NULL)
	{
		if( is_null($key) )
		{
			return $this->_postData;
		}
		
		if( $this->_postData->hasData($key) )
		{
			return $this->_postData->getData($key);
		}
		
		return $default;
	}
	
	
	
	/*
	** server
	*/
	
	
	
	/**
	 * get server object or value of the object
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function getServer($key = NULL, $default = NULL)
	{
		if( is_null($key) )
		{
			return $this->_serverData;
		}
		
		if( $this->_serverData->hasData($key) )
		{
			return $this->_serverData->getData($key);
		}
		
		return $default;
	}
	
	
	
	/*
	** routing
	*/
	
	
	
    /**
     * Get module name of currently used router
     * @param string $router
     * @return  string
     */
    public function getRouterModule($router = NULL)
    {
	    Profiler::start("Yamp_Core_Model_Request::getRouterModule");
	    
	    if( is_null($router) )
	    {
		    $router = $this->getRouterName();
	    }
	    
	    // if result is already in buffer
	    if( isset($this->_routerModule[$router]) )
	    {
		    return Profiler::stop("Yamp_Core_Model_Request::getRouterModule", $this->_routerModule[$router]);
	    }
	    
	    // check all router informations
        foreach( $this->getRouterInformation() as $alias => $routers )
	        foreach( $routers as $_router )
		        if( $_router == $router )
		        {
			        $this->_routerModule[$router] = $alias;
			        return Profiler::stop("Yamp_Core_Model_Request::getRouterModule", $alias);
		        }
	    
	    return Profiler::stop("Yamp_Core_Model_Request::getRouterModule", false);
	}
	
	/**
	 * retrieve the router name
	 * @return string
	 */
	public function getRouterName()
	{
		$router = $this->_routingData->getData($this->routerName);
		
		if( !$router )
		{
			return "core";
		}
		
		return $router;
	}
	
	/**
	 * retrieve the controller name
	 * @return string
	 */
	public function getControllerName()
	{
		$controller = $this->_routingData->getData($this->controllerName);

		if( !$controller )
		{
			return "index";
		}

		return $controller;
	}
	
	/**
	 * retrieve the action name
	 * @return string
	 */
	public function getActionName()
	{
		$action = $this->_routingData->getData($this->actionName);

		if( !$action )
		{
			return "index";
		}

		return $action;
	}
	
	/**
	 * retrieve the whole parameter string
	 * @return string
	 */
	public function getParameterString()
	{
		$parameter = $this->_routingData->getData($this->parameterName);

		if( !$parameter )
		{
			return "";
		}

		return $parameter;
	}
	
	/**
	 * get module router configuration
	 * @return array
	 */
	private function getRouterInformation()
	{
		Profiler::start("Yamp_Core_Model_Request::getRouterInformation");
		
		if( !$this->routerInformation )
		{
			$this->routerInformation = array();
			
			foreach( yamp::registry("_module") as $alias => $data )
				if( isset($data["routers"]["router"]) )
					if( is_array($data["routers"]["router"]) )
						$this->routerInformation[$alias] = $data["routers"]["router"];
					else
						$this->routerInformation[$alias] = array($data["routers"]["router"]);
		}
		
		return Profiler::stop("Yamp_Core_Model_Request::getRouterInformation", $this->routerInformation);
	}
}
