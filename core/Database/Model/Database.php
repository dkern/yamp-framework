<?php
class Yamp_Database_Model_Database
{
	/*
	** user set data fields
	*/
	
	
	
		/**
		 * database hostname
		 * @var string
		 */
		private $hostname = "localhost";
		
		/**
		 * database username
		 * @var string
		 */
		private $username = "root";
		
		/**
		 * password to access the database
		 * @var string
		 */
		private $password = "";
		
		/**
		 * actually used database
		 * @var string
		 */
		private $database = "";
		
		/**
		 * prefix for {PRE} or {PREFIX} replacement
		 * @var string
		 */
		private $prefix = "";
		
		/**
		 * selected connection type
		 * @var string
		 */
		private $type = "";
	
	
	
	/*
	** internal data fields
	*/
	
	
	
		/**
		 * actually database connection idetifyer
		 * @var integer
		 */
		private $identifyer;
		
		/**
		 * connection types for database handling
		 * @var string array
		 */
		private $types;
		
		/**
		 * replace data inside of mysql querys
		 * @var string array
		 */
		private $replaces;
		
		/**
		 * verbose on error
		 * @var boolean
		 */
		private $verbose;
	
	
	
	/*
	** internal query fields
	*/
	
	
	
		/**
		 * current created query type
		 * @var string
		 */
		private $current;
		
		/**
		 * chained query data
		 * @var array(string => array)
		 */
		private $query;
		
		/**
		 * last commited query
		 * @var resource
		 */
		private $result;
	
	
	
	/*
	** constuction and destruction
	*/
	
	
	
		/**
		 * create mysql class instance
		 * @param $type string
		 * @param $verbose boolean
		 * @return boolean
		 */
		function __construct( $type = "write", $verbose = true )
		{
			
			// create connetion types
			$this->types = array();
			$this->types["r"]     = "r";
			$this->types["read"]  = "r";
			$this->types["w"]     = "w";
			$this->types["write"] = "w";
			
			// check if choosen type exists
			if( array_key_exists($type, $this->types) )
			{
				$this->type = $this->types[$type];
			}
			else
			{
				return false;
			}
			
			// get verbose option
			if( is_bool($verbose) )
			{
				$this->verbose = $verbose;
			}
			
			// try to find config class
			if( class_exists("mysqlconfig") )
			{
				$this->hostname = mysqlconfig::hostname;
				$this->username = mysqlconfig::username;
				$this->password = mysqlconfig::password;
				$this->database = mysqlconfig::database;
				$this->prefix   = mysqlconfig::prefix;
			}
			
			// create default data replacement
			$this->updateReplacement();
			
			return $this;
		}
		
		/**
		 * destruct mysql class instance
		 * @return boolean
		 */
		function __destruct()
		{
			$this->close();
			unset($this);
			
			return true;
		}
	
	
	
	/*
	** error verbose function
	*/
	
	
	
		/**
		 * dies or throws an Sql_Connection_Exception
		 * @return void
		 */
		private function connectionError()
		{
			if( $this->verbose )
			{
				echo "<pre>";
				throw new Sql_Connection_Exception("connection could not be established. reason: " . mysql_error($this->identifyer) );
				die();
			}
			else
			{
				die("<strong>Connection Error:</strong> Connection could not be established!");
			}
		}
		
		/**
		 * dies or throws an Sql_Database_Exception
		 * @return void
		 */
		private function databaseError()
		{
			if( $this->verbose )
			{
				echo "<pre>";
				throw new Sql_Database_Exception("could not handle or get into the choosen database '" . $this->database . "'. reason: " . mysql_error($this->identifyer) );
				die();
			}
			else
			{
				die("<strong>Database Error:</strong> Could not handle or get into the choosen database '" . $this->database . "'!");
			}
		}
		
		/**
		 * dies or throws an Sql_Query_Exception 
		 * @return void
		 */
		private function queryError()
		{
			if( $this->verbose )
			{
				echo "<pre>";
				throw new Sql_Query_Exception("could not process the given query. reason: " . mysql_error($this->identifyer) );
				die();
			}
			else
			{
				die("<strong>Query Error:</strong> Could not process the given query!");
			}
		}
		
		/**
		 * dies or throws an Sql_Create_Exception 
		 * @param $reason string
		 * @return void
		 */
		private function createError( $reason = NULL )
		{
			if( $this->verbose )
			{
				echo "<pre>";
				
				if( empty($reason) )
				{
					throw new Sql_Create_Exception("could not create the mysql query");
				}
				else
				{
					throw new Sql_Create_Exception("could not create the mysql query. reason: " . strtolower($reason));
				}
				
				die();
			}
			else
			{
				if( empty($reason) )
				{
					die("<strong>Creation Error:</strong> Could not create the mysql query!");
				}
				else
				{
					die("<strong>Creation Error:</strong> Could not create the mysql query! Reason: " . $reason);
				}
			}
		}
		
		/**
		 * dies or throws an Sql_Permission_Exception 
		 * @return void
		 */
		private function permissionError()
		{
			if( $this->verbose )
			{
				echo "<pre>";
				throw new Sql_Permission_Exception("you have no permission for this query, change the connection type to 'write'");
				die();
			}
			else
			{
				die("<strong>Permission Error:</strong> You have no permission for this query! To use this query change the connection type to 'write' first.");
			}
		}
		
