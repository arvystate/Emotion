<?php
/***************************************************************************
 *   Application          : Emotion                                        *
 *   -------------------------------------------------------------------   *
 *   Package              : @library                                       *
 *   File                 : session.php                                    *
 *   Version              : 1.0.0                                          *
 *   Status               : Complete, Not tested                           *
 *   -------------------------------------------------------------------   *
 *   Begin                : Thursday, Apr 11, 2011                         *
 *   CopyRight            : (C) 2011 ArvYStaTe.net Team                    *
 *   E-Mail               : support@arvystate.net                          *
 *   Last edit            : Thursday, Apr 11, 2011                         *
 *                                                                         *
 *                                                                         *
 ***************************************************************************

 ***************************************************************************
 *   File description                                                      *
 *   -------------------------------------------------------------------   *
 *   Session is a class that provides basic session and user data          *
 *   functionality to be used across website.                              *
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
 * Session
 *
 * Session functionality to allow rich client dependent websites.
 *
 * @package	library
 * @require	false
 * @author	ArvYStaTe.net Team
 **/

class Session
{
	private $sessionData = array ();
	private $sessionValid = false;
	
	// This variable defines if session is based on browser or on server -> if its cookie it is browser based session
	private $sessionCookie = false;
	
	private $expiration = 7200;
	private $expireOnClose = false;
	// 0 -> *.*.*.* access, 1 -> x.*.*.*, 2 -> x.x.*.*, 3 -> x.x.x.*, 4-> host IP
	private $matchIpLevel = 2;
	private $matchUserAgent = true;
	
	// Emotion engine session id
	
	private $cookieName = 'eesid';
	private $cookiePath = '';
	private $cookieDomain = '';
	
	private $cookieEncrypt = false;
	
	// Cookie is encrypted with AES-256 encryption standard, so we need 32-char key!
	private $cookieEncryptionKey = 'asdgdfg54';
		
	/**
	 * Initialize session settings
	 *
	 * @access	public
	 * @param	boolean	use cookie based session, default false
	 * @param	string	cookie path (when first parameter is true)
	 * @param	string	cookie domain (when first parameter is true)
	 * @return	void
	 **/
	 
	public function Initialize ($cookie = false, $path = '', $domain = '')
	{
		$this->sessionCookie = $cookie;
		$this->cookiePath = $path;
		$this->cookieDomain = $domain;
	}

	/**
	 * Get variable from session
	 *
	 * @access	public
	 * @param	string	variable key
	 * @return	mixed	returns variable from session, null when none
	 **/
	
	public function Get ($key)
	{
		if ( (isset ($this->sessionData[$key]) === true) && ($this->sessionValid === true) )
		{
			return $this->sessionData[$key];
		}
		else
		{
			return null;
		}
	}
	
	/**
	 * Set custom session variable
	 *
	 * @access	public
	 * @param	mixed	array with keys and values or string
	 * @param	string	key of the session variable -> optional, if first parameter is string
	 * @return	boolean	returns true if setting was successful
	 **/
	
