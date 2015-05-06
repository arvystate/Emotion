<?php
/***************************************************************************
 *   Application          : Emotion                                        *
 *   -------------------------------------------------------------------   *
 *   Package              : @library                                       *
 *   File                 : database.php                                   *
 *   Version              : 1.0.0                                          *
 *   Status               : Complete, Not tested                           *
 *   -------------------------------------------------------------------   *
 *   Begin                : Wednesday, Aug 10, 2011                        *
 *   CopyRight            : (C) 2011 ArvYStaTe.net Team                    *
 *   E-Mail               : support@arvystate.net                          *
 *   Last edit            : Wednesday, Aug 10, 2011                        *
 *                                                                         *
 *                                                                         *
 ***************************************************************************
 
 ***************************************************************************
 *   File description                                                      *
 *   -------------------------------------------------------------------   *
 *   Database abstraction layer, use this class for all database related   *
 *   operations, as it will handle the low-level commands by itself.       *
 *   -------------------------------------------------------------------   *
 *                                                                         *
 ***************************************************************************
 
 ***************************************************************************
 *   Change log                                                            *
 *   -------------------------------------------------------------------   *
 *    + [08/10/11] - File created                                          *
 *   -------------------------------------------------------------------   *
 *                                                                         *
 ***************************************************************************

 ***************************************************************************
 *                                                                         *
 *   Emotion is a powerful PHP framework for website generation.           *
 *   -------------------------------------------------------------------   *
 *   Application is owned and copyrighted by ArvYStaTe.net Team, you are   *
 *   only allowed to modify code, not take ownership or in any way claim   *
 *   you are the creator of any thing else but modifications.              *
 *   -------------------------------------------------------------------   *
 *                                                                         *
 ***************************************************************************/

/**
* @ignore
**/

