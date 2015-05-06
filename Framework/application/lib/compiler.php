<?php

class EmotionCompiler
{
	private $parsedData = array ();

	private $fileList = array ();

	private $databaseCount = 0;

	private $compileClasses = array ();

	private $tempFiles = array ();

	private $directories = array ();

	private $archive;

	//
	// Main function, this should be called on entrance -> data is string stored in database
	//

	public function CreateFile ($filename, $data)
	{
		if (file_exists ('temp/' . $filename) === true)
		{
			unlink ('temp/' . $filename);
		}

		// Quite a procedure, huh, measuring time to get performance

		$mtime = microtime (true);

		// Prepares data from data string
		$this->_prepareData ($this->_parseRawData ($data) );

		// Gets the list of libraries and databases
		$this->_getFileList ();

		// Creates customizable files -> loader and configuration
		$this->_createCustomFiles ();

		// Creates main emotion file
		$this->_createSystemFile ();

		// Does post compile checks
		$this->_prepareFiles ();

		// Stores files to archive
		$this->_writeFiles ($filename);

		/*

		echo ('Time spent: ' . (microtime (true) - $mtime) . ' ms - memory usage: ' . number_format (memory_get_usage ()) . ' bytes<br /><br />');


		if (file_exists ('temp/' . $filename) === true)
		{
			echo ('File: <a href="/temp/' . $filename . '">' . $filename . '</a><br />Filesize: ' . number_format (filesize ('temp/' . $filename) ) );
		}
		else
		{
			echo ('Error creating archive. Log:');
			echo ('<pre>');

			EmotionEngine::GetInstance()->Load('Debug')->Display ();

			print_r ($this->parsedData);

			echo ('</pre>');
		}*/
	}

	//
	// Main supporting functions
	//

	private function _cleanComments ($code)
	{
		$cleaned = '';

		$commentTokens = array (T_COMMENT);

		if (defined ('T_DOC_COMMENT'))
		{
			$commentTokens[] = T_DOC_COMMENT;
		}
		if (defined ('T_ML_COMMENT'))
		{
			$commentTokens[] = T_ML_COMMENT;
		}

		$trim = false;

		foreach (token_get_all ($code) as $token)
		{
			if (is_array ($token))
			{
				if (in_array($token[0], $commentTokens))
				{
					$trim = true;
					continue;
				}

				$token = $token[1];
			}

			if ($trim === true)
			{
				$trim = false;
				$token = ltrim ($token);
			}
			$cleaned .= $token;
		}

		return $cleaned;
	}

	private function _cleanDebug ($code)
	{
		// Removing debug lines from text, we dont care if we're commented......

		// Searching for debug command
		while ( ($debug = strpos ($code, "('Debug')") ) !== false)
		{
			// Part code in two by found debug. (could be done with explode, but I assume substr is faster)
			$codeStart = substr ($code, 0, $debug);
			$codeEnd = substr ($code, $debug + 9);

			// Found it. Now remove the load function call.
			$codeStart = substr ($codeStart, 0, strrpos ($codeStart, '$this->Load'));
			// Remove right side extra space
			$codeStart = rtrim ($codeStart);

			// First part is now sorted. Take care of second part, a much more complicated solution.. Find the first function call bracket.
			$codeEnd = substr ($codeEnd, strpos ($codeEnd, '(') + 1);

			// After this point, we find next ) bracket on same block. -> the Debug stuff could be in an echo,
			// so there is no other way to find it.
			$bracketLevel = 1;
			$endBracket = 0;

			$inQuote = false;
			$doubleQuote = false;

			for ($i = 0; $i < strlen ($codeEnd); $i++)
			{
				if ($inQuote === false)
				{
					if ($codeEnd{$i} == '(')
					{
						$bracketLevel++;
					}
					else if ($codeEnd{$i} == ')')
					{
						$bracketLevel--;

						$endBracket = $i;
					}
					else if ($codeEnd{$i} == '"')
					{
						$doubleQuote = true;
						$inQuote = true;
					}
					else if ($codeEnd{$i} == '\'')
					{
						$inQuote = true;
					}

					if ($bracketLevel == 0)
					{
						$endBracket = $i;
						break;
					}
				}
				else
				{
					if ( ($codeEnd{$i} == '"') && ($doubleQuote === true) )
					{
						$inQuote = false;
						$doubleQuote = false;
					}
					else if ($codeEnd{$i} == '\'')
					{
						$inQuote = false;
					}
				}
			}

			// We now know where our command ended. If the next character is semicolon respectfully, we can delete whole command.
			if ($codeEnd{$endBracket + 1} == ';')
			{
				$codeEnd = substr ($codeEnd, $endBracket + 2);

				if (substr ($codeEnd, 0, 1) == "\n")
				{
					$codeEnd = substr ($codeEnd, 1);
				}
			}

			// Combine code together
			$code = $codeStart . "\n" . $codeEnd;
		}

		return $code;
	}

