<?php
class Yamp_Database_Model_Connection
{
	/**
	 * get a database read connection
	 * @return Yamp_Database_Model_Database
	 */
	public function getRead()
	{
		Profiler::start("Yamp_Database_Model_Connection::getRead");
		
		$model = yamp::registry("_database/read");
		
		if( is_null($model) )
		{
			$model = yamp::getModel("database/database");
			$model->setConnectionType(Yamp_Database_Model_Database::CONNECTION_TYPE_READ);
			
			if( !yamp::isDebugMode() )
			{
				$model->setVerbose(false);
			}
			
			$model->connect(true);
			
			yamp::register("_database/read", $model);
		}

		return Profiler::stop("Yamp_Database_Model_Connection::getRead", $model);
	}
	
	/**
	 * get a database write connection
	 * @return Yamp_Database_Model_Database
	 */
	public function getWrite()
	{
		Profiler::start("Yamp_Database_Model_Connection::getWrite");
		
		$model = yamp::registry("_database/write");
		
		if( is_null($model) )
		{
			$model = yamp::getModel("database/database");

			if( !yamp::isDebugMode() )
			{
				$model->setVerbose(false);
			}
			
			$model->connect(true);
			
			yamp::register("_database/write", $model);
		}

		return Profiler::stop("Yamp_Database_Model_Connection::getWrite", $model);
	}
}