if (!defined ('EMOTION_PAGE'))
{
	die ('<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
			<html><head>
			<title>404 Not Found</title>
			</head><body>
			<h1>Not Found</h1>
			<p>The requested URL /' . $_SERVER['PHP_SELF'] . ' was not found on this server.</p>
			<p>Additionally, a 404 Not Found
			error was encountered while trying to use an ErrorDocument to handle the request.</p>
			</body></html>');
}

/**
 * DatabaseDriver
 *
 * Base class for low-level database driver.
 *
 * @package	database
 * @require	false
 * @author	ArvYStaTe.net Team
 **/
 
abstract class DatabaseDriver
{
	private $errorArray = array ();
	
	protected $dbConnectId = false;
	protected $persistency = false;
	protected $newLink = false;
	protected $user = '';
	protected $server = '';
	protected $dbName = '';
	
	protected $serverVersion = '';
	protected $queryResult;
	
	/**
	 * Driver connects to DBMS
	 *
	 * @access	public
	 * @param	string	database server
	 * @param	string	database user
	 * @param	string	database password
	 * @param	string	database name
	 * @param	integer	database port
	 * @param	boolean	persistency
	 * @param	boolean	should database be opened as new link
	 * @return	boolean returns if connection was successful
	 **/
	 
	abstract public function Connect ($server, $user, $password, $dbName, $port = false, $persistency = false, $newLink = false);
	
	/**
	 * Closes current database connection
	 *
	 * @access	public
	 * @return	boolean	returns true if closing was successful
	 **/
	 
	abstract public function Close ();
	
	/**
	 * Returns last error
	 *
	 * @access	public
	 * @return	string error message
	 **/
	 
	abstract public function Error ();
	
	/**
	 * Executes query
	 *
	 * @access	public
	 * @param	string	SQL query
	 * @return	object	database result object
	 **/
	
	abstract public function Query ($query = '');
	
	/**
	 * Free memory allocated by database result object
	 *
	 * @access	public
	 * @param	object	database result object
	 * @return	boolean	returns true if deallocation was successful
	 **/
	
	abstract public function FreeResult ($result = false);
	
	/**
	 * Escape SQL dangerous characters
	 *
	 * @access	public
	 * @param	string	message
	 * @return	string	escaped message
	 **/
	 
	abstract public function Escape ($message);
	
	/**
	 * Fetches current row to array
	 *
	 * @access	public
	 * @param	object	database result object
	 * @return	array	array of fields of current row
	 **/
	 
	abstract public function FetchRow ($result = false);
	
	/**
	 * Constructs specified limit query
	 *
	 * @access	public
	 * @param	string	query
	 * @param	integer	total
	 * @param	integer	offset
	 * @return	string	limit constructed string
	 **/
	 
	abstract public function Limit ($query, $total, $offset = 0);
	
	/**
	 * Constructs correct like expression
	 *
	 * @access	public
	 * @param	string	expression
	 * @return	string	constructed like expression
	 **/
	
	abstract public function LikeExpression ($expression);
	
	/**
	 * Seeks to row number in result
	 *
	 * @access	public
	 * @param	integer	row number
	 * @param	object	database result object
	 * @return	boolean	returns true if seek was successful
	 **/
	
	abstract public function RowSeek ($rownum, &$result);
	
	/**
	 * Returns string of DBMS version
	 *
	 * @access	public
	 * @param	boolean	default false: if only version number is returned
	 * @return	string	server version
	 **/
	 
	abstract public function ServerVersion ($raw = false);
		
	/**
	 * Function is to be overloaded by specific driver, if DBMS has specific commands
	 *
	 * @access	public
	 * @param	string	command stage on which custom build needs to be performed
	 * @param	string	data query on which custom build is performed
	 * @return	string	data query with custom modifications
	 **/
	 
	public function CustomBuild ($stage, $data)
	{
		return $data;
	}
	
	/**
	 * Returns connection status of the driver
	 *
	 * @access	public
	 * @return	boolean true if driver is connected to a database, false if its not
	 **/
	 
	public function IsConnected ()
	{
		return ($this->dbConnectId !== false);
	}
	
	/**
	 * Returns driver status
	 *
	 * @access	public
	 * @return	string	text string of database version and driver
	 **/
	 
	public function GetStatus ()
	{
		if ($this->IsConnected())
		{	
			return $this->ServerVersion () . ' Connected: ' . $this->user . ' -> ' . $this->dbName;
		}
		else
		{
			return get_class ($this) . ' Driver Not Connected.';
		}
	}
	
	/**
	 * Stores current database error
	 *
	 * @access	protected
	 * @return	void
	 **/
	 
	protected function StoreError ()
	{
		$error = $this->Error ();
		
		if (!empty ($error))
		{
			$this->errorArray [count ($this->errorArray)] = $error;
		}
	}
	
	/**
	 * Clears error list
	 *
	 * @access	protected
	 * @return	void
	 **/
	 
	protected function ClearErrors ()
	{
		$this->errorArray = array ();
	}

	/**
	 * Returns current error list
	 *
	 * @access	public
	 * @return	array	array of error messages
	 **/

	public function GetErrorList ()
	{
		return $this->errorArray;
	}
}

/**
 * Database
 *
 * Database class to use with any database supported by Emotion.
 *
 * @package	database
 * @require	false
 * @author	ArvYStaTe.net Team
 **/

class Database
{
	// Driver
	protected $dbDriver = null;
	
	// Query arrays
	protected $execQueries = array ();
	
	// Float of query time
	protected $queryTime = 0;
	
	// Database prefix
	protected $dbPrefix = '';
	
	/**
	 * Returns number of executed queries
	 *
	 * @access	public
	 * @return	integer	number of executed queries
	 **/
	 
	public function GetQueryCount ()
	{
		return count ($execQueries);
	}
	
	/**
	 * Returns array of all executed queries
	 *
	 * @access	public
	 * @return	array	array of executed queries
	 **/

	public function GetQueryList ()
	{
		return $this->execQueries;
	}
	
	/**
	 * Returns time spent executing queries
	 *
	 * @access	public
	 * @return	double	returns true if driver is ready, false if there was an error
	 **/
	
	public function GetQueryTime ()
	{
		return $this->queryTime;
	}
	
	/**
	 * Returns array list of errors
	 *
	 * @access	public
	 * @return	array	array list of error messages
	 **/
	
	public function GetErrorList ()
	{
		if (isset ($this->dbDriver) === true)
		{
			return $this->dbDriver->GetErrorList ();
		}
		else
		{
			return array ();
		}
	}
	
	/**
	 * Returns last error from database
	 *
	 * @access	public
	 * @return	string	last error
	 **/
	
	public function GetLastError ()
	{
		if (isset ($this->dbDriver) === true)
		{
			$errorList = $this->dbDriver->GetErrorList ();
			return end ($errorList);
		}
		else
		{
			return null;
		}
	}
	
	/**
	 * Returns string of driver status information
	 *
	 * @access	public
	 * @return	string	driver status information
	 **/
	 
	public function DriverStatus ()
	{
		if (isset ($this->dbDriver) === true)
		{
			return $this->dbDriver->GetStatus ();
		}
		else
		{
			return 'No driver loaded.';	
		}
	}
	
	/**
	 * Returns true if driver is connected to database
	 *
	 * @access	public
	 * @return	boolean	returns true if driver is connected to database
	 **/
	 
	public function IsConnected ()
	{
		if (isset ($this->dbDriver) === true)
		{
			return $this->dbDriver->IsConnected ();
		}
		else
		{
			return false;
		}			
	}
	
	
	/**
	 * Loads and prepares driver to use.
	 *
	 * @access	public
	 * @param	string	driver name, supported are: Firebird, MSSQL, MySQL, MySQLi, ODBC, Oracle, PostgreSQL, SQLite
	 * @return	boolean	returns true if driver is ready, false if there was an error
	 **/
	 
	public function PrepareDriver ($driver)
	{
		// Try to load driver
		if ($this->_loadDriver ($driver) === true)
		{
			// Reset class variables
			$this->execQueries = array ();

			$this->queryTime = 0;
			
			$this->dbDriver = new $driver ();
			
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Sets database prefix to use with queries
	 *
	 * @access	public
	 * @param	string	database prefix
	 * @return	void
	 **/
	
	public function SetPrefix ($prefix)
	{
		$this->dbPrefix = $prefix;
	}
	
	/**
	 * Connects to database through abstraction layer
	 *
	 * @access	public
	 * @param	mixed	database server or array of configuration
	 * @param	string	database user
	 * @param	string	database password
	 * @param	string	database name
	 * @param	integer	database port
	 * @param	boolean	persistency
	 * @param	boolean	should database be opened as new link
	 * @return	boolean returns if connection was successful
	 **/
	 
	public function Connect ($server, $user = '', $password = '', $dbName = '', $port = false, $persistency = false, $newLink = false)
	{
		if ( (empty ($server) === false) && (empty ($user) === false) && (empty ($password) === false) )
		{
			if (is_array ($server) === true)
			{
				$this->SetPrefix ($server['database_prefix']);
				
				return $this->Connect ($server['database_host'], $server['database_user'], $server['database_password'], $server['database_name'], $server['database_port'], $server['database_persistency'], $server['database_link']);
			}
			else
			{
				if (isset ($this->dbDriver) === true)
				{
					return $this->dbDriver->Connect ($server, $user, $password, $dbName, $port, $persistency, $newLink);
				}
				else
				{
					return false;
				}
			}
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Closes any open database connection
	 *
	 * @access	public
	 * @return	void
	 **/
	 
	public function Close ()
	{
		if ($this->IsConnected () === true)
		{
			$this->dbDriver->Close ();
		}
	}
	
	/**
	 * Executes SQL query and parses result
	 *
	 * @access	public
	 * @param	string	SQL query
	 * @param	boolean	default true, to automatically parse resul
	 * @return	mixed	returns result based on second parameter
	 **/
	 
	public function Query ($query, $parse = true)
	{
		if ($this->IsConnected())
		{
			$this->execQueries[count ($this->execQueries)] = $query;
			
			$timer = microtime (true);
			
			if ($parse === true)
			{
				$result = $this->ParseResult ($this->dbDriver->Query ($query));
			}
			else
			{
				$result = $this->dbDriver->Query ($query);
			}
			
			$this->queryTime += (microtime (true) - $timer);
			
			return $result;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Parses SQL object
	 *
	 * @access	public
	 * @param	object	SQL data object to parse
	 * @return	array	parses result of SQL object
	 **/
	 
	public function ParseResult ($data)
	{
		$result = array();
		
		while ($row = $this->dbDriver->FetchRow ($data) )
		{
			$result[] = $row;
		}
		
		return $result;
	}
	
	/**
	 * Builds and executes SQL query
	 *
	 * @access	public
	 * @param	string	query type: INSERT, INSERT_SELECT, SELECT, SELECT_DISTINCT, UPDATE, DELETE, TRUNCATE
	 * @param	array	data array: VALUES, FIELDS, FROM, LEFT_JOIN, LIKE, WHERE, IN, GROUP_BY, ORDER_BY, LIMIT
	 * @param	boolean	default - true, executes query and returns parsed result
	 * @return	mixed	returns array of result if third parameter was true, runs constructed query if it was false
	 **/
	 
	public function BuildQuery ($query, $array, $execute = true)
	{
		if (is_array ($array) === false)
		{
			return false;
		}
		else
		{
			foreach ($array as $command => $commandValue)
			{
				$queryArray[strtoupper ($command) ] = $commandValue;
			}
			
			if ( ($array['VALUES'] !== '') && (is_array ($array['VALUES']) === false) )
			{
				$array['VALUES'] = $this->_parseValues ($array['VALUES']);
			}
		}
		
		$sql = '';
		
		if ( (strtoupper ($query) !== 'SELECT') && (strtoupper ($query) !== 'SELECT_DISTINCT') && (is_array ($array['FROM']) === true) )
		{
			$tables = '';
					
			foreach ($array['FROM'] as $tableName => $alias)
			{
				$tables .= $tableName . ',';
			}
					
			$tables = substr ($tables, 0, -1);
			
			$tables = $this->_tablelineConstruct ($tables);
		}
		
		if (!$array['FIELDS'])
		{
			$array['FIELDS'] = '*';
		}
		
		switch (strtoupper ($query) )
		{
			// Insert and insert select queries
			case 'INSERT':
			case 'INSERT_SELECT':			
				$sql .= 'INSERT INTO ' . $tables . ' (' . $this->_fieldConstruct ($array['FIELDS']) . ') ';
					
				$sql .= ($query === 'INSERT') ? 'VALUES (' . $this->_valueConstruct ($array['VALUES']) . ')' : 'SELECT ' . $array['VALUES'] . ' ';
								
				break;				
			case 'SELECT':
			case 'SELECT_DISTINCT':
			
				$sql .= str_replace ('_', ' ', $query) . ' ' . $this->_fieldConstruct ($array['FIELDS']) . ' FROM ';

				// Build table array. We also build an alias array for later checks.
				$tableArray = $aliases = array();
				$usedMultiAlias = false;

				foreach ($array['FROM'] as $tableName => $alias)
				{
					if (is_array ($alias) )
					{
						$usedMultiAlias = true;

						foreach ($alias as $multiAlias)
						{
							$tableArray[] = $this->dbPrefix . $tableName . ' ' . $multiAlias;
							$aliases[] = $multiAlias;
						}
					}
					else
					{
						$tableArray[] = $this->dbPrefix . $tableName . ' ' . $alias;
						$aliases[] = $alias;
					}
				}

				// We run the following code to determine if we need to re-order the table array. ;)
				// The reason for this is that for multi-aliased tables (two equal tables) in the FROM statement the last table need to match the first comparison.
				// DBMS who rely on this: Oracle, PostgreSQL and MSSQL. For all other DBMS it makes absolutely no difference in which order the table is.
				if ( (empty ($array['LEFT_JOIN']) === false) && (sizeof ($array['FROM']) > 1) && ($usedMultiAlias !== false) )
				{
					// Take first LEFT JOIN
					$join = current ($array['LEFT_JOIN']);

					// Determine the table used there (even if there are more than one used, we only want to have one
					preg_match ('/(' . implode ('|', $aliases) . ')\.[^\s]+/U', str_replace (array ('(', ')', 'AND', 'OR', ' '), '', $join['ON']), $matches);

					// If there is a first join match, we need to make sure the table order is correct
					if (empty ($matches[1]) === false)
					{
						$firstJoinMatch = trim ($matches[1]);
						$tableArray = $last = array();

						foreach ($array['FROM'] as $tableName => $alias)
						{
							if (is_array($alias))
							{
								foreach ($alias as $multiAlias)
								{
									($multiAlias === $firstJoinMatch) ? $last[] = $this->dbPrefix . $tableName . ' ' . $multiAlias : $tableArray[] = $tableName . ' ' . $multiAlias;
								}
							}
							else
							{
								($alias === $firstJoinMatch) ? $last[] = $this->dbPrefix . $tableName . ' ' . $alias : $tableArray[] = $this->dbPrefix . $tableName . ' ' . $alias;
							}
						}

						$tableArray = array_merge ($tableArray, $last);
					}
				}

				$sql .= $this->dbDriver->CustomBuild ('FROM', implode (', ', $tableArray));

				if (empty ($array['LEFT_JOIN']) === false)
				{
					foreach ($array['LEFT_JOIN'] as $join)
					{
						$sql .= ' LEFT JOIN ' . key ($join['FROM']) . ' ' . current ($join['FROM']) . ' ON (' . $join['ON'] . ')';
					}
				}
				
				if (empty ($array['LIKE']) === false)
				{
					$sql .= ' ' . $this->_likeExpression ($array['LIKE']);
				}

				if (empty ($array['WHERE']) === false)
				{
					$sql .= ' WHERE ' . $this->dbDriver->CustomBuild ('WHERE', $this->_fieldConstruct ($array['WHERE']) );
				}
				
				if (empty ($array['IN']) === false)
				{
					$sql .= $this->_inSet ( $this->_fieldConstruct ($array['IN']['FIELD']), $array['IN']['VALUES'], $array['IN']['NEGATE']);
				}

				if (empty ($array['GROUP_BY']) === false)
				{
					$sql .= ' GROUP BY ' . $this->_fieldConstruct ($array['GROUP_BY']);
				}

				if (empty ($array['ORDER_BY']) === false)
				{
					$sql .= ' ORDER BY ' . $this->_fieldConstruct ($array['ORDER_BY']);
				}
				
				if (empty ($array['LIMIT']) === false)
				{
					$sql = $this->_limit ($sql, $array['LIMIT']['TOTAL'], $array['LIMIT']['OFFSET']);
				}
				
				break;			
			case 'UPDATE':
			
				$sql .= 'UPDATE ' . $tables . ' SET ' . $this->_updateConstruct ($array['UPDATE']);
				
				if (empty ($array['LIKE']) === false)
				{
					$sql .= ' ' . $this->_likeExpression ($array['LIKE']);
				}

				if (empty ($array['WHERE']) === false)
				{
					$sql .= ' WHERE ' . $this->dbDriver->CustomBuild ('WHERE', $this->_fieldConstruct ($array['WHERE']) );
				}
				
				if (empty ($array['IN']) === false)
				{
					$sql .= $this->_inSet ( $this->_fieldConstruct ($array['IN']['FIELD']), $array['IN']['VALUES'], $array['IN']['NEGATE']);
				}
				
				break;
			
			case 'DELETE':
			
				$sql .= 'DELETE FROM ' . $tables;
				
				if (empty ($array['LIKE']) === false)
				{
					$sql .= ' ' . $this->_LikeExpression ($array['LIKE']);
				}

				if (empty ($array['WHERE']) === false)
				{
					$sql .= ' WHERE ' . $this->dbDriver->CustomBuild ('WHERE', $this->_fieldConstruct ($array['WHERE']) );
				}
				
				if (empty ($array['IN']) === false)
				{
					$sql .= $this->_inSet ( $this->_fieldConstruct ($array['IN']['FIELD']), $array['IN']['VALUES'], $array['IN']['NEGATE']);
				}
				
				break;
				
			case 'TRUNCATE':
				$sql .= 'TRUNCATE TABLE ' . $tables;
				
				break;
				
			default:
				$sql .= strtoupper ($query) . ' ' . implode('', $array);
				
				break;
		}
		
		if ($execute === true)
		{			
			$result = $this->Query ($sql);
			
			return ($result !== false) ? $result : array ();
		}
		else
		{
			return $sql;
		}
	}
	
	/**
	 * Selects data records from database
	 *
	 * @access	public
	 * @param	mixed	array or string of tables
	 * @param	string	condition of records
	 * @param	mixed	array or string of selected fields
	 * @return	array	parsed result from database
	 **/
	 
	public function Select ($tables, $condition = '', $fields = '*')
	{
		if (is_array ($tables))
		{
			$tables = $this->_parseValues ($tables, false);
		}
		
		if (is_array ($fields))
		{
			$fields = $this->_parseValues ($fields, false);
		}
		
		$fields = $this->_fieldConstruct ($fields);
		$tables = $this->_tablelineConstruct ($tables);
		
	
		if (empty ($condition) === true)
		{
			$query = 'SELECT ' . $fields . ' FROM ' . $tables;
		}
		else
		{
			$condition = $this->_fieldConstruct ($condition);
			
			$query = 'SELECT ' . $fields . ' FROM ' . $tables . ' WHERE ' . $condition;
		}
		
		return ($this->Query ($query));
	}
	
	/**
	 * Updates records in database
	 *
	 * @access	public
	 * @param	mixed	array or string of tables that will be updated
	 * @param	string	condition of records
	 * @param	string	update query
	 * @return	array	parsed result from database
	 **/
	 
	public function Update ($tables, $condition, $update)
	{
		if (is_array ($tables))
		{
			$tables = $this->_parseValues ($tables, false);
		}

		$tables = $this->_tablelineConstruct ($tables);
		$condition = $this->_fieldConstruct ($condition);
		$update = $this->_updateConstruct ($update);
	
		$query = 'UPDATE ' . $tables . ' SET ' . $update . ' WHERE ' . $condition;
		
		return ($this->Query ($query));
	}
	
	/**
	 * Insert a record to database
	 *
	 * If third parameter is empty, second parameter are values and first is the table.
	 *
	 * @access	public
	 * @param	mixed	array or string of tables
	 * @param	mixed	array or string of fields
	 * @param	mixed	array or string of values
	 * @return	array	parsed result from database
	 **/
	 
	public function Insert ($tables, $fields, $values = '')
	{
		if (is_array ($tables))
		{
			$tables = $this->_parseValues ($tables, false);
		}
		
		if (is_array ($fields))
		{
			if (empty ($values) === true)
			{
				$fields = $this->_parseValues ($fields);
			}
			else
			{
				$fields = $this->_parseValues ($fields, false);
			}
		}
		
		if (is_array ($values))
		{
			$values = $this->_parseValues ($values);
		}
		
		$tables = $this->_tablelineConstruct ($tables);
		
		if (empty ($values) === true)
		{
			$query = 'INSERT INTO ' . $tables . ' VALUES (' . $fields . ')';
		}
		else
		{
			$fields = $this->_fieldConstruct ($fields);
		
			$query = 'INSERT INTO ' . $tables . ' (' . $fields . ') VALUES (' . $values . ')';
		}
		
		return ($this->Query ($query));
	}
	
	/**
	 * Deletes records in database
	 *
	 * @access	public
	 * @param	mixed	array or string of tables that will be updated
	 * @param	string	condition of records
	 * @return	array	parsed result from database
	 **/
	 
	public function Delete ($tables, $condition = '')
	{
		if (is_array ($tables))
		{
			$tables = $this->_parseValues ($tables, false);
		}
		
		$tables = $this->_tablelineConstruct ($tables);
		
		if (empty ($condition) === true)
		{
			$query = 'DELETE FROM ' . $tables;
		}
		else
		{
			$condition = $this->_fieldConstruct ($condition);
		
			$query = 'DELETE FROM ' . $tables . ' WHERE ' . $condition;
		}
		
		return ($this->Query ($query));
	}
	
	/**
	 * Truncates table
	 *
	 * @access	public
	 * @param	string	table
	 * @return	array	parsed result from database
	 **/
	
	public function Truncate ($table)
	{
		// Check for only one table
		if (strpos ($table, ',') >= 0)
		{
			$table = substr ($table, 0, strpos ($table, ','));
		}
		
		$table = $this->_tablelineConstruct ($table);
		
		$query = 'TRUNCATE TABLE ' . $table;
		
		return ($this->Query ($query));
	}
	
	/**
	 * Loads DBMS driver from file and creates an instance
	 *
	 * @access	private
	 * @param	string	DBMS driver
	 * @return	boolean	returns true if loading was successful
	 **/
	 
	private function _loadDriver ($driver)
	{
		// If driver is not loaded yet
		if (class_exists ($driver) === false)
		{
			// Attempt to load it
			if (file_exists (DEFAULT_DATABASE . '/' . strtolower ($driver) . EXTENSION) === true)
			{
				include_once (DEFAULT_DATABASE . '/' . strtolower ($driver) . EXTENSION);
			}
			
			// If it now exists
			if (class_exists ($driver) === true)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return true;
		}
	}
	
	/**
	 * Limit construction helper function
	 *
	 * @access	private
	 * @param	string	query
	 * @param	integer	total
	 * @param	integer	offset
	 * @return	string	constructed limit query
	 **/
	
	private function _limit ($query, $total, $offset = 0)
	{
		if (empty ($query) === true)
		{
			return false;
		}

		// Never use a negative total or offset
		$total = ($total < 0) ? 0 : $total;
		$offset = ($offset < 0) ? 0 : $offset;

		return $this->dbDriver->Limit ($query, $total, $offset);
	}
	
	/**
	 * Parses database values
	 *
	 * @access	private
	 * @param	string	values
	 * @return	string	parsed values
	 **/
	
	private function _parseValues ($values, $quotes = true)
	{
		$values = explode (',', $values);
		
		foreach ($values as $key => $value)
		{
			$value = trim ($value);
			
			// Stripping start quote
			if (ord (substr ($value, 0, 1) ) == 39)
			{
				$value = substr ($value, 1);
			}
			
			// Stripping end quote
			if (ord (substr ($value, -1) ) == 39)
			{
				$value = substr ($value, 0, -1);
			}
			
			$value = $this->_validateValue ($value);
			
			if ($quotes === true)
			{
				$value = chr (39) . $value . chr (39);
			}
		}
		
		$values = implode (',', $values);
		
		return $values;
	}

	/**
	 * Validates values
	 *
	 * @access	private
	 * @param	mixed	value
	 * @return	mixed	validated value
	 **/
	 
	private function _validateValue ($var)
	{
		if (is_null ($var) === true)
		{
			return 'NULL';
		}
		else if (is_string ($var) === true)
		{
			return "'" . $this->dbDriver->Escape ($var) . "'";
		}
		else
		{
			return (is_bool ($var) === true) ? intval ($var) : $var;
		}
	}
	
	/**
	 * Correctly adjust LIKE expression for special characters
	 *
	 * @access	private
	 * @param	string	The expression to use. Every wildcard is escaped
	 * @return	string	LIKE expression including the keyword
	 **/
	 
	private function _likeExpression ($expression)
	{
		if (isset($this->dbDriver) === true)
		{
			$expression = str_replace (array ('_', '%'), array ("\_", "\%"), $expression);
			$expression = str_replace (array (chr (0) . "\_", chr (0) . "\%"), array ('_', '%'), $expression);
	
			return $this->dbDriver->LikeExpression('LIKE \'' . $this->dbDriver->Escape ($expression) . '\'');
		}
		else
		{
			return false;
		}
	}

	/**
	 * Build IN or NOT IN sql comparison string, uses <> or = on single element
	 * arrays to improve comparison speed
	 *
	 * @access private
	 * @param	string	name of the sql column that shall be compared
	 * @param	array	array of values that are allowed (IN) or not allowed (NOT IN)
	 * @param	bool	true for NOT IN (), false for IN () (default)
	 * @param	bool	If true, allow $array to be empty, this function will return 1=1 or 1=0 then. Default to false.
	 * @return	string	constructed IN query
	 **/
	
	private function _inSet ($field, $array, $negate = false, $allowEmptySet = false)
	{
		if (!sizeof ($array))
		{
			if ($allowEmptySet)
			{
				// NOT IN () actually means everything so use a tautology
				if ($negate)
				{
					return '1=1';
				}
				// IN () actually means nothing so use a contradiction
				else
				{
					return '1=0';
				}
			}
		}

		if (!is_array ($array))
		{
			$array = array ($array);
		}

		if (sizeof ($array) == 1)
		{
			@reset ($array);
			$var = current ($array);

			return $field . ($negate ? ' <> ' : ' = ') . $this->_validateValue ($var);
		}
		else
		{
			return $field . ($negate ? ' NOT IN ' : ' IN ') . '(' . implode(', ', array_map (array ($this, '_validateValue'), $array) ) . ')';
		}
	}
	
	/**
	 * Adds prefix to all tables
	 *
	 * @access	private
	 * @param	mixed	string with tables divided by comma or array of tables
	 * @return	string	constructed table list
	 **/
	
	private function _tablelineConstruct ($tables)
	{
		// No spaces between prefix & postfix and table name
		$tables = str_replace (' ', '', $tables);
		
		if (is_array ($tables) === false)
		{
			$tables = explode (',', $tables);
			$tableline = '';
		}
		
		for ($i = 0; $i < count ($tables); $i++)
		{
			$tableline .= $this->dbPrefix . $tables[$i];
			
			// Adding comma between tables
			if ($i != (count ($tables) - 1) )
			{
				$tableline .= ',';
			}
		}
		
		return $tableline;		
	}
	
	/**
	 * Adds prefix to fields, so they can be used connecting multiple tables
	 *
	 * @access	private
	 * @param	string	fields condition
	 * @return	string	constructed fields condition
	 **/
	
	private function _fieldConstruct ($condition)
	{
		// Preparing to save quotes
		$quoteBase = array ();
		
		$quote = false;
		
		$newCondition = '';
		
		// Going through query character by character
		for ($i = 0, $x = 0; $i < strlen ($condition); $i++)
		{
			// If we're not in quote
			if ($quote == false)
			{
				// Checking character for quote, constructing new update with {DATA<quote_number>}
				if (ord ($condition{$i}) == 39)
				{
					$quote = true;
					$newCondition .= '{DATA' . $x . '}';
					// Fixes notice of non-existing offset
					$quoteBase[$x] = '';
				}
				// Continuing to create the query
				else
				{
					$newCondition .= $condition{$i};
				}
			}
			// We're currently located inside SQL quotes
			else
			{
				// If we're ordered to skip next character using the \
				if (ord ($condition{$i}) == 92)
				{
					$quoteBase[$x] .= $condition{$i};
					$i++;
					$quoteBase[$x] .= $condition{$i};
				}
				// End of the quote
				elseif (ord ($condition{$i}) == 39)
				{
					// Fixes bug of empty quotes
					$quoteBase[$x] .= ' ';
					
					$quote = false;
					$x++;
				}
				// We're collecting the quotes into $quoteBase
				else
				{
					$quoteBase[$x] .= $condition{$i};
				}
			}
		}
		
		// Quotes removed from condition clause
		$condition = $newCondition;
		
		// Divide each word by space
		$condition = explode (' ', $condition);
						
		for ($i = 0; $i < count ($condition); $i++)
		{
			// Incase there is a single word of two compared different table fields
			$subCondition = explode ('=', $condition[$i]);

			// Going through both compared fields
			for ($x = 0; $x < count ($subCondition); $x++)
			{
				if (strpos ($subCondition[$x], '\'') !== false)
				{
					continue;
				}
								
				$subField = explode ('.', $subCondition[$x]);
				
				// Replacing the table with prefixes only if there is table in the field
				if (count ($subField) == 2)
				{
					$subField[0] = $this->dbPrefix . $subField[0];
				}
				
				// Joining parsed tables
				$subCondition[$x] = implode ('.', $subField);
			}
			
			// Joining parsed fields
			$condition[$i] = implode ('=', $subCondition);			
		}
		
		// Constructing parsed condition
		$condition = implode (' ', $condition);
		
		// Replacing collected quotes back with DATA
		for ($i = 0; $i < count ($quoteBase); $i++)
		{
			$condition = str_replace ('{DATA' . $i . '}', chr (39) . substr ($quoteBase[$i], 0, strlen ($quoteBase[$i]) - 1) . chr(39), $condition);
		}
		
		return $condition;
	}
	
	/**
	 * Adds prefix to update condition
	 *
	 * @access	private
	 * @param	string	update condition
	 * @return	string	constructed update condition
	 **/
	
	private function _updateConstruct ($update)
	{
		// Quote parsing switch
		$quote = false;
	
		// Removing quoted data from query
		$newUpdate = '';
	
		$quoteBase = array();
		
		// Going through query character by character
		for ($i = 0, $x = 0; $i < strlen ($update); $i++)
		{
			// If we're not in quote
			if ($quote == false)
			{
				// Checking character for quote, constructing new update with {DATA<quote_number>}
				if (ord ($update{$i}) == 39)
				{
					$quote = true;
					$newUpdate .= '{DATA' . $x . '}';
					$quoteBase[$x] = '';
				}
				// Continuing to create the query
				else
				{
					$newUpdate .= $update{$i};
				}
			}
			// We're currently located inside SQL quotes
			else
			{
				// If we're ordered to skip next character using the \
				if (ord ($update{$i}) == 92)
				{
					$quoteBase[$x] .= $update{$i};
					$i++;
					$quoteBase[$x] .= $update{$i};
				}
				// End of the quote
				elseif (ord ($update{$i}) == 39)
				{
					// Fixes bug of empty quotes
					$quoteBase[$x] .= ' ';
					
					$quote = false;
					$x++;
				}
				// We're collecting the quotes into $quote_base
				else
				{
					$quoteBase[$x] .= $update{$i};					
				}
			}
		}

		// Removing spaces from $new_update
		$newUpdate = str_replace (' ', '', $newUpdate);
		// Splitting new update string with ,
		$parsed = explode (',', $newUpdate);
		
		// For each of the 2 comparisons
		for ($i = 0; $i < count ($parsed); $i++)
		{
			$subUpdate = explode ('=', $parsed[$i]);
	
			for ($x = 0; $x < count ($subUpdate); $x++)
			{
				if (strpos ($subUpdate[$x], '\'') !== false)
				{
					continue;

				}
				
				// Each field needs prefix & postfix added
				$subField = explode ('.', $subUpdate[$x]);
								
				if (count ($subField) == 2)
				{
					$subField[0] = $this->dbPrefix . $subField[0];
				}
					
				$subUpdate[$x] = implode('.', $subField);
			}
				
			$parsed[$i] = implode ('=', $subUpdate);			
		}
			
		$parsed = implode (', ', $parsed);
		
		
		// Replacing collected quotes back with DATA
		for ($i = 0; $i < count ($quoteBase); $i++)
		{
			$parsed = str_replace ('{DATA' . $i . '}', chr (39) . substr ($quoteBase[$i], 0, strlen ($quoteBase[$i]) - 1) . chr (39), $parsed);
		}
		
		// Returning parsed result
		return $parsed;
	}
	
	/**
	 * Construct string of values, adds single quotes and comma
	 *
	 * @access	private
	 * @param	mixed	array with values
	 * @return	string	constructed value list with single quotes
	 **/
	
	private function _valueConstruct ($array)
	{
		if (is_array ($array) === true)
		{
			$arraySize = count ($array);
			
			for ($i = 0; $i < $arraySize; $i++)
			{
				$array[$i] = chr (39) . $array[$i] . chr (39);
			}
			
			return implode (',', $array);
		}
		else
		{
			return chr (39) . $array . chr (39);
		}
	}
}
?>