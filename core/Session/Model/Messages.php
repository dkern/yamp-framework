<?php
class Yamp_Session_Model_Messages
{
	/**
	 * name of the array inside session
	 * @var string
	 */
	const MESSAGES_CONTAINER = "_messagesContainer";
	
	/**
	 * messages types
	 * @var string
	 */
	const TYPE_SUCCESS = "success";
	const TYPE_ERROR = "error";
	const TYPE_NOTICE = "notice";
	
	
	
	/*
	** construct
	*/
	
	
	
	/**
	 * construct
	 */
	public function _construct()
	{
		Profiler::start("Yamp_Core_Model_Messages::_construct");
		
		yamp::getHelper("session")->setParam(self::MESSAGES_CONTAINER . "/" . self::TYPE_SUCCESS, array());
		yamp::getHelper("session")->setParam(self::MESSAGES_CONTAINER . "/" . self::TYPE_ERROR, array());
		yamp::getHelper("session")->setParam(self::MESSAGES_CONTAINER . "/" . self::TYPE_NOTICE, array());

		Profiler::stop("Yamp_Core_Model_Messages::_construct");
	}
	
	
	
	/*
	** public
	*/


	
	/**
	 * check if messages are available
	 * @return boolean
	 */
	public function hasMessages()
	{
		if( count($this->getMessages(self::TYPE_SUCCESS)) )
		{
			return true;
		}

		if( count($this->getMessages(self::TYPE_ERROR)) )
		{
			return true;
		}

		if( count($this->getMessages(self::TYPE_NOTICE)) )
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * create and add a new success message
	 * @param string $message
	 * @return boolean
	 */
	public function addSuccess($message)
	{
		return $this->addMessage(self::TYPE_SUCCESS, $message);
	}
	
	/**
	 * create and add a new error message
	 * @param string $message
	 * @return boolean
	 */
	public function addError($message)
	{
		return $this->addMessage(self::TYPE_ERROR, $message);
	}
	
	/**
	 * create and add a new notice message
	 * @param string $message
	 * @return boolean
	 */
	public function addNotice($message)
	{
		return $this->addMessage(self::TYPE_NOTICE, $message);
	}
	
	/**
	 * add a new message to the session array
	 * @param string $type
	 * @param string $message
	 * @return boolean
	 */
	public function addMessage($type, $message)
	{
		Profiler::start("Yamp_Core_Model_Messages::addMessage");
		
		if( $type == self::TYPE_SUCCESS || $type == self::TYPE_ERROR || $type == self::TYPE_NOTICE )
		{
			yamp::getHelper("session")->addParam(self::MESSAGES_CONTAINER . "/" . $type, $message);
			return Profiler::stop("Yamp_Core_Model_Messages::addMessage", true);
		}
		
		return Profiler::stop("Yamp_Core_Model_Messages::addMessage", false);
	}
	
	/**
	 * clears all success messages
	 * @return boolean
	 */
	public function clearSuccess()
	{
		return $this->clearMessages(self::TYPE_SUCCESS);
	}
	
	/**
	 * clears all error messages
	 * @return boolean
	 */
	public function clearError()
	{
		return $this->clearMessages(self::TYPE_ERROR);
	}
	
	/**
	 * clears all notice messages
	 * @return boolean
	 */
	public function clearNotice()
	{
		return $this->clearMessages(self::TYPE_NOTICE);
	}
	
	/**
	 * clear all messages of a type
	 * @param string $type
	 * @return boolean
	 */
	public function clearMessages($type)
	{
		Profiler::start("Yamp_Core_Model_Messages::clearMessages");
		
		if( $type == self::TYPE_SUCCESS || $type == self::TYPE_ERROR || $type == self::TYPE_NOTICE )
		{
			yamp::getHelper("session")->setParam(self::MESSAGES_CONTAINER . "/" . $type, array());
			return Profiler::stop("Yamp_Core_Model_Messages::clearMessages", true);
		}

		return Profiler::stop("Yamp_Core_Model_Messages::clearMessages", false);
	}
	
	/**
	 * returns the oldest success message from a type cue and remove them
	 * @return mixed
	 */
	public function readSuccess()
	{
		return $this->readMessages(self::TYPE_SUCCESS);
	}
	
	/**
	 * returns the oldest error message from a type cue and remove them
	 * @return mixed
	 */
	public function readError()
	{
		return $this->readMessages(self::TYPE_ERROR);
	}
	
	/**
	 * returns the oldest notice message from a type cue and remove them
	 * @return mixed
	 */
	public function readNotice()
	{
		return $this->readMessages(self::TYPE_NOTICE);
	}
	
	/**
	 * returns the oldest message from a type cue and remove them
	 * @param string $type
	 * @return mixed
	 */
	public function readMessages($type = NULL)
	{
		Profiler::start("Yamp_Core_Model_Messages::readMessages");
		
		if( is_null($type) )
		{
			if( ($return = $this->readMessages(self::TYPE_SUCCESS)) !== false )
			{
				return Profiler::stop("Yamp_Core_Model_Messages::readMessages", $return);
			}
			
			if( ($return = $this->readMessages(self::TYPE_ERROR)) !== false )
			{
				return Profiler::stop("Yamp_Core_Model_Messages::readMessages", $return);
			}
			
			if( ($return = $this->readMessages(self::TYPE_NOTICE)) !== false )
			{
				return Profiler::stop("Yamp_Core_Model_Messages::readMessages", $return);
			}
		}
		else
		{
			if( $type == self::TYPE_SUCCESS || $type == self::TYPE_ERROR || $type == self::TYPE_NOTICE )
			{
				$messages = $this->getMessages($type);
				
				// checks if messages left
				if( count($messages) > 0 )
				{
					// get the oldest one
					$msg = $messages[0];
					
					// delete the oldest
					yamp::getHelper("session")->setParam(self::MESSAGES_CONTAINER . "/" . $type, array_slice($messages, 1));
					
					// return message
					return Profiler::stop("Yamp_Core_Model_Messages::readMessages", array("type" => $type, "message" => $msg));
				}
			}
		}

		return Profiler::stop("Yamp_Core_Model_Messages::readMessages", false);
	}
	
	/**
	 * get all success messages as array
	 * @return array
	 */
	public function getSuccess()
	{
		return $this->getMessages(self::TYPE_SUCCESS);
	}
	
	/**
	 * get all error messages as array
	 * @return array
	 */
	public function getError()
	{
		return $this->getMessages(self::TYPE_ERROR);
	}
	
	/**
	 * get all notice messages as array
	 * @return array
	 */
	public function getNotice()
	{
		return $this->getMessages(self::TYPE_NOTICE);
	}
	
	/**
	 * get all messages of a type as array
	 * @param $type string
	 * @return array
	 */
	public function getMessages($type)
	{
		Profiler::start("Yamp_Core_Model_Messages::getMessages");
		
		if( $type == self::TYPE_SUCCESS || $type == self::TYPE_ERROR || $type == self::TYPE_NOTICE )
		{
			$return = yamp::getHelper("session")->getParam(self::MESSAGES_CONTAINER . "/" . $type);
			return Profiler::stop("Yamp_Core_Model_Messages::getMessages", $return);
		}

		return Profiler::stop("Yamp_Core_Model_Messages::getMessages", array());
	}
}
