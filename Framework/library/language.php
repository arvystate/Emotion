<?php
/***************************************************************************
 *   Application          : Emotion                                        *
 *   -------------------------------------------------------------------   *
 *   Package              : @library                                       *
 *   File                 : language.php                                   *
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
 *   Language class provides basic multi-language functionality. Loads     *
 *   language variables from filesystem or any other resource.             *
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
 * Language
 *
 * Multi-language functions for website. Language loading, substituting variables and similar.
 *
 * @package	library
 * @require	false
 * @author	ArvYStaTe.net Team
 **/

class Language
{
	private $langBase = array ();
	
	private $currentLanguage = '';
	
	/**
	 * Returns if language system is ready
	 *
	 * @access	public
	 * @return	boolean	returns true if at least one language var is loaded
	 **/
	 
	public function IsReady ()
	{
		return (count ($this->langBase) > 0);
	}
	 
	 /**
	 * Returns if language system is availible
	 *
	 * @access	public
	 * @return	boolean	returns true if at least one language var is loaded
	 **/
	 
	public function IsAvailible ()
	{
		if (!is_dir (DEFAULT_APPLICATION . '/lang'))
		{
			return false;
		}
		
		$files = scandir (DEFAULT_APPLICATION . '/lang');
		
		return (count ($files) > 2);
	}
	
	/**
	 * Selects as active language
	 *
	 * @access	public
	 * @return	boolean	returns true if selection was successful
	 **/
	
	public function SelectLanguage ($language, $load = true)
	{
		// If language is already loaded we can select it
		if (isset ($this->langBase[$language]) === true)
		{
			$this->currentLanguage = $language;
			
			return true;
		}
		else if ($load === true)
		{
			// We attempt to load language files
			if ($this->Load ($language) === true)
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
			return false;
		}
	}
	
	/**
	 * Returns currently selected language
	 *
	 * @access	public
	 * @return	string	returns true if closing was successful
	 **/
	
	public function GetSelectedLanguage ()
	{
		if (!empty ($this->currentLanguage) )
		{
			return $this->currentLanguage;
		}
		else
		{
			return '';
		}
	}
	
	/**
	 * Replaces language variables in output string.
	 *
	 * @access	public
	 * @param	string	string of text we search for replacing variables
	 * @param	string	optional language
	 * @return	string	string with replaced variables
	 **/
	
	public function Replace ($output, $language = '')
	{
		if (!empty ($language))
		{
			if (!$this->SelectLanguage ($language))
			{
				return false;
			}
		}
		
		foreach ($this->langBase[$this->currentLanguage] as $key => $value)
		{
			$output = str_replace ('{LANG_' . $key . '}', $value, $output);
		}
		
		return $output;
	}
	
	/**
	 * Gets language variable from language array
	 *
	 * @access	public
	 * @param	string	key of language variable
	 * @param	string	language key
	 * @return	mixed	returns false if language variable is not found, string if first parameter is set, array if its not
	 **/
	
	public function Get ($languageStr = '', $language = '')
	{
		if (!empty ($language))
		{
			if (!$this->SelectLanguage ($language))
			{
				return false;
			}
		}
		
		if ($languageStr != '')
		{
			if (!empty ($this->langBase[$this->currentLanguage][$languageStr]))
			{
				return $this->langBase[$this->currentLanguage][$languageStr];
			}
			else
			{
				return false;
			}
		}
		else
		{
			if ($this->IsReady () && (!empty ($this->currentLanguage) ) )
			{
				return $this->langBase[$this->currentLanguage];
			}
			else
			{
				return false;
			}
		}
	}
	
	/**
	 * Loads language variables from file
	 *
	 * @access	public
	 * @param	mixed	if its an array multiple files are loaded, if its string whole language is loaded
	 * @param	string	optional language parameter, if files do not have language in path
	 * @return	boolean	returns true when at least 1 language variable is successfully loaded
	 **/
	