	private function _compressCode ($code)
	{
		$compressed = '';

		foreach (token_get_all ($code) as $token)
		{
			if (is_string ($token))
			{
				$compressed .= $token;
			}
			else
			{
				switch ($token[0])
				{
					case T_COMMENT:
					case T_DOC_COMMENT:
					case T_OPEN_TAG:
					case T_CLOSE_TAG:
						break;
					case T_WHITESPACE:
						$compressed .= ' ';
						break;
					default:
						$compressed .= $token[1];
						break;
				}
			}
		}

		return '<?php ' . $compressed . ' ?>';
	}

	private function _createArchiveDirectories ()
	{
		$this->directories[] = 'system';
		$this->archive->addEmptyDir ('system');

		if ( ($this->databaseCount > 0) && ($this->parsedData['option_compile'] == 0) )
		{
			$this->archive->addEmptyDir ('database');
			$this->directories[] = 'database';
		}

		if ( ( (count ($this->fileList) - $this->databaseCount) > 0) && ($this->parsedData['option_compile'] == 0) )
		{
			$this->archive->addEmptyDir ('library');
			$this->directories[] = 'library';
		}

		// Procedural architecture
		if ($this->parsedData['architecture'] == 1)
		{
			if ($this->parsedData['sample_dir'] == 1)
			{
				$this->archive->addEmptyDir ('cache');
				$this->archive->addEmptyDir ('view');

				$this->directories[] = 'cache';
				$this->directories[] = 'view';
			}

			if ( ( ($this->parsedData['separate_config'] == 1) && ($this->parsedData['option_compile'] == 0) ) || ($this->parsedData['sample_dir'] == 1) || ($this->parsedData['sample_loader'] == 1) )
			{
				$this->archive->addEmptyDir ('config');
				$this->directories[] = 'config';
			}
		}
		// MVC architecture
		else if ($this->parsedData['architecture'] == 0)
		{
			$this->archive->addEmptyDir ('application');

			$this->directories[] = 'application';

            $this->archive->addEmptyDir ('application/controller');

			$this->directories[] = 'application/controller';

			if ( ( ($this->parsedData['separate_config'] == 1) && ($this->parsedData['option_compile'] == 0) ) || ($this->parsedData['sample_dir'] == 1) || ($this->parsedData['sample_loader'] == 1) )
			{
				$this->archive->addEmptyDir ('application/config');
				$this->directories[] = 'application/config';
			}

			if ($this->parsedData['sample_dir'] == 1)
			{
				$this->archive->addEmptyDir ('cache');
				$this->directories[] = 'cache';

				$this->archive->addEmptyDir ('application/lang');
				$this->archive->addEmptyDir ('application/lib');

				$this->directories[] = 'application/lang';
				$this->directories[] = 'application/lib';
			}

			if ( ($this->parsedData['sample_view'] == 1) || ($this->parsedData['sample_dir'] == 1) )
			{
				$this->archive->addEmptyDir ('application/view');
				$this->directories[] = 'application/view';
			}
		}
	}

	private function _createConfig ($config)
	{
		// Change double to single quotes
		$config = str_replace ('"', '\'', $config);

		$config = explode ("\n", $config);

		for ($i = 0; $i < count ($config); $i++)
		{
			// Get rid of space between vars
			$config[$i] = str_replace (array (' =', '= '), '=', $config[$i]);

			// Get rid of equation
			$config[$i] = str_replace ('=', '\', ', $config[$i]);

			$config[$i] = "\t\t" . '$this->Set (\'' . $config[$i] . ");";
		}

		return implode ("\n", $config);
	}

