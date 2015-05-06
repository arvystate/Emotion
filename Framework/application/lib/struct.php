<?php

class EmotionStructure
{
	private $fileBase = array ();
	
	public function __construct ()
	{
		$this->_scanDirectory ('database');
		$this->_scanDirectory ('library');
		$this->_scanDirectory ('system');
	}

	public function GetFiles ()
	{
		return $this->fileBase;
	}
	
	private function _scanDirectory ($directory)
	{
		$localFiles = @scandir ($directory);
		
		foreach ($localFiles as $value)
		{
			// Extension and filecheck 
			if ( (is_file ($directory . '/' . $value) === true) && ('.' . pathinfo ($directory . '/' . $value, PATHINFO_EXTENSION) === EXTENSION) )
			{
				$this->fileBase[$value]['hash'] = sha1_file ($directory . '/' . $value);
				$this->fileBase[$value]['path'] = $directory . '/' . $value;
				$this->fileBase[$value]['modified'] = filemtime ($directory . '/' . $value);
				$this->fileBase[$value]['db_status'] = 0;
			}
		}
	}
}

?>