		/**
		 * dies or throws an Unknown_Function_Exception
		 * @param $name string
		 * @param $params array
		 * @return void
		 */
		private function unknownFunction( $name, $params = array() )
		{
			$param = "";
			for( $i = 1; $i <= count($params); $i++ )
			{
				$param .= "param" . $i;
				
				if( $i != count($params) )
				{
					$param .= ", ";
				}
			}
			
			if( $this->verbose )
			{
				echo "<pre>";
				throw new Unknown_Function_Exception("function '" . $name . "(" . $param . ")' not found in class " . get_class($this));
				die();
			}
			else
			{
				die("<strong>Fatal Error:</strong> Function '<strong>" . $name . "(" . $param . ")</strong>' not found in class <strong>" . get_class($this) . "</strong>!");
			}
		}
	
	
	
	/*
	** getter and setter for internal data
	*/
	
	
	
		/**
		 * handles all not existing method calls
		 * @param $name string
		 * @param $params array
		 * @return mixed
		 */
		function __call( $name, $params )
		{
			$type  = substr($name, 0, 3);
			$field = strtolower(substr($name, 3));
			
			switch($type)
			{
				case "get":
					if( count($params) > 0 )
					{
						$this->unknownFunction($name, $params);
					}
					return $this->getData($field);
				break;
				
				case "set":
					if( count($params) > 1 )
					{
						$this->unknownFunction($name, $params);
					}
					return $this->setData($field, $params[0]);
				break;
			}
			
			$this->unknownFunction($name, $params);
		}
		
		/**
		 * handle all get calls
		 * @param $name string
		 * @return mixed
		 */
		private function getData( $name )
		{
			switch( $name )
			{
				case "hostname":
				case "username":
				case "password":
				case "database":
				case "prefix":
					return (string)$this->{$name};
				break;
				
				case "connection":
				case "identifyer":
					return $this->identifyer;
				break;
				
				case "verbose":
					return (bool)$this->verbose;
				break;
			}
			
			$this->unknownFunction($name);
		}
		
		/**
		 * handle all set calls
		 * @param $name string
		 * @param $value miced
		 * @return mixed
		 */
		private function setData( $name, $value )
		{
			switch( $name )
			{
				case "hostname":
				case "username":
				case "password":
				case "database":
				case "prefix":
					if( is_string($value) )
					{
						$this->{$name} = $value;
						$this->updateReplacement();
						return true;
					}
					return false;
				break;
				
				case "verbose":
					if( is_bool($value) )
					{
						$this->verbose = $value;
						return true;
					}
					return false;
				break;
				
				case "type":
				case "connectiontype":
					if( array_key_exists($value, $this->types) )
					{
						$this->type = $this->types[$value];
						return true;
					}
					return false;
				break;
			}
			
			$this->unknownFunction($name, array($value));
		}
		
		/**
		 * get class configuration as array
		 * @return string array
		 */
		public function getConfigArray()
		{
			$data = array();
			
			$data["hostname"] = $this->hostname;
			$data["username"] = $this->username;
			$data["password"] = $this->password;
			$data["database"] = $this->database;
			$data["prefix"]   = $this->prefix;
			
			return $data;
		}
		
		/**
		 * reset class configuration
		 * @return void
		 */
		public function resetConfig()
		{
			$this->close();
			
			$this->hostname = "localhost";
			$this->username = "root";
			$this->password = "";
			$this->database = "";
			$this->prefix   = "";
			
			$this->updateReplacement();
			
			return;
		}
	
	
	
	/*
	** replacement related functions
	*/
	
	
	
		/**
		 * replace all data inside mysql query
		 * @param $query string
		 * @return string
		 */
		private function replaceQuery( $query )
		{
			foreach( $this->replaces as $replace => $value )
			{
				$query = str_replace($replace, $value, $query);
			}
			
			return $query;
		}
		
		/**
		 * reset replacements to default
		 * @return void
		 */
		private function updateReplacement()
		{
			if( !is_array($this->replaces) )
			{
				$this->replaces = array();
			}
			
			$this->replaces["{DB}"]       = $this->database;
			$this->replaces["{DATABASE}"] = $this->database;
			$this->replaces["{PRE}"]      = $this->prefix;
			$this->replaces["{PREFIX}"]   = $this->prefix;
			
			return;
		}
		
		/**
		 * add a new replacement entry
		 * @param $replace string
		 * @param $value string
		 * @return boolean
		 */
		public function addReplacement( $replace, $value )
		{
			if( !is_array($this->replaces) )
			{
				$this->updateReplacement();
			}
			
			if( !empty($replace) && $replace != $value )
			{
				$this->replaces[$replace] = $value;
				return true;
			}
			
			return false;
		}
		
		/**
		 * remove a stingle replacement
		 * @param $replace string
		 * @return void
		 */
		public function removeReplacement( $replace )
		{
			if( !is_array($this->replaces) )
			{
				$this->updateReplacement();
			}
			
			if( isset($this->replaces[$replace]) )
			{
				unset($this->replaces[$replace]);
			}
			
			return;
		}
	
	
	
	/*
	** connection related calls
	*/
	
	
	
		/**
		 * open connection to database
		 * @param boolean $silent
		 * @return boolean
		 */
		public function connect($silent = false) 
		{
			// close possible open connection
			$this->close();
			
			// create new connection or throw error
			if( mysqlconfig::persistent )
			{
				$this->identifyer = @mysql_pconnect($this->hostname, $this->username, $this->password) or $silent ? NULL : $this->connectionError();
			}
			else
			{
				$this->identifyer = @mysql_connect($this->hostname, $this->username, $this->password) or $silent ? NULL : $this->connectionError();
			}
			
			// if connection was successfully, go to database
			if( $this->identifyer )
			{
				$dbSelect = @mysql_select_db($this->database, $this->identifyer) or $this->databaseError();
				
				if( $dbSelect )
				{
					return true;
				}
			}
			
			return false;
		}
		
		/**
		 * alias of connect()
		 * @return boolean
		 */
		public function reconnect()
		{
			return $this->connect();
		}
		