	private function _createCustomFiles ()
	{
		// Creating autoloader

		// We will create separate configuration and loader.
		if ($this->parsedData['separate_config'] == 1)
		{
			$loader .= "class AppLoader extends EmotionLoader\n";
			$loader .= "{\n";
			$loader .= "\tprotected function CustomLoad ()\n";
			$loader .= "\t{\n";
			$loader .= "\t}\n";
			$loader .= "}\n";

			$config .= "class AppConfig extends EmotionConfiguration\n";
			$config .= "{\n";
			$config .= "\tprotected function CustomVariables ()\n";
			$config .= "\t{\n";
			$config .= rtrim ($this->_createConfig ($this->parsedData['config'])) . "\n";
			$config .= "\t}\n";
			$config .= "}\n";

			// If we want them compiled, we will put them into compile classes to be compiled into system file
			if ($this->parsedData['option_compile'] == 1)
			{
				$this->compileClasses[] = new EmotionClass ($loader, 'AppLoader');
				$this->compileClasses[] = new EmotionClass ($config, 'AppConfig');
			}
			// We will put them into file list to be included
			else
			{
				$config = "<?php\n" . $config . "?>\n";
				$loader = "<?php\n" . $loader . "?>\n";

				if ($this->parsedData['architecture'] == 0)
				{
					$path = 'application/config/';
				}
				else
				{
					$path = 'config/';
				}

				$this->fileList[] = $path . 'config.php';
				$this->fileList[] = $path . 'loader.php';
				$this->tempFiles[$path . 'config.php'] = $config;
				$this->tempFiles[$path . 'loader.php'] = $loader;
			}
		}
		// We will find config class and reconstruct the custom function in it.
		else
		{
			foreach ($this->compileClasses as $key => $value)
			{
				// Searching for EmotionConfiguration
				if ($value->GetClassName () == 'EmotionConfiguration')
				{
					// Get configuration class code
					$data = $value->GetClassData ();

					// Finding correct function
					$position = strpos ($data, 'CustomVariables');

					// Searching for correct start position of the function
					for ($i = $position; $i < strlen ($data); $i++)
					{
						$position++;

						if ($data{$i} == '{')
						{
							break;
						}
					}

					// We have it here, so we insert our config at this point.
					$data = substr ($data, 0, $position) . "\n" . trim ($this->_createConfig ($this->parsedData['config'])) . "\n" . substr ($data, $position);

					// Store new class data
					$value->SetClassData ($data);

					$this->compileClasses[$key] = $value;

					break;
				}
			}
		}
	}

	private function _createSampleFiles ()
	{
		//
		// Create access points
		//

		// If we dont have a default access points defined, we create default controller or index
		if (empty ($this->parsedData['access_points']) === true)
		{
			if ($this->parsedData['architecture'] == 0)
			{
				$this->parsedData['access_points'] = 'Home';
			}
			else
			{
				$this->parsedData['access_points'] = 'index';
			}
		}

		$files = explode ("-", $this->parsedData['access_points']);

		foreach ($files as $value)
		{
			// Controllers
			if ($this->parsedData['architecture'] == 0)
			{
				$this->archive->addFromString ('application/controller/' . strtolower ($value) . '.' . $this->parsedData['option_extension'], $this->_sampleFile (1, array ('name' => ucwords ($value), 'package' => 'sample')) );
			}
			else
			{
				$this->archive->addFromString (strtolower ($value) . '.' . $this->parsedData['option_extension'], $this->_sampleFile (0, array ('filename' => (strtolower ($value) . '.' . $this->parsedData['option_extension']), 'package' => 'sample')) );
			}
		}

		// Add default access point, if we dont have it yet
		// Loaders
		if ($this->parsedData['sample_loader'] == 1)
		{
			$controllers = @scandir ('application/sample/loaders/');

			foreach ($controllers as $value)
			{
				if (is_file ('application/sample/loaders/' . $value) === true)
				{
					if ($this->parsedData['architecture'] == 1)
					{
						$this->archive->addFile ('application/sample/loaders/' . $value, 'config/' . $value);
					}
					else
					{
						$this->archive->addFile ('application/sample/loaders/' . $value, 'application/config/' . $value);
					}
				}
			}
		}

		// Add sample views
		if ($this->parsedData['sample_view'] == 1)
		{
			$views = @scandir ('application/sample/views/');

			foreach ($views as $value)
			{
				if (is_file ('application/sample/views/' . $value) === true)
				{
					if ($this->parsedData['architecture'] == 1)
					{
						$this->archive->addFile ('application/sample/views/' . $value, 'view/' . $value);
					}
					else
					{
						$this->archive->addFile ('application/sample/views/' . $value, 'application/view/' . $value);
					}
				}
			}
		}

		if ($this->parsedData['architecture'] == 0)
		{
			// Add vanilla home controller
			if (in_array ('Home', $files) === false)
			{
				$this->archive->addFromString ('application/controller/home' . '.' . $this->parsedData['option_extension'], $this->_sampleFile (1, array ('name' => 'home')));
			}

			if ($this->parsedData['sample_controller'] == 1)
			{
				$controllers = @scandir ('application/sample/controllers/');

				foreach ($controllers as $value)
				{
					if (is_file ('application/sample/controllers/' . $value) === true)
					{
						$this->archive->addFile ('application/sample/controllers/' . $value, 'application/controller/' . $value);
					}
				}
			}
		}
		else if ($this->parsedData['architecture'] == 1)
		{
			// Add vanilla home controller
			if (in_array ('Index', $files) === false)
			{
				$this->archive->addFromString ('index.' . $this->parsedData['option_extension'], $this->_sampleFile (0, array ('filename' => ('index.' . $this->parsedData['option_extension']), 'package' => 'sample')) );
			}
		}
	}

