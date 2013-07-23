<?php
class Yamp_Session_Helper_Data extends Yamp_Core_Model_Object
{
	/**
	 * session object
	 * @var Yamp_Core_Model_Object
	 */
	private $_sessionData = NULL;
	
	
	
	/**
	 * construct
	 */
	public function _construct()
	{
		Profiler::start("Yamp_Session_Helper_Data::_construct");
		
		// add raw data to object
		$this->_sessionData = $this->setData($_SESSION);
		
		// enable "magic" on all values
		foreach( $_SESSION as $key => $value )
		{
			if( strpos($key, "messagesContainer") === false )
			{
				$this->setData(strtolower($key), $value);
			}
		}
		
		return Profiler::stop("Yamp_Session_Helper_Data::_construct");
	}
	
	
	
    /*
	** public
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
			return $this;
		}
		
		if( $this->hasData($key) )
		{
			return $this->getData($key);
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
	
	/**
	 * set a single session parameter
	 * @param string $key
	 * @param mixed $value
	 * @return Yamp_Session_Helper_Data
	 */
	public function setParam($key, $value)
	{
		$this->setData($key, $value);
		$_SESSION[$key] = $value;
		
		return $this;
	}
	
	/**
	 * alias of setParam()
	 * @param string $key
	 * @param mixed $value
	 * @return Yamp_Session_Helper_Data
	 */
	public function setParameter($key, $value)
	{
		return $this->setParam($key, $value);
	}
	
	/**
	 * add an entry to a session array
	 * @param string $key
	 * @param mixed $value
	 * @return Yamp_Session_Helper_Data
	 */
	public function addParam($key, $value)
	{
		$data = $this->getData($key);
		
		if( is_array($data) )
		{
			$data[] = $value;
			$this->setData($key, $data);
			$_SESSION[$key] = $data;
		}
		else
		{
			$array = array($data, $value);
			$this->setData($key, $array);
			$_SESSION[$key] = $array;
		}
		
		return $this;
	}
	
	/**
	 * aliat of addParam()
	 * @param string $key
	 * @param mixed $value
	 * @return Yamp_Session_Helper_Data
	 */
	public function addParameter($key, $value)
	{
		return $this->addParam($key, $value);
	}
	
	
	
	/*
	** protected
	*/


	/**
	 * stop magic setter and use session function
	 * @param string $key
	 * @param mixed $value
	 * @return bool
	 */
	protected function beforeSet($key, $value)
	{
		if( is_string($key) )
		{
			$_SESSION[$key] = $value;
		}
		
		return true;
	}
}