		/**
		 * check if connection is established
		 * @return boolean
		 */
		public function isConnected()
		{
			if( $this->identifyer )
			{
				if( mysql_ping($this->identifyer) )
				{
					return true;
				}
			}
			
			return false;
		}
		
		/**
		 * close mysql connection
		 * @return boolean
		 */
		public function close() 
		{
			if( isset($this->identifyer) )
			{
				@mysql_close($this->identifyer);
				return true;
			}
			
			return false;
		}
	
	
	
	/*
	** query functions
	*/
	
	
	
		/**
		 * check if class has the permission for the mysql query
		 * @param $query string
		 * @return boolean
		 */
		private function hasQueryPermission( $query )
		{
			if( $this->type == "w" )
			{
				return true;
			}
			
			if( preg_match("/INSERT (.*)INTO (.*)VALUE/i", $query) )
			{
				return false;
			}
			
			if( preg_match("/UPDATE (.*)SET /i", $query) )
			{
				return false;
			}
			
			if( preg_match("/DELETE (.*)FROM (.*)/i", $query) )
			{
				return false;
			}
			
			if( preg_match("/(?:CREATE|DROP|ALTER|CACHE) (.*)(?:FUNCTION|TABLE|VIEW|EVENT|TRIGGER|INDEX|SERVER|USER|DATABASE|TABLESPACE|PROCEDURE) /i", $query) )
			{
				return false;
			}
			
			return true;
		}
		
		/**
		 * run query string agains database
		 * @param $query string
		 * @param $fetch boolean
		 * @return resource | array
		 */
		public function query( $query, $fetch = false ) 
		{
			// if query is not an empty string
			if( !empty($query) ) 
			{
				// replace data inside query
				$queryString = $this->replaceQuery($query);
				
				// check if query is allowed by given connection type
				if( $this->hasQueryPermission($queryString) )
				{
					$this->result = @mysql_query($queryString, $this->identifyer) or $this->queryError();
					
					if( $this->result )
					{
						if( $fetch )
						{
							// return fetched result
							return $this->fetch($this->result);
						}
						
						// return result
						return $this->result;
					}
				}
				else
				{
					$this->permissionError();
				}
			}
			
			return false;
		}
		
		/**
		 * alias of query()
		 * @param $query string
		 * @param $fetch boolean
		 * @return resource | array
		 */
		public function qry( $query, $fetch = false )
		{
			return $this->query($query, $fetch);
		}
		
		/**
		 * affected rows by the last query
		 * @return: integer
		 */
		public function getAffected()
		{
			return mysql_affected_rows($this->identifyer);
		}
		
		/**
		 * rows in the result
		 * @return: integer
		 */
		public function getNumRows()
		{
			return mysql_num_rows($this->result);
		}
		
		/**
		 * get last insert in value
		 * @return integer | boolean
		 */
		public function getLastId()
		{
			return @mysql_insert_id($this->identifyer);
		}
		
		/**
		 * alias of getLastId()
		 * @return integer | boolean
		 */
		public function getLastInsertId()
		{
			return $this->getLastId();
		}
		
		/**
		 * free result memory
		 * @return boolean
		 */
		public function free()
		{
			return @mysql_free_result($this->result);
		}
		
		/**
		 * fetch result to useable formats
		 * @param result resource | string
		 * @param $type string
		 * @return array
		 */
		public function fetch( $result = false, $type = "assoc" )
		{
			if( $result === false || is_string($result) )
			{
				$type    = ( is_string($result) ) ? $result : $type;
				$fetched = $this->fetch($this->result, $type);
				
				$this->free();
				return $fetched;
			}
			else
			{
				$fetched = array();
				
				switch( $type )
				{
					case "array":
						while( $row = mysql_fetch_array($result) )
						{
							$fetched[] = $row;
						}
					break;
					
					case "row":
						while( $row = mysql_fetch_row($result) )
						{
							$fetched[] = $row;
						}
					break;
					
					case "obj":
					case "object":
						$collection = new rowCollection();
						while( $row = mysql_fetch_assoc($result) )
						{
							$row = new resultRow($row, $this->verbose);
							$collection->add( $row );
						}
						return $collection;
					break;
					
					default:
						while( $row = mysql_fetch_assoc($result) )
						{
							$fetched[] = $row;
						}
					break;
				}
				
				return $fetched;
			}
			
			return NULL;
		}
		
		/**
		 * escape and quote value inside mysql query
		 * @param $value string
		 * @return string
		 */
		public function escape( $value )
		{
			if( is_string($value) )
			{
				$value = mysql_real_escape_string($value);
			}
			
			if( empty($value) )
			{
				$value = "NULL";
			}
			else
			{
				$value = "'" . $value . "'";
			}
			
			return $value;
		}
		
		/**
		 * alias of escape()
		 * @param $value string
		 * @return string
		 */
		public function e( $value )
		{
			return $this->escape($value);
		}
		
		/**
		 * alias of escape()
		 * @param $value string
		 * @return string
		 */
		public function __( $value )
		{
			return $this->escape($value);
		}
	
	
	
	/*
	** chained query functions
	*/
	
	
	
		/**
		 * reset query data
		 * @return void
		 */
		private function resetQuery()
		{
			$this->current = NULL;
			$this->query   = array();
			
			$this->query["command"] = array();
			$this->query["from"]    = array();
			$this->query["join"]    = array();
			$this->query["using"]    = array();
			$this->query["where"]   = array();
			$this->query["group"]   = array();
			$this->query["order"]   = array();
			$this->query["limit"]   = array();
			$this->query["fields"]  = array();
			$this->query["values"]  = array();
			$this->query["set"]     = array();
			$this->query["duplicate"] = array();
			
			return;
		}
		