	private function _createSystemFile ()
	{
		// We dont need controller, we delete it from compiled class base
		if ($this->parsedData['architecture'] == 1)
		{
			foreach ($this->compileClasses as $key => $value)
			{
				if ($value->GetClassName () == 'EmotionController')
				{
					unset ($this->compileClasses[$key]);
				}
			}

			$this->compileClasses = array_values ($this->compileClasses);
		}

		//
		// Compiling system file
		//

		// Write start of PHP file
		$system = "<?php\n";
		$system .= $this->_fileHeader('emotion.' . $this->parsedData['option_extension'], 'core');
		$system .= $this->_securePage ();
		$system .= $this->_constants ();

		// Put all stuff in
		foreach ($this->compileClasses as $value)
		{
			if ($value->IsReady () === true)
			{
				if ($this->parsedData['option_comment'] == 0)
				{
					$system .= "\n";
					$system .= $value->ConstructComment ();
				}

				$system .= "\n" . $value->GetClassConstruct() . "\n";
				$system .= "{\n";
				$system .= "\t" . $value->GetClassData ();
				$system .= "\n}\n";
			}
		}

		$system .= "\n?>\n";

		$this->tempFiles['system/emotion.php'] = $system;

	}

	private function _getFileList ()
	{
		// System file
		$this->fileList[] = 'system/emotion.php';

		// Required library files
		$this->fileList[] = 'library/common.php';
		$this->fileList[] = 'library/security.php';

		if ($this->parsedData['option_debug'] == 1)
		{
			$this->fileList[] = 'library/debug.php';
		}

		//
		// Library files
		//

		// Beginner file selection
		if ($this->parsedData['user_mode'] == 0)
		{
			// Get libraries
			$features[0] = array ('image', 'language', 'text');
			$features[1] = array ('database', 'image', 'language', 'text');
			$features[2] = array ('database', 'image', 'language', 'session', 'upload', 'text');
			$features[3] = array ('captcha', 'database', 'image', 'language', 'session', 'template', 'upload', 'text');
			$features[4] = array ('banlist', 'captcha', 'database', 'encryption', 'image', 'language', 'session', 'template', 'text', 'upload', 'zip');

			foreach ($features[$this->parsedData['features'][0]] as $value)
			{
				$this->fileList[] =  'library/' . $value . '.php';
			}
		}
		// Expert file selection
		else
		{
			$this->fileList[] = 'library/database.php';

			foreach ($this->parsedData['features'] as $value)
			{
				if ( is_numeric ($value) === false)
				{
					$this->fileList[] = 'library/' . $value . '.php';
				}
			}
		}

		//
		// Databases
		//

		$databases = explode ('-', $this->parsedData['databases']);

		$this->databaseCount = 0;

		foreach ($databases as $value)
		{
			if (file_exists ('database/' . $value . '.php') === true)
			{
				$this->fileList[] = 'database/' . $value . '.php';

				$this->databaseCount++;
			}
		}

		// Find database if we have no selected drivers and remove abstraction layer
		if ($this->databaseCount == 0)
		{
			foreach ($this->fileList as $key => $value)
			{
				if ( $value == 'library/database.php')
				{
					unset ($this->fileList[$key]);
				}
			}

			$this->fileList = array_values ($this->fileList);
		}

		// Check files that will be compiled for syntax errors

		foreach ($this->fileList as $key => $value)
		{
			if ($this->_checkSyntax ($value) === false)
			{
				unset ($this->fileList[$key]);
			}
		}

		$this->fileList = array_values ($this->fileList);

		//
		// If we are compiling, we will read all classes into our compile classes var
		//

		if ($this->parsedData['option_compile'] == 1)
		{
			foreach ($this->fileList as $value)
			{
				$buffer = file_get_contents ($value);

				// Files maybe have more classes, we need to find them
				$classes = $this->_getClasses ($buffer);

				foreach ($classes as $value2)
				{
					$this->compileClasses[] = new EmotionClass ($buffer, $value2);
				}
			}

			// If we are compiling, we dont need filelist anymore, but we still need system to run?
			$this->fileList = array ('system/emotion.php');
		}
		// Even if we arent compiling whole system, we still need to create kernel
		else
		{
			$index = array_search ('system/emotion.php', $this->fileList);

			$buffer = file_get_contents ($this->fileList[$index]);

			// Files maybe have more classes, we need to find them
			$classes = $this->_getClasses ($buffer);

			foreach ($classes as $value)
			{
				$this->compileClasses[] = new EmotionClass ($buffer, $value);
			}
		}
	}

