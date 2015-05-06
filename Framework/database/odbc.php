<?php
/***************************************************************************
 *   Application          : Emotion                                        *
 *   -------------------------------------------------------------------   *
 *   Package              : @database                                      *
 *   File                 : odbc.php                                       *
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
 *   Low level database abstraction layer for Microsoft ODBC Driver.       *
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
 * ODBC
 *
 * Low-level database driver for Microsoft ODBC driver
 *
 * @package	database
 * @require	false
 * @author	ArvYStaTe.net Team
 **/

class ODBC extends DatabaseDriver
{
	private $lastQueryText = '';

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

		$port_delimiter = (defined ('PHP_OS') && substr (PHP_OS, 0, 3) === 'WIN') ? ',' : ':';
		$this->server = $server . (($port) ? $port_delimiter . $port : '');

		$max_size = @ini_get ('odbc.defaultlrl');
		
		if (!empty ($max_size))
		{
			$unit = strtolower (substr($max_size, -1, 1));
			$max_size = (int) $max_size;

			if ($unit == 'k')
			{
				$max_size = floor ($max_size / 1024);
			}
			else if ($unit == 'g')
			{
				$max_size *= 1024;
			}
			else if (is_numeric ($unit))
			{
				$max_size = floor ((int) ($max_size . $unit) / 1048576);
			}
			
			$max_size = max (8, $max_size) . 'M';

			@ini_set ('odbc.defaultlrl', $max_size);
		}

		$this->dbConnectId = ($this->persistency) ? @odbc_pconnect ($this->server, $this->user, $password) : @odbc_connect ($this->server, $this->user, $password);

		if (!$this->dbConnectId)
		{
			$this->StoreError ();
			
			return false;
		}
		else
		{
			return true;
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
			return @odbc_close ($this->dbConnectId);
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
	
	public function Error()
	{
		return @odbc_errormsg ();
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
			$this->lastQueryText = $query;
			
			if ( ($this->queryResult = @odbc_exec ($this->dbConnectId, $query)) === false)
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
		
		return @odbc_free_result ($result);
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

		return ($result !== false) ? @odbc_fetch_array ($result) : false;
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

		$this->FreeResult ($result);
		
		$result = $this->Query ($this->lastQueryText);

		if ($result === false)
		{
			return false;
		}

		// We do not fetch the row for rownum == 0 because then the next resultset would be the second row
		for ($i = 0; $i < $rownum; $i++)
		{
			if (!$this->FetchRow ($result))
			{
				return false;
			}
		}

		return true;
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
			$result_id = @odbc_exec ($this->dbConnectId, "SELECT SERVERPROPERTY('productversion'), SERVERPROPERTY('productlevel'), SERVERPROPERTY('edition')");

			$row = false;
			
			if ($result_id)
			{
				$row = @odbc_fetch_array ($result_id);
				@odbc_free_result ($result_id);
			}

			$this->serverVersion = ($row) ? trim (implode (' ', $row)) : 0;
		}

		return ($raw === true) ? $this->serverVersion : ($this->serverVersion) ? 'MSSQL (ODBC)' . $this->serverVersion : 'MSSQL (ODBC)';
	}
}

?>