<?php
/***************************************************************************
 *   Application          : Emotion                                        *
 *   -------------------------------------------------------------------   *
 *   Package              : @database                                      *
 *   File                 : mssql.php                                      *
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
 *   Low level database abstraction layer for Microsoft SQL Server DBMS.   *
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
 * MSSQL
 *
 * Low-level database driver for Microsoft SQL Server DBMS
 *
 * @package	database
 * @require	false
 * @author	ArvYStaTe.net Team
 **/

class MSSQL extends DatabaseDriver
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
		
		$this->persistency = $persistency;
		$this->user = $user;
		$this->dbName = $dbName;

		// Check if its windows, because it uses different port format
		$port_delimiter = (defined ('PHP_OS') && substr (PHP_OS, 0, 3) === 'WIN') ? ',' : ':';
		$this->server = $server . (($port) ? $port_delimiter . $port : '');

		@ini_set ('mssql.charset', 'UTF-8');
		@ini_set ('mssql.textlimit', 2147483647);
		@ini_set ('mssql.textsize', 2147483647);

		if (version_compare (PHP_VERSION, '5.1.0', '>=') || (version_compare (PHP_VERSION, '5.0.0-dev', '<=') && version_compare (PHP_VERSION, '4.4.1', '>=')) )
		{
			$this->dbConnectId = ($this->persistency) ? @mssql_pconnect ($this->server, $this->user, $password, $newLink) : @mssql_connect ($this->server, $this->user, $password, $newLink);
		}
		else
		{
			$this->dbConnectId = ($this->persistency) ? @mssql_pconnect ($this->server, $this->user, $password) : @mssql_connect ($this->server, $this->user, $password);
		}

		if ($this->dbConnectId && $this->dbName != '')
		{
			if (!@mssql_select_db ($this->dbName, $this->dbConnectId))
			{
				$this->dbConnectId = false;
				
				$this->StoreError ();
				
				return false;
			}
			else
			{
				return true;
			}
		}
		else
		{
			$this->dbConnectId = false;
				
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
		if ($this->IsConnected())
		{
			return @mssql_close ($this->dbConnectId);
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
		$message = @mssql_get_last_message ();

		// Get full error message if possible
		$sql = 'SELECT CAST(description as varchar(255)) as message
			FROM master.dbo.sysmessages
			WHERE error = ' . $error['code'];
		$result_id = @mssql_query ($sql);
		
		if ($result_id)
		{
			$row = @mssql_fetch_assoc ($result_id);
			
			if (!empty($row['message']))
			{
				$message .= ' ' . $row['message'];
			}
			
			@mssql_free_result ($result_id);
		}

		return $message;
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
			if (($this->queryResult = @mssql_query ($query, $this->dbConnectId)) === false)
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

		return @mssql_free_result ($result);
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
		return str_replace (array ("'", "\0"), array ("''", ''), $message);
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

		$row = @mssql_fetch_assoc ($result);

		// I hope i am able to remove this later... hopefully only a PHP or MSSQL bug
		if ($row)
		{
			foreach ($row as $key => $value)
			{
				$row[$key] = ($value === ' ' || $value === NULL) ? '' : $value;
			}
		}

		return $row;
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
		// Since TOP is only returning a set number of rows we won't need it if total is set to 0 (return all rows)
		if ($total)
		{
			// We need to grab the total number of rows + the offset number of rows to get the correct result
			if (strpos ($query, 'SELECT DISTINCT') === 0)
			{
				$query = 'SELECT DISTINCT TOP ' . ($total + $offset) . ' ' . substr ($query, 15);
			}
			else
			{
				$query = 'SELECT TOP ' . ($total + $offset) . ' ' . substr ($query, 6);
			}
		}

		$result = $this->Query ($query);

		// Seek by $offset rows
		if ($offset)
		{
			$this->RowSeek ($offset, $result);
		}

		return $result;
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
		return $expression . " ESCAPE '\\'";
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

		return ($result !== false) ? @mssql_data_seek ($result, $rownum) : false;
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
			$result = @mssql_query ("SELECT SERVERPROPERTY('productversion'), SERVERPROPERTY('productlevel'), SERVERPROPERTY('edition')", $this->dbConnectId);

			$row = false;
			
			if ($result)
			{
				$row = @mssql_fetch_assoc ($result_id);
				@mssql_free_result ($result_id);
			}

			$this->serverVersion = ($row) ? trim (implode (' ', $row)) : 0;
		}

		return ($raw === true) ? $this->serverVersion : ($this->serverVersion) ? 'MSSQL ' . $this->serverVersion : 'MSSQL';
	}
}

?>