<?php
/***************************************************************************
 *   Application          : Emotion                                        *
 *   -------------------------------------------------------------------   *
 *   Package              : @library                                       *
 *   File                 : text.php                                       *
 *   Version              : 1.0.0                                          *
 *   Status               : Complete, Not tested                           *
 *   -------------------------------------------------------------------   *
 *   Begin                : Thursday, Apr 4, 2011                          *
 *   CopyRight            : (C) 2011 ArvYStaTe.net Team                    *
 *   E-Mail               : support@arvystate.net                          *
 *   Last edit            : Thursday, Apr 4, 2011                          *
 *                                                                         *
 *                                                                         *
 ***************************************************************************

 ***************************************************************************
 *   File description                                                      *
 *   -------------------------------------------------------------------   *
 *   Text is a class that provides extended string operations and text     *
 *   related functions.                                                    *
 *   -------------------------------------------------------------------   *
 *                                                                         *
 ***************************************************************************
 
 ***************************************************************************
 *   Change log                                                            *
 *   -------------------------------------------------------------------   *
 *    + [08/04/11] - File created                                          *
 *   -------------------------------------------------------------------   *
 *                                                                         *
 ***************************************************************************

 ***************************************************************************
 *                                                                         *
 *   Emotion is a powerful PHP framework for website generation.           *
 *   -------------------------------------------------------------------   *
 *   Application is owned and copyrighted by ArvYStaTe.net Team, you are   *
 *   only allowed to modify code, not take ownership or in any way claim   *
 *   you are the creator of any thing else but modifications.              *
 *   -------------------------------------------------------------------   *
 *                                                                         *
 ***************************************************************************/

/**
* @ignore
**/

