<?php
/**
 * Sample_Database
 *
 * Short sample loader to display Database library usage.
 *
 * @package	sample
 * @require	false
 * @author	ArvYStaTe.net Team
 **/

class Sample_Database extends EmotionLoader
{
	protected function CustomLoad ()
	{
		//
		// Prepare Database driver
		//
		
		$this->Load ('Database')->PrepareDriver ('Oracle');
		
		// Print out driver status
		echo ($this->Load ('Database')->DriverStatus () . '<br />');
		
		//
		// Attempt to connect to database.
		// We use constants stored in our configuration file.
		//

		if ($this->Load ('Database')->Connect ($this->Config ('database_host'), $this->Config ('database_user'), $this->Config ('database_pass'), $this->Config ('database_name')))
		{
			// Print successful connection
			echo ('Connection successful.<br /><br />');
			
			// Select all users from database
			$users = $this->Load ('Database')->Select ("users");
			
			// Print them line by line.
			foreach ($users as $value)
			{
				echo ($value['username'] . ', created: ' . date ("j.m.Y", $value['created']) . '<br />');
			}
		}
		else
		{
			// Print unsuccessful connection
			echo ('Connection unsuccessful.<br />');
			
			// Print error
			echo ('Error: ' . $this->Load ('Database')->GetLastError ());
		}
	}
}
?>