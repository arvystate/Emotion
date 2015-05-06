<?php

class Admin extends EmotionController
{
	public function index ($password = '')
	{
		if ($password == 'qwertasdfg')
		{
			$this->View ('header_no_menu');
			
			echo ('<div id="main_content">
					<div id="content">
						<div class="front_page">');
						
			echo ($this->Load ('Debug')->GetLog ());
			
			echo ('</div><!-- end .front_page -->
					</div><!-- end #content -->
				</div><!-- end #main_content -->');
			
			$this->View ('footer');
		}
		else
		{
			$this->View ('404');
		}
	}
}

?>