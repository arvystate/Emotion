<?php
/***************************************************************************
 *   Application          : Emotion                                        *
 *   -------------------------------------------------------------------   *
 *   Package              : @library                                       *
 *   File                 : template.php                                   *
 *   Version              : 1.0.0                                          *
 *   Status               : Complete, Not tested                           *
 *   -------------------------------------------------------------------   *
 *   Begin                : Friday, Apr 12, 2011                           *
 *   CopyRight            : (C) 2011 ArvYStaTe.net Team                    *
 *   E-Mail               : support@arvystate.net                          *
 *   Last edit            : Friday, Apr 12, 2011                           *
 *                                                                         *
 *                                                                         *
 ***************************************************************************

 ***************************************************************************
 *   File description                                                      *
 *   -------------------------------------------------------------------   *
 *   Template is a class that template parsing functionality for basic     *
 *   HTML files or custom template files.                                  *
 *   -------------------------------------------------------------------   *
 *                                                                         *
 ***************************************************************************
 
 ***************************************************************************
 *   Change log                                                            *
 *   -------------------------------------------------------------------   *
 *    + [08/12/11] - File created                                          *
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
 * Template
 *
 *  Extended templating system, increases performance, loosely based on Smarty.
 *
 * @package	library
 * @require	false
 * @author	ArvYStaTe.net Team
 **/

class Template
{
	// Stores template data
	// Format: $fileData['.'][] = filename - root
	// Format: $fileData['customtheme'][] = filename
	protected $fileData = array ();
	
	// Currently selected template, by default we use root template
	protected $selectedTemplate = '.';
	
	protected $templateExtension = '.html';
	
	// Compiler variable
	protected $compiler = null;
	
	protected $templateVars = array ();
	
	// Selects template
	public function SelectTemplate ($template = '.')
	{
		if (isset ($this->fileData[$template]) === true)
		{
			$this->selectedTemplate = $template;
			
			return true;
		}
		else
		{
			if ($this->Load ($template))
			{
				$this->selectedTemplate = $template;
				
				return true;
			}
			else
			{
				return false;
			}
		}
	}
	
	// Loads all views in folder -> actually loads a template
	// Saying view
	public function Load ($template = '.')
	{
		// If we dont have template loaded
		if (!array_key_exists ($template, $this->fileData))
		{
			if ($template == '.')
			{
				$directory = DEFAULT_APPLICATION . '/view';
			}
			else
			{
				$directory = DEFAULT_APPLICATION . '/view/' . $template;
			}
			
			// Test if its a directory
			if (is_dir ($directory) === true)
			{
				// Parse files
				$files = @scandir ($directory);
				$parsed = array ();
				
				foreach ($files as $value)
				{
					// Get extension
					$ext = strrpos ($value, '.');
					
					// Directory check
					if ( (is_dir ($directory . '/' . $value) === false) && ($ext == $this->templateExtension) )
					{
						$parsed[] = $value;
					}
				}
			}
			else
			{
				return false;
			}
		}
		
		return true;
	}
	
	// Displays, returns bool
	public function Display ($view, $data = array (), $return = false)
	{
		// Weak check for template existence
		if (!in_array ($view, $this->fileData[$this->selectedTemplate]))
		{
			return false;
		}
		
		// Displaying compiled handle
		if (in_array ($view, $this->fileData[$this->selectedTemplate]) === true)
		{
			ob_start ();
			
			// Compiler handles cache, compiled file database
			if (isset ($this->compiler) === true)
			{
				$compiledData = $this->compiler->GetFile ($this->selectedTemplate, $view, array_merge ($data, $this->templateVars) );
				
				// Need special
				echo eval (' ?>' . $compiledData . '<?php ');
				
				return true;
			}
			else
			{
				// To make data availible as normal variables
				extract ($data);
				
				if ($this->selectedTemplate == '.')
				{
					$loadFile = DEFAULT_APPLICATION . '/view/' . $view . $this->templateExtension;
				}
				else
				{
					$loadFile = DEFAULT_APPLICATION . '/view/' . $this->selectedTemplate . '/' . $view . $this->templateExtension;
				}
				
				// All variables that are stored here will be taken into consideration later				
				($includeOnce) ? include_once ($loadFile) : include ($loadFile);
			}
			
			$buffer = ob_get_contents ();
			@ob_end_clean ();
			
			// Return the file data if requested
			if ($return === true)
			{
				return $buffer;
			}
			else
			{
				echo ($buffer);
				
				return true;
			}
		}
	}
	
	// Prepares compiler to use
	public function SetupCompiler ($cache)
	{
		if (class_exists ('TemplateCompiler') === true)
		{
			$this->compiler = new TemplateCompiler ();
			
			if ($cache === true)
			{
				$this->compiler->UseCache ();
			}
			
			return true;
		}
		
		return false;
	}
	
