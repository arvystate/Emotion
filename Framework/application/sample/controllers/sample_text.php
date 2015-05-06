<?php
/**
 * Sample_Text
 *
 * Short sample controller to display Text library usage.
 *
 * @package	sample
 * @require	false
 * @author	ArvYStaTe.net Team
 **/

class Sample_Text extends EmotionController
{
	public function index ()
	{
		//
		// Create some random strings
		//
		
		echo ('Random Alpha: ' . $this->Load ('Text')->Random ('alpha', 10) . '<br />');
		echo ('Random AlphaNumeric: ' . $this->Load ('Text')->Random ('alnum', 10) . '<br />');
		echo ('Random Numeric: ' . $this->Load ('Text')->Random ('numeric') . '<br />');
		echo ('Random Without Zero: ' . $this->Load ('Text')->Random ('nozero') . '<br />');
		echo ('Random MD5: ' . $this->Load ('Text')->Random ('md5') . '<br />');
		echo ('Random: ' . $this->Load ('Text')->Random () . '<br />');
		echo ('<br />');
		
		//
		// Format filesize
		//
		
		echo ('My file 1: ' . $this->Load ('Text')->FormatFileSize (6365124) . '<br />');
		echo ('My file 2: ' . $this->Load ('Text')->FormatFileSize (100345) . '<br />');
		echo ('My file 3: ' . $this->Load ('Text')->FormatFileSize (856526345124) . '<br />');
		echo ('<br />');
		
		//
		// Insert breaks with dots every 10 characters
		//
		
		echo ('Insert string:<br />');
		echo ($this->Load ('Text')->InsertString ('averylongsinglestringtoshowyouhowicaninsertbreaks.', 10, '...<br />'));
	}
}

?>