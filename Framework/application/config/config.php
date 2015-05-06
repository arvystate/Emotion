<?php

/***************************************************************************
 *   Application          : Emotion [Athena]                               *
 *   -------------------------------------------------------------------   *
 *   Package              : @sample                                        *
 *   File                 : config.php                                      *
 *   -------------------------------------------------------------------   *
 *   Begin                : Wednesday, Jun 30, 2011                        *
 *   CopyRight            : (C) 2011 ArvYStaTe.net Team                    *
 *   E-Mail               : support@arvystate.net                          *
 *   Last edit            : Wednesday, Jun 30, 2011                        *
 *                                                                         *
 *                                                                         *
 ***************************************************************************

 ***************************************************************************
 *   File description                                                      *
 *   -------------------------------------------------------------------   *
 *   This is a sample config file, to show how are the variables stored    *
 *   in config. It can be generated by online compiler or manually by      *
 *   the developer.                                                        *
 *   It is important to derive from EmotionConfiguration, otherwise        *
 *   automatic loading system will not find it and application will fail   *
 *   to load.                                                              *
 *   -------------------------------------------------------------------   *
 *                                                                         *
 ***************************************************************************
 
 ***************************************************************************
 *   Change log                                                            *
 *   -------------------------------------------------------------------   *
 *    + [06/30/11] - No changes since file was created                     *
 *   -------------------------------------------------------------------   *
 *                                                                         *
 ***************************************************************************

 ***************************************************************************
 *                                                                         *
 *   Emotion is a powerful PHP framework (kernel) for site generation.     *
 *   -------------------------------------------------------------------   *
 *   Application is owned and copyrighted by ArvYStaTe.net Team, you are   *
 *   only allowed to modify code, not take ownership or in any way claim   *
 *   you are the creator of any thing else but modifications.              *
 *   -------------------------------------------------------------------   *
 *                                                                         *
 ***************************************************************************/

class AppConfig extends EmotionConfiguration
{
	//
	// All config variables are here the same as default ones (incase config is missing, default is already statically compiled into core)
	// SetupVariables function	
	//
	
	protected function CustomVariables ()
	{
		// Overwrite variables if you want them custom
		$this->Set ('autoload', true);
		$this->Set ('enable_query_strings', false);
		
		// Add your own variables
		$this->Set ('database_host', 'localhost');
		$this->Set ('database_user', 'database_username');
		$this->Set ('database_pass', 'database_password');
		$this->Set ('database_name', 'database_name');
		$this->Set ('database_prefix', 'database_prefix');
	}
}
?>