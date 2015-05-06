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
 *   Low level database abstraction layer for SQLite DBMS.                 *
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
 * SQLite
 *
 * Low-level database driver for SQLite DBMS
 *
 * @package	database
 * @require	false
 * @author	ArvYStaTe.net Team
 **/

class SQLite extends DatabaseDriver
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
		$this->server = $server . (($port) ? ':' . $port : '');
		$this->dbName = $dbName;

		$error = '';
		$this->dbConnectId = ($this->persistency) ? @sqlite_popen ($this->server, 0666, $error) : @sqlite_open ($this->server, 0666, $error);

		if ($this->dbConnectId)
		{
			@sqlite_query ('PRAGMA short_column_names = 1', $this->dbConnectId);
			//@sqlite_query('PRAGMA encoding = "UTF-8"', $this->dbConnectId);
			
			return true;
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
			return @sqlite_close ($this->dbConnectId);
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
		return @sqlite_error_string (@sqlite_last_error ($this->dbConnectId));
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
			if (($this->query_result = @sqlite_query ($query, $this->dbConnectId)) === false)
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

		return true;
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
		return @sqlite_escape_string ($message);
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

		return ($result !== false) ? @sqlite_fetch_array ($result, SQLITE_ASSOC) : false;
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
			$total = -1;
		}

		$query .= "\n LIMIT " . ((!empty ($offset)) ? $offset . ', ' . $total : $total);

		return $this->Query ($query);
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
		// Unlike LIKE, GLOB is case sensitive (unfortunatly). SQLite users need to live with it!
		// We only catch * and ? here, not the character map possible on file globbing.
		$expression = str_replace (array(chr(0) . '_', chr(0) . '%'), array (chr (0) . '?', chr (0) . '*'), $expression);

		$expression = str_replace (array ('?', '*'), array ("\?", "\*"), $expression);
		$expression = str_replace (array (chr(0) . "\?", chr (0) . "\*"), array ('?', '*'), $expression);

		return 'GLOB \'' . $this->Escape ($expression) . '\'';
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

		return ($result !== false) ? @sqlite_seek ($result, $rownum) : false;
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
			$result = @sqlite_query ('SELECT sqlite_version() AS version', $this->dbConnectId);
			$row = @sqlite_fetch_array ($result, SQLITE_ASSOC);

			$this->serverVersion = (!empty ($row['version'])) ? $row['version'] : 0;
		}

		return ($raw === true) ? $this->serverVersion : 'SQLite ' . $this->serverVersion;
	}
}

?>