	public function CleanVars ()
	{
		$this->templateVars = array ();
	}
	
	
	/**
	* Assign key variable pairs from an array
	* @access public
	*/
	public function AssignVars ($varArray, $varValue = '')
	{
		// Both variables are strings
		if ( ($varValue != '') && (is_array ($varArray) === false) )
		{
			$this->templateVars[$varArray] = $varValue;
		}
		// Both variables are arrays
		else if ( (is_array ($varArray) === true) && (is_array ($varValue) === true) && (count ($varArray) == count ($varValue)) )
		{
			$varCount = count ($varArray);
			
			for ($i = 0; $i < $varCount; $i++)
			{
				$this->templateVars[$varArray[$i]] = $varValue[$i];
			}
		}
		// All data is stored in first variable
		else
		{
			foreach ($varArray as $key => $val)
			{
				$this->templateVars[$key] = $val;
			}
		}
	}
	
	public function Bbcode ($text)
	{
		$text = preg_replace ('#(script|about|applet|activex|chrome):#is', "\\1&#058;", $text);
		
		// Replace html entities
		$text = htmlspecialchars ($text, ENT_QUOTES);
		
		// [list] and [/list] for a list
		$text = str_replace ('[list]', '<ul>', $text);
		$text = str_replace ('[/list]', '</ul>', $text);
		
		// [*] and [/*] for listing
		$text = str_replace ('[*]', '<li>', $text);
		$text = str_replace ('[/*]', '</li>', $text);
		
		// Incase [b] appears more times, we close it, so we do not break site design
		if (substr_count ($text, '[b]') > substr_count ($text, '[/b]') )
		{
			$text = $text . '[/b]';
		}
		
		// [b] and [/b] for bolding text.
		$text = str_replace ('[b]', '<b>', $text);
		$text = str_replace ('[/b]', '</b>', $text);
	
		// Incase [u] appears more times, we close it, so we do not break site design
		if (substr_count ($text, '[u]') > substr_count ($text, '[/u]') )
		{
			$text = $text . '[/u]';
		}
	
		// [u] and [/u] for underlining text.
		$text = str_replace ('[u]', '<span style="text-decoration: underline">', $text);
		$text = str_replace ('[/u]', '</span>', $text);
		
		// Incase [i] appears more times, we close it, so we do not break site design
		if (substr_count ($text, '[i]') > substr_count ($text, '[/i]') )
		{
			$text = $text . '[/i]';
		}
	
		// [i] and [/i] for italicizing text.
		$text = str_replace ('[i]', '<i>', $text);
		$text = str_replace ('[/i]', '</i>', $text);
	
		// [align=??]and [/align] for aligning text.
		$text = preg_replace ('#\[align=(left|center|right)\](.*?)\[\/align\]#', '<div align=\"\\1\">\\2</div>', $text);
	
		// [color=#XXXXX] and [/color] for colourizing text.
		$text = preg_replace ('@\[color=(#[0-9A-F]{6}|[a-z\-]+)\](.*?)\[/color\]@si', '<span style="color: \\1">\\2</span>', $text);
	
		// [size=X] and [/size] for resizing text.
		$text = preg_replace ('#\[size=([\-\+]?[1-2]?[0-9])\](.*?)\[/size\]#si', '<span style="font-size: \\1px; line-height: normal;">\\2</span>', $text);
	
		// [img] and [/img] for images replacing text.
		$text = preg_replace ('#\[img\]([^?].*?)\[/img\]#si', '<img src="\\1" alt="" />', $text);
		$text = preg_replace ('#\[img width=([0-9]?[0-9]?[0-9]) height=([0-9]?[0-9]?[0-9])\](.*?)\[/img\]#si', '<img src="\\3" width="\\1px" height="\\2px" alt="" />', $text);
		
		// [url] and [/url] tags & [url=] and [/url] tags.
		$text = preg_replace ('#\[url\]([\w]+?://([\w\#$%&~/.\-;:=,?@\]+]|\[(?!url=))*?)\[/url\]#is', '<a href="\\1">\\1</a>', $text);
		$text = preg_replace ('#\[url\]((www|ftp)\.([\w\#$%&~/.\-;:=,?@\]+]|\[(?!url=))*?)\[/url\]#is', '<a href="http://\\1">\\1</a>', $text);
		$text = preg_replace ('#\[url=([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*?)\]([^?\n\r\t].*?)\[/url\]#is', '<a href="\\1">\\2</a>', $text);
		$text = preg_replace ('#\[url=((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*?)\]([^?\n\r\t].*?)\[/url\]#is', '<a href="http://\\1">\\3</a>', $text);
		
		// [outurl] and [/outurl] tags & [outurl=] and [/outurl] tags.
		$text = preg_replace ('#\[outurl\]([\w]+?://([\w\#$%&~/.\-;:=,?@\]+]|\[(?!outurl=))*?)\[/outurl\]#is', '<a href="\\1" target=\"_blank\">\\1</a>', $text);
		$text = preg_replace ('#\[outurl\]((www|ftp)\.([\w\#$%&~/.\-;:=,?@\]+]|\[(?!outurl=))*?)\[/outurl\]#is', '<a href="http://\\1" target=\"_blank\">\\1</a>', $text);
		$text = preg_replace ('#\[outurl=([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*?)\]([^?\n\r\t].*?)\[/outurl\]#is', '<a href="\\1" target=\"_blank\">\\2</a>', $text);
		$text = preg_replace ('#\[outurl=((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*?)\]([^?\n\r\t].*?)\[/outurl\]#is', '<a href="http://\\1" target=\"_blank\">\\3</a>', $text);
		
		// [email] and [/email] tags
		$text = preg_replace ('#\[email\]([a-z0-9&\-_.]+?@[\w\-]+\.([\w\-\.]+\.)?[\w]{2,4})\[/email\]#si', '<a href="mailto:\\1">\\1</a>', $text);
		$text = preg_replace ('#\[email=([a-z0-9&\-_.]+?@[\w\-]+\.([\w\-\.]+\.)?[\w]{2,4})\]([^?\n\r\t].*?)\[/email\]#si', '<a href="mailto:\\1">\\3</a>', $text);
	
		// self standing urls and emails
		$text = preg_replace ("#(^|[\n ])([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<a href=\"\\2\" target=\"_blank\">\\2</a>", $text);
		$text = preg_replace ("#(^|[\n ])((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*)#is", "\\1<a href=\"http://\\2\" target=\"_blank\">\\2</a>", $text);
		$text = preg_replace ("#(^|[\n ])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>", $text);
	
		// table replacing
		$text = preg_replace ('#\[table(.*?)\](.*?)\[\/table\]#', '<table\\1><tbody>\\2</tbody></table>', $text);
		$text = preg_replace ('#\[row(.*?)\](.*?)\[\/row\]#', '<tr\\1>\\2</tr>', $text);
		$text = preg_replace ('#\[col(.*?)\](.*?)\[\/col\]#', '<td\\1>\\2</td>', $text);
	
		// Replace 2 spaces with "&nbsp; " so non-tabbed code indents without making huge long lines.
		$text = str_replace ("  ", "&nbsp; ", $text);
		// Now Replace 2 spaces with " &nbsp;" to catch odd #s of spaces.
		$text = str_replace ("  ", " &nbsp;", $text);
		// Replace tabs with "&nbsp; &nbsp;" so tabbed code indents sorta right without making huge long lines.
		$text = str_replace ("\t", "&nbsp; &nbsp;", $text);
		// Now replace space occurring at the beginning of a line.
		$text = preg_replace ("/^ {1}/m", "&nbsp;", $text);
		
		// Now replace the break like with html character
		$text = str_replace ("\n", "<br />", $text);
		
		// Get rid of slashes and console characters
		$text = stripslashes ($text);
		$text = trim ($text);
		
		return $text;
	}
}

