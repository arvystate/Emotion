<?php

class EmotionClass
{
	private $className = '';
	private $classDescription = '';
	private $classPackage = '';
	private $classRequire = false;
	private $classAuthor = '';
	
	// Extends, Implements
	private $classPostData = '';
	// Final, Abstract
	private $classPreData = '';
	
	private $classData = '';
	
	public function __construct ($data, $className)
	{
		//
		// We found the class marker, but it could be commented and if its commented, we should find other -> luckily PHP does not allow class redefinitions
		// This is a problem, because you can basically comment anything and as many times as you want..
		//
		
		// This finds correct class position if it exists in filedata
		$classPosition = $this->_findClass ($data, $className);
		
		// We found class, so we will parse class data
		if ($classPosition !== false)
		{			
			$this->className = $className;
			
			// Comment is before class name.. If its not there, skip
			
			$this->_parseComment (substr ($data, 0, $classPosition));
			
			// Code is after class name
			$this->_parseCode (substr ($data, $classPosition));
			
			// Class modifier
			$this->_parseClassData ($data, $classPosition);
		}
	}
	
	public function IsReady ()
	{
		return ($this->className != '');
	}
	
	public function GetClassConstruct ()
	{
		return trim ($this->classPreData . ' class ' . $this->className . ' ' . $this->classPostData);
	}
	
	public function GetClassName ()
	{
		return $this->className;
	}
	
	public function GetClassData ()
	{
		return $this->classData;
	}
	
	public function SetClassData ($data)
	{
		$this->classData = $data;
	}
	
	public function ConstructComment ()
	{
		if ( (empty ($this->classPackage) === false) && (empty ($this->classAuthor) === false) && (empty ($this->classRequire) === false) )
		{
			return '';
		}
		
		$comment .= "/**\n";
		$comment .= " * " . $this->className . "\n";

		if (empty ($this->classDescription) === false)
		{
			$comment .= " *\n";
			$comment .= " * " . $this->classDescription . "\n";
			$comment .= " *\n";
		}
		
		if (empty ($this->classPackage) === false)
		{
			$comment .= " * " . $this->classPackage . "\n";
		}
		if (empty ($this->classRequire) === false)
		{
			$comment .= " * " . $this->classRequire . "\n";
		}
		if (empty ($this->classAuthor) === false)
		{
			$comment .= " * " . $this->classAuthor . "\n";
		}
		
		$comment .= " **/\n";
		
		return $comment;		
	}
	
	// Check if we have an abstract or final class
	private function _parseClassData ($code, $position)
	{
		// Looking for predata
		$pre = substr ($code, 0, $position);
		$pre = trim ($pre);
		
		$i = strlen ($pre) - 1;
		
		for ($i = strlen ($pre) - 1; $i >= 0; $i--)
		{
			if ( ($pre{$i} == "\n") || ($pre{$i} == "}") || ($pre{$i} == "/") || ($pre{$i} == ";") )
			{
				break;
			}
		}
		
		$pre = substr ($pre, $i + 1);
		$pre = trim ($pre);
		
		// Looking for postdata
		$post = substr ($code, $position);
		
		// Cut away from class name to first {
		$post = substr ($post, strpos ($post, $this->className) + strlen ($this->className));
		$post = substr ($post, 0, strpos ($post, '{'));
		
		$this->classPostData = trim ($post);
		$this->classPreData = $pre;
	}
	
	// Function returns code to the end of the class
	private function _parseCode ($code)
	{
		// A short version of getBlocks code
		
		// Going from first { to the last } of the class, skipping comments and quotes
		$code = substr ($code, strpos ($code, '{') + 1);
				
		$inCommentMultiline = false;
		$inComment = false;
		$inQuote = false;
		
		$doubleQuote = false;
		
		$blockLevel = 1;
		$endClass = 0;
	
		for ($i = 0; $i < strlen ($code); $i++)
		{
			// Only parsing what is between php tags
			if ( ($inComment === false) && ($inCommentMultiline === false) && ($inQuote === false) )
			{
				// Skipping single line commentaries
				if (substr ($code, $i, 2) == '//')
				{
					$inComment = true;
					continue;
				}
				// Skipping multi line commentaries
				else if (substr ($code, $i, 2) == '/*')
				{
					$inCommentMultiline = true;
					continue;
				}
				// Parsing blocks
				else
				{
					$char = substr ($code, $i, 1);
					
					// If we detected quote
					if ( (ord ($char) == 34) || (ord ($char) == 39) )
					{
						$inQuote = true;
						
						if (ord ($char) == 34)
						{
							$doubleQuote = true;
						}
					}
					// Moving up the block levels
					else if ($char == '{')
					{
						$blockLevel++;
					}
					// Moving down the block levels
					else if ($char == '}')
					{
						$blockLevel--;
						
						$endClass = $i;
						
						//
						// When we reach block level 0, meaning we're out of class, we stop data collection
						//
						
						if ($blockLevel == 0)
						{							
							break;
						}
					}
				}
			}
			
			//
			// No parsing
			//
			
			else
			{
				// Quote system
				if ($inQuote == true)
				{
					// Checking for next quote
					if ( ($doubleQuote === true) && ($code{$i} == chr (34)) )
					{
						if ( ( ($code{($i - 1)} != chr(92) ) || ($code{($i - 2)} == chr (92) ) ) && ($i >= 2) )
						{
							$inQuote = false;
							$doubleQuote = false;
						}
						
					}
					else if ($code{$i} == chr(39))
					{
						if ( ( ($code{($i - 1)} != chr(92) ) || ($code{($i - 2)} == chr (92) ) ) && ($i >= 2) )
						{
							$inQuote = false;
						}
					}
				}
				// Single line comment
				else if ( ($inComment === true) && (substr ($code, $i, 1) == "\n") )
				{
					$inComment = false;
				}
				// Multiline comment
				else if ( ($inCommentMultiline === true) && (substr ($code, $i, 2) == '*/') )
				{
					$inCommentMultiline = false;
				}
			}
		}
		
		$this->classData = trim (substr ($code, 0, $endClass) );
	}
	
