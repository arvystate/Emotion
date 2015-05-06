<?php

class Home extends EmotionController
{
	public function index ()
	{		
		$this->View ('header');
		
		$this->View ('home', array ('news' => $this->Load ('Database')->Select ("users, news", "news.user_id = users.user_id AND active<>'0' ORDER BY timestamp DESC LIMIT 3"), 'newsCount' => $this->Load ('Database')->Select ("users, news", "news.user_id = users.user_id AND active<>'0'", "count(*)")) );
		
		$this->View ('footer');
	}
}

?>