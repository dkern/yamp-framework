<?php
class Yamp_Setup_Model_Installer extends Yamp_Core_Model_Abstract
{
	/**
	 * sql connection instance
	 * @var Yamp_Database_Model_Database
	 */
	private $sql;
	
	/**
	 * check container
	 * @var array
	 */
	private $checked = array();



	/*
	** public
	*/
	
	
	
	/**
	 * start a new installer job
	 * @param string $file
	 * @return boolean
	 */
	public function start($file)
	{
		if( !$this->sql )
		{
			$this->sql = $this->getConnection()->getWrite();
		}
		
		$this->reset();
		
		if( file_exists($file) )
		{
			include_once($file);
		}
		
		return $this->isExecuted();
	}

	/**
	 * get isntaller instance
	 * @return Yamp_Setup_Model_Installer
	 */
	public function getInstaller()
	{
		return $this;
	}

	/**
	 * run a direct database query
	 * @param string $query
	 * @return void
	 */
	public function run($query)
	{
		$result = $this->sql->query($query);
		
		if( $result )
		{
			$this->checked[] = true;
		}
		else
		{
			$this->checked[] = false;
		}
		
		return;
	}
	
	
	
	/*
	** private
	*/

	

	/**
	 * check if everytrink is ok for now
	 * @return boolean
	 */
	private function isExecuted()
	{
		$executed = true;

		foreach( $this->checked as $check )
		{
			if( !$check )
			{
				$executed = false;
				break;
			}
		}
		
		return $executed;
	}

	/**
	 * reset class instance
	 * @return void
	 */
	private function reset()
	{
		$this->checked = array();
		return;
	}
}