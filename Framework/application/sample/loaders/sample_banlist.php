<?php
/**
 * Sample_Banlist
 *
 * Short sample loader to display Banlist library usage.
 *
 * @package	sample
 * @require	false
 * @author	ArvYStaTe.net Team
 **/

class Sample_Banlist extends EmotionLoader
{
	protected function CustomLoad ()
	{
		//
		// Construct banned IP addresses (white list)
		//
		
		$bannedIp[0] = '*.*.*.*';
		$bannedIp[1] = '123.4.*.* e';
		$bannedIp[2] = '127.0.0.1 e';
		
		// Print IP
		echo ('Your IP Address: ' . $this->Load ('Session')->IpAddress () . '<br />');
		
		//
		// Checking ban list
		//
		
		if ($this->Load ('Banlist')->CheckBanList ($bannedIp, $this->Load ('Session')->IpAddress ()) === true)
		{
			//
			// Deny access or redirect user
			//
			
			echo ('You are banned. I need you to go away.<br />');
		}
		else
		{
			echo ('You have an exception. You must be the miracle worker.<br />');
		}
		
		unset ($bannedIp);
		
		//
		// Construct banned IP addresses (black list)
		//
		
		$bannedIp[0] = '155.5.*.*';
		$bannedIp[1] = '155.5.5.* e';
		
		// Checking ban list again
		if ($this->Load ('Banlist')->CheckBanList ($bannedIp, $this->Load ('Session')->IpAddress ()) === true)
		{
			echo ('You are banned. I need you to go away second time...<br />');
		}
		else
		{
			echo ('You have ANOTHER exception. You will be truly worshipped.<br />');
		}
	}
}
?>