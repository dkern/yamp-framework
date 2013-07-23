<?php
class Yamp_Session_Model_Handler_File extends Yamp_Core_Helper_Abstract implements Yamp_Session_Model_Handler_Handler
{
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
		Profiler::start("Yamp_Session_Model_Handler_File::open");
		
		$this->savePath = $savePath;
		$this->sessionName = $sessionName;
		
		if( !is_dir($this->savePath) )
		{
			mkdir($this->savePath);
		}

		return Profiler::stop("Yamp_Session_Model_Handler_File::open", true);
	}
	
	/**
	 * destructor - called at the session end
	 * @return bool
	 */
	public function close()
	{
		Profiler::start("Yamp_Session_Model_Handler_File::close");
		return Profiler::stop("Yamp_Session_Model_Handler_File::close", true);
	}

	/**
	 * read session data by id
	 * @param string $sessionId
	 * @return string
	 */
	public function read($sessionId)
	{
		Profiler::start("Yamp_Session_Model_Handler_File::read");
		
		$read = (string)@file_get_contents($this->getFilename($sessionId));
		
		return Profiler::stop("Yamp_Session_Model_Handler_File::read", $read);
	}

	/**
	 * write session data for id
	 * @param string $sessionId
	 * @param string $sessionData
	 * @return bool
	 */
	public function write($sessionId, $sessionData)
	{
		Profiler::start("Yamp_Session_Model_Handler_File::write");
		
		$write = file_put_contents($this->getFilename($sessionId), $sessionData) === false ? false : true;
		
		return Profiler::stop("Yamp_Session_Model_Handler_File::write", $write);
	}

	/**
	 * destroy a session entry
	 * @param string $sessionId
	 * @return bool
	 */
	public function destroy($sessionId)
	{
		Profiler::start("Yamp_Session_Model_Handler_File::destroy");
		
		$file = $this->getFilename($sessionId);
		
		if( file_exists($file) )
		{
			unlink($file);
		}

		return Profiler::stop("Yamp_Session_Model_Handler_File::destroy", true);
	}

	/**
	 * randomly called garbage collector
	 * @param integer $lifetime
	 * @return bool
	 */
	public function gc($lifetime)
	{
		Profiler::start("Yamp_Session_Model_Handler_File::gc");
		
		foreach( glob($this->savePath .DS . "*.session") as $file )
		{
			if( filemtime($file) + $lifetime < time() && file_exists($file) ) 
			{
				unlink($file);
			}
		}

		return Profiler::stop("Yamp_Session_Model_Handler_File::gc", true);
	}
	
	
	
	/*
	** private
	*/
	
	
	
	/**
	 * get session filename
	 * @param string $sessionId
	 * @return string
	 */
	private function getFilename($sessionId)
	{
		Profiler::start("Yamp_Session_Model_Handler_File::getFilename");
		
		$name =  YAMP_ROOT . DS . $this->savePath . DS . $sessionId . ".session";

		return Profiler::stop("Yamp_Session_Model_Handler_File::getFilename", $name);
	}
}