class TemplateCompiler
{
	private $cacheExtension = '.tpl';
	private $templateExtension = '.html';
	
	// Filebase format in compiler: $fileBase[$template][$file][$compiledData]
	
	private $fileBase = array ();
	private $cache = false;
	
	private $block_names = array();
	private $block_else_level = array();
	
	// Returns filedata
	public function GetFile ($template, $view, $data = array ())
	{
		// Attempt to load file from template dir
		if (isset ($this->fileBase[$template][$view]) === false)
		{
			if ($template == '.')
			{
				$loadFile = DEFAULT_APPLICATION . '/view/' . $view . $this->templateExtension;
			}
			else
			{
				$loadFile = DEFAULT_APPLICATION . '/view/' . $template . '/' . $view . $this->templateExtension;
			}
			
			if (file_exists ($loadFile) === true)
			{
				$this->fileBase[$template][$view] = file_get_contents ($loadFile);
				$this->fileBase[$template][$view] = $this->Compile ($this->fileBase[$template][$view], $template);
				
				// Save into cache if we are using it
				if ($this->cache === true)
				{
					if ($template == '.')
					{
						$template = 'root';
					}
					
					file_put_contents ('cache/' . $template . '_' . $view . $this->cacheExtension, $this->fileBase[$template][$view]);
				}
				
				return true;
			}
			else
			{
				return false;
			}
		}
		
		$compiledData = $this->fileBase[$template][$view];
						
		// Replace all vars in data array
		foreach ($data as $key => $value)
		{
			if (is_string ($value) === true)
			{
				$compiledData = str_replace ('{EE_' . strtoupper ($key) . '}', $value, $compiledData);
			}
		}
		
		return $compiledData;
	}
	