		/**
		 * add 'select' to query
		 * @return Yamp_Database_Model_Database
		 */
		public function select()
		{			
			// reset
			$this->resetQuery();
			$this->current = "select";
			
			// if no parameter was given
			if( func_num_args() == 0 )
			{
				$this->query["command"][] = "*";
				return $this;
			}
			
			foreach( func_get_args() as $param )
			{
				if( !is_array($param) )
				{
					$this->query["command"][] = $param;
				}
				else
				{
					foreach( $param as $field => $name )
					{
						$this->query["command"][] = $field . " AS " . $name;
					}
				}
			}
			
			return $this;
		}
		
		/**
		 * add 'from' to query
		 * @return Yamp_Database_Model_Database
		 */
		public function from()
		{
			// if no parameter was given
			if( func_num_args() == 0 )
			{
				$this->unknownFunction(__FUNCTION__);
			}
			
			foreach( func_get_args() as $param )
			{
				if( !is_array($param) )
				{
					$this->query["from"][] = $param;
				}
				else
				{
					foreach( $param as $database => $name )
					{
						if( !is_numeric($database) )
						{
							$this->query["from"][] = $database . " AS " . $name;
						}
						else
						{
							$this->query["from"][] = $name;
						}
					}
				}
			}
			
			return $this;
		}
		
		/**
		 * add 'where' to query
		 * @return Yamp_Database_Model_Database
		 */
		public function where()
		{	
			$params = func_num_args();
			$args   = func_get_args();
			
			// if no parameter was given
			if( $params == 0 )
			{
				$this->unknownFunction(__FUNCTION__);
			}
			
			switch( $params )
			{
				case 1:
					$this->query["where"][] = $args[0];
					$this->query["where"][] = "and";
				break;
				
				case 2:
					$this->query["where"][] = str_replace("?", $this->escape($args[1]), $args[0]);
					$this->query["where"][] = "and";
				break;
				
				case 3:
					$args[2] = strtolower($args[2]);
					$this->query["where"][] = str_replace("?", $this->escape($args[1]), $args[0]);
					$this->query["where"][] = ( $args[2] == "and" || $args[2] == "or") ? $args[2] : "and";
				break;
				
				default:
					$this->unknownFunction(__FUNCTION__, $args);
				break;
			}
			
			return $this;
		}
		
		/**
		 * add 'group' to query
		 * @return Yamp_Database_Model_Database
		 */
		public function group()
		{
			// if no parameter was given
			if( func_num_args() == 0 )
			{
				$this->unknownFunction(__FUNCTION__);
			}
			
			foreach( func_get_args() as $param )
			{
				if( !is_array($param) )
				{
					$this->query["group"][] = $param;
				}
				else
				{
					foreach( $param as $field )
					{
						$this->query["group"][] = $field;
					}
				}
			}
			
			return $this;
		}
		
		/**
		 * add 'order' to query
		 * @return Yamp_Database_Model_Database
		 */
		public function order()
		{
			$params = func_num_args();
			$args   = func_get_args();
			
			// if no parameter was given
			if( $params == 0 )
			{
				$this->unknownFunction(__FUNCTION__);
			}
			
			switch( $params )
			{
				case 1:
					$this->query["order"][] = $args[0];
				break;
				
				case 2:
					$this->query["order"][] = $args[0] . " " . $args[1];
				break;
				
				default:
					$this->unknownFunction(__FUNCTION__, $args );
				break;
			}
			
			return $this;
		}
		
		/**
		 * add 'limit' to query
		 * @return Yamp_Database_Model_Database
		 */
		public function limit()
		{
			// if no parameter was given
			if( func_num_args() == 0 )
			{
				$this->unknownFunction(__FUNCTION__);
			}
			
			foreach( func_get_args() as $param )
			{
				if( !is_array($param) )
				{
					$this->query["limit"][] = $param;
				}
				else
				{
					foreach( $param as $field )
					{
						$this->query["limit"][] = $field;
					}
				}
			}
			
			return $this;
		}
		
		/**
		 * add 'join' to query
		 * @return Yamp_Database_Model_Database
		 */
		public function join()
		{
			$params = func_num_args();
			$args   = func_get_args();
			
			// if no parameter was given
			if( $params == 0 )
			{
				$this->unknownFunction(__FUNCTION__);
			}
			
			switch( $params )
			{
				case 1:
					$this->query["join"][] = "JOIN \n    " . $args[0];
				break;
				
				case 2:
					$this->query["join"][] = "JOIN \n    " . $args[0] . " \nON \n    " . $args[1];
				break;
				
				default:
					$this->unknownFunction(__FUNCTION__, $args);
				break;
			}
			
			return $this;
		}
		
		/**
		 * add 'left join' to query
		 * @return Yamp_Database_Model_Database
		 */
		public function leftJoin()
		{
			$params = func_num_args();
			$args   = func_get_args();
			
			// if no parameter was given
			if( $params == 0 )
			{
				$this->unknownFunction(__FUNCTION__);
			}
			
			switch( $params )
			{
				case 1:
					$this->query["join"][] = "LEFT JOIN \n    " . $args[0];
				break;
				
				case 2:
					$this->query["join"][] = "LEFT JOIN \n    " . $args[0] . " \nON \n    " . $args[1];
				break;
				
				default:
					$this->unknownFunction(__FUNCTION__, $args);
				break;
			}
			
			return $this;
		}
		
