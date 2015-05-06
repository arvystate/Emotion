<?php
/**
 * Sample_Debug
 *
 * Short sample loader to display Debug library usage.
 *
 * @package	sample
 * @require	false
 * @author	ArvYStaTe.net Team
 **/

class Sample_Debug extends EmotionLoader
{
	protected function CustomLoad ()
	{
		//
		// Start debug timer
		//
		
		$this->Load ('Debug')->StartTimer ();
		
		//
		// Do something that takes a lot of time.
		// We wait for 0.6 second, 600.000 nanoseconds
		//
		
		usleep (600000);
		
		echo ('I have slept for ' . $this->Load ('Debug')->MeasureTime () . ' seconds.<br />');
		
		//
		// Save debug messages to notify developer
		//
		
		$this->Load ('Debug')->SaveMessage ('I need to write something to see the application loading routine.');
		$this->Load ('Debug')->SaveMessage ('I need to remember something for debugging process again.');
		
		//
		// Start watching memory usage
		//
		
		$this->Load ('Debug')->StartMemoryTimer ();
		
		//
		// Pollute array
		//
		
		$array = array ();
		
		for ($i = 0; $i < 200; $i++)
		{
			$array[$i] = "$i$i$i";
		}

		$this->Load ('Debug')->SaveMessage ('I used ' . $this->Load ('Debug')->MeasureMemory () . ' bytes of extra memory.');
		
		//
		// Display messsage log
		//
		
		$this->Load ('Debug')->Display ();
	}
}
?>