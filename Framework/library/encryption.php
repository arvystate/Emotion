<?php

/***************************************************************************
 *   Application          : Emotion                                        *
 *   -------------------------------------------------------------------   *
 *   Package              : @library                                       *
 *   File                 : encryption.php                                 *
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
 *   Encryption is a class that provides basic encryption extensions.      *
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
 * Encryption
 *
 * Basic encryption algorithms across world wide web.
 *
 * @package	library
 * @require	false
 * @author	ArvYStaTe.net Team
 **/

class Encryption
{
	private $passwordKey = '';
	
	/**
	 * Sets password encryption key
	 *
	 * @access	public
	 * @param	string	encryption key
	 * @return	void
	 **/
	
	public function SetPasswordKey ($key)
	{
		$this->passwordKey = $key;
	}
	
	/**
	 * AES-256 bit encryption function
	 *
	 * @access	public
	 * @param	string	plain text
	 * @param	string	encryption key
	 * @return	mixed	false when encryption failed otherwise AES-256 encrypted string
	 **/
	
	public function AesEncrypt ($buffer, $key)
	{
		if (strlen ($key) != 32)
		{
			return false;	
		}

		$size = mcrypt_get_iv_size (MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
		$iv = mcrypt_create_iv ($size, MCRYPT_RAND);
		
		return bin2hex ($iv . mcrypt_encrypt (MCRYPT_RIJNDAEL_256, $key, $buffer, MCRYPT_MODE_CBC, $iv));
	}
	
	/**
	 * AES-256 bit decryption function
	 *
	 * @access	public
	 * @param	string	AES-256 encrypted text
	 * @param	string	key we want to decrypt text with
	 * @return	mixed	false when decryption failed otherwise decrypted string
	 **/
	
	public function AesDecrypt ($buffer, $key)
	{
		if (strlen ($key) != 32)
		{
			return false;	
		}
		
		$buffer = $this->_hex2bin ($buffer);
		
		$size = mcrypt_get_iv_size (MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);

		if ($size > strlen ($buffer))
		{
			return false;
		}

		$iv = substr ($buffer, 0, $size);		
		$buffer = substr ($buffer, $size);
		
		return rtrim (mcrypt_decrypt (MCRYPT_RIJNDAEL_256, $key, $buffer, MCRYPT_MODE_CBC, $iv) );
	}
	
	/**
	 * TripleDES Encryption function
	 *
	 * @access	public
	 * @param	string	plain text
	 * @param	string	encryption key
	 * @return	string	TripleDES encrypted text
	 **/
	
	public function TripleDesEncrypt ($buffer, $key)
	{
		if (strlen ($key) != 24)
		{
			return false;	
		}
		
		$size = mcrypt_get_iv_size (MCRYPT_3DES, MCRYPT_MODE_CBC);
		$iv = mcrypt_create_iv ($size, MCRYPT_RAND);
		
		$cipher = mcrypt_module_open (MCRYPT_3DES, '', 'cbc', '');
	
		//
		// Get the amount of bytes to pad
		//
		
		$extra = 8 - (strlen ($buffer) % 8);
	
		//
		// Add the zero padding
		//
		if ($extra > 0)
		{
			for ($i = 0; $i < $extra; $i++)
			{
				$buffer .= "\0";
			}
		}
	
		mcrypt_generic_init ($cipher, $key, $iv);
		
		$result = bin2hex ($iv . mcrypt_generic ($cipher, $buffer));
		
		mcrypt_generic_deinit ($cipher);
		
		return $result;
	}

	/**
	 * TripleDES Decryption function
	 *
	 * @access	public
	 * @param	string	TripleDES encrypted text
	 * @param	string	encryption key
	 * @return	string	decrypted text
	 **/
	
	public function TripleDesDecrypt ($buffer, $key)
	{
		if (strlen ($key) != 24)
		{
			return false;	
		}
		
		$buffer = $this->_hex2bin ($buffer);
		
		$size = mcrypt_get_iv_size (MCRYPT_3DES, MCRYPT_MODE_CBC);

		if ($size > strlen ($buffer))
		{
			return false;
		}

		$iv = substr ($buffer, 0, $size);
		$buffer = substr ($buffer, $size);
		
		$cipher = mcrypt_module_open (MCRYPT_3DES, '', 'cbc', '');
	
		mcrypt_generic_init ($cipher, $key, $iv);
		
		$result = rtrim (mdecrypt_generic ($cipher, $buffer), "\0");
		
		mcrypt_generic_deinit ($cipher);
		
		return $result;
	}
	
	/**
	 * Secure password hash
	 *
	 * @access	public
	 * @param	string	password string
	 * @return	string	password hash
	 **/
	 
	 public function SecurePassword ($password)
	 {
		 return (sha1 ($this->passwordKey . md5 ($password . $this->passwordKey) ) );
	 }
	
	/**
	 * Hex to binary function
	 *
	 * @access	private
	 * @param	string	hexadecimal string
	 * @return	string	binary string
	 **/
	
	private function _hex2bin ($data)
	{
		$len = strlen ($data);
		return pack ("H" . $len, $data);
	}
}
?>