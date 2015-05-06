<?php
/***************************************************************************
 *   Application          : Emotion                                        *
 *   -------------------------------------------------------------------   *
 *   Package              : @library                                       *
 *   File                 : security.php                                   *
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
 *   Security is class that provides functions to enhance security on      *
 *   webpages. It is recommended to use parsed global vars of security     *
 *   class instead of global PHP variables.                                *
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
 * Security
 *
 * Security functions and protections against most common internet attacks.
 *
 * @package	library
 * @require	true
 * @author	ArvYStaTe.net Team
 **/

class Security
{
	protected $allowGetArray = true;
	protected $standardizeNewlines = true;
	protected $enableXss = false;
	protected $enableCsrf = false;

	protected $headers = array();
	
	protected $xssHash = '';
	
	protected $csrfHash = '';
	protected $csrfExpire = 7200; 
	protected $csrfTokenName = 'eecsrf';
	protected $csrfCookieName = 'eecsrf';

	/* never allowed, string replacement */
	protected $neverAllowedStr = array (
					'document.cookie'	=> '[removed]',
					'document.write'	=> '[removed]',
					'.parentNode'		=> '[removed]',
					'.innerHTML'		=> '[removed]',
					'window.location'	=> '[removed]',
					'-moz-binding'		=> '[removed]',
					'<!--'				=> '&lt;!--',
					'-->'				=> '--&gt;',
					'<![CDATA['			=> '&lt;![CDATA['
	);

	/* never allowed, regex replacement */
	protected $neverAllowedRegex = array (
					"javascript\s*:"			=> '[removed]',
					"expression\s*(\(|&\#40;)"	=> '[removed]', // CSS and IE
					"vbscript\s*:"				=> '[removed]', // IE, surprise!
					"Redirect\s+302"			=> '[removed]'
	);	
	
	/**
	 * Fetch an item from the GET array
	 *
	 * @access	public
	 * @param	string	requested index key
	 * @param	bool	default true, clean for xss attempt
	 * @return	string	requested get string
	 **/
	
	public function Get ($index = '', $xssClean = true)
	{
		// Check if a field has been provided
		if (empty ($index) && !empty ($_GET) )
		{
			$get = array();

			// loop through the full _GET array
			foreach (array_keys ($_GET) as $key)
			{
				$get[$key] = $this->_fetchFromArray ($_GET, $key, $xssClean);
			}
			
			return $get;
		}

		return $this->_fetchFromArray ($_GET, $index, $xssClean);
	}

	/**
	 * Fetch an item from the POST array
	 *
	 * @access	public
	 * @param	string	requested index key
	 * @param	bool	default true, clean for xss attempt
	 * @return	string	requested post string
	 **/
	
	public function Post ($index = '', $xssClean = true)
	{
		// Check if a field has been provided
		if (empty ($index) && !empty($_POST) )
		{
			$post = array();

			// Loop through the full _POST array and return it
			foreach (array_keys ($_POST) as $key)
			{
				$post[$key] = $this->_fetchFromArray ($_POST, $key, $xssClean);
			}
			
			return $post;
		}
		
		return $this->_fetchFromArray ($_POST, $index, $xssClean);
	}

	/**
	 * Fetch an item from REQUEST array
	 *
	 * @access	public
	 * @param	string	requested index key
	 * @param	bool	default true, clean for xss attempt
	 * @return	string	requested request string
	 **/
	
	public function Request ($index = '', $xssClean = true)
	{
		return $this->_fetchFromArray ($_REQUEST, $index, $xssClean);
	}

	/**
	 * Fetch an item from the COOKIE array
 	*
	 * @access	public
	 * @param	string	requested index key
	 * @param	bool	default true, clean for xss attempt
	 * @return	string	requested cookie string
	 **/
	
	public function Cookie ($index = '', $xssClean = true)
	{
		return $this->_fetchFromArray ($_COOKIE, $index, $xssClean);
	}
	
	/**
	 * Fetch an item from the SERVER array
	 *
	 * @access	public
	 * @param	string	requested index key
	 * @param	bool	default true, clean for xss attempt
	 * @return	string	requested server string
	 **/
	
	public function Server ($index = '', $xssClean = true)
	{
		return $this->_fetchFromArray ($_SERVER, $index, $xssClean);
	}
	
