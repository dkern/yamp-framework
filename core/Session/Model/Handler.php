<?php
class Yamp_Session_Model_Handler extends Yamp_Core_Helper_Abstract
{
	/**
	 * selected session handler class
	 * @var object
	 */
	private $sessionHandler = NULL;
	
	
	
	/*
	** public
	*/



	/**
	 * constructor - called before session is used
	 * @param string $savePath
	 * @param string $sessionName
	 * @return bool
	 */
	public function open($savePath, $sessionName)
	{
		Profiler::start("Yamp_Session_Model_Handler::open");
		
		// check if database handler is available
		if( !config::sessionForceFile && $this->getHelper("database")->databaseAvailable() )
		{
			if( $this->getHelper("database")->tableExists(tables::coreSession) )
			{
				$this->sessionHandler = $this->getModel("session/handler_database");
				return Profiler::stop("Yamp_Session_Model_Handler::open", $this->sessionHandler->open($savePath, $sessionName));
			}
		}
		
		// fallback to filesystem 
		$this->sessionHandler = $this->getModel("session/handler_file");
		return Profiler::stop("Yamp_Session_Model_Handler::open", $this->sessionHandler->open($savePath, $sessionName));
	}

	/**
	 * destructor - called at the session end
	 * @return bool
	 */
	public function close()
	{
		return $this->sessionHandler->close();
	}

	/**
	 * read session data by id
	 * @param string $sessionId
	 * @return string
	 */
	public function read($sessionId)
	{
		return $this->sessionHandler->read($sessionId);
	}

	/**
	 * write session data for id
	 * @param string $sessionId
	 * @param string $data
	 * @return bool
	 */
	public function write($sessionId, $data)
	{
		return $this->sessionHandler->write($sessionId, $data);
	}

	/**
	 * destroy a session entry
	 * @param string $sessionId
	 * @return bool
	 */
	public function destroy($sessionId)
	{
		return $this->sessionHandler->destroy($sessionId);
	}

	/**
	 * randomly called garbage collector
	 * @param integer $lifetime
	 * @return bool
	 */
	public function gc($lifetime)
	{
		return $this->sessionHandler->gc($lifetime);
	}
}