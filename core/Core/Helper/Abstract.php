<?php
class Yamp_Core_Helper_Abstract
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
	 * connection instance
	 * @var Yamp_Database_Model_Connection
	 */
	private $_connection = NULL;

	/**
	 * translator instance
	 * @var Yamp_Translator_Model_Translator
	 */
	private $_translator = NULL;



	/*
	** public
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