	/**
	 * Secure globals
	 *
	 * This function does the following:
	 *
	 * Unsets $_GET data (if query strings are not enabled)
	 *
	 * Unsets all globals if register_globals is enabled
	 *
	 * Standardizes newline characters to \n
	 *
	 * @access	public
	 * @return	void
	 **/
	
	public function SecureGlobals ()
	{
		// It would be "wrong" to unset any of these GLOBALS.
		$protected = array ('_SERVER', '_GET', '_POST', '_FILES', '_REQUEST', 
							'_SESSION', '_ENV', 'GLOBALS', 'HTTP_RAW_POST_DATA',
							'system_folder', 'application_folder', 'BM', 'EXT', 
							'CFG', 'URI', 'RTR', 'OUT', 'IN');

		// Unset globals for securiy.
		// This is effectively the same as register_globals = off
		foreach (array ($_GET, $_POST, $_COOKIE) as $global)
		{
			if (!is_array ($global))
			{
				if (!in_array ($global, $protected))
				{
					global $$global;
					$$global = null;
				}
			}
			else
			{
				foreach ($global as $key => $val)
				{
					if (!in_array ($key, $protected))
					{
						global $$key;
						$$key = null;
					}
				}
			}
		}

		// Is $_GET data allowed? If not we'll set the $_GET to an empty array
		if ($this->allowGetArray == false)
		{
			$_GET = array();
		}
		else
		{
			if ( (is_array ($_GET)) && (count($_GET) > 0) )
			{
				foreach ($_GET as $key => $value)
				{
					$_GET[$this->_cleanInputKeys ($key)] = $this->_cleanInputData ($value);
				}
			}
		}

		// Clean $_POST Data
		if (is_array ($_POST) && (count ($_POST) > 0) )
		{
			foreach ($_POST as $key => $value)
			{
				$_POST[$this->_cleanInputKeys ($key)] = $this->_cleanInputData ($value);
			}
		}

		// Clean $_COOKIE Data
		if (is_array ($_COOKIE) && (count ($_COOKIE) > 0) )
		{
			// Also get rid of specially treated cookies that might be set by a server
			// or silly application, that are of no use to an application anyway
			// but that when present will trip our 'Disallowed Key Characters' alarm
			// http://www.ietf.org/rfc/rfc2109.txt
			// note that the key names below are single quoted strings, and are not PHP variables
			unset ($_COOKIE['$Version']);
			unset ($_COOKIE['$Path']);
			unset ($_COOKIE['$Domain']);

			foreach ($_COOKIE as $key => $value)
			{
				$_COOKIE[$this->_cleanInputKeys ($key)] = $this->_cleanInputData ($value);
			}
		}

		// Sanitize PHP_SELF
		$_SERVER['PHP_SELF'] = strip_tags ($_SERVER['PHP_SELF']);


		// CSRF Protection check
		if ($this->enableCsrf === true)
		{
			$this->CsrfVerify ();
		}
	}

	/**
	 * Request Headers
	 *
	 * In Apache, you can simply call apache_request_headers(), however for 
	 * people running other webservers the function is undefined.
	 *
	 * @access	public
	 * @param	string	string of requested key or blank for whole header array
	 * @param	boolean	default false, clean header for XSS
	 * @return	mixed	returns string if first parameter is set or array if its not
	 **/
	
	public function RequestHeaders ($indexKey = '', $xssClean = false)
	{
		if (count ($this->headers) == 0)
		{
			// Look at Apache go!
			if (function_exists ('apache_request_headers'))
			{
				$headers = apache_request_headers ();
			}
			else
			{
				$headers['Content-Type'] = (isset ($_SERVER['CONTENT_TYPE'])) ? $_SERVER['CONTENT_TYPE'] : @getenv ('CONTENT_TYPE');
	
				foreach ($_SERVER as $key => $val)
				{
					if (strncmp ($key, 'HTTP_', 5) === 0)
					{
						$headers[substr($key, 5)] = $this->_fetchFromArray ($_SERVER, $key, $xssClean);
					}
				}
			}
	
			// take SOME_HEADER and turn it into Some-Header
			foreach ($headers as $key => $val)
			{
				$key = str_replace ('_', ' ', strtolower ($key));
				$key = str_replace (' ', '-', ucwords ($key));
				
				$this->headers[$key] = $val;
			}
		}
		
		if (empty ($indexKey))
		{
			return $this->headers;
		}
		else
		{
			return $this->headers[$indexKey];
		}
	}

