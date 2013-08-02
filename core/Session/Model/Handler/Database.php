<?php
class Yamp_Session_Model_Handler_Database extends Yamp_Core_Helper_Abstract implements Yamp_Session_Model_Handler_Handler
{
	/**
	 * database object
	 * @var Yamp_Database_Model_Database
	 */
	private $sql = NULL;
	
	/**
	 * session file save path
	 * @var string
	 */
	private $savePath = NULL;
	
	/**
	 * session name
	 * @var string
	 */
	private $sessionName = NULL;
	
	
	
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
		Profiler::start("Yamp_Session_Model_Handler_Database::open");
		
		$this->savePath = $savePath;
		$this->sessionName = $sessionName;
		
		$this->sql = $this->getConnection()->getWrite();
		return Profiler::stop("Yamp_Session_Model_Handler_Database::open", true);
	}

	/**
	 * destructor - called at the session end
	 * @return bool
	 */
	public function close()
	{
		Profiler::start("Yamp_Session_Model_Handler_Database::close");
		return Profiler::stop("Yamp_Session_Model_Handler_Database::close", true);
	}
	
	/**
	 * read session data by id
	 * @param string $sessionId
	 * @return string
	 */
	public function read($sessionId)
	{
		Profiler::start("Yamp_Session_Model_Handler_Database::read");
		
		$result = $this->sql->select("session_data")
							->from("{DB}.{PRE}" . Yamp_Core_Helper_Tables::coreSession)
							->where("session_id = ?", $sessionId)
							->limit(1)
							->run()
							->fetch();
		
		if( count($result) == 1 )
		{
			$data = base64_decode($result[0]["session_data"]);
			return Profiler::stop("Yamp_Session_Model_Handler_Database::read", $data);
		}
		
		return Profiler::stop("Yamp_Session_Model_Handler_Database::read", NULL);
	}
	
	/**
	 * write session data for id
	 * @param string $sessionId
	 * @param string $sessionData
	 * @return bool
	 */
	public function write($sessionId, $sessionData)
	{
		Profiler::start("Yamp_Session_Model_Handler_Database::write");
		
		if( !empty($sessionData) )
		{
			$data = base64_encode($sessionData);
			
			$result = $this->sql->insert("{DB}.{PRE}" . Yamp_Core_Helper_Tables::coreSession)
								->fields("session_id", "session_time", "session_data")
								->values($sessionId, time(), $data)
								->onDuplicate("session_time = VALUES(session_time), session_data = VALUES(session_data)")
								->run(true);
			
			if( $result )
			{
				return Profiler::stop("Yamp_Session_Model_Handler_Database::write", true);
			}
		}
		
		return Profiler::stop("Yamp_Session_Model_Handler_Database::write", false);
	}
	
	/**
	 * destroy a session entry
	 * @param string $sessionId
	 * @return bool
	 */
	public function destroy($sessionId)
	{
		Profiler::start("Yamp_Session_Model_Handler_Database::destroy");
		
		$result = $this->sql->delete("{DB}.{PRE}" . Yamp_Core_Helper_Tables::coreSession)
							->where("session_id = ?", $sessionId)
							->limit(1)
							->run(true);
		
		if( $result )
		{
			return Profiler::stop("Yamp_Session_Model_Handler_Database::destroy", true);
		}
		
		return Profiler::stop("Yamp_Session_Model_Handler_Database::destroy", false);
	}
	
	/**
	 * randomly called garbage collector
	 * @param integer $lifetime
	 * @return bool
	 */
	public function gc($lifetime)
	{
		Profiler::start("Yamp_Session_Model_Handler_Database::gc");
		
		$result = $this->sql->delete("{DB}.{PRE}" . Yamp_Core_Helper_Tables::coreSession)
							->where("session_time + " . $lifetime . " <= ?", time())
							->run(true);

		if( $result )
		{
			return Profiler::stop("Yamp_Session_Model_Handler_Database::gc", true);
		}
		
		return Profiler::stop("Yamp_Session_Model_Handler_Database::gc", false);
	}
}