	// Deletes cached file
	public function ResetFile ($template, $view)
	{
		if (isset ($this->fileBase[$template][$view]) && ($this->cache === true) )
		{
			if ($template == '.')
			{
				$template = 'root';
			}
			
			$fileReset = 'cache/' . $template . '_' . $view . $this->cacheExtension;
			
			if (file_exists ($fileReset) === true)
			{
				@unlink ($fileReset);
			}
			
			unset ($this->fileBase[$template][$view]);
			
			$this->GetFile ($template, $view);
			
			return true;
		}
		else if (isset ($this->fileBase[$template][$view]))
		{
			unset ($this->fileBase[$template][$view]);
			$this->GetFile ($template, $view);
			
			return true;
		}
		
		return false;
	}
	
	// Setup cache
	public function UseCache ()
	{
		if ($this->cache === false)
		{
			// Check if cache dir exists
			if (is_dir ('cache') === true)
			{
				$files = @scandir ('cache');
				
				// Loop through files and load them
				foreach ($files as $value)
				{
					$extension = strrpos ($value, '.');
					
					$value = substr ($value, strrpos ($value, '.'));
					
					// It is a cached view!
					if ($extension == $this->cacheExtension)
					{
						$parse = explode ('_', $value);
						
						if (count ($parse) >= 2)
						{
							if ($parse[0] == 'root')
							{
								$template = '.';
							}
							else
							{
								$template = $parse[0];
							}
							
							$filename = implode ('_', array_slice ($parse, 1) );
							
							$this->fileBase[$template][$filename] = file_get_contents ('cache/' . $value . $this->cacheExtension);
						}						
					}
				}
			}
		}
	}
	
	/**
	* The all seeing all doing compile method. Parts are inspired by or directly from Smarty
	* @access public
	*/
	private function Compile ($code, $template)
	{
		$this->_removePhpTags ($code);

		// Pull out all block/statement level elements and separate plain text
		preg_match_all ('#<!-- PHP -->(.*?)<!-- ENDPHP -->#s', $code, $matches);
		$php_blocks = $matches[1];
		$code = preg_replace ('#<!-- PHP -->.*?<!-- ENDPHP -->#s', '<!-- PHP -->', $code);

		preg_match_all ('#<!-- INCLUDE ([a-zA-Z0-9\_\-\+\./]+) -->#', $code, $matches);
		$include_blocks = $matches[1];
		$code = preg_replace ('#<!-- INCLUDE [a-zA-Z0-9\_\-\+\./]+ -->#', '<!-- INCLUDE -->', $code);

		preg_match_all ('#<!-- INCLUDEPHP ([a-zA-Z0-9\_\-\+\./]+) -->#', $code, $matches);
		$includephp_blocks = $matches[1];
		$code = preg_replace ('#<!-- INCLUDEPHP [a-zA-Z0-9\_\-\+\./]+ -->#', '<!-- INCLUDEPHP -->', $code);

		preg_match_all ('#<!-- ([^<].*?) (.*?)? ?-->#', $code, $blocks, PREG_SET_ORDER);

		$text_blocks = preg_split ('#<!-- [^<].*? (?:.*?)? ?-->#', $code);

		for ($i = 0, $j = sizeof ($text_blocks); $i < $j; $i++)
		{
			$this->_compileVarTags ($text_blocks[$i]);
		}
		
		$compile_blocks = array ();

		for ($curr_tb = 0, $tb_size = sizeof ($blocks); $curr_tb < $tb_size; $curr_tb++)
		{
			$block_val = &$blocks[$curr_tb];

			switch ($block_val[1])
			{
				case 'BEGIN':
					$this->block_else_level[] = false;
					$compile_blocks[] = '<?php ' . $this->_compileTagBlock ($block_val[2]) . ' ?>';
				break;

				case 'BEGINELSE':
					$this->block_else_level[sizeof ($this->block_else_level) - 1] = true;
					$compile_blocks[] = '<?php }} else { ?>';
				break;

				case 'END':
					array_pop($this->block_names);
					$compile_blocks[] = '<?php ' . ((array_pop($this->block_else_level)) ? '}' : '}}') . ' ?>';
				break;

				case 'IF':
					$compile_blocks[] = '<?php ' . $this->_compileTagIf ($block_val[2], false) . ' ?>';
				break;

				case 'ELSE':
					$compile_blocks[] = '<?php } else { ?>';
				break;

				case 'ELSEIF':
					$compile_blocks[] = '<?php ' . $this->_compileTagIf ($block_val[2], true) . ' ?>';
				break;

				case 'ENDIF':
					$compile_blocks[] = '<?php } ?>';
				break;

				case 'DEFINE':
					$compile_blocks[] = '<?php ' . $this->_compileTagDefine ($block_val[2], true) . ' ?>';
				break;

				case 'UNDEFINE':
					$compile_blocks[] = '<?php ' . $this->_compileTagDefine ($block_val[2], false) . ' ?>';
				break;

				case 'INCLUDE':
					$temp = array_shift ($include_blocks);

					$file = $temp;

					$compile_blocks[] = '<?php ' . $this->_compileTagInclude ($temp) . ' ?>';

					// No point in checking variable includes
					if ($file)
					{
						if ($template == '.')
						{
							$loadFile = DEFAULT_APPLICATION . '/view/' . $file . $this->templateExtension;
						}
						else
						{
							$loadFile = DEFAULT_APPLICATION . '/view/' . $template . '/' . $file . $this->templateExtension;
						}

						if (file_exists ($loadFile) === true)
						{
							$this->fileBase[$template][$file] = file_get_contents ($loadFile);
							$this->fileBase[$template][$file] = $this->Compile ($this->fileBase[$template][$file], $template);
							
							// Save into cache if we are using it
							if ($this->cache === true)
							{
								if ($template == '.')
								{
									$template = 'root';
								}
								
								file_put_contents ('cache/' . $template . '_' . $file . $this->cacheExtension, $this->fileBase[$template][$view]);
							}
						}
					}
				break;

				case 'INCLUDEPHP':
					$compile_blocks[] = ($config['tpl_allow_php']) ? '<?php ' . $this->_compileTagIncludePhp (array_shift ($includephp_blocks)) . ' ?>' : '';
				break;

				case 'PHP':
					$compile_blocks[] = ($config['tpl_allow_php']) ? '<?php ' . array_shift($php_blocks) . ' ?>' : '';
				break;

				default:
					$this->_compileVarTags ($block_val[0]);
					$trim_check = trim($block_val[0]);
					$compile_blocks[] = (!$no_echo) ? ((!empty($trim_check)) ? $block_val[0] : '') : ((!empty($trim_check)) ? $block_val[0] : '');
				break;
			}
		}

		$template_php = '';
		
		for ($i = 0, $size = sizeof($text_blocks); $i < $size; $i++)
		{
			$trim_check_text = trim($text_blocks[$i]);
			$template_php .= (!$no_echo) ? (($trim_check_text != '') ? $text_blocks[$i] : '') . ((isset($compile_blocks[$i])) ? $compile_blocks[$i] : '') : (($trim_check_text != '') ? $text_blocks[$i] : '') . ((isset($compile_blocks[$i])) ? $compile_blocks[$i] : '');
		}

		// There will be a number of occasions where we switch into and out of
		// PHP mode instantaneously. Rather than "burden" the parser with this
		// we'll strip out such occurences, minimising such switching
		$template_php = str_replace(' ?><?php ', ' ', $template_php);

		return $template_php;
	}