	/**
	 * Is Ajax Request?
	 *
	 * Test to see if a request contains the HTTP_X_REQUESTED_WITH header
	 *
	 * @access	public
	 * @return 	boolean	returns true if page was Ajax requested
	 **/
	
	public function IsAjaxRequest ()
	{
		return ($this->Server ('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest');
	}
	
	/**
	 * Remove SQL injection
	 *
	 * Basic helper function to remove SQL injection.
	 *
	 * @access	public
	 * @param	string	text to be parsed
	 * @return 	string	parsed text
	 **/
	
	public function RemoveSqlInjection ($text)
	{
		$escape = array ("\x00", "\\", "'", "\x1a");
		$replacers = array ("\\\x00", "\\\\", "\\'", "\\\x1a");
		
		$text = str_replace ($escape, $replacers, $text);
		// Slashes for all quotes
		
		return $text;
	}
	
	/**
	 * Verify Cross Site Request Forgery Protection
	 *
	 * @access	public
	 * @return	boolean	returns true if verification is successful
	 **/
	
	public function CsrfVerify ()
	{
		// If no POST data exists we will set the CSRF cookie
		if (count ($_POST) == 0)
		{
			return $this->_csrfSetCookie ();
		}

		// Do the tokens exist in both the _POST and _COOKIE arrays?
		if (!isset ($_POST[$this->csrfTokenName]) || !isset ($_COOKIE[$this->csrfCookieName]) )
		{
			return false;
		}

		// Do the tokens match?
		if ($_POST[$this->csrfTokenName] != $_COOKIE[$this->csrfCookieName])
		{
			return false;
		}

		// We kill this since we're done and we don't want to 
		// polute the _POST array
		unset ($_POST[$this->csrfTokenName]);

		// Nothing should last forever
		unset ($_COOKIE[$this->csrfCookieName]);
		
		$this->_csrfSetHash ();
		$this->_csrfSetCookie ();
		
		return true;
	}

	/**
	 * XSS Clean
	 *
	 * Sanitizes data so that Cross Site Scripting Hacks can be
	 * prevented.  This function does a fair amount of work but
	 * it is extremely thorough, designed to prevent even the
	 * most obscure XSS attempts.  Nothing is ever 100% foolproof,
	 * of course, but I haven't been able to get anything passed
	 * the filter.
	 *
	 * Note: This function should only be used to deal with data
	 * upon submission.  It's not something that should
	 * be used for general runtime processing.
	 *
	 * This function was based in part on some code and ideas I
	 * got from Bitflux: http://channel.bitflux.ch/wiki/XSS_Prevention
	 *
	 * To help develop this script I used this great list of
	 * vulnerabilities along with a few other hacks I've
	 * harvested from examining vulnerabilities in other programs:
	 * http://ha.ckers.org/xss.html
	 *
	 * @param	mixed	string or array
	 * @return	string	xss cleaned string
	 **/
	
	public function XssClean ($str, $isImage = false)
	{
		/*
		 * Is the string an array?
		 *
		 */
		 
		if (is_array ($str))
		{
			while (list ($key) = each ($str) )
			{
				$str[$key] = $this->XssClean($str[$key]);
			}

			return $str;
		}

		/*
		 * Remove Invisible Characters
		 */
		$str = $this->_removeInvisibleCharacters ($str);

		// Validate Entities in URLs
		$str = $this->_validateEntities ($str);

		/*
		 * URL Decode
		 *
		 * Just in case stuff like this is submitted:
		 *
		 * <a href="http://%77%77%77%2E%67%6F%6F%67%6C%65%2E%63%6F%6D">Google</a>
		 *
		 * Note: Use rawurldecode() so it does not remove plus signs
		 *
		 */
		$str = rawurldecode ($str);

		/*
		 * Convert character entities to ASCII
		 *
		 * This permits our tests below to work reliably.
		 * We only convert entities that are within tags since
		 * these are the ones that will pose security problems.
		 *
		 */

		$str = preg_replace_callback ("/[a-z]+=([\'\"]).*?\\1/si", array ($this, '_convertAttribute'), $str);
	
		$str = preg_replace_callback ("/<\w+.*?(?=>|<|$)/si", array ($this, '_entityDecode'), $str);

		/*
		 * Remove Invisible Characters Again!
		 */
		 
		$str = $this->_removeInvisibleCharacters ($str);

		/*
		 * Convert all tabs to spaces
		 *
		 * This prevents strings like this: ja	vascript
		 * NOTE: we deal with spaces between characters later.
		 * NOTE: preg_replace was found to be amazingly slow here on 
		 * large blocks of data, so we use str_replace.
		 */

		if (strpos ($str, "\t") !== false)
		{
			$str = str_replace ("\t", ' ', $str);
		}

		/*
		 * Capture converted string for later comparison
		 */
		$convertedString = $str;

		// Remove Strings that are never allowed
		$str = $this->_doNeverAllowed ($str);

		/*
		 * Makes PHP tags safe
		 *
		 * Note: XML tags are inadvertently replaced too:
		 *
		 * <?xml
		 *
		 * But it doesn't seem to pose a problem.
		 */
		if ($isImage === true)
		{
			// Images have a tendency to have the PHP short opening and 
			// closing tags every so often so we skip those and only 
			// do the long opening tags.
			$str = preg_replace ('/<\?(php)/i', "&lt;?\\1", $str);
		}
		else
		{
			$str = str_replace (array ('<?', '?'.'>'),  array ('&lt;?', '?&gt;'), $str);
		}

		/*
		 * Compact any exploded words
		 *
		 * This corrects words like:  j a v a s c r i p t
		 * These words are compacted back to their correct state.
		 */
		$words = array (
				'javascript', 'expression', 'vbscript', 'script', 
				'applet', 'alert', 'document', 'write', 'cookie', 'window'
			);
			
		foreach ($words as $word)
		{
			$temp = '';

			for ($i = 0, $wordlen = strlen ($word); $i < $wordlen; $i++)
			{
				$temp .= substr ($word, $i, 1) . "\s*";
			}

			// We only want to do this when it is followed by a non-word character
			// That way valid stuff like "dealer to" does not become "dealerto"
			$str = preg_replace_callback ('#(' . substr ($temp, 0, -3) . ')(\W)#is', array ($this, '_compactExplodedWords'), $str);
		}

		/*
		 * Remove disallowed Javascript in links or img tags
		 * We used to do some version comparisons and use of stripos for PHP5, 
		 * but it is dog slow compared to these simplified non-capturing 
		 * preg_match(), especially if the pattern exists in the string
		 */
		do
		{
			$original = $str;

			if (preg_match ("/<a/i", $str) )
			{
				$str = preg_replace_callback ("#<a\s+([^>]*?)(>|$)#si", array ($this, '_jsLinkRemoval'), $str);
			}

			if (preg_match ("/<img/i", $str))
			{
				$str = preg_replace_callback ("#<img\s+([^>]*?)(\s?/?>|$)#si", array($this, '_jsImgRemoval'), $str);
			}

			if (preg_match ("/script/i", $str) || preg_match ("/xss/i", $str))
			{
				$str = preg_replace ("#<(/*)(script|xss)(.*?)\>#si", '[removed]', $str);
			}
		}
		while ($original != $str);

		unset ($original);

		// Remove evil attributes such as style, onclick and xmlns
		$str = $this->_removeEvilAttributes ($str, $isImage);

		/*
		 * Sanitize naughty HTML elements
		 *
		 * If a tag containing any of the words in the list
		 * below is found, the tag gets converted to entities.
		 *
		 * So this: <blink>
		 * Becomes: &lt;blink&gt;
		 */
		$naughty = 'alert|applet|audio|basefont|base|behavior|bgsound|blink|body|embed|expression|form|frameset|frame|head|html|ilayer|iframe|input|isindex|layer|link|meta|object|plaintext|style|script|textarea|title|video|xml|xss';
		$str = preg_replace_callback ('#<(/*\s*)('.$naughty.')([^><]*)([><]*)#is', array($this, '_sanitizeNaughtyHtml'), $str);

		/*
		 * Sanitize naughty scripting elements
		 *
		 * Similar to above, only instead of looking for
		 * tags it looks for PHP and JavaScript commands
		 * that are disallowed.  Rather than removing the
		 * code, it simply converts the parenthesis to entities
		 * rendering the code un-executable.
		 *
		 * For example:	eval('some code')
		 * Becomes:		eval&#40;'some code'&#41;
		 */
		$str = preg_replace ('#(alert|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si', "\\1\\2&#40;\\3&#41;", $str);


		// Final clean up
		// This adds a bit of extra precaution in case
		// something got through the above filters
		$str = $this->_doNeverAllowed ($str);

		/*
		 * Images are Handled in a Special Way
		 * - Essentially, we want to know that after all of the character 
		 * conversion is done whether any unwanted, likely XSS, code was found.  
		 * If not, we return true, as the image is clean.
		 * However, if the string post-conversion does not matched the 
		 * string post-removal of XSS, then it fails, as there was unwanted XSS 
		 * code found and removed/changed during processing.
		 */

		if ($isImage === true)
		{
			return ($str == $convertedString) ? true: false;
		}
		
		return $str;
	}

	/**
	 * Random Hash for protecting URLs
	 *
	 * @access	public
	 * @return	string	random hash
	 **/
	
	public function XssHash ()
	{
		if ($this->xssHash == '')
		{
			if (phpversion() >= 4.2)
			{
				mt_srand();
			}
			else
			{
				mt_srand (hexdec (substr (md5 (microtime ()), -8)) & 0x7fffffff);
			}

			$this->xssHash = md5 (time() + mt_rand (0, 1999999999));
		}

		return $this->xssHash;
	}

	/**
	 * Filename Security
	 *
	 * @access	public
	 * @param	string	filename
	 * @param	boolean	relative
	 * @return	string	parsed filename
	 **/
	
	public function SanitizeFilename ($str, $relative = false)
	{
		$bad = array (
						"../",
						"<!--",
						"-->",
						"<",
						">",
						"'",
						'"',
						'&',
						'$',
						'#',
						'{',
						'}',
						'[',
						']',
						'=',
						';',
						'?',
						"%20",
						"%22",
						"%3c",		// <
						"%253c",	// <
						"%3e",		// >
						"%0e",		// >
						"%28",		// (
						"%29",		// )
						"%2528",	// (
						"%26",		// &
						"%24",		// $
						"%3f",		// ?
						"%3b",		// ;
						"%3d"		// =
					);
		
		if (!$relative)
		{
			$bad[] = './';
			$bad[] = '/';
		}

		$str = _removeInvisibleCharacters ($str, false);
		return stripslashes (str_replace ($bad, '', $str));
	}
		
	/**
	 * Fetch from array
	 *
	 * This is a helper function to retrieve values from global arrays
	 *
	 * @access	private
	 * @param	array
	 * @param	string
	 * @param	bool
	 * @return	string
	 **/
	
	private function _fetchFromArray (&$array, $index = '', $xssClean = true)
	{
		if (!isset ($array[$index]))
		{
			return null;
		}
		
		$array[$index] = $this->RemoveSqlInjection ($array[$index]);

		if ($xssClean === true)
		{
			return $this->XssClean ($array[$index]);
		}

		return $array[$index];
	}
	
	/**
	 * Clean Input Data
	 *
	 * This is a helper function. It escapes data and
	 * standardizes newline characters to \n
	 *
	 * @access	private
	 * @param	string	input data
	 * @return	string	escaped input data
	 **/
	
	private function _cleanInputData ($str)
	{
		if (is_array ($str))
		{
			$newArray = array();
			
			foreach ($str as $key => $value)
			{
				$newArray[$this->_cleanInputKeys ($key)] = $this->_cleanInputData ($value);
			}
			
			return $newArray;
		}

		// We strip slashes if magic quotes is on to keep things consistent
		if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc())
		{
			$str = stripslashes ($str);
		}
		
		// Remove control characters
		$str = $this->_removeInvisibleCharacters ($str);

		// Should we filter the input data?
		if ($this->enableXss === true)
		{
			$str = $this->XssClean ($str);
		}

		// Standardize newlines if needed
		if ($this->standardizeNewlines == true)
		{
			if (strpos ($str, "\r") !== false)
			{
				$str = str_replace (array ("\r\n", "\r", "\r\n\n"), PHP_EOL, $str);
			}
		}

		return $str;
	}
	