	private function _prepareData ($data)
	{
		$this->parsedData['user_mode'] = intval ($data[0][0]); // DONE -> doesnt have much difference
		$this->parsedData['architecture'] = intval ($data[0][1]); // DONE

		$this->parsedData['features'] = $data[2]; // DONE

		$this->parsedData['databases'] = $data[3][0]; // DONE
		$this->parsedData['access_points'] = $data[3][1]; // DONE
		$this->parsedData['config'] = $data[3][2]; // DONE
		$this->parsedData['separate_config'] = intval ($data[3][3]); // DONE

		$this->parsedData['option_debug'] = intval ($data[4][0]); // DONE
		$this->parsedData['option_htaccess'] = intval ($data[4][1]); // DONE

		$this->parsedData['sample_dir'] = intval ($data[4][2]); // DONE
		$this->parsedData['sample_loader'] = intval ($data[4][3]); // DONE
		$this->parsedData['sample_controller'] = intval ($data[4][4]); // DONE
		$this->parsedData['sample_view'] = intval ($data[4][5]); // DONE

		$this->parsedData['option_comment'] = intval ($data[4][6]); // DONE
		$this->parsedData['option_shrink'] = intval ($data[4][7]); // DONE
		$this->parsedData['option_compile'] = intval ($data[4][8]); // DONE

		$this->parsedData['option_extension'] = $data[4][9]; // DONE
	}

	private function _prepareFiles ()
	{
		// If we dont have anything to do, its useless to go over costly fileloads..
		if ( ($this->parsedData['option_debug'] == 0) || ($this->parsedData['option_comment'] == 1) || ($this->parsedData['option_shrink'] == 1) )
		{
			foreach ($this->fileList as $value)
			{
				// Get code either from tempfile or read file
				if (isset ($this->tempFiles[$value]) === true)
				{
					$code = $this->tempFiles[$value];
				}
				else
				{
					$code = file_get_contents ($value);
				}

				$changed = false;

				if ($this->parsedData['option_debug'] == 0)
				{
					$code = $this->_cleanDebug ($code);
					$changed = true;
				}

				// Dont need to clean comments twice, if we're already shrinking code.
				if ( ($this->parsedData['option_comment'] == 1) && ($this->parsedData['option_shrink'] == 0) )
				{
					$code = $this->_cleanComments ($code);
					$changed = true;
				}

				if ($this->parsedData['option_shrink'] == 1)
				{
					$code = $this->_compressCode ($code);
					$changed = true;
				}

				if ($changed === true)
				{
					$this->tempFiles[$value] = $code;
				}
			}
		}
	}

	private function _writeFiles ($filename)
	{
		if (file_exists ('temp/' . $filename) === true)
		{
			return false;
		}

		// Open zip archive
		$this->archive = new ZipArchive ();
		$this->archive->open ('temp/' . $filename, ZipArchive::CREATE);

		$this->_createArchiveDirectories ();

		$this->_createSampleFiles ();

		// If its not compiled, we will add databases and stuff
		// Adding libraries and databases
		$dirs = array ();

		//print_r ($this->parsedData);

		foreach ($this->fileList as $value)
		{
			// We may have a temp version of file done in post processing
			if (isset ($this->tempFiles[$value]) === true)
			{
				$this->archive->addFromString (pathinfo ($value, PATHINFO_DIRNAME) . '/' . $this->_rawName ($value) . '.' . $this->parsedData['option_extension'], $this->tempFiles[$value]);
			}
			else
			{
				$this->archive->addFile ($value, pathinfo ($value, PATHINFO_DIRNAME) . '/' . $this->_rawName ($value) . '.' . $this->parsedData['option_extension']);
			}

			$dir = pathinfo ($value, PATHINFO_DIRNAME);

			if (in_array ($dir, $this->directories) === false)
			{
				$this->directories[] = $dir;
			}
		}

		// For MVC we add default access point and htaccess
		if ($this->parsedData['architecture'] == 0)
		{
			$this->archive->addFromString ('index.' . $this->parsedData['option_extension'], $this->_sampleFile (2));

			if ($this->parsedData['option_htaccess'] == 1)
			{
				$this->archive->addFromString ('.htaccess', $this->_sampleFile (3));
			}
		}

		// Go to directories and put empty index.html files in them.
		foreach ($this->directories as $value)
		{
			$this->archive->addFromString ($value . '/index.html', $this->_sampleFile (4));
		}

		//print_r ($this->archive->getStatusString());

		$this->archive->close ();
	}

