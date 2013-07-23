<?php
class Yamp_Log_Model_Log
{
	/**
	 * logging levels
	 * @var integer
	 */
	const LOG_LEVEL_EMERG  = 0;  // Emergency
    const LOG_LEVEL_ALERT  = 1;  // Alert
    const LOG_LEVEL_CRIT   = 2;  // Critical
    const LOG_LEVEL_ERR    = 3;  // Error
    const LOG_LEVEL_WARN   = 4;  // Warning
    const LOG_LEVEL_NOTICE = 5;  // Notice
    const LOG_LEVEL_INFO   = 6;  // Informational
    const LOG_LEVEL_DEBUG  = 7;  // Debug
	
	/**
     * add a new entry to log file
	 * @param string $message
	 * @param integer $level
	 * @param string $file
	 * @return void
	 */
    public static function log($message, $level = NULL, $file = NULL)
    {
	    Profiler::start("Yamp_Log_Model_Log::log");
	    
		// logging informations
		$level = is_null($level) ? self::LOG_LEVEL_DEBUG : $level;
		$priority = self::getPriorityName($level);;
		$file = is_null($file) ? config::defaultLogFile : $file;
		
		// get folder and file data
		$logDir  = yamp::getBaseDir("var") . DS . "log";
		$logFile = $logDir . DS . $file;
		
		// create log dir if missing
		if( !is_dir($logDir) )
		{
			mkdir($logDir);
			chmod($logDir, 0777);
		}
		
		// create file if missing
		if( !file_exists($logFile) )
		{
			file_put_contents($logFile, "");
			chmod($logFile, 0777);
		}
		
		// format message
		if( is_array($message) || is_object($message) )
		{
			$message = print_r($message, true);
		}
		
		// write new log entry to file
		$line = date("d.m.Y - H:i:s") . " " . $priority . " (" . $level . "): " . $message . PHP_EOL;
		file_put_contents($logFile, $line, FILE_APPEND);

	    Profiler::stop("Yamp_Log_Model_Log::log");
    }
	
    /**
     * write exception to single report log file
     * @param Exception $e
	 * @return void
     */
    public static function logException(Exception $e)
    {
        self::log($e->getMessage() . "\n" . $e->__toString() . "\n", self::LOG_LEVEL_ERR, "exception.log");
    }
	
	
	
	/*
	** private
	*/
	
	
	
	/**
	 * get priority name by level id
	 * @param integer $level
	 * @return string
	 */
	private static function getPriorityName($level)
	{
		Profiler::start("Yamp_Log_Model_Log::getPriorityName");
		
		// get priority name by log level
		switch( $level )
		{
			case 0:  $priority = "Emergency"; break;
			case 1:  $priority = "Alert"; break;
			case 2:  $priority = "Critical"; break;
			case 3:  $priority = "Error"; break;
			case 4:  $priority = "Warning"; break;
			case 5:  $priority = "Notice"; break;
			case 6:  $priority = "Informational"; break;
			case 7:  $priority = "Debug"; break;
			default: $priority = "Unknown"; break;
		}
		
		return Profiler::stop("Yamp_Log_Model_Log::getPriorityName", $priority);
	}
}