	/**
	 * Remove Invisible Characters
	 *
	 * This prevents sandwiching null characters
	 * between ascii characters, like Java\0script.
	 *
	 * @access	private
	 * @param	string	string to have removed
	 * @return	string	string with removed invisible characters
	 **/
	
	private function _removeInvisibleCharacters ($str, $urlEncoded = true)
	{
		$nonDisplayables = array();
		
		// every control character except newline (dec 10)
		// carriage return (dec 13), and horizontal tab (dec 09)
		
		if ($urlEncoded)
		{
			$nonDisplayables[] = '/%0[0-8bcef]/';	// url encoded 00-08, 11, 12, 14, 15
			$nonDisplayables[] = '/%1[0-9a-f]/';	// url encoded 16-31
		}
		
		$nonDisplayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';	// 00-08, 11, 12, 14-31, 127

		do
		{
			$str = preg_replace ($nonDisplayables, '', $str, -1, $count);
		}
		while ($count);

		return $str;
	}

	/**
	 * Clean Keys
	 *
	 * This is a helper function. To prevent malicious users
	 * from trying to exploit keys we make sure that keys are
	 * only named with alpha-numeric text and a few other items.
	 *
	 * @access	private
	 * @param	string	input keys
	 * @return	string	cleaned input keys
	 **/
	