	/**
	* Compile variables
	* @access private
	*/
	private function _compileVarTags (&$text_blocks)
	{
		// change template varrefs into PHP varrefs
		$varrefs = array ();

		// This one will handle varrefs WITH namespaces
		preg_match_all ('#\{((?:[a-z0-9\-_]+\.)+)(\$)?([A-Z0-9\-_]+)\}#', $text_blocks, $varrefs, PREG_SET_ORDER);

		foreach ($varrefs as $var_val)
		{
			$namespace = $var_val[1];
			$varname = $var_val[3];
			$new = $this->_generateBlockVarref ($namespace, $varname, true, $var_val[2]);

			$text_blocks = str_replace($var_val[0], $new, $text_blocks);
		}

		// This will handle the remaining root-level varrefs
		// transform vars prefixed by L_ into their language variable pendant if nothing is set within the tpldata array
		if (strpos($text_blocks, '{L_') !== false)
		{
			$text_blocks = preg_replace('#\{L_([a-z0-9\-_]*)\}#is', "<?php echo ((isset(\$this->_rootref['L_\\1'])) ? \$this->_rootref['L_\\1'] : ((isset(\$user->lang['\\1'])) ? \$user->lang['\\1'] : '{ \\1 }')); ?>", $text_blocks);
		}

		// Handle addslashed language variables prefixed with LA_
		// If a template variable already exist, it will be used in favor of it...
		if (strpos($text_blocks, '{LA_') !== false)
		{
			$text_blocks = preg_replace('#\{LA_([a-z0-9\-_]*)\}#is', "<?php echo ((isset(\$this->_rootref['LA_\\1'])) ? \$this->_rootref['LA_\\1'] : ((isset(\$this->_rootref['L_\\1'])) ? addslashes(\$this->_rootref['L_\\1']) : ((isset(\$user->lang['\\1'])) ? addslashes(\$user->lang['\\1']) : '{ \\1 }'))); ?>", $text_blocks);
		}

		// Handle remaining varrefs
		$text_blocks = preg_replace('#\{([a-z0-9\-_]+)\}#is', "<?php echo (isset(\$this->_rootref['\\1'])) ? \$this->_rootref['\\1'] : ''; ?>", $text_blocks);
		$text_blocks = preg_replace('#\{\$([a-z0-9\-_]+)\}#is', "<?php echo (isset(\$this->_tpldata['DEFINE']['.']['\\1'])) ? \$this->_tpldata['DEFINE']['.']['\\1'] : ''; ?>", $text_blocks);

		return;
	}

