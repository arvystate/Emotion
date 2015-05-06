<?php

class Learn extends EmotionController
{
	public function index ()
	{
		$this->View ('header');
		
		$this->View ('learn');
		
		$this->View ('footer');
	}
}

?>