	public function Set ($data, $keyArray = '')
	{
		if ($this->sessionValid === false)
		{
			return false;
		}
		
		if (is_array ($data) === true)
		{
			foreach ($data as $key => $value)
			{
				$this->sessionData[$key] = $value;
			}
			
			return true;
		}
		else if ( (is_string ($data) === true) && ($keyArray != '') )
		{
			$this->sessionData[$keyArray] = $data;
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Read session data
	 *
	 * @access	public
	 * @return	boolean	returns true if session was successfully read and is valid
	 **/
	
	public function SessionRead ()
	{
		$this->sessionData = array ();
		$this->sessionValid = false;
		
		// Session data is a Cookie
		if ($this->sessionCookie)
		{
			// Encryption key check -> min 8 characters
			if (strlen ($this->cookieEncryptionKey) != 32)
			{
				$session = $_COOKIE[$this->cookieName];
				
				if (!empty ($session))
				{
					// Handling the cookie encryption
					if ($this->cookieEncrypt === true)
					{
						if (function_exists ('mcrypt_encrypt'))
						{
							$initSize = mcrypt_get_iv_size (MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);

							if ($initSize > strlen ($session))
							{
								$this->SessionDestroy ();
								return false;
							}
					
							$initVect = substr ($session, 0, $initSize);
							$data = substr ($session, $initSize);
							$session = rtrim (mcrypt_decrypt (MCRYPT_RIJNDAEL_256, $this->cookieEncryptionKey, $session, MCRYPT_MODE_CBC, $initVect), "\0");
						}
					}
					
					$hash = substr ($session, strlen($session) - 32); // get last 32 chars
					$session = substr ($session, 0, strlen ($session) - 32);
		
					if (md5 ($session . $this->cookieEncryptionKey) !== $hash)
					{
						$this->SessionDestroy ();
						return false;
					}
					
					// Deserialize session data
					$this->sessionData = _deserialize ($session);
				}
				else
				{
					$this->SessionDestroy ();
					return false;
				}
			}
			else
			{
				$this->SessionDestroy ();
				return false;
			}
		}
		else
		{
			// Start session, shall we?
			session_start ();
			
			// Read session variables
			foreach ($_SESSION as $key => $value)
			{
				$this->sessionData [$key] = $value;
			}
		}
		
		// Session data check
		if (count ($this->sessionData) > 0)
		{
			// IP Check -> session hijacking attempt
			if ($this->matchIpLevel > 0)
			{
				if (isset ($this->sessionData['ip_address']))
				{
					$currentIp = $this->IpAddress ();
					
					if ($currentIp != '0.0.0.0')
					{
						$currentIp = explode ('.', $currentIp);
						$previousIp = explode ('.', $this->sessionData['ip_address']);
						
						$ipMatch = true;
						
						// Testing IP
						if ( ($this->matchIpLevel == 1) && ($currentIp[0] != $previousIp[0]) )
						{
							$ipMatch = false;
						}
						else if ( ($this->matchIpLevel == 2) && ( ($currentIp[0] != $previousIp[0]) || ($currentIp[1] != $previousIp[1]) ) )
						{
							$ipMatch = false;
						}
						else if ( ($this->matchIpLevel == 3) && ( ($currentIp[0] != $previousIp[0]) || ($currentIp[1] != $previousIp[1]) || ($currentIp[2] != $previousIp[2])) )
						{
							$ipMatch = false;
						}
						else if ( ($this->matchIpLevel >= 4) && (implode ('.', $currentIp) != implode ('.', $previousIp) ) )
						{
							$ipMatch = false;
						}
						
						if ($ipMatch === false)
						{
							$this->SessionDestroy ();
							return false;
						}
					}
					else
					{
						$this->SessionDestroy ();
						return false;
					}
				}
				else
				{
					$this->SessionDestroy ();
					return false;
				}
			}
			
			// User agent check
			if ($this->matchUserAgent === true)
			{
				if (substr ($this->UserAgent (), 0, 50) != $this->sessionData['user_agent'])
				{
					$this->SessionDestroy ();
					return false;
				}
			}
			
			// Expiration check
			if ( ($this->sessionData['last_activity'] + $this->expiration) < time () )
			{
				$this->SessionDestroy ();
				return false;
			}
			
			$this->sessionData['last_activity'] = time ();
			
			// Write last activity
			$this->SessionWrite ();
		
			$this->sessionValid = true;
			
			return true;
		}
		// We have a session, but no data
		else
		{
			$this->SessionDestroy ();
			return false;
		}
	}
	
	/**
	 * Sends session data to browser
	 *
	 * @access	public
	 * @return	boolean	returns true, if session was successfully sent
	 **/
	
	public function SessionWrite ()
	{
		if ( (count ($this->sessionData) > 0) && ($this->sessionValid === true) )
		{
			if ($this->sessionCookie === true)
			{
				// Serialize the userdata for the cookie
				$cookieString = $this->_serialize ($this->sessionData);
				
				$cookieString = $cookieString . md5 ($cookieString . $this->cookieEncryptionKey);
		
				if ($this->cookieEncrypt == true)
				{
					$initSize = mcrypt_get_iv_size (MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
					$initVect = mcrypt_create_iv ($initSize, MCRYPT_RAND);
					$cookieString = mcrypt_encrypt (MCRYPT_RIJNDAEL_256, $this->cookieEncryptionKey, $cookieString, MCRYPT_MODE_CBC, $initVect);
				}
		
				$expire = ($this->expireOnClose === true) ? 0 :  time() + $this->expiration;
		
				// Set the cookie
				setcookie ($this->cookieName, $cookieString, $expire, $this->cookiePath, $this->cookieDomain);
			}
			else
			{
				session_start ();
				
				$this->sessionData['session_id'] = session_id ();
				
				foreach ($this->sessionData as $key => $value)
				{
					$_SESSION[$key] = $value;
				}
			}
			
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Create new session and prepare basic session data
	 *
	 * @access	public
	 * @return	void
	 **/
	
	public function SessionCreate ()
	{
		$this->sessionData = array ();
		$this->sessionData['ip_address'] = $this->IpAddress ();
		$this->sessionData['user_agent'] = substr ($this->UserAgent (), 0, 50);
		$this->sessionData['last_activity'] = time ();
		
		if ($this->sessionCookie === true)
		{
			$this->sessionData['session_id'] = md5 (uniqid ('eesid', true));
		}
		
		$this->sessionValid = true;
		
		return ($this->SessionWrite ());
	}
	
	/**
	 * Destroy user session
	 *
	 * @access	public
	 * @return	void
	 **/
	
	public function SessionDestroy ()
	{
		$this->sessionData = array ();
		$this->sessionValid = false;
		
		if ($this->sessionCookie === true)
		{
			setcookie ($this->cookieName, '', time () - 31500000, $this->cookiePath, $this->cookieDomain, 0);
		}
		else
		{
			// Reset all session keys
			foreach ($_SESSION as $key => $value)
			{
				$_SESSION[$key] = '';
				unset ($_SESSION);
			}
			
			session_unset ();	
			session_destroy ();
		}
	}
	
	/**
	 * Fetch the IP Address
	 *
	 * @access	public
	 * @return	string	current IP address
	 **/
	
	public function IpAddress ($proxyIps = '')
	{
		$ipAddress = false;
		
		if ( ($proxyIps != '') && ($_SERVER['HTTP_X_FORWARDED_FOR']) && ($_SERVER['REMOTE_ADDR']) )
		{
			$proxies = preg_split ('/[\s,]/', $proxyIps, -1, PREG_SPLIT_NO_EMPTY);
			$proxies = is_array ($proxies) ? $proxies : array($proxies);

			$ipAddress = in_array ($_SERVER['REMOTE_ADDR'], $proxies) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
		}
		else if ($_SERVER['REMOTE_ADDR'] && !empty ($_SERVER['HTTP_CLIENT_IP']) )
		{
			$ipAddress = $_SERVER['HTTP_CLIENT_IP'];
		}
		else if ($_SERVER['REMOTE_ADDR'])
		{
			$ipAddress = $_SERVER['REMOTE_ADDR'];
		}
		else if ($_SERVER['HTTP_CLIENT_IP'])
		{
			$ipAddress = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif ($_SERVER['HTTP_X_FORWARDED_FOR'])
		{
			$ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}

		if ($ipAddress === false)
		{
			return '0.0.0.0';
		}

		if (strpos ($ipAddress, ',') !== false)
		{
			$x = explode (',', $ipAddress);
			$ipAddress = trim (end ($x));
		}

		if (!$this->ValidateIp ($ipAddress))
		{
			$ipAddress = '0.0.0.0';
		}

		return $ipAddress;
	}

	/**
	 * Validate IP Address
	 *
	 * @access	public
	 * @param	string	IP address
	 * @return	boolean	returns true when IP is valid
	 **/
	
	public function ValidateIp ($ip)
	{
		$ipSegments = explode ('.', $ip);

		// Always 4 segments needed
		if (count ($ipSegments) != 4)
		{
			return false;
		}
		// IP can not start with 0
		if ($ipSegments[0][0] == '0')
		{
			return false;
		}
		// Check each segment
		foreach ($ipSegments as $segment)
		{
			// IP segments must be digits and can not be
			// longer than 3 digits or greater then 255
			if ( ($segment == '') || preg_match ("/[^0-9]/", $segment) || ($segment > 255) || strlen ($segment) > 3)
			{
				return false;
			}
		}

		return true;
	}
	
	/**
	 * User Agent
	 *
	 * @access	public
	 * @return	string	user agent
	 **/
	
	public function UserAgent ()
	{
		$userAgent = (!isset ($_SERVER['HTTP_USER_AGENT'])) ? false : $_SERVER['HTTP_USER_AGENT'];

		return $userAgent;
	}
	
	/**
	 * Serialize data to string
	 *
	 * @access	private
	 * @return	string	serialized string
	 **/
	
	private function _serialize ($data)
	{
		if (is_array ($data) === true)
		{
			foreach ($data as $key => $val)
			{
				if (is_string ($val))
				{
					$data[$key] = str_replace ('\\', '{{BSLASH}}', $val);
				}
			}
		}
		else
		{
			if (is_string ($data) === true)
			{
				$data = str_replace ('\\', '{{BSLASH}}', $data);
			}
		}

		return serialize ($data);
	}
	
	/**
	 * Deserialize string back into array
	 *
	 * @access	private
	 * @return	string	array of objects
	 **/
	
	private function _deserialize ($data)
	{
		if (is_array ($data) === true)
		{
			foreach ($data as $key => $value)
			{
				$data[$key] = stripslashes ($value);
			}
		}
		else
		{
			$data = stripslashes ($value);
		}
		
		$data = @unserialize ($data);

		if (is_array ($data) === true)
		{
			foreach ($data as $key => $val)
			{
				if (is_string ($val))
				{
					$data[$key] = str_replace ('{{slash}}', '\\', $val);
				}
			}

			return $data;
		}

		return (is_string ($data)) ? str_replace ('{{slash}}', '\\', $data) : $data;
	}
	
}