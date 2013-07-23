<?php
class Yamp_Core_Model_Object extends Yamp_Core_Model_Abstract
{
	/**
	 * internal data storage array
	 * @var array
	 */
	private $_data = array();
	
	/**
	 * if some data was changed
	 * @var boolean
	 */
	private $_hasChangedData = false;
	
	/**
	 * cache for formated names
	 * @var array
	 */
	private $_formatNameCache = array();
	
	
	
	/*
	** magic
	*/
	
	
	
	/**
	 * handle all function calls the class didn't know
	 * @param string $method
	 * @param mixed array $args
	 * @return mixed
	 */
	public function __call($method, $args)
	{
		$type = substr($method, 0, 3);
		$key  = $this->formatFunctionName(substr($method, 3));
		
		if( $type == "get" )
		{
			return $this->getData($key);
		}
		
		if( $type == "set" )
		{
			$param = isset($args[0]) ? $args[0] : NULL;
			return $this->setData($key, $param);
		}
		
		if( $type == "uns" )
		{
			return $this->unsetData($key);
		}
		
		if( $type == "has" )
		{
			return $this->hasData($key);
		}

		Yamp::throwException("Invalid method %s::%s();", get_class($this), $method);
		return false;
	}
	
	/**
	 * helperfunction to set internal data
	 * @param string $key
	 * @param mixed $value
	 * @return Yamp_Core_Model_Object
	 */
	public function setData($key, $value = NULL)
    {
		if( !$this->callEvent("beforeSet", $key, $value) )
		{
			return $this;
		}
		
		if( is_array($key) )
		{
			foreach( $key as $name => $value )
			{
				$this->_data[$name] = $value;
			}
		}
		else
		{
			$this->_data[$key] = $value;
		}
        
		$this->setDataChanged();
		$this->callEvent("afterSet", $key, $value);
		
        return $this;
    }
	
	/**
	 * helperfunction to get internal data
	 * @param string $key
	 * @return mixed
	 */
	public function getData($key = NULL)
    {
		if( !$this->callEvent("beforeGet", $key) )
		{
			return NULL;
		}
		
		if( $key == NULL )
		{
			return $this->_data;
		}
		
		if( array_key_exists($key, $this->_data) )
		{
			return $this->_data[$key];
		}
		
		$this->callEvent("afterGet", $key);
		
		return NULL;
    }
	
	/**
	 * helperfunction to unset internal data
	 * @param string $key
	 * @return Yamp_Core_Model_Object
	 */
	public function unsetData($key = NULL)
	{
		if( !$this->callEvent("beforeUns", $key) )
		{
			return $this;
		}
		
		if( $key == NULL )
		{
			$this->_data = array();
		}
		
		unset($this->_data[$key]);
        
		$this->setDataChanged();
		$this->callEvent("afterUns", $key);
		
        return $this;
	}
	
	/**
	 * helperfunction to check if data is set
	 * @param string $key
	 * @return boolean
	 */
	public function hasData($key = NULL)
	{
		if( !$this->callEvent("beforeHas", $key) )
		{
			return false;
		}
		
		if( $key == NULL )
		{
			return empty($this->_data);
		}
		
		$this->callEvent("afterHas", $key);
		
		return array_key_exists($key, $this->_data);
	}
	
	
	
	/*
	** public
	*/
	
	
	
	/**
	 * if some data is changed
	 * @return boolean
	 */
	public function hasChangedData()
	{
		return $this->_hasChangedData;
	}
	
	/**
	 * returns if some data is set
	 * @return boolean
	 */
	public function isEmpty()
    {
        if( empty($this->_data) )
		{
            return true;
        }
		
        return false;
    }
	
	
	
	/*
	** private
	*/
	
	
	
	/**
	 * formates the name of the called function
	 * @param string $name
	 * @return string
	 */
	private function formatFunctionName($name)
    {
		if( array_key_exists($name, $this->_formatNameCache) )
		{
			return $this->_formatNameCache[$name];
		}
		
		$format = preg_replace('/(.)([A-Z])/', "$1_$2", $name);
        $format = strtolower($format);
		
		$this->_formatNameCache[$name] = $format;
        return $format;
    }
	
	/**
	 * try to allocate if a given method exists and call them
	 * @param string $method
	 * @param string $param1
	 * @param mixed $param2
	 * @return boolean
	 */
	private function callEvent($method, $param1 = NULL, $param2 = NULL)
	{
		if( is_callable(array($this, $method)) && method_exists($this, $method) )
		{
			return $this->{$method}($param1, $param2);
		}
		
		return true;
	}
	
	
	
	/*
	** protected
	*/
	
	
	
	/**
	 * set the changed status
	 * @param boolean $changed
	 * @return void
	 */
	protected function setDataChanged($changed = true)
	{
		$this->_hasChangedData = $changed;
		return;
	}
	
	/**
	 * clear changed data info
	 * @return Yamp_Core_Model_Object
	 */
	protected function clearDataChanged()
	{
		$this->setDataChanged(false);
		return $this;
	}
}