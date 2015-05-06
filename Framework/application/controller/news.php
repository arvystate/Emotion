<?php

class News extends EmotionController
{
	public function index ($newsId = '')
	{
		$this->View ('header_no_menu');
		
		$this->View ('news', array ('news' => $this->Load ('Database')->Select ("users, news", "news.user_id = users.user_id AND active<>'0' ORDER BY timestamp DESC LIMIT 15"), 'newsCount' => $this->Load ('Database')->Select ("users, news", "news.user_id = users.user_id AND active<>'0'", "count(*)")) );
		
		$this->View ('footer');
	}	
}

?>