		/**
		 * add 'right join' to query
		 * @return Yamp_Database_Model_Database
		 */
		public function rightJoin()
		{
			$params = func_num_args();
			$args   = func_get_args();
			
			// if no parameter was given
			if( $params == 0 )
			{
				$this->unknownFunction(__FUNCTION__);
			}
			
			switch( $params )
			{
				case 1:
					$this->query["join"][] = "RIGHT JOIN \n    " . $args[0];
				break;
				
				case 2:
					$this->query["join"][] = "RIGHT JOIN \n    " . $args[0] . " \nON \n    " . $args[1];
				break;
				
				default:
					$this->unknownFunction(__FUNCTION__, $args);
				break;
			}
			
			return $this;
		}
		
		/**
		 * add 'inner join' to query
		 * @return Yamp_Database_Model_Database
		 */
		public function innerJoin()
		{
			$params = func_num_args();
			$args   = func_get_args();
			
			// if no parameter was given
			if( $params == 0 )
			{
				$this->unknownFunction(__FUNCTION__);
			}
			
			switch( $params )
			{
				case 1:
					$this->query["join"][] = "INNER JOIN \n    " . $args[0];
				break;
				
				case 2:
					$this->query["join"][] = "INNER JOIN \n    " . $args[0] . " \nON \n    " . $args[1];
				break;
				
				default:
					$this->unknownFunction(__FUNCTION__, $args);
				break;
			}
			
			return $this;
		}
		
		/**
		 * add 'cross join' to query
		 * @return Yamp_Database_Model_Database
		 */
		public function crossJoin()
		{
			$params = func_num_args();
			$args   = func_get_args();
			
			// if no parameter was given
			if( $params == 0 )
			{
				$this->unknownFunction(__FUNCTION__);
			}
			
			switch( $params )
			{
				case 1:
					$this->query["join"][] = "CROSS JOIN \n    " . $args[0];
				break;
				
				case 2:
					$this->query["join"][] = "CROSS JOIN \n    " . $args[0] . " \nON \n    " . $args[1];
				break;
				
				default:
					$this->unknownFunction(__FUNCTION__, $args);
				break;
			}
			
			return $this;
		}
		
		/**
		 * add 'left outer join' to query
		 * @return Yamp_Database_Model_Database
		 */
		public function leftOuterJoin()
		{
			$params = func_num_args();
			$args   = func_get_args();
			
			// if no parameter was given
			if( $params == 0 )
			{
				$this->unknownFunction(__FUNCTION__);
			}
			
			switch( $params )
			{
				case 1:
					$this->query["join"][] = "LEFT OUTER JOIN \n    " . $args[0];
				break;
				
				case 2:
					$this->query["join"][] = "LEFT OUTER JOIN \n    " . $args[0] . " \nON \n    " . $args[1];
				break;
				
				default:
					$this->unknownFunction(__FUNCTION__, $args);
				break;
			}
			
			return $this;
		}
		
		/**
		 * add 'right outer join' to query
		 * @return Yamp_Database_Model_Database
		 */
		public function rightOuterJoin()
		{
			$params = func_num_args();
			$args   = func_get_args();
			
			// if no parameter was given
			if( $params == 0 )
			{
				$this->unknownFunction(__FUNCTION__);
			}
			
			switch( $params )
			{
				case 1:
					$this->query["join"][] = "RIGHT OUTER JOIN \n    " . $args[0];
				break;
				
				case 2:
					$this->query["join"][] = "RIGHT OUTER JOIN \n    " . $args[0] . " \nON \n    " . $args[1];
				break;
				
				default:
					$this->unknownFunction(__FUNCTION__, $args);
				break;
			}
			
			return $this;
		}
		
		/**
		 * add 'on' to query
		 * @return Yamp_Database_Model_Database
		 */
		public function on()
		{
			$params = func_num_args();
			$args   = func_get_args();
			
			// if wrong parameter was given
			if( $params != 1 )
			{
				$this->unknownFunction(__FUNCTION__);
			}
			
			$last = count($this->query["join"]) - 1;
			$this->query["join"][$last] = $this->query["join"][$last] . " \nON \n    " . $args[0];
			
			return $this;
		}
		
		/**
		 * add 'using' to query
		 * @return Yamp_Database_Model_Database
		 */
		public function using()
		{
			// if no parameter was given
			if( func_num_args() == 0 )
			{
				$this->unknownFunction(__FUNCTION__);
			}
			
			foreach( func_get_args() as $param )
			{
				if( !is_array($param) )
				{
					$this->query["using"][] = $param;
				}
				else
				{
					foreach( $param as $field )
					{
						$this->query["using"][] = $field;
					}
				}
			}
			
			return $this;
		}
		
		/**
		 * add 'insert' to query
		 * @return Yamp_Database_Model_Database
		 */
		public function insert()
		{
			// reset
			$this->resetQuery();
			$this->current = "insert";
			
			$params = func_get_args();
			
			if( func_num_args() == 1 )
			{
				$this->query["command"][] = $params[0];
			}
			else
			{
				$this->unknownFunction(__FUNCTION__, $params);
			}
			
			return $this;
		}
		
		/**
		 * alias of insert()
		 * @return Yamp_Database_Model_Database
		 */
		public function insertInto()
		{
			$params = func_get_args();
			
			if( func_num_args() == 1 )
			{
				$this->insert($params[0]);
			}
			else
			{
				$this->unknownFunction(__FUNCTION__, $params);
			}
			
			return $this;
		}

		/**
		 * add 'insert ignore' to query
		 * @return Yamp_Database_Model_Database
		 */
		public function insertIgnore()
		{
			// reset
			$this->resetQuery();
			$this->current = "insert ignore";
	
			$params = func_get_args();
	
			if( func_num_args() == 1 )
			{
				$this->query["command"][] = $params[0];
			}
			else
			{
				$this->unknownFunction(__FUNCTION__, $params);
			}
	
			return $this;
		}
	
