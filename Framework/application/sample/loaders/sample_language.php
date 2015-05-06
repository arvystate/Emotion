<?php
/**
 * Sample_Language
 *
 * Short sample loader to display Language library usage.
 *
 * @package	sample
 * @require	false
 * @author	ArvYStaTe.net Team
 **/

class Sample_Language extends EmotionLoader
{
	protected function CustomLoad ()
	{
		//
		// Check if we have language variables
		//
		
		if ($this->Load ('Language')->IsAvailible () === true)
		{
			echo ('Language system is availible, proceeding...<br />');
			
			$this->Load ('Language')->SelectLanguage ('en');
			
			echo ($this->Load ('Language')->Replace ('{LANG_YES}, this is {LANG_NAME}!'));
		}
		else
		{
			echo ('Write some languages first!<br />');
		}
	}
}
?>