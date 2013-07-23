<?php
class Yamp_Core_Controller_Abstract
{
	/**
	 * session instance
	 * @var Yamp_Session_Helper_Data
	 */
	private $_session = NULL;

	/**
	 * messages instance
	 * @var Yamp_Session_Model_Messages
	 */
	private $_messages = NULL;

	/**
	 * request instance
	 * @var Yamp_Core_Model_Request
	 */
	private $_request = NULL;
	
	/**
	 * response instance
	 * @var Yamp_Core_Model_Response
	 */
	private $_response = NULL;

	/**
	 * connection instance
	 * @var Yamp_Database_Model_Connection
	 */
	private $_connection = NULL;

	/**
	 * translator instance
	 * @var Yamp_Translator_Model_Translator
	 */
	private $_translator = NULL;
	
	/**
	 * layout instance
	 * @var Yamp_Layout_Model_Layout
	 */
	private $_layout = NULL;



	/*
	** public
	*/
	
	
	
	/**
	 * prepare layout, will called before every action
	 * @return Yamp_Core_Controller_Abstract
	 */
	public function prepareLayout()
	{
		return $this;
	}

	/**
	 * called before every action
	 * @param string $action
	 * @return Yamp_Core_Controller_Abstract
	 */
	public function beforeAction($action)
	{
		return $this;
	}

	/**
	 * called after every action
	 * @param string $action
	 * @return Yamp_Core_Controller_Abstract
	 */
	public function afterAction($action)
	{
		return $this;
	}
	
	
	
	/*
	** protected
	*/



	/**
	 * get a new model instance by identifier
	 * @param string $identifier
	 * @param mixed $argument
	 * @return object
	 */
	protected function getModel($identifier, $argument = NULL)
	{
		return yamp::getModel($identifier, $argument);
	}

	/**
	 * get a single instance of a model
	 * @param string $identifier
	 * @return object
	 */
	protected function getSingleton($identifier)
	{
		return yamp::getSingleton($identifier);
	}

	/**
	 * get a single helper instance by identifier
	 * @param string $identifier
	 * @return object
	 */
	protected function getHelper($identifier)
	{
		return yamp::getHelper($identifier);
	}

	/**
	 * get a new block instance by identifier
	 * @param string $identifier
	 * @return object
	 */
	protected function getBlock($identifier)
	{
		return yamp::getBlock($identifier);
	}
	
	/**
	 * get request object
	 * @return Yamp_Session_Helper_Data
	 */
	protected function getSession()
	{
		if( !$this->_session )
		{
			$this->_session = yamp::getHelper("session");
		}
		
		return $this->_session; 
	}
	
	/**
	 * get request object
	 * @return Yamp_Session_Model_Messages
	 */
	protected function getMessages()
	{
		if( !$this->_messages )
		{
			$this->_messages = yamp::getSingleton("session/messages");
		}

		return $this->_messages;
	}
	
	/**
	 * get request object
	 * @return Yamp_Core_Model_Request
	 */
	protected function getRequest()
	{
		if( !$this->_request )
		{
			$this->_request = yamp::getSingleton("core/request");
		}

		return $this->_request;
	}
	
	/**
	 * get response object
	 * @return Yamp_Core_Model_Response
	 */
	protected function getResponse()
	{
		if( !$this->_response )
		{
			$this->_response = yamp::getSingleton("core/response");
		}

		return $this->_response;
	}

	/**
	 * get a default database connection
	 * @return Yamp_Database_Model_Connection
	 */
	protected function getConnection()
	{
		if( !$this->_connection )
		{
			$this->_connection = yamp::getSingleton("database/connection");
		}

		return $this->_connection;
	}

	/**
	 * get translator object instance
	 * @return Yamp_Translator_Model_Translator
	 */
	protected function getTranslator()
	{
		if( !$this->_translator )
		{
			$this->_translator = yamp::getHelper("translator")->getTranslator();
		}
		
		return $this->_translator;
	}
	
	/**
	 * get template layout object
	 * @return Yamp_Layout_Model_Layout
	 */
	protected function getLayout()
	{
		if( !$this->_layout )
		{
			$this->_layout = yamp::getSingleton("layout/layout");
		}

		return $this->_layout;
	}
	