		/**
		 * add fields to insert query
		 * @return Yamp_Database_Model_Database
		 */
		public function fields()
		{
			// if no parameter was given
			if( func_num_args() == 0 )
			{
				$this->unknownFunction(__FUNCTION__);
			}
			
			foreach( func_get_args() as $param )
			{
				if( !is_array($param) )
				{
					$this->query["fields"][] = $param;
				}
				else
				{
					foreach( $param as $field )
					{
						$this->query["fields"][] = $field;
					}
				}
			}
			
			return $this;
		}
		
		/**
		 * add 'values' to insert query
		 * @return Yamp_Database_Model_Database
		 */
		public function values()
		{
			// if no parameter was given
			if( func_num_args() == 0 )
			{
				$this->unknownFunction(__FUNCTION__);
			}
			
			// count params
			$count = 0;
			$array = array();
			foreach( func_get_args() as $param )
			{
				if( !is_array($param) )
				{
					$count++;
					$array[] = $this->escape($param);
				}
				else
				{
					foreach( $param as $field )
					{
						$count++;
						$array[] = $this->escape($field);
					}
				}
			}
			
			// check if params count match fields
			if( $count == count($this->query["fields"]) )
			{
				$this->query["values"][] = $array;
			}
			else
			{
				$this->createError("Value count doesn't match fields.");
			}
			
			return $this;
		}
		
		/**
		 * add 'onDuplicate' to insert query
		 * @return Yamp_Database_Model_Database
		 */
		public function onDuplicate()
		{
			// if no parameter was given
			if( func_num_args() < 1 && func_num_args() > 2 )
			{
				$this->unknownFunction(__FUNCTION__);
			}
			
			$params = func_get_args();
			
			// set on duplicate key data
			if( func_num_args() == 1 )
			{
				$this->query["duplicate"][] = $params[0];
			}
			elseif( func_num_args() == 2 )
			{
				$this->query["duplicate"][] = str_replace("?", $this->escape($params[1]), $params[0]);
			}
			
			return $this;
		}
		
		/**
		 * add 'update' to query
		 * @return Yamp_Database_Model_Database
		 */
		public function update()
		{
			// reset
			$this->resetQuery();
			$this->current = "update";
			
			$params = func_get_args();
			
			if( func_num_args() == 1 )
			{
				$this->query["command"][] = $params[0];
			}
			else
			{
				$this->unknownFunction(__FUNCTION__, $params);
			}
			
			return $this;
		}
		
		/**
		 * add 'set' to update query
		 * @return Yamp_Database_Model_Database
		 */
		public function set()
		{
			$params = func_num_args();
			$args   = func_get_args();
			
			// if no parameter was given
			if( $params == 0 )
			{
				$this->unknownFunction(__FUNCTION__);
			}
			
			switch( $params )
			{
				case 1:
					$this->query["set"][] = $args[0];
				break;
				
				case 2:
					$this->query["set"][] = $args[0] . " = " . $this->escape($args[1]);
				break;
				
				default:
					$this->unknownFunction(__FUNCTION__, $args);
				break;
			}
			
			return $this;
		}
		
		/**
		 * add 'delete' to query
		 * @return Yamp_Database_Model_Database
		 */
		public function delete()
		{
			// reset
			$this->resetQuery();
			$this->current = "delete";
			
			$params = func_get_args();
			
			if( func_num_args() == 1 )
			{
				$this->query["command"][] = $params[0];
			}
			else
			{
				$this->unknownFunction(__FUNCTION__, $params);
			}
			
			return $this;
		}
		
		/**
		 * alias of delete()
		 * @return Yamp_Database_Model_Database
		 */
		public function deleteFrom()
		{
			$params = func_get_args();
			
			if( func_num_args() == 1 )
			{
				$this->delete($params[0]);
			}
			else
			{
				$this->unknownFunction(__FUNCTION__, $params);
			}
			
			return $this;
		}
		
		/**
		 * run mysql query against database
		 * @param $return boolean
		 * @return Yamp_Database_Model_Database | resource
		 */
		public function run( $return = true )
		{
			$query = $this->buildQuery();
			$this->result = $this->query($query);
			
			if( $return )
			{
				return $this->result;
			}
			
			return $this;
		}
		
		/**
		 * alias of run()
		 * @param $return boolean
		 * @return Yamp_Database_Model_Database
		 */
		public function execute( $return = true )
		{
			return $this->run($return);
		}
		
		/*
		 * print out mysql query
		 * @return Yamp_Database_Model_Database
		 */
		public function showQuery()
		{
			$query  = $this->buildQuery();
			$query .= "\n\n";
			
			echo $query;
			return $this;
		}
		
		/**
		 * return query string
		 * @return string
		 */
		public function getQuery()
		{
			$query  = $this->buildQuery();
			$query .= "\n\n";
			
			return $query;
		}
	
	
	
	/*
	** building query
	*/
	
	
	
		/**
		 * build mysql query string
		 * @return string
		 */
		private function buildQuery()
		{
			switch( $this->current )
			{
				case "select":
					return $this->buildSelect();
				break;

				case "insert":
					return $this->buildInsert();
				break;

				case "insert ignore":
					return $this->buildInsert(true);
				break;
				
				case "update":
					return $this->buildUpdate();
				break;
				
				case "delete":
					return $this->buildDelete();
				break;
			}
			
			$this->createError();
		}
		
