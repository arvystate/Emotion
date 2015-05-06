<?php
/**
 * Sample_Session
 *
 * Short sample controller to display Session library usage.
 *
 * @package	sample
 * @require	false
 * @author	ArvYStaTe.net Team
 **/

class Sample_Session extends EmotionController
{
	public function index ()
	{
		//
		// Initialize Session handler
		//
		
		$this->Load ('Session')->Initialize ();
		
		echo ('Reading session data...<br />');
		
		//
		// Read Session data
		//
		
		if (!$this->Load ('Session')->SessionRead ())
		{
			//
			// Session data not yet created, we can proceed with creation
			//
			
			echo ('Session not created, proceed with creation...<br />');
			
			// Create it
			if ($this->Load ('Session')->SessionCreate ())
			{
				//
				// Store a string into Session data
				//
				
				$this->Load ('Session')->Set ($this->Load ('Text')->Random ('alnum', 50), 'random_str');
				
				// Write Session data
				$this->Load ('Session')->SessionWrite ();
				
				echo ('Session successfully created. SID: ' . $this->Load ('Session')->Get ('session_id') . '<br />');
			}
			
			// There was an error with creating session
			else
			{
				echo ('Error creating session.<br />');
			}
		}
		
		//
		// Session data was successfully read, we just output it to screen.
		//
		
		else
		{
			echo ('Session successfully read. SID: ' . $this->Load ('Session')->Get ('session_id') . '<br />');
			
			// We might have Session data, but we do not have random string yet.
			if ($this->Load ('Session')->Get ('random_str') == '')
			{
				// Set session data
				$this->Load ('Session')->Set ($this->Load ('Text')->Random ('alnum', 50), 'random_str');
				
				// Write Session data
				$this->Load ('Session')->SessionWrite ();
			}
			
			echo ('Random string: ' . $this->Load ('Session')->Get ('random_str') . '<br />');
		}
	}
}

?>