	/**
	* Compile blocks
	* @access private
	*/
	private function _compileTagBlock ($tag_args)
	{
		$no_nesting = false;

		// Is the designer wanting to call another loop in a loop?
		if (strpos($tag_args, '!') === 0)
		{
			// Count the number if ! occurrences (not allowed in vars)
			$no_nesting = substr_count($tag_args, '!');
			$tag_args = substr($tag_args, $no_nesting);
		}

		// Allow for control of looping (indexes start from zero):
		// foo(2)    : Will start the loop on the 3rd entry
		// foo(-2)   : Will start the loop two entries from the end
		// foo(3,4)  : Will start the loop on the fourth entry and end it on the fifth
		// foo(3,-4) : Will start the loop on the fourth entry and end it four from last
		if (preg_match('#^([^()]*)\(([\-\d]+)(?:,([\-\d]+))?\)$#', $tag_args, $match))
		{
			$tag_args = $match[1];

			if ($match[2] < 0)
			{
				$loop_start = '($_' . $tag_args . '_count ' . $match[2] . ' < 0 ? 0 : $_' . $tag_args . '_count ' . $match[2] . ')';
			}
			else
			{
				$loop_start = '($_' . $tag_args . '_count < ' . $match[2] . ' ? $_' . $tag_args . '_count : ' . $match[2] . ')';
			}

			if (strlen($match[3]) < 1 || $match[3] == -1)
			{
				$loop_end = '$_' . $tag_args . '_count';
			}
			else if ($match[3] >= 0)
			{
				$loop_end = '(' . ($match[3] + 1) . ' > $_' . $tag_args . '_count ? $_' . $tag_args . '_count : ' . ($match[3] + 1) . ')';
			}
			else //if ($match[3] < -1)
			{
				$loop_end = '$_' . $tag_args . '_count' . ($match[3] + 1);
			}
		}
		else
		{
			$loop_start = 0;
			$loop_end = '$_' . $tag_args . '_count';
		}

		$tag_template_php = '';
		array_push($this->block_names, $tag_args);

		if ($no_nesting !== false)
		{
			// We need to implode $no_nesting times from the end...
			$block = array_slice($this->block_names, -$no_nesting);
		}
		else
		{
			$block = $this->block_names;
		}

		if (sizeof($block) < 2)
		{
			// Block is not nested.
			$tag_template_php = '$_' . $tag_args . "_count = (isset(\$this->_tpldata['$tag_args'])) ? sizeof(\$this->_tpldata['$tag_args']) : 0;";
			$varref = "\$this->_tpldata['$tag_args']";
		}
		else
		{
			// This block is nested.
			// Generate a namespace string for this block.
			$namespace = implode('.', $block);

			// Get a reference to the data array for this block that depends on the
			// current indices of all parent blocks.
			$varref = $this->_generateBlockDataRef ($namespace, false);

			// Create the for loop code to iterate over this block.
			$tag_template_php = '$_' . $tag_args . '_count = (isset(' . $varref . ')) ? sizeof(' . $varref . ') : 0;';
		}

		$tag_template_php .= 'if ($_' . $tag_args . '_count) {';

		/**
		* The following uses foreach for iteration instead of a for loop, foreach is faster but requires PHP to make a copy of the contents of the array which uses more memory
		* <code>
		*	if (!$offset)
		*	{
		*		$tag_template_php .= 'foreach (' . $varref . ' as $_' . $tag_args . '_i => $_' . $tag_args . '_val){';
		*	}
		* </code>
		*/

		$tag_template_php .= 'for ($_' . $tag_args . '_i = ' . $loop_start . '; $_' . $tag_args . '_i < ' . $loop_end . '; ++$_' . $tag_args . '_i){';
		$tag_template_php .= '$_'. $tag_args . '_val = &' . $varref . '[$_'. $tag_args. '_i];';

		return $tag_template_php;
	}