	private function _cleanInputKeys ($str)
	{
		if (!preg_match ("/^[a-z0-9:_\/-]+$/i", $str))
		{
			exit ('Disallowed Key Characters.');
		}

		$str = $this->_cleanUtf8String ($str);

		return $str;
	}

	/**
	 * Clean UTF-8 strings
	 *
	 * Ensures strings are UTF-8
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 **/

	private function _cleanUtf8String ($str)
	{
		if ($this->_isAscii ($str) === false)
		{
			$str = @iconv ('UTF-8', 'UTF-8//IGNORE', $str);
		}

		return $str;
	}
	
		
	/**
	 * Tests if a string is standard 7-bit ASCII or not
	 *
	 * @access	private
	 * @param	string	plain text
	 * @return	bool	returns true if string is in 7-bit ASCII
	 **/
	
	private function _isAscii ($str)
	{
		return (preg_match ('/[^\x00-\x7F]/S', $str) == 0);
	}
	
	/**
	 * HTML Entities Decode
	 *
	 * This function is a replacement for html_entity_decode()
	 *
	 * In some versions of PHP the native function does not work
	 * when UTF-8 is the specified character set, so this gives us
	 * a work-around.  More info here:
	 * http://bugs.php.net/bug.php?id=25670
	 *
	 * NOTE: html_entity_decode() has a bug in some PHP versions when UTF-8 is the
	 * character set, and the PHP developers said they were not back porting the
	 * fix to versions other than PHP 5.x.
	 *
	 * @access	private
	 * @param	string	text
	 * @param	string	charset
	 * @return	string	decoded entity text
	 **/
	
