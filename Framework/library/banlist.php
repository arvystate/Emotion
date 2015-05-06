<?php
/***************************************************************************
 *   Application          : Emotion                                        *
 *   -------------------------------------------------------------------   *
 *   Package              : @library                                       *
 *   File                 : banlist.php                                    *
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
 *   Banlist is a class that provides basic site access functionality.     *
 *   Site user can define various ranges and IP addresses and system       *
 *   can deny site access based on those lists.                            *
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
 * Banlist
 *
 * Basic banlist to limit site accessibility based on client IP address.
 *
 * @package	library
 * @require	false
 * @author	ArvYStaTe.net Team
 **/
 
class Banlist
{
	/**
	 * Check IP on Ban list
	 *
	 * @access	public
	 * @param	array	each row has an IPv4 IP address and optional letter 'e' seperated by space if an exception is defined
	 * @param	string	ip we are looking on ban list
	 * @param	boolean	optional true, if you want exceptions to be taken into consideration
	 * @return	boolean returns true if IP address is banned, false otherwise
	 **/
	 
	public function CheckBanList ($banList, $searchIp, $exception = true)
	{
		$banned = false;
		
		foreach ($banList as $value)
		{			
			// Check for exceptions
			if ( (strpos ($value, 'e') >= 0) && ($exception === true) )
			{
				$exceptionNow = true;
			}
			
			// Security -> remove spaces and E's
			$value = str_replace (' ', '', $value);
			$value = str_replace ('e', '', $value);
			
			// Check if it is a valid IP
			if ($this->ValidateIp ($value) === true)
			{
				$match = $this->MatchRange ($value, $searchIp);
				
				// If it is an exception, we are not banned
				if ($exceptionNow === true)
				{
					return false;
				}
				else
				{
					$banned = true;
				}
				
			}
			// Check if it is range
			else if (strpos ($value, '-') >= 0)
			{
				$match = $this->MatchRangeLong ($value, $searchIp);
				
				// If it is an exception, we are not banned
				if ($exceptionNow === true)
				{
					return false;
				}
				else
				{
					$banned = true;
				}
			}
		}
		
		return $banned;
	}
	
	/**
	 * Returns IP match in wildcard range
	 *
	 * @access	public
	 * @param	string	ip range with wildcards (*.*.*.*, xx.*.*.*, xx.xx.*.*, ...)
	 * @param	string	search ip
	 * @return	boolean returns true if IP address matches range, false otherwise
	 **/
	 
	public function MatchRange ($range, $searchIp)
	{
		$match = 0;
		
		$rangeSubnets = explode ('.', $range);
		$searchSubnets = explode ('.', $searchIp);
		
		// Security check for correct ips
		if ($this->ValidateIp ($range) === false)
		{
			return false;
		}
		if ($this->ValidateIp ($searchIp) === false)
		{
			return false;
		}
		
		if ( (count ($rangeSubnets) != 4) || (count ($searchSubnets) != 4) )
		{
			return false;
		}
		
		for ($i = 0; $i < 4; $i++)
		{
			if ( ($rangeSubnets[$i] == '*') || ($rangeSubnets[$i] == $searchSubnets[$i]) )
			{
				$match++;
			}
		}
		
		if ($match == 4)
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns true if IP is valid with wildcards
	 *
	 * @access	public
	 * @param	string	IP address
	 * @return	boolean	returns true if IP address is valid, false otherwise
	 **/
	 
	public function ValidateIp ($ip)
	{
		if (preg_match ("/^(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|[*])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0|[*])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0|[*])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9]|[*])$/", $ip) > 0)
		{
			$subnets = explode ('.', $ip);
			
			// IPv4 has exactly 4 subnets
			if (count ($subnets) != 4)
			{
				return false;
			}
			
			foreach ($subnets as $sub)
			{
				// Skip wildcards
				if ($sub == '*')
				{
					continue;
				}
				else if ( (intval ($sub) > 255) || (intval ($sub) < 0) )
				{
					return false;
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
	 * Returns IP match in range without wildcards
	 *
	 * @access	public
	 * @param	string	ip range (xx.xx.xx.xx-xx.xx.xx.xx), the dash is important and second IP needs to be higher than first
	 * @param	string	search ip
	 * @return	boolean returns true if IP address matches range, false otherwise
	 **/
	 
	public function MatchRangeLong ($range, $searchIp)
	{
		$range = str_replace (' ', '', $range);
		
		$range = explode ('-', $range);
		
		if (count ($range) != 2)
		{
			return false;
		}
		
		// Security check for correct ips
		if (preg_match('^(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])$', $range[0]) <= 0)
		{
			return false;
		}
		if (preg_match('^(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])$', $range[0]) <= 0)
		{
			return false;
		}
		if (preg_match('^(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])$', $searchIp) <= 0)
		{
			return false;
		}
		
		// Check if the search IP is in range
		
		$range[0] = explode ('.', $range[0]);
		$range[1] = explode ('.', $range[1]);
		
		$searchIp = explode ('.', $searchIp);
		
		$match = 0;
		
		for ($i = 0; $i < 4; $i++)
		{
			if ( (intval ($range[0][$i]) <= intval ($searchIp[$i]) ) && (intval ($searchIp[$i]) <= intval ($range[1][$i]) ) )
			{
				$match++;
			}
		}
		
		if ($match == 4)
		{
			return true;
		}
		
		return false;
	}
}