	//
	// Sub supporting functions
	//

	private function _constants ()
	{
		$constants = "define ('EMOTION_VERSION', '" . EMOTION_VERSION . "');\n";
		$constants .= "define ('EXTENSION', '." . $this->parsedData['option_extension'] . "');\n";
		$constants .= "\n";

		if ($this->parsedData['architecture'] == 0)
		{
			$constants .= "define ('DEFAULT_APPLICATION', 'application');\n";
			$constants .= "define ('DEFAULT_CONFIG', 'application/config/config');\n";
		}
		else
		{
			$constants .= "define ('DEFAULT_APPLICATION', '.');\n";
			$constants .= "define ('DEFAULT_CONFIG', 'config/config');\n";
		}

		$constants .= "\n";

		$constants .= "define ('DEFAULT_DATABASE', 'database');\n";
		$constants .= "define ('DEFAULT_LIBRARY', 'library');\n";
		$constants .= "define ('DEFAULT_SYSTEM', 'system');\n";

		$constants .= "\n";

		return $constants;
	}

	// PHP parser alternative that checks for syntax errors
	private function _checkSyntax ($file)
	{
		return true;
	}

	private function _fileHeader ($file, $package, $description = 'Emotion class file automatically generated by compile system.')
	{
		$header = "/***************************************************************************" . "\n";
		$header .= " *   Application          : Emotion                                        *" . "\n";
		$header .= " *   -------------------------------------------------------------------   *" . "\n";

		$header .= $this->_finishCommentLine (" *   Package              : @{$package}") . "\n";
		$header .= $this->_finishCommentLine (" *   File                 : @{$file}") . "\n";
		$header .= $this->_finishCommentLine (" *   Version              : " . EMOTION_VERSION) . "\n";

		$header .= " *   Status               : Compiled                                       *" . "\n";
		$header .= " *   -------------------------------------------------------------------   *" . "\n";

		$header .= $this->_finishCommentLine (" *   Begin                : " . date ('l, M j, Y')) . "\n";

		$header .= " *   CopyRight            : (C) 2011 ArvYStaTe.net Team                    *" . "\n";
		$header .= " *   E-Mail               : support@arvystate.net                          *" . "\n";
		$header .= $this->_finishCommentLine (" *   Last edit            : " . date ('l, M j, Y')) . "\n";
		$header .= " *                                                                         *" . "\n";
		$header .= " *                                                                         *" . "\n";
		$header .= " ***************************************************************************" . "\n";
		$header .= "\n";
		$header .= " ***************************************************************************" . "\n";
		$header .= " *   File description                                                      *" . "\n";
		$header .= " *   -------------------------------------------------------------------   *" . "\n";
		$header .= $this->_finishCommentLine (" *   " . $description) . "\n";
		$header .= " *   -------------------------------------------------------------------   *" . "\n";
		$header .= " *                                                                         *" . "\n";
		$header .= " ***************************************************************************" . "\n";
		$header .= "\n";
		$header .= " ***************************************************************************" . "\n";
		$header .= " *   Change log                                                            *" . "\n";
		$header .= " *   -------------------------------------------------------------------   *" . "\n";
		$header .= " *    + [" . date ('m/d/y') . "] - File created                                          *" . "\n";
		$header .= " *   -------------------------------------------------------------------   *" . "\n";
		$header .= " *                                                                         *" . "\n";
		$header .= " ***************************************************************************" . "\n";
		$header .= "\n";
		$header .= " ***************************************************************************" . "\n";
		$header .= " *                                                                         *" . "\n";
		$header .= " *   Emotion is a powerful PHP framework for website generation.           *" . "\n";
		$header .= " *   -------------------------------------------------------------------   *" . "\n";
		$header .= " *   Application is owned and copyrighted by ArvYStaTe.net Team, you are   *" . "\n";
		$header .= " *   only allowed to modify code, not take ownership or in any way claim   *" . "\n";
		$header .= " *   you are the creator of any thing else but modifications.              *" . "\n";
		$header .= " *   -------------------------------------------------------------------   *" . "\n";
		$header .= " *                                                                         *" . "\n";
		$header .= " ***************************************************************************/" . "\n";

		return $header;
	}

