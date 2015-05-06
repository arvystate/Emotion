<?php

class EmotionParser
{
	public function ParseConfig ($text)
	{
		$text = trim ($text);
		
		// There are some things we can do ourselves...
		$illegal = array ('\'', ';', ':', ',');
		
		// Remove all illegal characters
		$text = str_replace ($illegal, '', $text);
		
		$mode = 1;
		
		$varName = '';
		$varValue = '';
		
		$parsed = '';
		
		// Go through text
		for ($i = 0; $i < strlen ($text); $i++)
		{
			switch ($mode)
			{
				// Reading name
				case 1:
					if ($text{$i} == '=')
					{
						$mode = 0;
					}
					else if ( ($text{$i} == "\n") || ($text{$i} == ' ') )
					{
						continue;
					}
					else
					{
						$varName .= $text{$i};
					}
				
					break;
				// Reading var value
				case 2:
					// Finished reading one line, follow to the other
					if ($text{$i} == '"')
					{
						$mode = 1;
						
						$parsed .= $this->ConstructConfigLine ($varName, $varValue);
						
						$varName = '';
						$varValue = '';
					}
					else if ($text{$i} == "\n")
					{
						$mode = 1;
						
						$parsed .= $this->ConstructConfigLine ($varName, $varValue);
						
						$varName = '';
						$varValue = '';
					}
					else
					{
						$varValue .= $text{$i};
					}
				
					break;
				// Reading nothing
				default:
					if ($text{$i} == '"')
					{
						$mode = 2;
					}
					else if ($text{$i} == ' ')
					{
						continue;
					}
					else
					{
						$mode = 2;
						$varValue  .= $text{$i};
					}
				
					break;
			}
		}
		
		$varName = trim ($varName);
		$varValue = trim ($varValue);
		
		if ( (empty ($varName) === false) && (empty ($varValue) === false) )
		{
			$parsed .= $this->ConstructConfigLine ($varName, $varValue);
		}
		
		return trim ($parsed);
	}
	
	public function ParseFiles ($text)
	{
		$illegal = array (' ', ';', ':', ',', '\'', '"');
		
		// Remove all spaces and illegal characters
		$text = str_replace ($illegal, '', $text);
		
		// Get files line by line
		$text = explode ("\n", $text);
		
		// Removing all kinds of directories
		foreach ($text as $key => $value)
		{
			$text[$key] = trim (basename ($value));
			
			// Remove extension
			if (strrpos ($text[$key], '.') >= strlen ($text[$key]) - 4)
			{
				$text[$key] = substr ($text[$key], 0, strrpos ($text[$key], '.'));
			}
			
			// Uppercase first character
			$text[$key] = ucwords ($text[$key]);
			
			if (empty ($text[$key]) === true)
			{
				unset ($text[$key]);
			}
		}
		
		$text = array_values ($text);
		
		return trim (implode ("-", $text));
	}
	
	public function ParseExtension ($extension)
	{
		// Remove first dot
		if (substr ($extension, 0, 1) == '.')
		{
			$extension = substr ($extension, 1);
		}
		
		// Allowing only max 4-char extensions
		if (strlen ($extension) > 4)
		{
			$extension = substr ($extension, 0, 4);
		}
		
		// No extension security?
		if (strlen ($extension) == 0)
		{
			$extension = 'php';
		}
		
		return $extension;
	}
	
	public function ConstructConfigLine ($varName, $varValue)
	{
		$varName = trim ($varName);
		$varValue = trim ($varValue);
		
		$line = $varName . ' = ';
		
		// Numeric values
		if (is_numeric ($varValue) === true)
		{
			$line .= $varValue;
		}
		else if ( (strtolower ($varValue) == 'true') || (strtolower ($varValue) == 'false') || (strtolower ($varValue) == 'null') )
		{
			$line .= strtolower ($varValue);
		}
		else
		{
			$line .= '"' . $varValue . '"';
		}
		
		$line .= "\n";
		
		return $line;
	}
}

?>