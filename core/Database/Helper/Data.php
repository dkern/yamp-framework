<?php
class Yamp_Database_Helper_Data extends Yamp_Core_Helper_Abstract
{
	/**
	 * sql connection instance
	 * @var Yamp_Database_Model_Database
	 */
	private $sql;

	/**
	 * database available
	 * @var bool
	 */
	private $databaseAvailable = false;

	/**
	 * table name buffer
	 * @var array
	 */
	private $tableBuffer = array();
	
	
	
	/*
	** public
	*/
	
	
	
	/**
	 * check if a database connection is available
	 * @return boolean
	 */
	public function databaseAvailable()
	{
		Profiler::start("Yamp_Database_Helper_Data::databaseAvailable");
		
		// if allready detected availability
		if( $this->databaseAvailable )
		{
			return Profiler::stop("Yamp_Database_Helper_Data::databaseAvailable", true);
		}
		
		// check if available
		if( mysqlconfig::useDatabase && $this->_getConnection()->isConnected() )
		{
			$this->databaseAvailable = true;
			return Profiler::stop("Yamp_Database_Helper_Data::databaseAvailable", true);
		}

		return Profiler::stop("Yamp_Database_Helper_Data::databaseAvailable", false);
	}

	/**
	 * check if a specific table exists in database instance
	 * @param string $table
	 * @param Yamp_Database_Model_Database $sqlInstance
	 * @return boolean
	 */
	public function tableExists($table, $sqlInstance = NULL)
	{
		Profiler::start("Yamp_Database_Helper_Data::tableExists");
		
		// if database is not used
		if( !mysqlconfig::useDatabase )
		{
			return Profiler::stop("Yamp_Database_Helper_Data::tableExists", false);
		}
		
		// return from buffer if available
		if( isset($this->tableBuffer[$table]) )
		{
			return $this->tableBuffer[$table];
		}
		
		$sql = NULL;
		
		if( !is_null($sqlInstance) )
		{
			$sql = $sqlInstance;
		}
		else
		{
			$sql = $this->_getConnection();
		}
		
		$result = $sql->select("table_name")
					  ->from("information_schema.tables")
					  ->where("table_schema = ?", mysqlconfig::database)
					  ->where("table_name = ?", $table)
					  ->run()
					  ->fetch();
		
		if( count($result) == 1 )
		{
			$this->tableBuffer[$table] = true;
			return Profiler::stop("Yamp_Database_Helper_Data::tableExists", true);
		}
		
		$this->tableBuffer[$table] = false;
		return Profiler::stop("Yamp_Database_Helper_Data::tableExists", false);
	}

	/**
	 * clear table exists buffer
	 * @param boolean $falseOnly
	 * @return Yamp_Database_Helper_Data
	 */
	public function clearTableBuffer($falseOnly = false)
	{
		// remove only non-existing tables from buffer
		if( $falseOnly )
		{
			foreach( $this->tableBuffer as $table => $exists )
			{
				if( $exists === false )
				{
					unset($this->tableBuffer[$table]);
				}
			}
			
			return $this;
		}
		
		// remove all tables from buffer
		$this->tableBuffer = array();
		return $this;
	}

	
	
	/*
	** private
	*/



	/**
	 * get database instance
	 * @return Yamp_Database_Model_Database
	 */
	private function _getConnection()
	{
		if( !$this->sql )
		{
			$this->sql = $this->getConnection()->getRead();
		}

		return $this->sql;
	}
}