	private function _entityDecode ($str, $charset = 'UTF-8')
	{
		if (stristr ($str, '&') === false)
		{
			return $str;
		}

		// The reason we are not using html_entity_decode() by itself is because
		// while it is not technically correct to leave out the semicolon
		// at the end of an entity most browsers will still interpret the entity
		// correctly.  html_entity_decode() does not convert entities without
		// semicolons, so we are left with our own little solution here. Bummer.

		if (function_exists ('html_entity_decode') && (strtolower ($charset) != 'utf-8') )
		{
			$str = html_entity_decode ($str, ENT_COMPAT, $charset);
			$str = preg_replace ('~&#x(0*[0-9a-f]{2,5})~ei', 'chr(hexdec("\\1"))', $str);
			return preg_replace ('~&#([0-9]{2,4})~e', 'chr(\\1)', $str);
		}

		// Numeric Entities
		$str = preg_replace ('~&#x(0*[0-9a-f]{2,5});{0,1}~ei', 'chr(hexdec("\\1"))', $str);
		$str = preg_replace ('~&#([0-9]{2,4});{0,1}~e', 'chr(\\1)', $str);

		// Literal Entities - Slightly slow so we do another check
		if (stristr ($str, '&') === false)
		{
			$str = strtr ($str, array_flip (get_html_translation_table (HTML_ENTITIES) ) );
		}

		return $str;
	}

