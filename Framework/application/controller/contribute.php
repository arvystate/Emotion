<?php

class Contribute extends EmotionController
{
	public function index ()
	{
		$this->View ('header_no_menu');
		
		$this->View ('contribute');
		
		$this->View ('footer');
	}
}

?>