	/**
	* Compile IF tags - much of this is from Smarty with
	* some adaptions for our block level methods
	* @access private
	*/
	private function _compileTagIf ($tag_args, $elseif)
	{
		// Tokenize args for 'if' tag.
		preg_match_all('/(?:
			"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"         |
			\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'     |
			[(),]                                  |
			[^\s(),]+)/x', $tag_args, $match);

		$tokens = $match[0];
		$is_arg_stack = array();

		for ($i = 0, $size = sizeof($tokens); $i < $size; $i++)
		{
			$token = &$tokens[$i];

			switch ($token)
			{
				case '!==':
				case '===':
				case '<<':
				case '>>':
				case '|':
				case '^':
				case '&':
				case '~':
				case ')':
				case ',':
				case '+':
				case '-':
				case '*':
				case '/':
				case '@':
				break;

				case '==':
				case 'eq':
					$token = '==';
				break;

				case '!=':
				case '<>':
				case 'ne':
				case 'neq':
					$token = '!=';
				break;

				case '<':
				case 'lt':
					$token = '<';
				break;

				case '<=':
				case 'le':
				case 'lte':
					$token = '<=';
				break;

				case '>':
				case 'gt':
					$token = '>';
				break;

				case '>=':
				case 'ge':
				case 'gte':
					$token = '>=';
				break;

				case '&&':
				case 'and':
					$token = '&&';
				break;

				case '||':
				case 'or':
					$token = '||';
				break;

				case '!':
				case 'not':
					$token = '!';
				break;

				case '%':
				case 'mod':
					$token = '%';
				break;

				case '(':
					array_push($is_arg_stack, $i);
				break;

				case 'is':
					$is_arg_start = ($tokens[$i-1] == ')') ? array_pop($is_arg_stack) : $i-1;
					$is_arg	= implode('	', array_slice($tokens,	$is_arg_start, $i -	$is_arg_start));

					$new_tokens	= $this->_parseIsExpr ($is_arg, array_slice($tokens, $i+1));

					array_splice($tokens, $is_arg_start, sizeof($tokens), $new_tokens);

					$i = $is_arg_start;

				// no break

				default:
					if (preg_match('#^((?:[a-z0-9\-_]+\.)+)?(\$)?(?=[A-Z])([A-Z0-9\-_]+)#s', $token, $varrefs))
					{
						$token = (!empty($varrefs[1])) ? $this->generate_block_data_ref(substr($varrefs[1], 0, -1), true, $varrefs[2]) . '[\'' . $varrefs[3] . '\']' : (($varrefs[2]) ? '$this->_tpldata[\'DEFINE\'][\'.\'][\'' . $varrefs[3] . '\']' : '$this->_rootref[\'' . $varrefs[3] . '\']');
					}
					else if (preg_match('#^\.((?:[a-z0-9\-_]+\.?)+)$#s', $token, $varrefs))
					{
						// Allow checking if loops are set with .loopname
						// It is also possible to check the loop count by doing <!-- IF .loopname > 1 --> for example
						$blocks = explode('.', $varrefs[1]);

						// If the block is nested, we have a reference that we can grab.
						// If the block is not nested, we just go and grab the block from _tpldata
						if (sizeof($blocks) > 1)
						{
							$block = array_pop($blocks);
							$namespace = implode('.', $blocks);
							$varref = $this->generate_block_data_ref($namespace, true);

							// Add the block reference for the last child.
							$varref .= "['" . $block . "']";
						}
						else
						{
							$varref = '$this->_tpldata';

							// Add the block reference for the last child.
							$varref .= "['" . $blocks[0] . "']";
						}
						$token = "sizeof($varref)";
					}
					else if (!empty($token))
					{
						$token = '(' . $token . ')';
					}

				break;
			}
		}

		// If there are no valid tokens left or only control/compare characters left, we do skip this statement
		if (!sizeof($tokens) || str_replace(array(' ', '=', '!', '<', '>', '&', '|', '%', '(', ')'), '', implode('', $tokens)) == '')
		{
			$tokens = array('false');
		}
		return (($elseif) ? '} else if (' : 'if (') . (implode(' ', $tokens) . ') { ');
	}

	/**
	* Compile DEFINE tags
	* @access private
	*/
	private function compile_tag_define ($tag_args, $op)
	{
		preg_match('#^((?:[a-z0-9\-_]+\.)+)?\$(?=[A-Z])([A-Z0-9_\-]*)(?: = (\'?)([^\']*)(\'?))?$#', $tag_args, $match);

		if (empty($match[2]) || (!isset($match[4]) && $op))
		{
			return '';
		}

		if (!$op)
		{
			return 'unset(' . (($match[1]) ? $this->generate_block_data_ref(substr($match[1], 0, -1), true, true) . '[\'' . $match[2] . '\']' : '$this->_tpldata[\'DEFINE\'][\'.\'][\'' . $match[2] . '\']') . ');';
		}

		// Are we a string?
		if ($match[3] && $match[5])
		{
			$match[4] = str_replace(array('\\\'', '\\\\', '\''), array('\'', '\\', '\\\''), $match[4]);

			// Compile reference, we allow template variables in defines...
			$match[4] = $this->compile($match[4]);

			// Now replace the php code
			$match[4] = "'" . str_replace(array('<?php echo ', '; ?>'), array("' . ", " . '"), $match[4]) . "'";
		}
		else
		{
			preg_match('#true|false|\.#i', $match[4], $type);

			switch (strtolower($type[0]))
			{
				case 'true':
				case 'false':
					$match[4] = strtoupper($match[4]);
				break;

				case '.':
					$match[4] = doubleval($match[4]);
				break;

				default:
					$match[4] = intval($match[4]);
				break;
			}
		}

		return (($match[1]) ? $this->generate_block_data_ref(substr($match[1], 0, -1), true, true) . '[\'' . $match[2] . '\']' : '$this->_tpldata[\'DEFINE\'][\'.\'][\'' . $match[2] . '\']') . ' = ' . $match[4] . ';';
	}