	/**
	 * Compact Exploded Words
	 *
	 * Callback function for XssClean () to remove whitespace from
	 * things like j a v a s c r i p t
	 *
	 * @access	private
	 * @param	array	array of matches
	 * @return	array	array without exploded words
	 **/
	
	private function _compactExplodedWords ($matches)
	{
		return preg_replace ('/\s+/s', '', $matches[1]) . $matches[2];
	}
	
	/**
	 * Remove Evil HTML Attributes (like evenhandlers and style)
	 *
	 * It removes the evil attribute and either:
	 * 	- Everything up until a space
	 *		For example, everything between the pipes:
	 *		<a |style=document.write('hello');alert('world');| class=link>
	 * 	- Everything inside the quotes 
	 *		For example, everything between the pipes:
	 *		<a |style="document.write('hello'); alert('world');"| class="link">
	 *
	 * @access	private
	 * @param	string	The string to check
	 * @param	boolean	true if this is an image
	 * @return	string	The string with the evil attributes removed
	 **/
	
	private function _removeEvilAttributes ($str, $isImage)
	{
		// All javascript event handlers (e.g. onload, onclick, onmouseover), style, and xmlns
		$evilAttributes = array ('on\w*', 'style', 'xmlns');

		if ($isImage === true)
		{
			/*
			 * Adobe Photoshop puts XML metadata into JFIF images, 
			 * including namespacing, so we have to allow this for images.
			 */
			unset ($evilAttributes[array_search ('xmlns', $evilAttributes)]);
		}
		
		do
		{
			$str = preg_replace (
				"#<(/?[^><]+?)([^A-Za-z\-])(" . implode ('|', $evilAttributes) . ")(\s*=\s*)([\"][^>]*?[\"]|[\'][^>]*?[\']|[^>]*?)([\s><])([><]*)#i",
				"<$1$6",
				$str, -1, $count
			);
		}
		while ($count);
		
		return $str;
	}
	
	/**
	 * Sanitize Naughty HTML
	 *
	 * Callback function for XssClean () to remove naughty HTML elements
	 *
	 * @access	private
	 * @param	array	matches
	 * @return	string	parsed text
	 **/
	
	private function _sanitizeNaughtyHtml ($matches)
	{
		// encode opening brace
		$str = '&lt;' . $matches[1] . $matches[2] . $matches[3];

		// encode captured opening or closing brace to prevent recursive vectors
		$str .= str_replace (array ('>', '<'), array ('&gt;', '&lt;'), $matches[4]);

		return $str;
	}

	/**
	 * JS Link Removal
	 *
	 * Callback function for XssClean() to sanitize links
	 * This limits the PCRE backtracks, making it more performance friendly
	 * and prevents PREG_BACKTRACK_LIMIT_ERROR from being triggered in
	 * PHP 5.2+ on link-heavy strings
	 *
	 * @access	private
	 * @param	array	matches
	 * @return	string	parsed string
	 **/
	
	private function _jsLinkRemoval ($match)
	{
		$attributes = $this->_filterAttributes (str_replace (array ('<', '>'), '', $match[1]));
		
		return str_replace ($match[1], preg_replace ("#href=.*?(alert\(|alert&\#40;|javascript\:|livescript\:|mocha\:|charset\=|window\.|document\.|\.cookie|<script|<xss|base64\s*,)#si", "", $attributes), $match[0]);
	}

	/**
	 * JS Image Removal
	 *
	 * Callback function for XssClean() to sanitize image tags
	 * This limits the PCRE backtracks, making it more performance friendly
	 * and prevents PREG_BACKTRACK_LIMIT_ERROR from being triggered in
	 * PHP 5.2+ on image tag heavy strings
	 *
	 * @access	private
	 * @param	array	matches
	 * @return	string	parsed string
	 **/
	