	// If class has Emotion style comment written, this function will retrieve data and store it in class variables
	private function _parseComment ($code)
	{
		// Find first comment from behind, they usually start with */ -> we expect Emotion Engine syntax, otherwise we cannot read
		$commentPosition = strrpos ($code, '/*');
		
		// Parsing comment only if there is one
		if ($commentPosition !== false)
		{
			// We find the comment and cut away starting /* and ending */
			$code = substr ($code, $commentPosition);
			$code = substr ($code, 0, strrpos ($code, '*/'));
			$code = str_replace (array ('/**', '/*', '**/', '*/', ' * ', " *\n"), '', $code);
			$code = trim ($code);
			
			// We have position of a comment, we need to check if the comment belogs to our class
			$commentLines = explode ("\n", $code);
			
			//
			// We will go through array and clean empty lines away
			// But not before we replace spaces and stars on start
			//
			
			for ($i = 0; $i < count ($commentLines); $i++)
			{
				// Removing stars and spaces
				
				$str = '';
				
				$passed = false;
				
				for ($x = 0; $x < strlen ($commentLines[$i]); $x++)
				{
					if ( ( ($commentLines[$i]{$x} == ' ') || ($commentLines[$i]{$x} == '*') ) && ($passed === false) )
					{
						continue;
					}
					else
					{
						$str .= $commentLines[$i]{$x};
						$passed = true;
					}
				}
				
				// Once we're done, we trim the string
				
				$commentLines[$i] = trim ($str);
				
				// If its empty string, we remove it
				if (empty ($commentLines[$i]) === true)
				{
					unset ($commentLines[$i]);
				}
			}
			
			// Reorganize array
			
			$commentLines = array_values ($commentLines);
			
			//
			// Now we are able to read our comment syntax, but it could still be from another class,
			// so we check if class name is correct.
			//
			
			if ($commentLines[0] == $this->className)
			{
				// Now we find our parameters
				
				for ($i = 1; $i < count ($commentLines); $i++)
				{
					if (strpos ($commentLines[$i], '@package') !== false)
					{
						$this->classPackage = str_replace (array ('@package ', '@package'), '', $commentLines[$i]);
					}
					else if (strpos ($commentLines[$i], '@require') !== false)
					{
						$this->classRequire = str_replace (array ('@require ', '@require'), '', $commentLines[$i]);
					}
					else if (strpos ($commentLines[$i], '@author') !== false)
					{
						$this->classAuthor = str_replace (array ('@author', '@author '), '', $commentLines[$i]);
					}
					else
					{
						$this->classDescription .= $commentLines[$i] . ' ';
					}
				}
				
				$this->classDescription = trim ($this->classDescription);
			}
		}
	}
	
	// Function returns the position of class start or false if class is not found
	private function _findClass ($data, $className)
	{
		// Until we find
		$found = false;
		
		$offset  = 0;
		
		// Until we find the class or come to the end of the file
		while ( ($position = strpos ($data, 'class ' . $className)) !== false)
		{
			// If its in comment, we remove string before this
			if ($this->_isInComment ($data, 'class ' . $className) === true)
			{
				$data = substr ($data, $position + strlen ('class ' . $className));
				$offset = $offset + $position + strlen ('class ' . $className);
			}
			else
			{
				// Need to test if name is truly a name, not first part of another class
				$classNameParse = substr ($data, $position + strlen ('class ' . $className), 1);
				
				// ClassNameParse should now contain next char
				
				if ( ($classNameParse == "{") || ($classNameParse == "\n") || ($classNameParse == " ") || ($classNameParse == "\t") )
				{
					$found = $position;
					break;
				}
				else
				{
					$data = substr ($data, $position + strlen ('class ' . $className));
					$offset = $offset + $position + strlen ('class ' . $className);
				}
			}
		}
		
		if ($found !== false)
		{
			$found = $found + $offset;
		}
		
		return $found;
	}
	
	// Check if class is in comment
	private function _isInComment ($data, $text)
	{
		// Check if its a single line comment
		
		$textPos = strpos ($data, $text);
		
		// Text not found, we arent in comment?
		if ($textPos === false)
		{
			return false;
		}
		
		// We will check if there is a // before next \n
		$lineComment = substr ($data, 0, $textPos);
		
		$inComment = false;
		
		for ($i = $textPos; $i >= 1; $i--)
		{
			// We found the comment before newline, we're in comment :(
			if (substr ($data, $i, 2) == '//')
			{
				$inComment = true;
				break;
			}
			// Newline, no comment
			else if ($data{$i} == "\n")
			{
				break;
			}
		}
	
		// Dont have to check for multiline comments, if we are in single line comment.
		if ($inComment === true)
		{
			return true;
		}
		
		// Multiline comment is easier, we look for first /*, which is the start of comment.
		// If we reach */ before that, we're not in comment
		
		for ($i = $textPos; $i >= 1; $i--)
		{
			// First thing we found, its start of the comment sign, we're in comment
			if (substr ($data, $i, 2) == '/*')
			{
				$inComment = true;
				break;
			}
			// We found end comment sign
			else if (substr ($data, $i, 2) == "*/")
			{
				break;
			}

		}
		
		return $inComment;
	}
	
	private function _boolToString ($bool)
	{
		if ($bool === true)
		{
			return 'true';
		}
		else
		{
			return 'false';
		}
	}
}
?>