	/**
	* Compile INCLUDE tag
	* @access private
	*/
	private function compile_tag_include ($tag_args)
	{
		return "\$this->_tpl_include('$tag_args');";
	}

	/**
	* Compile INCLUDE_PHP tag
	* @access private
	*/
	private function compile_tag_include_php ($tag_args)
	{
		return "include('" . $tag_args . "');";
	}

	/**
	* parse expression
	* This is from Smarty
	* @access private
	*/
	private function _parse_is_expr ($is_arg, $tokens)
	{
		$expr_end = 0;
		$negate_expr = false;

		if (($first_token = array_shift($tokens)) == 'not')
		{
			$negate_expr = true;
			$expr_type = array_shift($tokens);
		}
		else
		{
			$expr_type = $first_token;
		}

		switch ($expr_type)
		{
			case 'even':
				if (@$tokens[$expr_end] == 'by')
				{
					$expr_end++;
					$expr_arg = $tokens[$expr_end++];
					$expr = "!(($is_arg / $expr_arg) % $expr_arg)";
				}
				else
				{
					$expr = "!($is_arg & 1)";
				}
			break;

			case 'odd':
				if (@$tokens[$expr_end] == 'by')
				{
					$expr_end++;
					$expr_arg = $tokens[$expr_end++];
					$expr = "(($is_arg / $expr_arg) % $expr_arg)";
				}
				else
				{
					$expr = "($is_arg & 1)";
				}
			break;

			case 'div':
				if (@$tokens[$expr_end] == 'by')
				{
					$expr_end++;
					$expr_arg = $tokens[$expr_end++];
					$expr = "!($is_arg % $expr_arg)";
				}
			break;
		}

		if ($negate_expr)
		{
			$expr = "!($expr)";
		}

		array_splice($tokens, 0, $expr_end, $expr);

		return $tokens;
	}

	/**
	* Generates a reference to the given variable inside the given (possibly nested)
	* block namespace. This is a string of the form:
	* ' . $this->_tpldata['parent'][$_parent_i]['$child1'][$_child1_i]['$child2'][$_child2_i]...['varname'] . '
	* It's ready to be inserted into an "echo" line in one of the templates.
	* NOTE: expects a trailing "." on the namespace.
	* @access private
	*/
	private function generate_block_varref ($namespace, $varname, $echo = true, $defop = false)
	{
		// Strip the trailing period.
		$namespace = substr($namespace, 0, -1);

		// Get a reference to the data block for this namespace.
		$varref = $this->generate_block_data_ref($namespace, true, $defop);
		// Prepend the necessary code to stick this in an echo line.

		// Append the variable reference.
		$varref .= "['$varname']";
		$varref = ($echo) ? "<?php echo $varref; ?>" : ((isset($varref)) ? $varref : '');

		return $varref;
	}

	/**
	* Generates a reference to the array of data values for the given
	* (possibly nested) block namespace. This is a string of the form:
	* $this->_tpldata['parent'][$_parent_i]['$child1'][$_child1_i]['$child2'][$_child2_i]...['$childN']
	*
	* If $include_last_iterator is true, then [$_childN_i] will be appended to the form shown above.
	* NOTE: does not expect a trailing "." on the blockname.
	* @access private
	*/
	private function generate_block_data_ref ($blockname, $include_last_iterator, $defop = false)
	{
		// Get an array of the blocks involved.
		$blocks = explode('.', $blockname);
		$blockcount = sizeof($blocks) - 1;

		// DEFINE is not an element of any referenced variable, we must use _tpldata to access it
		if ($defop)
		{
			$varref = '$this->_tpldata[\'DEFINE\']';
			// Build up the string with everything but the last child.
			for ($i = 0; $i < $blockcount; $i++)
			{
				$varref .= "['" . $blocks[$i] . "'][\$_" . $blocks[$i] . '_i]';
			}
			// Add the block reference for the last child.
			$varref .= "['" . $blocks[$blockcount] . "']";
			// Add the iterator for the last child if requried.
			if ($include_last_iterator)
			{
				$varref .= '[$_' . $blocks[$blockcount] . '_i]';
			}
			return $varref;
		}
		else if ($include_last_iterator)
		{
			return '$_'. $blocks[$blockcount] . '_val';
		}
		else
		{
			return '$_'. $blocks[$blockcount - 1] . '_val[\''. $blocks[$blockcount]. '\']';
		}
	}
	
	private function _removePhpTags (&$code)
	{
		// This matches the information gathered from the internal PHP lexer
		$match = array
		(
			'#<([\?%])=?.*?\1>#s',
			'#<script\s+language\s*=\s*(["\']?)php\1\s*>.*?</script\s*>#s',
			'#<\?php(?:\r\n?|[ \n\t]).*?\?>#s'
		);

		$code = preg_replace($match, '', $code);
	}
}
?>