	/**
	 * translate a text
	 * @return mixed
	 */
	protected function __()
	{
		$args = func_get_args();
		return call_user_func_array(array($this->getTranslator(), "__"), $args);
	}

	/**
	 * translate a text
	 * @return mixed
	 */
	protected function t()
	{
		$args = func_get_args();
		return call_user_func_array(array($this->getTranslator(), "t"), $args);
	}
	
	/**
	 * retrieve application root absolute path
	 * @param string $sub
	 * @return string
	 */
	protected function getBaseDir($sub = NULL)
	{
		return yamp::getBaseDir($sub);
	}

	/**
	 * retrieve module root folder
	 * @param string $alias
	 * @param string $sub
	 * @return string
	 */
	protected function getModulDir($alias, $sub = NULL)
	{
		return yamp::getModulDir($alias, $sub);
	}

	/**
	 * get base url
	 * @param string $sub
	 * @return string
	 */
	protected function getBaseUrl($sub = NULL)
	{
		return yamp::getBaseUrl($sub);
	}

	/**
	 * get image url
	 * @param string $image
	 * @param string $module
	 * @return string
	 */
	public function getImageUrl($image, $module = NULL)
	{
		return yamp::getImageUrl($image, $module);
	}
	
	/**
	 * get an url with additional parts
	 * @param string $identifier
	 * @param array $parameter
	 * @return string
	 */
	protected function getUrl($identifier, $parameter = array())
	{
		return yamp::getUrl($identifier, $parameter);
	}

	/**
	 * redirect to another location
	 * @param string $identifier
	 * @return void
	 */
	protected function redirect($identifier)
	{
		$this->getResponse()->redirect($identifier);
	}

	/**
	 * redirect to another address
	 * @param string $url
	 * @return void
	 */
	protected function redirectUrl($url)
	{
		$this->getResponse()->redirectUrl($url);
	}

	/**
	 * forward call to anoter router, controller or action
	 * @param string $router
	 * @param string $controller
	 * @param string $action
	 * @return void
	 */
	protected function forward($router, $controller = NULL, $action = NULL)
	{
		Profiler::start("Yamp_Core_Controller_Abstract::forward");
		
		// call again if only router is set
		if( empty($controller) && empty($action) && strpos($router, "/") !== false )
		{
			$parts = explode("/", $router);

			if( count($parts) == 3 )
			{
				$this->forward($parts[0], $parts[1], $parts[2]);
			}
			else if( count($parts) == 2 )
			{
				$this->forward($parts[0], $parts[1]);
			}
			
			return;
		}

		$parts = yamp::parseUrlIdentifier($router . "/" . $controller . "/" . $action, true);
		$called = $this->getRequest()->getRouterName() . "/" . $this->getRequest()->getControllerName() . "/" . $this->getRequest()->getActionName();
		$routed = $parts["router"] . "/" . $parts["controller"] . "/" . $parts["action"];
		
		// start routing
		if( $routed != $called )
		{
			$module = $this->getRequest()->getRouterModule($parts["router"]);

			if( $module !== false )
			{
				$className = yamp::getControllerClassName($module . "/" . $parts["controller"]);
				$action = $parts["action"] . "Action";

				yamp::dispatch("before_controller_forward", array("router" => $parts["router"], "controller" => $parts["controller"], "action" => $parts["action"]));
				
				// load controller
				$class = new $className();

				if( method_exists($class, "_construct") )
				{
					$controller->_construct();
				}

				if( method_exists($class, $action) )
				{
					$class->$action();
				}
				else
				{
					yamp::throwException("action '" . $parts["action"] . "' not found in controller class '" . $className . "'");
				}

				yamp::dispatch("after_controller_forward", array("class" => $className, "action" => $action));
			}
		}
		Profiler::stop("Yamp_Core_Controller_Abstract::forward");
		return;
	}
	
	/**
	 * get module of this class (even extendet)
	 * @return boolean
	 */
	protected function getCurrentModule()
	{
		$class = explode("_", get_called_class());
		$name = $class[0] . "_" . $class[1];
		
		
		foreach( yamp::registry("_module") as $module )
		{
			if( $name == $module["name"] )
			{
				return $module["alias"];
			}
		}
		
		return false;
	}
}