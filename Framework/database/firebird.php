<?php
/***************************************************************************
 *   Application          : Emotion                                        *
 *   -------------------------------------------------------------------   *
 *   Package              : @database                                      *
 *   File                 : firebird.php                                   *
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
 *   Low level database abstraction layer for Firebird/Interbase DBMS.     *
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
 * Firebird
 *
 * Low-level database driver for Firebird/Interbase DBMS
 *
 * @package	database
 * @require	false
 * @author	ArvYStaTe.net Team
 **/

class Firebird extends DatabaseDriver
{
	private $lastQueryText = '';
	private $serviceHandle = false;

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
		$this->dbName = str_replace ('\\', '/', $dbName);

		// There are three possibilities to connect to an interbase db
		if (!$this->server)
		{
			$useDatabase = $this->dbName;
		}
		else if (strpos ($this->server, '//') === 0)
		{
			$useDatabase = $this->server . $this->dbName;
		}
		else
		{
			$useDatabase = $this->server . ':' . $this->dbName;
		}

		$this->dbConnectId = ($this->persistency) ? @ibase_pconnect($useDatabase, $this->user, $password, false, false, 3) : @ibase_connect ($useDatabase, $this->user, $password, false, false, 3);

		$this->serviceHandle = (function_exists('ibase_service_attach') && $this->server) ? @ibase_service_attach($this->server, $this->user, $password) : false;

		if ( (!$this->dbConnectId) || ($this->dbName == '') )
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

	public function Close ()
	{
		if ($this->IsConnected ())
		{
			if ($this->serviceHandle !== false)
			{
				@ibase_service_detach ($this->serviceHandle);
			}
	
			return @ibase_close ($this->dbConnectId);
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
		return @ibase_errmsg ();
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
			$array = array ();
			
			$this->lastQueryText = $query;
			// We overcome Firebird's 32767 char limit by binding vars
			
			if (strlen ($query) > 32767) 
			{
				if (preg_match ('/^(INSERT INTO[^(]++)\\(([^()]+)\\) VALUES[^(]++\\((.*?)\\)$/s', $query, $regs))
				{
					if (strlen ($regs[3]) > 32767)
					{
						preg_match_all ('/\'(?:[^\']++|\'\')*+\'|[\d-.]+/', $regs[3], $vals, PREG_PATTERN_ORDER);

						$inserts = $vals[0];
						unset ($vals);

						foreach ($inserts as $key => $value)
						{
							if (!empty ($value) && $value[0] === "'" && strlen ($value) > 32769) // check to see if this thing is greater than the max + 'x2
							{
								$inserts[$key] = '?';
								$array[] = str_replace("''", "'", substr ($value, 1, -1));
							}
						}

						$query = $regs[1] . '(' . $regs[2] . ') VALUES (' . implode (', ', $inserts) . ')';
					}
				}
				else if (preg_match ('/^(UPDATE ([\\w_]++)\\s+SET )([\\w_]++\\s*=\\s*(?:\'(?:[^\']++|\'\')*+\'|\\d+)(?:,\\s*[\\w_]++\\s*=\\s*(?:\'(?:[^\']++|\'\')*+\'|[\d-.]+))*+)\\s+(WHERE.*)$/s', $query, $data))
				{
					if (strlen ($data[3]) > 32767)
					{
						$update = $data[1];
						$where = $data[4];
						preg_match_all ('/(\\w++)\\s*=\\s*(\'(?:[^\']++|\'\')*+\'|[\d-.]++)/', $data[3], $temp, PREG_SET_ORDER);
						unset ($data);

						$cols = array();
						foreach ($temp as $value)
						{
							if (!empty ($value[2]) && $value[2][0] === "'" && strlen ($value[2]) > 32769) // check to see if this thing is greater than the max + 'x2
							{
								$array[] = str_replace ("''", "'", substr ($value[2], 1, -1));
								$cols[] = $value[1] . '=?';
							}
							else
							{
								$cols[] = $value[1] . '=' . $value[2];
							}
						}

						$query = $update . implode (', ', $cols) . ' ' . $where;
						unset ($cols);
					}
				}
			}

			if (sizeof ($array))
			{
				$pQuery = @ibase_prepare ($this->dbConnectId, $query);
				array_unshift ($array, $pQuery);
				$this->queryResult = call_user_func_array ('ibase_execute', $array);
				
				unset ($array);

				if ($this->queryResult === false)
				{
					$this->StoreError ();
				}
			}
			else if (($this->queryResult = @ibase_query ($this->dbConnectId, $query) ) === false)
			{
				$this->StoreError ();
				
				return false;
			}
			
			return $this->queryResult;
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

		return @ibase_free_result ($result);
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


		$row = array();
		$curRow = @ibase_fetch_object ($result, IBASE_TEXT);

		if (!$curRow)
		{
			return false;
		}

		foreach (get_object_vars ($curRow) as $key => $value)
		{
			$row[strtolower($key)] = (is_string($value)) ? trim (str_replace (array("\\0", "\\n"), array("\0", "\n"), $value)) : $value;
		}

		return (sizeof($row)) ? $row : false;
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
		$query = 'SELECT FIRST ' . $total . ((!empty ($offset)) ? ' SKIP ' . $offset : '') . substr ($query, 6);

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
		if ( ($this->serviceHandle !== false) && (function_exists ('ibase_server_info') === true) )
		{
			return @ibase_server_info ($this->serviceHandle, IBASE_SVC_SERVER_VERSION);
		}

		return ($raw === true) ? '2.0' : 'Firebird/Interbase';
	}
}

?>