	private function _finishCommentLine ($string, $maxLength = " ***************************************************************************")
	{
		// To check for maxlength
		if (is_numeric ($maxLength) === false)
		{
			$maxLength = strlen ($maxLength);
		}

		// Add spaces until last char
		while (strlen ($string) < ($maxLength - 1) )
		{
			$string = $string . ' ';
		}

		// Add star on end
		$string = $string . '*';

		return $string;
	}

	private function _getClasses ($code)
	{
		//
		// Rewrote a much faster method
		//

		$blocks = array ();

		$block = 'class ';

		while ( ($position = strpos ($code, $block)) !== false)
		{
			if ($this->_isInComment ($code, $block) === false)
			{
				// Easily get block name by taking first bracket
				$blockName = substr ($code, $position + strlen ($block));
				$blockName = substr ($blockName, 0, strpos ($blockName, '{'));

				// Classes have more things like: extends or implements
				$blockName = trim ($blockName);
				$blockName = explode (' ', $blockName);

				// If its an array, its likely class that extends or implements, we verify this for enhanced security
				if (is_array ($blockName) === true)
				{
					if (count ($blockName) > 1)
					{
						if ( ($blockName[1] == 'extends') || ($blockName[1] == 'implements') )
						{
							$blockName = $blockName[0];
						}
						else
						{
							$blockName = '';
						}
					}
					else
					{
						$blockName = $blockName[0];
					}
				}

				$blockName = trim ($blockName);

				if ($blockName != '')
				{
					$blocks[] = $blockName;
				}
			}

			// Cut away code to get rid of unlimited loop

			$code = substr ($code, $position + strlen ($block) );
		}

		return $blocks;
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

	private function _nltrim ($string)
	{
		$string = str_replace ('\\n', '', $string);
		$string = trim ($string);

		return $string;
	}

	private function _parseRawData ($data)
	{
		// Handle data, if it is not created
		$data = explode (';', $data);

		// Errorneous data, recreate
		if (count ($data) != 5)
		{
			for ($i = 0; $i < count ($data); $i++)
			{
				if (isset ($data[$i]) === false)
				{
					$data[$i] = '';
				}
			}
		}

		for ($i = 0; $i < count ($data); $i++)
		{
			$data[$i] = explode (':', $data[$i]);
		}

		return $data;
	}

	private function _rawName ($filename)
	{
		$filename = basename ($filename);

		$filename = substr ($filename, 0, strrpos ($filename, '.'));

		return $filename;
	}

	private function _sampleFile ($type = 0, $data = array ())
	{
		switch ($type)
		{
			// Sample controller
			case 1:
				$file = "<?php\n";
				$file .= $this->_securePage ();
				$file .= "class " . $data['name'] . " extends EmotionController" . "\n";
				$file .= "{\n";
				$file .= "\tpublic function index ()\n";
				$file .= "\t{\n";
				$file .= "\t\techo ('Hello world.<br /><br />Powered by Emotion Engine " . EMOTION_VERSION . "');\n";
				$file .= "\t}\n";
				$file .= "}\n\n";

				$file .= "?>\n";
				break;
			// Entry point for MVC
			case 2:
				$file = "<?php\n";
				$file .= $this->_fileHeader ('index', 'core', 'User access point for MVC applications.');

				$file .= "\n";
				$file .= "define ('EMOTION_PAGE', true);" . "\n";
				$file .= "require_once ('system/emotion." . $this->parsedData['option_extension'] . "');" . "\n\n";
				$file .= '$system = new EmotionEngine ();' . "\n";
				$file .= '$system->UseFrontController ();' . "\n";

				$file .= "\n?>\n";

				break;
			// htaccess page
			case 3:
				$file = '<FilesMatch "\\.(engine|inc|info|install|make|module|profile|test|po|sh|.*sql|theme|tpl(\\.php)?|xtmpl|svn-base)$|^(code-style\\.pl|Entries.*|Repository|Root|Tag|Template|all-wcprops|entries|format)$">' . "\n";
				$file .= '	Order allow,deny'. "\n";
				$file .= '</FilesMatch>'. "\n";
				$file .= "\n";
				$file .= 'Options -Indexes'. "\n";
				$file .= "\n";
				$file .= 'Options +FollowSymLinks'. "\n";
				$file .= "\n";
				$file .= 'DirectoryIndex index.'. $this->parsedData['option_extension'] . "\n";
				$file .= "\n";
				$file .= '<IfModule mod_php4.c>'. "\n";
				$file .= '  php_value magic_quotes_gpc                0'. "\n";
				$file .= '  php_value register_globals                0'. "\n";
				$file .= '  php_value session.auto_start              0'. "\n";
				$file .= '  php_value mbstring.http_input             pass'. "\n";
				$file .= '  php_value mbstring.http_output            pass'. "\n";
				$file .= '  php_value mbstring.encoding_translation   0'. "\n";
				$file .= '</IfModule>'. "\n";
				$file .= "\n";
				$file .= '<IfModule sapi_apache2.c>'. "\n";
				$file .= '  php_value magic_quotes_gpc                0'. "\n";
				$file .= '  php_value register_globals                0'. "\n";
				$file .= '  php_value session.auto_start              0'. "\n";
				$file .= '  php_value mbstring.http_input             pass'. "\n";
				$file .= '  php_value mbstring.http_output            pass'. "\n";
				$file .= '  php_value mbstring.encoding_translation   0'. "\n";
				$file .= '</IfModule>'. "\n";
				$file .= "\n";
				$file .= '<IfModule mod_php5.c>'. "\n";
				$file .= '  php_value magic_quotes_gpc                0'. "\n";
				$file .= '  php_value register_globals                0'. "\n";
				$file .= '  php_value session.auto_start              0'. "\n";
				$file .= '  php_value mbstring.http_input             pass'. "\n";
				$file .= '  php_value mbstring.http_output            pass'. "\n";
				$file .= '  php_value mbstring.encoding_translation   0'. "\n";
				$file .= '</IfModule>'. "\n";
				$file .= "\n";
				$file .= '<IfModule mod_expires.c>'. "\n";
				$file .= '  ExpiresActive On'. "\n";
				$file .= "\n";
				$file .= '  ExpiresDefault A1209600'. "\n";
				$file .= "\n";
				$file .= '  <FilesMatch \\.php$>'. "\n";
				$file .= "\n";
				$file .= '    ExpiresActive Off'. "\n";
				$file .= '  </FilesMatch>'. "\n";
				$file .= '</IfModule>'. "\n";
				$file .= "\n";
				$file .= '<IfModule mod_rewrite.c>'. "\n";
				$file .= '  RewriteEngine on'. "\n";
				$file .= "\n";
				$file .= '  RewriteCond %{REQUEST_FILENAME} !-f'. "\n";
				$file .= '  RewriteCond %{REQUEST_FILENAME} !-d'. "\n";
				$file .= '  RewriteCond %{REQUEST_URI} !=/favicon.ico'. "\n";
				$file .= '  RewriteRule ^(.*)$ index.php?q=$1 [L,QSA]'. "\n";
				$file .= '</IfModule>'. "\n";

				break;
			case 4:
				$file = '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">' . "\n";
				$file .= '<html><head>' . "\n";
				$file .= '<title>404 Not Found</title>' . "\n";
				$file .= '</head><body>' . "\n";
				$file .= '<h1>Not Found</h1>' . "\n";
				$file .= '<p>The requested URL /index.html was not found on this server.</p>' . "\n";
				$file .= '<p>Additionally, a 404 Not Found error was encountered while trying to use an ErrorDocument to handle the request.</p>' . "\n";
				$file .= '</body></html>' . "\n";

				break;

			// Entry point for procedural
			default:
				$file = "<?php\n";
				$file .= $this->_fileHeader ($data['filename'], $data['package']);

				$file .= "\n";
				$file .= "define ('EMOTION_PAGE', true);" . "\n";
				$file .= "require_once ('system/emotion.php');" . "\n\n";
				$file .= '$system = new EmotionEngine ();' . "\n";
				$file .= "echo ('Hello world.<br /><br />Powered by Emotion Engine " . EMOTION_VERSION . "');\n";

				$file .= "?>\n";

				break;
		}

		return $file;
	}

	private function _securePage ()
	{
		$page ="\n";
		$page .= "/**" . "\n";
		$page .= "* @ignore" . "\n";
		$page .= "**/" . "\n";
		$page .= "\n";
		$page .= "if (!defined ('EMOTION_PAGE'))" . "\n";
		$page .= "{" . "\n";
		$page .= "	die ('<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">" . "\n";
		$page .= "			<html><head>" . "\n";
		$page .= "			<title>404 Not Found</title>" . "\n";
		$page .= "			</head><body>" . "\n";
		$page .= "			<h1>Not Found</h1>" . "\n";
		$page .= '			<p>The requested URL /\' . $_SERVER[\'PHP_SELF\'] . \' was not found on this server.</p>' . "\n";
		$page .= "			<p>Additionally, a 404 Not Found" . "\n";
		$page .= "			error was encountered while trying to use an ErrorDocument to handle the request.</p>" . "\n";
		$page .= "			</body></html>');" . "\n";
		$page .= "}" . "\n\n";

		return $page;
	}
}

?>