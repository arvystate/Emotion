<?php

/***************************************************************************
 *   Application          : Emotion                                        *
 *   -------------------------------------------------------------------   *
 *   Package              : @library                                       *
 *   File                 : debug.php                                      *
 *   Version              : 1.0.0                                          *
 *   Status               : Complete, Not tested                           *
 *   -------------------------------------------------------------------   *
 *   Begin                : Thursday, Apr 4, 2011                          *
 *   CopyRight            : (C) 2011 ArvYStaTe.net Team                    *
 *   E-Mail               : support@arvystate.net                          *
 *   Last edit            : Thursday, Apr 4, 2011                          *
 *                                                                         *
 *                                                                         *
 ***************************************************************************

 ***************************************************************************
 *   File description                                                      *
 *   -------------------------------------------------------------------   *
 *   Debug is a class that provides basic debugging tools. It is to be     *
 *   used mainly during the development stages of website. Later can       *
 *   present certain security risk, if attacker is aware of the system.    *
 *   -------------------------------------------------------------------   *
 *                                                                         *
 ***************************************************************************
 
 ***************************************************************************
 *   Change log                                                            *
 *   -------------------------------------------------------------------   *
 *    + [08/04/11] - File created                                          *
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
 * Debug
 *
 * Basic PHP debugging tools
 *
 * @package	library
 * @require	false
 * @author	ArvYStaTe.net Team
 **/

class Debug
{
	//
	// Class variables
	//
	
	private $outputLine = '';
	private $measure = 0;
	private $bytes = 0;
	
	/**
	 * Starts measuring timer
	 *
	 * @access	public
	 * @return	void
	 **/
	
	public function StartTimer ()
	{
		$this->measure = microtime (true);
	}
	
	/**
	 * Starts memory measuring timer
	 *
	 * @access	public
	 * @return	void
	 **/
	
	public function StartMemoryTimer ()
	{
		$this->bytes = memory_get_usage ();
	}
	
	/**
	 * Measures time from the first time timer was started
	 *
	 * @access	public
	 * @return	float	unix timestamp in microseconds that lapsed between last time timer was started
	 **/
	
	public function MeasureTime ()
	{
		return abs (microtime (true)- $this->measure);
	}
	
	/**
	 * Measures difference of currently allocated memory from the time timer was started
	 *
	 * @access	public
	 * @return	long	long integer of bytes
	 **/
	
	public function MeasureMemory ()
	{
		return (memory_get_usage () - $this->bytes);
	}
	
	/**
	 * Sets system error reporting, similar to PHP function
	 *
	 * @access	public
	 * @param	integer level of errors displayed to user (0 - 4)
	 * @return	void
	 **/
	
	public function ErrorReporting ($level = 0)
	{
		set_error_handler (array ($this, "ErrorHandler"));
		
		switch ($level)
		{
			case 1:
				error_reporting (E_ERROR | E_WARNING | E_PARSE);
				break;
			case 2:
				error_reporting (E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
				break;
			case 3:
				error_reporting (E_ALL ^ E_NOTICE);
				break;
			case 4:
				error_reporting (E_ALL);
				break;
			default:
				error_reporting (0);
				break;
		}
	}
	
	/**
	 * Sets system error reporting, similar to PHP function
	 *
	 * @access	public
	 * @param	integer error number
	 * @param	string	error string
	 * @param	string	error file
	 * @param	integer	error line
	 * @return	mixed	boolean true if error was success, void otherwise
	 **/
	
	public function ErrorHandler ($errorNo, $errorStr, $errorFile, $errorLine)
	{
		if (!(error_reporting () & $errorNo))
		{
			return;
    	}
		
		$errorFile = basename ($errorFile);

    	switch ($errorNo)
		{
			case E_USER_ERROR:
			case E_ERROR:
				$this->SaveMessage ('<b>Fatal Error</b>: [' . $errorFile . ', line ' . $errorLine . '] ' . $errorStr . ' PHP ' . PHP_VERSION . ' (' . PHP_OS . ')');
				$this->Display ();
				
				exit (1);
				break;
			
			case E_USER_WARNING:
			case E_WARNING:
				$this->SaveMessage ('<b>Warning</b>: [' . $errorFile . ', line ' . $errorLine . '] ' . $errorStr);
				break;
		
			case E_USER_NOTICE:
			case E_NOTICE:
				$this->SaveMessage ('<b>Notice</b>: [' . $errorFile . ', line ' . $errorLine . '] ' . $errorStr);
				break;
		
			default:
				$this->SaveMessage ('<b>Unknown (' . $errorNo . ')</b>: [' . $errorFile . ', line ' . $errorLine . '] ' . $errorStr);
				break;
		}
	
		// Don't execute PHP internal error handler
		return true;
	}

	/**
	 * Stores message in log
	 *
	 * @access	public
	 * @param	string	message you want to save
	 * @return	void
	 **/
	
	public function SaveMessage ($string)
	{
		$this->outputLine .= '[' . date('H:i:s') . ':' . rand (1000, 9999) . '][DBG]: ' . $string . '<br />';
	}
	
	/**
	 * Stores object state -> serialization
	 *
	 * @access	public
	 * @return	void
	 **/
	
	public function SaveObject ($object, $name = '')
	{
		if ($name != '')
		{
			$this->SaveMessage ('Saved object state: ' . $name);
		}
		else
		{
			$this->SaveMessage ('Saved object state.');
		}
		
		$this->outputLine .= print_r ($object, true) . '<br />';
	}
	
	/**
	 * Returns current log as string
	 *
	 * @access	public
	 * @return	string	current message log
	 **/
	
	public function GetLog ()
	{
		return $this->outputLine;
	}
	
	/**
	 * Clears current log
	 *
	 * @access	public
	 * @return	void
	 **/
	
	public function Clear ()
	{
		$this->outputLine = '';
	}
	
	/**
	 * Formats and displays current log
	 *
	 * @access	public
	 * @return	void
	 **/
	
	public function Display ()
	{
		echo ($this->_formatLog ());
	}
	
	/**
	 * Saves log to a file
	 *
	 * @access	public
	 * @param	string	filename, if not chosen, random name is selected
	 * @return	void
	 **/
	
	public function SaveLog ($filename = '')
	{
		if ($filename == '')
		{
			$filename = 'log_' . time () . rand (1000, 9999) . '.log';
		}
		
		$this->SaveMessage('Saving system log to file: ' . $filename); 
		
		$data = str_replace ('<br />', "\n", $this->outputLine);
	
		file_put_contents ($filename, $data);
	}
	
	/**
	 * Cleans log from errors and returns it in HTML format
	 *
	 * @access	private
	 * @return	string	formatted log string
	 **/
	
	private function _formatLog ()
	{
		$this->outputLine = str_replace ('<br /><br />', '<br />', $this->outputLine);
		
		return ('<br /><center><table width="700px" cellpadding="0" cellspacing="0" align="center" border="0" style="border: #000066 1px solid; font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 9px; font-weight: bold; color: #000066;"><tbody><tr><td><pre>' . substr ($this->outputLine, 0, -6) . '</pre></td></tr></tbody></table></center><br />');
	}
}
?>