		/**
		 * build mysql select query string
		 * @return string
		 */
		private function buildSelect()
		{
			// add fields
			$query = "SELECT \n";
			for( $i = 0; $i < count($this->query["command"]); $i++ )
			{
				$query .= "    " . $this->query["command"][$i];
				
				if( $i < count($this->query["command"]) - 1 )
				{
					$query .= ",";
				}
				
				$query .= " \n";
			}
			
			// add from
			if( sizeof($this->query["from"]) )
			{
				$query .= "FROM \n";
				for( $i = 0; $i < count($this->query["from"]); $i++ )
				{
					$query .= "    " . $this->query["from"][$i];
					
					if( $i < count($this->query["from"]) - 1 )
					{
						$query .= ",";
					}
					
					$query .= " \n";
				}
			}
			
			// add join
			if( sizeof($this->query["join"]) )
			{
				for( $i = 0; $i < count($this->query["join"]); $i++ )
				{
					$query .= $this->query["join"][$i];
					
					if( $i < count($this->query["join"]) - 1 )
					{
						$query .= ",";
					}
					
					$query .= " \n";
				}
			}
			
			// add using
			if( sizeof($this->query["using"]) )
			{
				$query .= "USING \n";
				$query .= "( \n";
				for( $i = 0; $i < count($this->query["using"]); $i++ )
				{
					$query .= "    " . $this->query["using"][$i];
					
					if( $i < count($this->query["using"]) - 1 )
					{
						$query .= ",";
					}
					
					$query .= " \n";
				}
				$query .= ") \n";
			}
			
			// add where
			if( sizeof($this->query["where"]) )
			{
				$query .= "WHERE \n";
				for( $i = 0; $i < count($this->query["where"]); $i = $i + 2 )
				{
					$query .= "    " . $this->query["where"][$i];
					
					if( $i < count($this->query["where"]) - 2 )
					{
						if( $this->query["where"][$i + 1] == "or" )
						{
							$query .= " \nOR ";
						}
						else
						{
							$query .= " \nAND ";
						}
					}
					
					$query .= " \n";
				}
			}
			
			// add group
			if( sizeof($this->query["group"]) )
			{
				$query .= "GROUP BY \n";
				for( $i = 0; $i < count($this->query["group"]); $i++ )
				{
					$query .= "    " . $this->query["group"][$i];
					
					if( $i < count($this->query["group"]) - 1 )
					{
						$query .= ",";
					}
					
					$query .= " \n";
				}
			}
			
			// add order
			if( sizeof($this->query["order"]) )
			{
				$query .= "ORDER BY \n";
				for( $i = 0; $i < count($this->query["order"]); $i++ )
				{
					$query .= "    " . $this->query["order"][$i];
					
					if( $i < count($this->query["order"]) - 1 )
					{
						$query .= ",";
					}
					
					$query .= " \n";
				}
			}
			
			// add limit
			if( sizeof($this->query["limit"]) )
			{
				$query .= "LIMIT \n";
				for( $i = 0; $i < count($this->query["limit"]); $i++ )
				{
					$query .= "    " . $this->query["limit"][$i];
					
					if( $i < count($this->query["limit"]) - 1 )
					{
						$query .= ",";
					}
					
					$query .= " \n";
				}
			}
			
			return $query;
		}
		
		/**
		 * build mysql insert query string
		 * @param boolean $ignore
		 * @return string
		 */
		private function buildInsert($ignore = false)
		{
			// add database
			$query = "INSERT INTO \n";
			
			if( $ignore )
			{
				$query = "INSERT IGNORE INTO \n";
			}
			
			for( $i = 0; $i < count($this->query["command"]); $i++ )
			{
				$query .= "    " . $this->query["command"][$i];	
				$query .= " \n";
			}
			
			// add fields
			if( sizeof($this->query["fields"]) )
			{
				$query .= "( \n";
				for( $i = 0; $i < count($this->query["fields"]); $i++ )
				{
					$query .= "    " . $this->query["fields"][$i];
					
					if( $i < count($this->query["fields"]) - 1 )
					{
						$query .= ",";
					}
					
					$query .= " \n";
				}
				$query .= ") \n";
			}
			
			// add values
			if( sizeof($this->query["values"]) )
			{
				$query .= "VALUES \n";
				for( $i = 0; $i < count($this->query["values"]); $i++ )
				{
					$query .= "    (";
					
					for( $c = 0; $c < count($this->query["values"][$i]); $c++ )
					{
						$query .= $this->query["values"][$i][$c];
						
						if( $c < count($this->query["values"][$i]) - 1 )
						{
							$query .= ", ";
						}
					}
					
					$query .= ")";
					
					if( $i < count($this->query["values"]) - 1 )
					{
						$query .= ",";
					}
					
					$query .= " \n";
				}
			}
			
			// add on duplicate
			if( sizeof($this->query["duplicate"]) )
			{
				$query .= "ON DUPLICATE KEY UPDATE \n";
				
				for( $i = 0; $i < count($this->query["duplicate"]); $i++ )
				{
					$query .= "    " . $this->query["duplicate"][$i];
					
					if( $i < count($this->query["duplicate"]) - 1 )
					{
						$query .= ", ";
					}
					
					$query .= " \n";
				}
			}
			
			return $query;
		}
		
		/**
		 * build mysql update query string
		 * @return string
		 */
		private function buildUpdate()
		{
			// add fields
			$query = "UPDATE \n";
			for( $i = 0; $i < count($this->query["command"]); $i++ )
			{
				$query .= "    " . $this->query["command"][$i];
				
				if( $i < count($this->query["command"]) - 1 )
				{
					$query .= ",";
				}
				
				$query .= " \n";
			}
			
			// add set
			if( sizeof($this->query["set"]) )
			{
				$query .= "SET \n";
				for( $i = 0; $i < count($this->query["set"]); $i++ )
				{
					$query .= "    " . $this->query["set"][$i];
					
					if( $i < count($this->query["set"]) - 1 )
					{
						$query .= ",";
					}
					
					$query .= " \n";
				}
			}
			
			// add where
			if( sizeof($this->query["where"]) )
			{
				$query .= "WHERE \n";
				for( $i = 0; $i < count($this->query["where"]); $i = $i + 2 )
				{
					$query .= "    " . $this->query["where"][$i];
					
					if( $i < count($this->query["where"]) - 2 )
					{
						if( $this->query["where"][$i + 1] == "or" )
						{
							$query .= " \nOR ";
						}
						else
						{
							$query .= " \nAND ";
						}
					}
					
					$query .= " \n";
				}
			}
			
			return $query;
		}
		
