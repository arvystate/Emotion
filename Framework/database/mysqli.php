<?php
/***************************************************************************
 *   Application          : Emotion                                        *
 *   -------------------------------------------------------------------   *
 *   Package              : @database                                      *
 *   File                 : mysqli.php                                     *
 *   Version              : 1.0.0                                          *
 *   Status               : Complete, Not tested                           *
 *   -------------------------------------------------------------------   *
 *   Begin                : Thursday, Aug 11, 2011                         *
 *   CopyRight            : (C) 2011 ArvYStaTe.net Team                    *
 *   E-Mail               : support@arvystate.net                          *
 *   Last edit            : Thursday, Aug 11, 2011                         *
 *                                                                         *
 *                                                                         *
 ***************************************************************************
 
 ***************************************************************************
 *   File description                                                      *
 *   -------------------------------------------------------------------   *
 *   Low level database abstraction layer for MySQL Improved DBMS.         *
 *   -------------------------------------------------------------------   *
 *                                                                         *
 ***************************************************************************
 
 ***************************************************************************
 *   Change log                                                            *
 *   -------------------------------------------------------------------   *
 *    + [08/11/11] - File created                                          *
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
 * MySQLi
 *
 * Low-level database driver for MySQL improved DBMS
 *
 * @package	database
 * @require	false
 * @author	ArvYStaTe.net Team
 **/

class MySQLi extends DatabaseDriver
{
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
	
	public function Connect ($server, $user, $password, $dbName, $port = false, $persistency = false, $newLink = false)
	{
		$this->ClearErrors ();
		
		$this->user = $user;
		$this->server = $server . ((isset ($port) === true) ? ':' . $port : '');
		$this->dbName = $dbName;
		$this->newLink = $newLink;
		$this->persistency = $persistency;

		// Persistant connections not supported by the mysqli extension?
		$this->dbConnectId = @mysqli_connect ($this->server, $this->user, $password, $this->dbName, $port);

		if ( ($this->dbConnectId !== false) && ($this->dbName != '') )
		{
			if ( @mysqli_select_db ($this->dbName, $this->dbConnectId) )
			{
				@mysqli_query ($this->dbConnectId, "SET NAMES 'utf8'");
	
				// enforce strict mode on databases that support it
				if (version_compare ($this->ServerVersion (true), '5.0.2', '>='))
				{
					$result = @mysqli_query ($this->dbConnectId, 'SELECT @@session.sql_mode AS sql_mode');
					$row = @mysqli_fetch_assoc ($result);
					@mysqli_free_result ($result);
	
					$modes = array_map ('trim', explode (',', $row['sql_mode']));
	
					// TRADITIONAL includes STRICT_ALL_TABLES and STRICT_TRANS_TABLES
					if (!in_array('TRADITIONAL', $modes))
					{
						if (!in_array ('STRICT_ALL_TABLES', $modes))
						{
							$modes[] = 'STRICT_ALL_TABLES';
						}
	
						if (!in_array ('STRICT_TRANS_TABLES', $modes))
						{
							$modes[] = 'STRICT_TRANS_TABLES';
						}
					}
	
					$mode = implode (',', $modes);
					@mysqli_query ($this->dbConnectId, "SET SESSION sql_mode='{$mode}'");
				}
				
				return true;
			}
			else
			{
				@mysqli_close ();
				$this->dbConnectId = false;
				
				$this->StoreError ();
				
				return false;
			}
		}
		else
		{
			$this->StoreError ();
			
			return false;
		}
	}	

	/**
	 * Closes current database connection
	 *
	 * @access	public
	 * @return	boolean	returns true if closing was successful
	 **/

	public function Close()
	{
		if ($this->IsConnected ())
		{
			return @mysqli_close ($this->dbConnectId);
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Returns last error
	 *
	 * @access	public
	 * @return	string error message
	 **/
	
	public function Error ()
	{
		if (!$this->dbConnectId)
		{
			return @mysqli_error;
		}

		return @mysqli_error ($this->dbConnectId);
	}
	
	/**
	 * Executes query
	 *
	 * @access	public
	 * @param	string	SQL query
	 * @return	object	database result object
	 **/

	public function Query ($query = '')
	{
		if ($query != '')
		{			
			if ( ($this->queryResult = @mysqli_query ($query, $this->dbConnectId)) === false)
			{				
				$this->StoreError ();
				
				return false;
			}
			
			return $this->queryResult;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Free memory allocated by database result object
	 *
	 * @access	public
	 * @param	object	database result object
	 * @return	boolean	returns true if deallocation was successful
	 **/
	
	public function FreeResult ($result = false)
	{
		if ($result === false)
		{
			$result = $this->queryResult;
		}
		
		return @mysqli_free_result ($result);
	}
	
	/**
	 * Escape SQL dangerous characters
	 *
	 * @access	public
	 * @param	string	message
	 * @return	string	escaped message
	 **/
	
	public function Escape ($message)
	{
		return @mysqli_real_escape_string ($this->dbConnectId, $message);
	}
	
	/**
	 * Fetches current row to array
	 *
	 * @access	public
	 * @param	object	database result object
	 * @return	array	array of fields of current row
	 **/
	
	public function FetchRow ($result = false)
	{
		if ($result === false)
		{
			$result = $this->queryResult;
		}
				
		return ($result !== false) ? @mysqli_fetch_assoc ($result) : false;
	}
	
	/**
	 * Constructs specified limit query
	 *
	 * @access	public
	 * @param	string	query
	 * @param	integer	total
	 * @param	integer	offset
	 * @return	string	limit constructed string
	 **/
	
	public function Limit ($query, $total, $offset = 0)
	{
		// if $total is set to 0 we do not want to limit the number of rows
		if ($total == 0)
		{
			// Having a value of -1 was always a bug
			$total = '18446744073709551615';
		}

		$query .= ' LIMIT ' . ( (empty ($offset) === false) ? $offset . ', ' . $total : $total);

		return $query;
	}
	
	/**
	 * Constructs correct like expression
	 *
	 * @access	public
	 * @param	string	expression
	 * @return	string	constructed like expression
	 **/
	
	public function LikeExpression ($expression)
	{
		return $expression;
	}
	
	/**
	 * Seeks to row number in result
	 *
	 * @access	public
	 * @param	integer	row number
	 * @param	object	database result object
	 * @return	boolean	returns true if seek was successful
	 **/
		
	public function RowSeek ($rownum, &$result)
	{
		if ($result === false)
		{
			$result = $this->queryResult;
		}

		return ($result !== false) ? @mysqli_data_seek ($result, $rownum) : false;
	}
	
	/**
	 * Returns string of DBMS version
	 *
	 * @access	public
	 * @param	boolean	default false: if only version number is returned
	 * @return	string	server version
	 **/
	
	public function ServerVersion ($raw = false)
	{
		if (empty ($this->serverVersion) === true)
		{
			$result = @mysqli_query ('SELECT VERSION() AS version', $this->dbConnectId);
			$row = @mysqli_fetch_assoc ($result);
			@mysqli_free_result ($result);
	
			$this->serverVersion = $row['version'];
		}

		return ($raw === true) ? $this->serverVersion : 'MySQL(i) ' . $this->serverVersion;
	}
	
	/**
	 * DBMS specifid builds
	 *
	 * @access	public
	 * @param	string	command stage on which custom build needs to be performed
	 * @param	string	data query on which custom build is performed
	 * @return	string	data query with custom modifications
	 **/
	
	public function CustomBuild ($stage, $data)
	{
		switch ($stage)
		{
			case 'FROM':
				$data = '(' . $data . ')';
				break;
		}

		return $data;
	}
}

?>