if (!defined ('EMOTION_PAGE'))
{
	die ('<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
			<html><head>
			<title>404 Not Found</title>
			</head><body>
			<h1>Not Found</h1>
			<p>The requested URL /' . $_SERVER['PHP_SELF'] . ' was not found on this server.</p>
			<p>Additionally, a 404 Not Found
			error was encountered while trying to use an ErrorDocument to handle the request.</p>
			</body></html>');
}

/**
 * Text
 *
 * Text and string related operations and enhancements.
 *
 * @package	library
 * @require	false
 * @author	ArvYStaTe.net Team
 **/

class Text
{
	// Block level elements that should not be wrapped inside <p> tags
	private $blockElements = 'address|blockquote|div|dl|fieldset|form|h\d|hr|noscript|object|ol|p|pre|script|table|ul';

	// Elements that should not have <p> and <br /> tags within them.
	private $skipElements	= 'p|pre|ol|ul|dl|object|table|h\d';

	// Tags we want the parser to completely ignore when splitting the string.
	private $inlineElements = 'a|abbr|acronym|b|bdo|big|br|button|cite|code|del|dfn|em|i|img|ins|input|label|map|kbd|q|samp|select|small|span|strong|sub|sup|textarea|tt|var';

	// array of block level elements that require inner content to be within another block level element
	private $innerBlockRequired = array ('blockquote');

	// the last block element parsed
	private $lastBlockElement = '';

	// whether or not to protect quotes within { curly braces }
	private $protectBracedQuotes = false;
	
	/**
	 * Trim Slashes
	 *
	 * Removes any leading/trailing slashes from a string:
	 *
	 * /this/that/theother/
	 *
	 * becomes:
	 *
	 * this/that/theother
	 *
	 * @access	public
	 * @param	string	untrimmed string
	 * @return	string	trimmed string
	 **/
	
	public function TrimSlashes ($str)
	{
		return trim ($str, '/');
	}
	
	/**
	 * Strip Slashes
	 *
	 * Removes slashes contained in a string or in an array
	 *
	 * @access	public
	 * @param	mixed	string or array
	 * @return	mixed	string or array
	 **/
	
	public function StripSlashes ($str)
	{
		if (is_array ($str))
		{
			foreach ($str as $key => $val)
			{
				$str[$key] = StripSlashes ($val);
			}
		}
		else
		{
			$str = stripslashes ($str);
		}

		return $str;
	}
	
	/**
	 * Strip Quotes
	 *
	 * Removes single and double quotes from a string
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 **/
 
	public function StripQuotes ($str)
	{
		return str_replace (array ('"', "'"), '', $str);
	}
	
	/**
	 * Quotes to Entities
	 *
	 * Converts single and double quotes to entities
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 **/
	 
	public function QuotesToEntities($str)
	{
		return str_replace (array ("\'","\"","'",'"'), array ("&#39;","&quot;","&#39;","&quot;"), $str);
	}
	
	/**
	 * Reduce Double Slashes
	 *
	 * Converts double slashes in a string to a single slash,
	 * except those found in http://
	 *
	 * http://www.some-site.com//index.php
	 *
	 * becomes:
	 *
	 * http://www.some-site.com/index.php
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 **/

	public function ReduceDoubleSlashes ($str)
	{
		return preg_replace ("#(^|[^:])//+#", "\\1/", $str);
	}
	
	/**
	 * Reduce Multiples
	 *
	 * Reduces multiple instances of a particular character.  Example:
	 *
	 * Fred, Bill,, Joe, Jimmy
	 *
	 * becomes:
	 *
	 * Fred, Bill, Joe, Jimmy
	 *
	 * @access	public
	 * @param	string	plain text
	 * @param	string	the character you wish to reduce
	 * @param	bool	TRUE/FALSE - whether to trim the character from the beginning/end
	 * @return	string	parsed string
	 **/
	
	public function ReduceMultiples ($str, $character = ',', $trim = false)
	{
		$str = preg_replace ('#' . preg_quote ($character, '#').'{2,}#', $character, $str);

		if ($trim === true)
		{
			$str = trim ($str, $character);
		}

		return $str;
	}

	/**
	 * This function converts text, making it typographically correct:
	 *	- Converts double spaces into paragraphs.
	 *	- Converts single line breaks into <br /> tags
	 *	- Converts single and double quotes into correctly facing curly quote entities.
	 *	- Converts three dots into ellipsis.
	 *	- Converts double dashes into em-dashes.
	 *  - Converts two spaces into entities
	 *
	 * @access	public
	 * @param	string	plain text
	 * @param	bool	whether to reduce more then two consecutive newlines to two
	 * @return	string	typographically correct text
	 **/
	
	public function AutoTypography ($str, $reduceLinebreaks = false)
	{
		if ($str == '')
		{
			return '';
		}

		// Standardize Newlines to make matching easier
		if (strpos ($str, "\r") !== false)
		{
			$str = str_replace (array ("\r\n", "\r"), "\n", $str);
		}

		// Reduce line breaks.  If there are more than two consecutive linebreaks
		// we'll compress them down to a maximum of two since there's no benefit to more.
		if ($reduceLinebreaks === true)
		{
			$str = preg_replace ("/\n\n+/", "\n\n", $str);
		}

		// HTML comment tags don't conform to patterns of normal tags, so pull them out separately, only if needed
		$htmlComments = array();
		
		if (strpos ($str, '<!--') !== FALSE)
		{
			if (preg_match_all ("#(<!\-\-.*?\-\->)#s", $str, $matches))
			{
				for ($i = 0, $total = count ($matches[0]); $i < $total; $i++)
				{
					$htmlComments[] = $matches[0][$i];
					$str = str_replace ($matches[0][$i], '{@HC' . $i . '}', $str);
				}
			}
		}

		// match and yank <pre> tags if they exist.  It's cheaper to do this separately since most content will
		// not contain <pre> tags, and it keeps the PCRE patterns below simpler and faster
		if (strpos ($str, '<pre') !== false)
		{
			$str = preg_replace_callback ("#<pre.*?>.*?</pre>#si", array ($this, 'ProtectCharacters'), $str);
		}

		// Convert quotes within tags to temporary markers.
		$str = preg_replace_callback ("#<.+?>#si", array ($this, 'ProtectCharacters'), $str);

		// Do the same with braces if necessary
		if ($this->protectBracedQuotes === true)
		{
			$str = preg_replace_callback ("#\{.+?\}#si", array ($this, 'ProtectCharacters'), $str);
		}

		// Convert "ignore" tags to temporary marker.  The parser splits out the string at every tag
		// it encounters.  Certain inline tags, like image tags, links, span tags, etc. will be
		// adversely affected if they are split out so we'll convert the opening bracket < temporarily to: {@TAG}
		$str = preg_replace ("#<(/*)("  .$this->inlineElements . ")([ >])#i", "{@TAG}\\1\\2\\3", $str);

		// Split the string at every tag.  This expression creates an array with this prototype:
		//
		//	[array]
		//	{
		//		[0] = <opening tag>
		//		[1] = Content...
		//		[2] = <closing tag>
		//		Etc...
		//	}
		$chunks = preg_split ('/(<(?:[^<>]+(?:"[^"]*"|\'[^\']*\')?)+>)/', $str, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

		// Build our finalized string.  We cycle through the array, skipping tags, and processing the contained text
		$str = '';
		$process = true;
		$paragraph = false;
		$currentChunk = 0;
		$totalChunks = count ($chunks);

		foreach ($chunks as $chunk)
		{
			$currentChunk++;

			// Are we dealing with a tag? If so, we'll skip the processing for this cycle.
			// Well also set the "process" flag which allows us to skip <pre> tags and a few other things.
			if (preg_match ("#<(/*)(" . $this->blockElements . ").*?>#", $chunk, $match))
			{
				if (preg_match ("#" . $this->skipElements . "#", $match[2]))
				{
					$process =  ($match[1] == '/') ? TRUE : FALSE;
				}

				if ($match[1] == '')
				{
					$this->lastBlockElement = $match[2];
				}

				$str .= $chunk;
				continue;
			}

			if ($process == false)
			{
				$str .= $chunk;
				continue;
			}

			//  Force a newline to make sure end tags get processed by _format_newlines()
			if ($currentChunk == $totalChunks)
			{
				$chunk .= "\n";
			}

			//  Convert Newlines into <p> and <br /> tags
			$str .= $this->FormatNewlines ($chunk);
		}

		// No opening block level tag?  Add it if needed.
		if (!preg_match ("/^\s*<(?:" . $this->blockElements . ")/i", $str))
		{
			$str = preg_replace ("/^(.*?)<(" . $this->blockElements . ")/i", '<p>$1</p><$2', $str);
		}

		// Convert quotes, elipsis, em-dashes, non-breaking spaces, and ampersands
		$str = $this->FormatCharacters ($str);

		// restore HTML comments
		for ($i = 0, $total = count ($htmlComments); $i < $total; $i++)
		{
			// remove surrounding paragraph tags, but only if there's an opening paragraph tag
			// otherwise HTML comments at the ends of paragraphs will have the closing tag removed
			// if '<p>{@HC1}' then replace <p>{@HC1}</p> with the comment, else replace only {@HC1} with the comment
			$str = preg_replace ('#(?(?=<p>\{@HC' . $i . '\})<p>\{@HC' . $i . '\}(\s*</p>)|\{@HC' . $i . '\})#s', $htmlComments[$i], $str);
		}

		// Final clean up
		$table = array (

						// If the user submitted their own paragraph tags within the text
						// we will retain them instead of using our tags.
						'/(<p[^>*?]>)<p>/'	=> '$1', // <?php BBEdit syntax coloring bug fix

						// Reduce multiple instances of opening/closing paragraph tags to a single one
						'#(</p>)+#'			=> '</p>',
						'/(<p>\W*<p>)+/'	=> '<p>',

						// Clean up stray paragraph tags that appear before block level elements
						'#<p></p><(' . $this->blockElements . ')#'	=> '<$1',

						// Clean up stray non-breaking spaces preceeding block elements
						'#(&nbsp;\s*)+<(' . $this->blockElements . ')#'	=> '  <$2',

						// Replace the temporary markers we added earlier
						'/\{@TAG\}/'		=> '<',
						'/\{@DQ\}/'			=> '"',
						'/\{@SQ\}/'			=> "'",
						'/\{@DD\}/'			=> '--',
						'/\{@NBS\}/'		=> '  ',

						// An unintended consequence of the _format_newlines function is that
						// some of the newlines get truncated, resulting in <p> tags
						// starting immediately after <block> tags on the same line.
						// This forces a newline after such occurrences, which looks much nicer.
						"/><p>\n/"			=> ">\n<p>",

						// Similarly, there might be cases where a closing </block> will follow
						// a closing </p> tag, so we'll correct it by adding a newline in between
						"#</p></#"			=> "</p>\n</"
						);

		// Do we need to reduce empty lines?
		if ($reduceLinebreaks === true)
		{
			$table['#<p>\n*</p>#'] = '';
		}
		else
		{
			// If we have empty paragraph tags we add a non-breaking space
			// otherwise most browsers won't treat them as true paragraphs
			$table['#<p></p>#'] = '<p>&nbsp;</p>';
		}

		return preg_replace (array_keys ($table), $table, $str);

	}

	/**
	 * This function mainly converts double and single quotes
	 * to curly entities, but it also converts em-dashes,
	 * double spaces, and ampersands
	 *
	 * @access	public
	 * @param	string	plain text
	 * @return	string	formatted text
	 **/
	
	public function FormatCharacters ($str)
	{
		static $table;

		if (!isset($table))
		{
			$table = array(
							// nested smart quotes, opening and closing
							// note that rules for grammar (English) allow only for two levels deep
							// and that single quotes are _supposed_ to always be on the outside
							// but we'll accommodate both
							// Note that in all cases, whitespace is the primary determining factor
							// on which direction to curl, with non-word characters like punctuation
							// being a secondary factor only after whitespace is addressed.
							'/\'"(\s|$)/'					=> '&#8217;&#8221;$1',
							'/(^|\s|<p>)\'"/'				=> '$1&#8216;&#8220;',
							'/\'"(\W)/'						=> '&#8217;&#8221;$1',
							'/(\W)\'"/'						=> '$1&#8216;&#8220;',
							'/"\'(\s|$)/'					=> '&#8221;&#8217;$1',
							'/(^|\s|<p>)"\'/'				=> '$1&#8220;&#8216;',
							'/"\'(\W)/'						=> '&#8221;&#8217;$1',
							'/(\W)"\'/'						=> '$1&#8220;&#8216;',

							// single quote smart quotes
							'/\'(\s|$)/'					=> '&#8217;$1',
							'/(^|\s|<p>)\'/'				=> '$1&#8216;',
							'/\'(\W)/'						=> '&#8217;$1',
							'/(\W)\'/'						=> '$1&#8216;',

							// double quote smart quotes
							'/"(\s|$)/'						=> '&#8221;$1',
							'/(^|\s|<p>)"/'					=> '$1&#8220;',
							'/"(\W)/'						=> '&#8221;$1',
							'/(\W)"/'						=> '$1&#8220;',

							// apostrophes
							"/(\w)'(\w)/"					=> '$1&#8217;$2',

							// Em dash and ellipses dots
							'/\s?\-\-\s?/'					=> '&#8212;',
							'/(\w)\.{3}/'					=> '$1&#8230;',

							// double space after sentences
							'/(\W)  /'						=> '$1&nbsp; ',

							// ampersands, if not a character entity
							'/&(?!#?[a-zA-Z0-9]{2,};)/'		=> '&amp;'
						);
		}

		return preg_replace (array_keys ($table), $table, $str);
	}
	
	/**
	 * Protect Characters
	 *
	 * Protects special characters from being formatted later
	 * We don't want quotes converted within tags so we'll temporarily convert them to {@DQ} and {@SQ}
	 * and we don't want double dashes converted to emdash entities, so they are marked with {@DD}
	 * likewise double spaces are converted to {@NBS} to prevent entity conversion
	 *
	 * @access	public
	 * @param	array	array of matched characters
	 * @return	string	parsed string
	 **/
	
	public function ProtectCharacters ($match)
	{
		return str_replace (array ("'",'"','--','  '), array ('{@SQ}', '{@DQ}', '{@DD}', '{@NBS}'), $match[0]);
	}
	
	/**
	 * Generates random object
	 *
	 * @access	public
	 * @param	string	types: basic, alpha, alnum, numeric, nozero, unique, md5, encrypt, sha1
	 * @param	integer	length of the string
	 * @return	string	randomly generated string
	 **/
	
	public function Random ($type = 'basic', $length = 8)
	{
		switch ($type)
		{
			case 'alnum':
			case 'numeric':
			case 'nozero':
			case 'alpha':
			
				switch ($type)
				{
					case 'alpha':
						$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
						break;
					case 'alnum':
						$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
						break;
					case 'numeric':
						$pool = '0123456789';
						break;
					case 'nozero':
						$pool = '123456789';
						break;
				}

				$str = '';
				
				for ($i = 0; $i < $length; $i++)
				{
					$str .= substr ($pool, mt_rand (0, strlen ($pool) -1), 1);
				}
				
				return $str;
				break;
			case 'unique':
			case 'md5'	:
			
				return md5 (uniqid (mt_rand()));
				break;
			case 'encrypt':
			case 'sha1'	:
				return sha1 (uniqid (mt_rand()));
				break;
			default:
				return mt_rand();
				break;
		}
	}
	
	/**
	 * Generates random string
	 *
	 * @access	public
	 * @param	long	size in bytes
	 * @return	string	smart formated filesize
	 **/
	 
	public function FormatFileSize ($size)
	{
		$units = array(' B', ' kB', ' MB', ' GB', ' TB');
		
		for ($i = 0; ($size >= 1024) && ($i < 4); $i++)
		{
			$size /= 1024;
		}
		
		return round ($size, 2) . $units[$i];
	}
	
	/**
	 * Attempts to convert a string to UTF-8
	 *
	 * @access	public
	 * @param	string	plain text
	 * @param	string	input encoding
	 * @return	string	plain text in UTF-8
	 **/
	 
	public function ConvertToUtf8 ($str, $encoding)
	{
		if (function_exists ('iconv'))
		{
			$str = @iconv ($encoding, 'UTF-8', $str);
		}
		elseif (function_exists ('mb_convert_encoding'))
		{
			$str = @mb_convert_encoding ($str, 'UTF-8', $encoding);
		}
		else
		{
			return false;
		}

		return $str;
	}
	
	/**
	 * Inserts breaks after number of specified characters
	 *
	 * @access	public
	 * @param	string	plain text
	 * @param	integer	number of characters before break
	 * @param	string	string to insert - default <br />
	 * @return	string	formatted text
	 **/
	 
	 public function InsertString ($text, $num, $insert = '<br />')
	 {
		 $newText = '';
		 
		 for ($i = 0; $i < strlen ($text); $i++)
		 {
			 $newText .= $text{$i};
			 
			 if ( (($i + 1) % $num) == 0)
			 {
				 $newText .= $insert;
			 }
		 }
		 
		 return $newText;
	 }
	
	/**
	 * Converts newline characters into either <p> tags or <br />
	 *
	 * @access	public
	 * @param	string	plain text
	 * @return	string	formatted text
	 **/
	 
	public function FormatNewlines ($str)
	{
		if ($str == '')
		{
			return $str;
		}

		if ( (strpos ($str, "\n") === false) && (!in_array ($this->lastBlockElement, $this->innerBlockEequired )) )
		{
			return $str;
		}

		// Convert two consecutive newlines to paragraphs
		$str = str_replace ("\n\n", "</p>\n\n<p>", $str);

		// Convert single spaces to <br /> tags
		$str = preg_replace ("/([^\n])(\n)([^\n])/", "\\1<br />\\2\\3", $str);

		// Wrap the whole enchilada in enclosing paragraphs
		if ($str != "\n")
		{
			// We trim off the right-side new line so that the closing </p> tag
			// will be positioned immediately following the string, matching
			// the behavior of the opening <p> tag
			$str =  '<p>' . rtrim ($str) . '</p>';
		}

		// Remove empty paragraphs if they are on the first line, as this
		// is a potential unintended consequence of the previous code
		$str = preg_replace ("/<p><\/p>(.*)/", "\\1", $str, 1);

		return $str;
	}
	
	/**
	 * Convert newlines to HTML line breaks except within PRE tags
	 *
	 * @access	public
	 * @param	string	plain text
	 * @return	string	converted text
	 **/
	
	public function Nl2brExceptPre ($str)
	{
		$ex = explode ("pre>", $str);
		$ct = count ($ex);

		$newstr = "";
		
		for ($i = 0; $i < $ct; $i++)
		{
			if ( ($i % 2) == 0)
			{
				$newstr .= nl2br ($ex[$i]);
			}
			else
			{
				$newstr .= $ex[$i];
			}

			if ($ct - 1 != $i)
			{
				$newstr .= "pre>";
			}
		}

		return $newstr;
	}
	
	/**
	 * Function returns true if needle occurs on start of the haystack
	 *
	 * @access	public
	 * @param	string	plain text
	 * @param	string	searched string
	 * @param	boolean	case sensitive - default ture
	 * @return	boolean	returns true if string starts with needle
	 **/
	
	public function StartsWith ($haystack, $needle, $case = true)
	{
		if ($case === true)
		{
			return strpos ($haystack, $needle, 0) === 0;
		}
	
		return stripos ($haystack, $needle, 0) === 0;
	}
	
	/**
	 * Function returns true if needle occurs on end of the haystack
	 *
	 * @access	public
	 * @param	string	plain text
	 * @param	string	searched string
	 * @param	boolean	case sensitive - default ture
	 * @return	boolean	returns true if string ends with needle
	 **/
	
	public function EndsWith ($haystack ,$needle, $case = true)
	{
		$expectedPosition = strlen ($haystack) - strlen ($needle);
		
		if ($case === true)
		{
			return strrpos ($haystack, $needle, 0) === $expectedPosition;
		}
		
		return strripos ($haystack, $needle, 0) === $expectedPosition;
	}
}
?>