		/**
		 * build mysql delete query string
		 * @return string
		 */
		private function buildDelete()
		{
			// add database
			$query = "DELETE FROM \n";
			for( $i = 0; $i < count($this->query["command"]); $i++ )
			{
				$query .= "    " . $this->query["command"][$i];	
				$query .= " \n";
			}
			
			// add where
			if( sizeof($this->query["where"]) )
			{
				$query .= "WHERE \n";
				for( $i = 0; $i < count($this->query["where"]); $i = $i + 2 )
				{
					$query .= "    " . $this->query["where"][$i];
					
					if( $i < count($this->query["where"]) - 2 )
					{
						if( $this->query["where"][$i + 1] == "or" )
						{
							$query .= " \nOR ";
						}
						else
						{
							$query .= " \nAND ";
						}
					}
					
					$query .= " \n";
				}
			}
			
			return $query;
		}
}



/*
** row collection class
*/



class rowCollection implements Countable
{
	/*
	** internal data
	*/
	
	
	
		/**
		 * array with row data
		 * @var array
		 */
		private $data = array();
	
	
	
	/*
	** methods
	*/
	
	
	
	/**
	 * add a new result row to collection
	 * @param $row resultRow
	 * @return boolean
	*/
	public function add( $row )
	{
		if( $row instanceof resultRow )
		{
			$this->data[] = $row;
			return true;
		}
		
		return false;
	}
	
	/**
	 * get alls rows as array
	 * @return resultRow array
	 */
	public function getRows()
	{
		return $this->data;
	}
	
	/**
	 * get single result row by index
	 * @param $num integer
	 * @return resultRow
	 */
	public function getRow( $num )
	{
		if( $num >= 0 && $num < count($this->data) )
		{
			return $this->data[$num];
		}
		
		return new resultRow(array(), false);
	}
	
	/**
	 * get first resultRow of collection
	 * @return resultRow
	 */
	public function getFirstRow()
	{
		return $this->getRow(0);
	}
	
	/**
	 * get last resultRow of collection
	 * @return resultRow
	 */
	public function getLastRow()
	{
		$last = count($this->data) - 1;
		return $this->getRow($last);
	}
	
	/**
	 * returns the count of rows
	 * @retrun integer
	 */
	public function count()
	{
		return count($this->data);
	}
}



/*
** row object class
*/



class resultRow
{
	/*
	** internal data
	*/
	
	
	
		/**
		 * verbose on error
		 * @var boolean
		 */
		private $verbose;
		
		/**
		 * array with row data
		 * @var array
		 */
		private $data = array();
	
	
	
	/*
	** construct
	*/
	
	
	
		/**
		 * create result row instance
		 * @param $data array
		 * @param $verbose boolean
		 * @return resultRow
		 */
		function __construct($data, $verbose)
		{
			if( is_array($data) )
			{
				$this->data = $data;
			}
			
			$this->verbose = $verbose;
			
			return $this;
		}
	
	
	
	/*
	** methods
	*/
	
	
	
		/**
		 * handle all unknown method calls
		 * @param $name string
		 * @param $params array
		 * @return mixed
		 */
		function __call( $name, $params )
		{
			$type  = substr($name, 0, 3);
			$field = strtolower(substr($name, 3));
			
			switch($type)
			{
				case "get":
					if( count($params) == 0 )
					{
						return $this->getData($field, NULL);
					}
					
					if( count($params) == 1 )
					{
						return $this->getData($field, $params[0]);
					}
				break;
			}
			
			$this->unknownFunction($name, $params);
		}
		
		/**
		 * handle all get calls
		 * @param $name string
		 * @param $default mixed
		 * @return mixed
		 */
		private function getData( $name, $default )
		{
			if( isset($this->data[$name]) )
			{
				return $this->data[$name];
			}
			else
			{
				return $default;
			}
		}
		
		/**
		 * dies or throws an Unknown_Function_Exception
		 * @param $name string
		 * @param $params array
		 * @return void
		 */
		private function unknownFunction( $name, $params = array() )
		{
			$param = "";
			for( $i = 1; $i <= count($params); $i++ )
			{
				$param .= "param" . $i;
				
				if( $i != count($params) )
				{
					$param .= ", ";
				}
			}
			
			if( $this->verbose )
			{
				echo "<pre>";
				throw new Unknown_Function_Exception("function '" . $name . "(" . $param . ")' not found in class " . get_class($this));
				die();
			}
			else
			{
				die("<strong>Fatal Error:</strong> Function '<strong>" . $name . "(" . $param . ")</strong>' not found in class <strong>" . get_class($this) . "</strong>!");
			}
		}
}



/*
** exception types
*/



	/* mysql connection exception */
	class Sql_Connection_Exception extends Exception {}
	
	/* mysql database exception */
	class Sql_Database_Exception extends Exception {}
	
	/* mysql query exception */
	class Sql_Query_Exception extends Exception {}
	
	/* mysql permission exception */
	class Sql_Permission_Exception extends Exception {}
	
	/* mysql query creation error */
	class Sql_Create_Exception extends Exception {}
	
	/* unknown function exception */
	class Unknown_Function_Exception extends Exception {}
?>