	private function _jsImgRemoval ($match)
	{
		$attributes = $this->_filterAttributes (str_replace (array ('<', '>'), '', $match[1]));
		
		return str_replace ($match[1], preg_replace ("#src=.*?(alert\(|alert&\#40;|javascript\:|livescript\:|mocha\:|charset\=|window\.|document\.|\.cookie|<script|<xss|base64\s*,)#si", "", $attributes), $match[0]);
	}

	/**
	 * Attribute Conversion
	 *
	 * Used as a callback for XSS Clean
	 *
	 * @access	private
	 * @param	array
	 * @return	string
	 **/
	 
	private function _convertAttribute ($match)
	{
		return str_replace (array ('>', '<', '\\'), array ('&gt;', '&lt;', '\\\\'), $match[0]);
	}
	
	/**
	 * Filter Attributes
	 *
	 * Filters tag attributes for consistency and safety
	 *
	 * @access	private
	 * @param	string	text
	 * @return	string	parsed text
	 **/
	
	private function _filterAttributes ($str)
	{
		$out = '';

		if (preg_match_all ('#\s*[a-z\-]+\s*=\s*(\042|\047)([^\\1]*?)\\1#is', $str, $matches))
		{
			foreach ($matches[0] as $match)
			{
				$out .= preg_replace ("#/\*.*?\*/#s", '', $match);
			}
		}

		return $out;
	}
	
	/**
	 * Validate URL entities
	 *
	 * Called by XssClean()
	 *
	 * @access	private
	 * @param 	string	
	 * @return 	string
	 **/
	
	private function _validateEntities ($str)
	{
		/*
		 * Protect GET variables in URLs
		 */
		
		 // 901119URL5918AMP18930PROTECT8198
		
		$str = preg_replace ('|\&([a-z\_0-9\-]+)\=([a-z\_0-9\-]+)|i', $this->XssHash() . "\\1=\\2", $str);

		/*
		 * Validate standard character entities
		 *
		 * Add a semicolon if missing.  We do this to enable
		 * the conversion of entities to ASCII later.
		 *
		 */
		$str = preg_replace ('#(&\#?[0-9a-z]{2,})([\x00-\x20])*;?#i', "\\1;\\2", $str);

		/*
		 * Validate UTF16 two byte encoding (x00)
		 *
		 * Just as above, adds a semicolon if missing.
		 *
		 */
		$str = preg_replace ('#(&\#x?)([0-9A-F]+);?#i',"\\1\\2;", $str);

		/*
		 * Un-Protect GET variables in URLs
		 */
		$str = str_replace ($this->XssHash(), '&', $str);
		
		return $str;
	}

	/**
	 * Do Never Allowed
	 *
	 * A utility function for XssClean ()
	 *
	 * @access	private
	 * @param 	string
	 * @return 	string
	 **/
	
	private function _doNeverAllowed ($str)
	{
		foreach ($this->neverAllowedStr as $key => $value)
		{
			$str = str_replace ($key, $value, $str);
		}

		foreach ($this->neverAllowedRegex as $key => $value)
		{
			$str = preg_replace ("#" . $key . "#i", $value, $str);
		}
		
		return $str;
	}
	
	/**
	 * Set Cross Site Request Forgery Protection Cookie
	 *
	 * @access	public
	 * @return	void
	 **/
	
	private function _csrfSetCookie ()
	{
		$expire = time () + $this->csrfExpire;
		
		setcookie ($this->csrfCookieName, $this->csrfHash, $expire, '','', false);
	}

	/**
	 * Set Cross Site Request Forgery Protection Cookie
	 *
	 * @access	private
	 * @return	string	hash
	 **/
	
	private function _csrfSetHash()
	{
		if ($this->csrfHash == '')
		{
			// If the cookie exists we will use it's value.  
			// We don't necessarily want to regenerate it with
			// each page load since a page could contain embedded 
			// sub-pages causing this feature to fail
			if (isset ($_COOKIE[$this->csrfCookieName]) && $_COOKIE[$this->csrfCookieName] != '')
			{
				return $this->csrfHash = $_COOKIE[$this->csrfCookieName];
			}
			
			return $this->csrfHash = md5 (uniqid (rand (), true));
		}

		return $this->csrfHash;
	}
}
?>