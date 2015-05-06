<?php
/***************************************************************************
 *   Application          : Emotion                                        *
 *   -------------------------------------------------------------------   *
 *   Package              : @database                                      *
 *   File                 : oracle.php                                     *
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
 *   Low level database abstraction layer for Oracle DBMS.                 *
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
 * Oracle
 *
 * Low-level database driver for Oracle DBMS
 *
 * @package	database
 * @require	false
 * @author	ArvYStaTe.net Team
 **/

class Oracle extends DatabaseDriver
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
		$this->server = $server . (($port) ? ':' . $port : '');
		$this->dbName = $dbName;
		$this->newLink = $newLink;
		
		$connect = $dbName;

		// support for "easy connect naming"
		if ( ($sserver !== '') && ($server !== '/') )
		{
			if (substr ($server, -1, 1) == '/')
			{
				$server == substr ($server, 0, -1);
			}
			
			$connect = $server . (($port) ? ':' . $port : '') . '/' . $dbName;
		}

		$this->dbConnectId = ($new_link) ? @ocinlogon ($this->user, $password, $connect, 'UTF8') : (($this->persistency) ? @ociplogon ($this->user, $password, $connect, 'UTF8') : @ocilogon ($this->user, $password, $connect, 'UTF8'));

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
	
	public function Close ()
	{
		if ($this->IsConnected())
		{
			return @ocilogoff ($this->dbConnectId);
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
		$error = @ocierror ();
		$error = (!$error) ? @ocierror ($this->queryResult) : $error;
		$error = (!$error) ? @ocierror ($this->dbConnectId) : $error;

		if (is_array ($error) === true)
		{
			return $error['message'];
		}
		else
		{
			return '';
		}
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
			
			$array = array ();

			// We overcome Oracle's 4000 char limit by binding vars
			if (strlen ($query) > 4000)
			{
				if (preg_match ('/^(INSERT INTO[^(]++)\\(([^()]+)\\) VALUES[^(]++\\((.*?)\\)$/s', $query, $regs) )
				{
					if (strlen ($regs[3]) > 4000)
					{
						$cols = explode (', ', $regs[2]);
						preg_match_all ('/\'(?:[^\']++|\'\')*+\'|[\d-.]+/', $regs[3], $vals, PREG_PATTERN_ORDER);
						
						$inserts = $vals[0];
						unset ($vals);

						foreach ($inserts as $key => $value)
						{
							if ( (!empty($value) === true) && ($value[0] === "'") && (strlen ($value) > 4002) ) // check to see if this thing is greater than the max + 'x2
							{
								$inserts[$key] = ':' . strtoupper ($cols[$key]);
								$array[$inserts[$key]] = str_replace ("''", "'", substr ($value, 1, -1));
							}
						}

						$query = $regs[1] . '(' . $regs[2] . ') VALUES (' . implode (', ', $inserts) . ')';
					}
				}
				else if (preg_match_all ('/^(UPDATE [\\w_]++\\s+SET )([\\w_]++\\s*=\\s*(?:\'(?:[^\']++|\'\')*+\'|[\d-.]+)(?:,\\s*[\\w_]++\\s*=\\s*(?:\'(?:[^\']++|\'\')*+\'|[\d-.]+))*+)\\s+(WHERE.*)$/s', $query, $data, PREG_SET_ORDER) )
				{
					if (strlen ($data[0][2]) > 4000)
					{
						$update = $data[0][1];
						$where = $data[0][3];
						preg_match_all ('/([\\w_]++)\\s*=\\s*(\'(?:[^\']++|\'\')*+\'|[\d-.]++)/', $data[0][2], $temp, PREG_SET_ORDER);
						unset ($data);

						$cols = array();
						foreach ($temp as $value)
						{
							if ( (empty($value[2]) === true) && ($value[2][0] === "'") && (strlen($value[2]) > 4002) ) // check to see if this thing is greater than the max + 'x2
							{
								$cols[] = $value[1] . '=:' . strtoupper ($value[1]);
								$array[$value[1]] = str_replace ("''", "'", substr ($value[2], 1, -1));
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

			switch (substr ($query, 0, 6) )
			{
				case 'DELETE':
					if (preg_match ('/^(DELETE FROM [\w_]++ WHERE)((?:\s*(?:AND|OR)?\s*[\w_]+\s*(?:(?:=|<>)\s*(?>\'(?>[^\']++|\'\')*+\'|[\d-.]+)|(?:NOT )?IN\s*\((?>\'(?>[^\']++|\'\')*+\',? ?|[\d-.]+,? ?)*+\)))*+)$/', $query, $regs) )
					{
						$query = $regs[1] . $this->_rewriteWhere ($regs[2]);
						unset ($regs);
					}
					break;

				case 'UPDATE':
					if (preg_match ('/^(UPDATE [\\w_]++\\s+SET [\\w_]+\s*=\s*(?:\'(?:[^\']++|\'\')*+\'|[\d-.]++|:\w++)(?:, [\\w_]+\s*=\s*(?:\'(?:[^\']++|\'\')*+\'|[\d-.]++|:\w++))*+\\s+WHERE)(.*)$/s',  $query, $regs) )
					{
						$query = $regs[1] . $this->_rewriteWhere ($regs[2]);
						unset ($regs);
					}
					break;

				case 'SELECT':
					$query = preg_replace_callback ('/([\w_.]++)\s*(?:(=|<>)\s*(?>\'(?>[^\']++|\'\')*+\'|[\d-.]++|([\w_.]++))|(?:NOT )?IN\s*\((?>\'(?>[^\']++|\'\')*+\',? ?|[\d-.]++,? ?)*+\))/', array ($this, 'handler_rewrite_col_compare'), $query);
					break;
			}

			$this->queryResult = @ociparse ($this->dbConnectId, $query);

			foreach ($array as $key => $value)
			{
				@ocibindbyname ($this->queryResult, $key, $array[$key], -1);
			}

			$success = @ociexecute ($this->queryResult, OCI_DEFAULT);

			if (!$success)
			{
				$this->StoreError ();
				
				return false;
			}
			else
			{
				return $this->queryResult;
			}
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

		return @ocifreestatement ($result);
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

		if ($result !== false)
		{
			$row = array ();
			$result = @ocifetchinto ($result, $row, OCI_ASSOC + OCI_RETURN_NULLS);

			if ( (!$result) || (!$row) )
			{
				return false;
			}

			$resultRow = array ();
			
			foreach ($row as $key => $value)
			{
				// Oracle treats empty strings as null
				if (is_null ($value) === true)
				{
					$value = '';
				}

				// OCI->CLOB?
				if (is_object ($value) === true)
				{
					$value = $value->load ();
				}

				$resultRow[strtolower ($key)] = $value;
			}

			return $resultRow;
		}

		return false;
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
		$query = 'SELECT * FROM (SELECT /*+ FIRST_ROWS */ rownum AS xrownum, a.* FROM (' . $query . ') a WHERE rownum <= ' . ($offset + $total) . ') WHERE xrownum >= ' . $offset;

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

		if ($result === false)
		{
			return false;
		}

		// Reset internal pointer
		@ociexecute ($result, OCI_DEFAULT);

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
			$this->serverVersion = @ociserverversion ($this->dbConnectId);
		}

		return $this->serverVersion;
	}


	/**
	 * Oracle specific code to handle it's lack of sanity
	 *
	 * @access	private
	 * @param	string	where string to rewrite
	 * @return	string	rewritten where clause
	 **/
	
	private function _rewriteWhere ($whereClause)
	{
		preg_match_all ('/\s*(AND|OR)?\s*([\w_.]++)\s*(?:(=|<[=>]?|>=?)\s*((?>\'(?>[^\']++|\'\')*+\'|[\d-.]+))|((NOT )?IN\s*\((?>\'(?>[^\']++|\'\')*+\',? ?|[\d-.]+,? ?)*+\)))/', $whereClause, $result, PREG_SET_ORDER);
		$out = '';
		foreach ($result as $val)
		{
			if (isset ($val[5]) === false)
			{
				if ($val[4] !== "''")
				{
					$out .= $val[0];
				}
				else
				{
					$out .= ' ' . $val[1] . ' ' . $val[2];
					
					if ($val[3] == '=')
					{
						$out .= ' is NULL';
					}
					else if ($val[3] == '<>')
					{
						$out .= ' is NOT NULL';
					}
				}
			}
			else
			{
				$inClause = array ();
				$subExp = substr ($val[5], strpos ($val[5], '(') + 1, -1);
				$extra = false;
				
				preg_match_all ('/\'(?>[^\']++|\'\')*+\'|[\d-.]++/', $subExp, $subVals, PREG_PATTERN_ORDER);
				
				$i = 0;
				
				foreach ($subVals[0] as $subVal)
				{
					// two things:
					// 1) This determines if an empty string was in the IN clausing, making us turn it into a NULL comparison
					// 2) This fixes the 1000 list limit that Oracle has (ORA-01795)
					if ($subVal !== "''")
					{
						$inClause[(int) $i++ / 1000][] = $subVal;
					}
					else
					{
						$extra = true;
					}
				}
				if ( (!$extra) && ($i < 1000) )
				{
					$out .= $val[0];
				}
				else
				{
					$out .= ' ' . $val[1] . '(';
					$inArray = array ();

					// constuct each IN() clause
					foreach ($inClause as $inValues)
					{
						$inArray[] = $val[2] . ' ' . (isset ($val[6]) ? $val[6] : '') . 'IN(' . implode (', ', $inValues) . ')';
					}

					// Join the IN() clauses against a few ORs (IN is just a nicer OR anyway)
					$out .= implode (' OR ', $inArray);

					// handle the empty string case
					if ($extra)
					{
						$out .= ' OR ' . $val[2] . ' is ' . (isset ($val[6]) ? $val[6] : '') . 'NULL';
					}
					$out .= ')';

					unset ($inArray, $inClause);
				}
			}
		}

		return $out;
	}
	
	/**
	 * Oracle specific code to handle the fact that it does not compare columns properly
	 *
	 * @access	private
	 * @param	array	column array
	 **/
	
	private function _rewriteColCompare ($args)
	{
		if (sizeof ($args) == 4)
		{
			if ($args[2] == '=')
			{
				return '(' . $args[0] . ' OR (' . $args[1] . ' is NULL AND ' . $args[3] . ' is NULL))';
			}
			else if ($args[2] == '<>')
			{
				// really just a fancy way of saying foo <> bar or (foo is NULL XOR bar is NULL) but SQL has no XOR :P
				return '(' . $args[0] . ' OR ((' . $args[1] . ' is NULL AND ' . $args[3] . ' is NOT NULL) OR (' . $args[1] . ' is NOT NULL AND ' . $args[3] . ' is NULL)))';
			}
		}
		else
		{
			return $this->_rewriteWhere ($args[0]);
		}
	}
}
?>