	public function Load ($langArray, $language = '')
	{
		//
		// Preparing file list to load
		//
		
		$fileList = array ();
		$loadingLang = '';
		
		// If its an array bigger than 0, we have the basic list of files
		if ( (is_array ($langArray) === true) && (count ($langArray) > 0) )
		{
			$foundLang = true;
			
			// One option is to get language from files, if they have path
			foreach ($langArray as $value)
			{
				if (strpos ($langArray, '/') === false)
				{
					$foundLang = false;
					break;
				}
			}
			
			// If at least one file has no language written, we will not load it
			if ($foundLang === true)
			{
				$fileList = $langArray;
				$loadingLang = substr ($langArray[0], 0, strpos ($langArray[0], '/'));
			}
			// If files have no language in them, we have 2 options, first one is currently selected language
			else if (!empty ($this->currentLanguage) )
			{
				$fileList = $langArray;
				$loadingLang = $this->currentLanguage;
			}
			// Third option is the second parameter
			else if (!empty ($language) )
			{
				$fileList = $langArray;
				$loadingLang = $language;
			}
			else
			{
				return false;
			}
			
			// Dont forget to add extension to all languages
			for ($i = 0; $i < count ($fileList); $i++)
			{
				// Add them language incase they dont have it
				if (strpos ($fileList[$i], '/') === false)
				{
					$fileList[$i] = $loadingLang . '/' . $fileList[$i];
				}
				
				$fileList[$i] = $fileList[$i] . EXTENSION;
			}
		}
		// Handle if first parameter is string -> either one file or whole language
		else if (is_string ($langArray) === true)
		{
			// If parameter is 1 file with language, we expect at least one /
			if (strpos ($langArray, '/') === false)
			{
				$fileList[] = $langArray . EXTENSION;
				$loadingLang = substr ($langArray, 0, strpos ($langArray, '/'));
			}
			// Otherwise we want all variables from language
			else
			{
				// So language might be a directory, if so we scan files in it
				if (is_dir (DEFAULT_APPLICATION . '/lang/' . $langArray) === true)
				{
					$fileList[] = scandir (DEFAULT_APPLICATION . '/lang/' . $langArray);	
				}
				// Maybe there are not enough language files and all are stored in one file
				else if (file_exists (DEFAULT_APPLICATION . '/lang/' . $langArray . EXTENSION) === true)
				{
					$fileList[] = DEFAULT_APPLICATION . '/lang/' . $langArray . EXTENSION;
				}
				// There is no other option
				else
				{
					return false;
				}
				
				$loadingLang = $langArray;
			}
		}
		else
		{
			return false;
		}
		
		//
		// Loading files, checking if we have something to load
		//
		
		if ( (count ($fileList) > 0) && (!empty ($loadingLang) ) )
		{
			$return = false;
			
			foreach ($fileList as $value)
			{
				// Load file
				$vars = $this->_loadFile ($value);
				
				// If we have loaded some vars
				if ($vars !== false)
				{
					$return = true;
					
					$this->StoreVars ($vars, $loadingLang);					
				}
			}
			
			return $return;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Stores array of variables into language base
	 *
	 * @access	public
	 * @param	array	array with variables
	 * @param	string	language of variables
	 * @return	void
	 **/
	
	public function StoreVars ($varArray, $language = '')
	{
		foreach ($varArray as $key => $value)
		{
			// If we have an array, key is actually language (for hardcoded languages)
			if (is_array ($value) === true)
			{
				$this->StoreVars ($value, $key);
			}
			// Else we require language
			else if (!empty ($language) )
			{
				$this->langBase[$language][$key] = $value;
			}
		}
	}
	
	/**
	 * Loads language variables and returns them as array from file
	 *
	 * @access	private
	 * @param	string	file
	 * @return	mixed	returns array of language variables or false on failure
	 **/
	 
	private function _loadFile ($filename, $mode = 'include')
	{
		if (is_file ($filename) === false)
		{
			return false;
		}
		
		//
		// Both modes have the same result, however raw mode should be more secure, 
		// due to script searching only language vars, regardless of text
		//
		switch ($mode)
		{
			case 'raw':
				// A bit slower script, but more secure, this way language system will not load any other code
				// Adds whitespace, so we are we certain, something is before $lang
				$file = ' ' . file_get_contents ($filename);
				$parsed = array ();
				
				// Explode by language variable
				$vars = explode ('$lang[\'', $file);
				
				// Someone might use double quotes
				if (count ($vars) <= 1)
				{
					// Explode by language variable
					$vars = explode ('$lang["', $file);
				}
				
				// Loop through all language vars
				for ($i = 1; $i < count ($vars); $i++)
				{
					// To put apart the keys and values
					$var = explode ('=', $vars[$i]);
					
					// We should have 2 here
					if (count ($var) == 2)
					{
						// Get variable key by from the start, which is [' to first single quote
						$varKey = substr ($vars[$i], 0, strpos ($vars[$i], '\''));
						
						if (empty ($varKey))
						{
							$varKey = substr ($vars[$i], 0, strpos ($vars[$i], '"'));
						}
					
						// Cut away at first '
						$varValue = substr ($varValue, strpos ($varValue, '\''));
						// Cut away at next '
						$varValue = substr ($varValue, 0, strpos ($varValue, '\''));
						
						// For double quotes
						if (empty ($varValue))
						{
							// Cut away at first "
							$varValue = substr ($varValue, strpos ($varValue, '"'));
							// Cut away at next "
							$varValue = substr ($varValue, 0, strpos ($varValue, '"'));
						}
					
						if (!empty ($varKey) && !empty ($varValue) )
						{
							$parsed[$varKey] = $varValue;
						}
					}
				}
				
				// Check if we have successfully parsed anything
				if (count ($parsed) > 0)
				{
					return $parsed;
				}
				else
				{
					return false;
				}
			
				break;
			default:
				include_once ($filename);
				
				// Test for lang variable which should contain keys
				if ( (isset ($lang) === true) && (count ($lang) > 0) )
				{
					return $lang;
				}
				else
				{
					return false;
				}
				
			 	break;
				
		}
	}
}
?>