<?php
/***************************************************************************
 *   Application          : Emotion                                        *
 *   -------------------------------------------------------------------   *
 *   Package              : @core                                          *
 *   File                 : emotion.php                                    *
 *   Version              : 1.0.0                                          *
 *   Status               : Alpha, Not tested                              *
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
 *   This file contains the core classes of Emotion project. All classes   *
 *   in this file are required to work. Modify this file by your own       *
 *   risks and make sure you know what you are doing.                      *
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

//
// Emotion base constants
//

define ('EMOTION_VERSION', '0.6.6');
define ('EXTENSION', '.php');

define ('DEFAULT_APPLICATION', 'application');
define ('DEFAULT_DATABASE', 'database');
define ('DEFAULT_LIBRARY', 'library');
define ('DEFAULT_SYSTEM', 'system');

define ('DEFAULT_CONFIG', 'application/config/config');

class EmotionLoader
{
	protected $config;
	protected $modules;
	
	// This method is always called and contains automatic default loading
	public final function StartLoad ($modules, $config)
	{
		// Store config and modules
		$this->config = $config;
		$this->modules = $modules;
		
		$this->Load ('Debug')->SaveMessage ('Started autoloader.');
		
		//
		// Do automatic loading here
		//
		
		// Custom loader by either compiler or manual configuration
		$this->CustomLoad ();
	}
	
	// This function should be overriden
	protected function CustomLoad ()
	{
	}
	
	public final function GetModules ()
	{
		return $this->modules;
	}
	
	protected final function Config ($key)
	{
		if (is_object ($this->config) === true)
		{
			return $this->config->Get ($key);
		}
		else
		{
			return null;	
		}
	}
	
	// To support Emotion loading system in autoloaders.
	protected final function Load ($classVariable, $className = '', $instance = true)
	{
		if ( empty ($className) === true)
		{
			$className = $classVariable;
		}
		
		if (isset ($this->modules[$classVariable]) === true)
		{
			return $this->modules[$classVariable];
		}
		else
		{
			if (class_exists ($className) === false)
			{
				if (file_exists (DEFAULT_LIBRARY . '/' . strtolower ($className) . EXTENSION) === true)
				{
					include_once (DEFAULT_LIBRARY . '/' . strtolower ($className) . EXTENSION);
				}
				else
				{
					return null;
				}
				
				if ( (class_exists ($className) === true) && ($instance === true) )
				{
					$this->modules[$classVariable] = new $className ();
					
					return $this->modules[$classVariable];
				}
				else
				{
					return null;
				}				
			}
			else
			{
				if ($instance === true)
				{
					$this->modules[$classVariable] = new $className ();
					return $this->modules[$classVariable];
				}
			}
		}
	}
}

class EmotionConfiguration
{
	private $config = array ();
	
	public function __construct ()
	{
		$this->SetupVariables ();
	}
	
	//
	// This function is always called, so we always have few default vars, required for system to operate
	//
	
	public final function SetupVariables ()
	{
		$this->Set ('application_dir', 'application');
		$this->Set ('loader', 'config/loader');
		$this->Set ('autoload', true);
		$this->Set ('default_controller', 'home');
		$this->Set ('enable_query_strings', false);
		$this->Set ('controller_trigger', 'c');
		$this->Set ('function_trigger', 'm');
		
		// Call custom variables function
		$this->CustomVariables ();
	}
	
	protected function CustomVariables ()
	{
	}
	
	protected final function Set ($key, $value)
	{
		$this->config[$key] = $value;
	}
	
	public final function Get ($variable)
	{
		if (isset ($this->config[$variable]) === true)
		{
			return $this->config[$variable];
		}
		else
		{
			return null;
		}
	}
}

class EmotionController
{
	protected $modules = array ();
	protected $config;
	
	public function index ()
	{
	}
	
	public final function Setup ($modules, $config)
	{
		$this->modules = $modules;
		$this->config = $config;
	}
	
	protected final function Config ($key)
	{
		if (is_object ($this->config) === true)
		{
			return $this->config->Get ($key);
		}
		else
		{
			return null;	
		}
	}

	protected final function Load ($classVariable, $className = '', $instance = true)
	{	
		if ( empty ($className) === true)
		{
			$className = $classVariable;
		}
		
		if (isset ($this->modules[$classVariable]) === true)
		{
			return $this->modules[$classVariable];
		}
		else
		{
			if (class_exists ($className) === false)
			{
				if (file_exists (DEFAULT_LIBRARY . '/' . strtolower ($className) . EXTENSION) === true)
				{
					include_once (DEFAULT_LIBRARY . '/' . strtolower ($className) . EXTENSION);
				}
				else
				{
					return null;
				}
				
				if ( (class_exists ($className) === true) && ($instance === true) )
				{
					$this->modules[$classVariable] = new $className ();
					return $this->modules[$classVariable];
				}
				else
				{
					return null;
				}				
			}
			else
			{
				if ($instance === true)
				{
					$this->modules[$classVariable] = new $className ();
					return $this->modules[$classVariable];
				}
				else
				{
					return null;
				}
			}
		}
	}
	
	//
	// Loading views
	//
	
	protected final function View ($view, $data = array (), $return = false)
	{
		$templateModule = $this->_getModuleKey ('Template');
		$languageModule = $this->_getModuleKey ('Language');
		
		// We use template loading system if we have it
		if ($templateModule !== false)
		{
			// Language check
			if ($languageModule !== false)
			{
				$data = array_merge ($data, $this->Load ($languageModule)->Get ());
			}
			
			return ($this->Load ($templateModule)->Display ($view, $data, $return) );
		}
		else
		{
			ob_start();
			
			if (file_exists ($this->config->Get ('application_dir') . '/view/' . $view . '.html') === true)
			{
				$path = $this->config->Get ('application_dir') . '/view/' . $view . '.html';
			}
			else if (file_exists ($this->config->Get ('application_dir') . '/view/' . $view . EXTENSION) === true)
			{
				$path = $this->config->Get ('application_dir') . '/view/' . $view . EXTENSION;
			}
			else
			{
				return false;
			}

			extract ($data);

			// Fixes short open tags <?=, if server does not support it
			if (@ini_get ('short_open_tag') === false)
			{
				echo eval ('?>' . preg_replace("/;*\s*\?>/", "; ?>", str_replace ('<?=', '<?php echo ', file_get_contents ($path))));
			}
			else
			{
				include ($path);
			}
			
			$buffer = ob_get_contents ();
			@ob_end_clean ();
			
			// Language auto-replace
			if ($languageModule !== false)
			{
				if ($this->modules[$languageModule]->IsReady () === true)
				{
					$buffer = $this->modules[$languageModule]->Replace ($buffer);
				}
			}
			
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
	
	// Returns module key of desired class - used for template
	protected function _getModuleKey ($className)
	{
		if (isset ($this->modules[$className]) === true)
		{
			if (get_class ($this->modules[$className]) == $className)
			{
				return $className;
			}
			else
			{
				foreach ($this->modules as $key => $value)
				{
					if (get_class ($this->modules[$key]) == $className)
					{
						return $key;
					}
				}
				
				return false;			
			}
		}
		else
		{
			return false;
		}
	}
}

class EmotionEngine
{
	// Singleton instance
	private static $instance;
	// Stores current loaded class list
	//private $classes = array ();
	// Stores configuration
	private $config;
	// Stores loader
	private $loader;
	// Stores modules
	private $modules = array ();
	
	public function __construct ()
	{
		$a = func_get_args ();
        $i = func_num_args ();
		
        if (method_exists ($this, $f = '__construct' . $i))
		{
            call_user_func_array (array ($this, $f), $a);
        }
		else
		{
			// This is the default constructor with default path to config file, we can use 
			$this->__construct1 (DEFAULT_CONFIG  . EXTENSION);
		}
	}

	// Custom class constructor
	public function __construct1 ($file)
	{
		error_reporting (0);
		
		// Save instance
		self::$instance =& $this;
		
		$this->Load ('Debug')->StartTimer ();
		$this->Load ('Debug')->SaveMessage ('Created Emotion Engine instance.');
		$this->Load ('Debug')->ErrorReporting (4);

		if ($file === false)
		{
			return;
		}
		
		// Try to load custom config if it exists, otherwise we will load default config
		if (file_exists ($file) === true)
		{
			$this->Load ('Debug')->SaveMessage ('Included config file: ' . $file);
			
			include_once ($file);
		}
		
		// Always Autoload Configuration
		$className = $this->GetClassFinal ('EmotionConfiguration');
		$this->config = new $className ();
		
		$this->Load ('Debug')->SaveMessage ('Configuration class selected: ' . $className);
		
		$this->Load ('Debug')->SaveMessage ('Attempting to load application loader: ' . $this->config->Get ('application_dir') . '/' . $this->config->Get ('loader') . EXTENSION);
		
		// Check if we need to load custom loader
		if (file_exists ($this->config->Get ('application_dir') . '/' . $this->config->Get ('loader') . EXTENSION) == true)
		{
			include_once ($this->config->Get ('application_dir') . '/' . $this->config->Get ('loader') . EXTENSION);
			
			$this->Load ('Debug')->SaveMessage ('Included application loader file.');
		}
		// Check again not in main directory
		else if (file_exists ($this->config->Get ('loader') . EXTENSION) == true)
		{
			include_once ($this->config->Get ('loader') . EXTENSION);
			
			$this->Load ('Debug')->SaveMessage ('Included custom loader file.');
		}

		// Create loader (default or custom -> custom one contains default anyway)
		$className = $this->GetClassFinal ('EmotionLoader');
		$this->loader = new $className ();
		
		$this->Load ('Debug')->SaveMessage ('Loader class selected: ' . $className);
		
		unset ($className);
		
		// Check if we are autoloading the system
		if ($this->config->Get ('autoload') === true)
		{
			$this->Load ('Debug')->SaveMessage ('Autoloading system.');
			
			// Load required modules with loader (need to pass configuration with this, for database and such)
			$this->loader->StartLoad (&$this->modules, &$this->config);
			
			$this->modules = $this->loader->GetModules ();
		}
	}
	
	//
	// Returns the name of final class -> first one if there are more derived from same
	//
	
	private function GetClassFinal ($className)
	{
		$classes = get_declared_classes ();
		
		$count = count ($classes);
		
		for ($i = 0; $i < $count; $i++)
		{
			if (get_parent_class ($classes[$i]) === $className)
			{
				return $classes[$i];
			}
		}
		
		return $className;
	}

	//
	// Controller functions
	//

	// Front Controller logic -> Similar to CodeIgniter
	public function UseFrontController ()
	{
		//
		// Simple Front Controller
		//
		
		// For query strings we use different behaviour
		if ($this->config->Get ('enable_query_strings') === true)
		{
			$class = $_GET[$this->config->Get('controller_trigger')];
			$function = $_GET[$this->config->Get('function_trigger')];
			
			// Clean triggers from parameters
			$parameters = array ();
			
			foreach ($_GET as $key => $value)
			{
				if ( ($key != $this->config->Get('controller_trigger')) && ($key != $this->config->Get('function_trigger')) )
				{
					$parameters[$key] = $value;
				}
			}
		}
		// Routing controller
		else
		{
			if ($_GET['q'] != '')
			{
				$routes = explode ('/', $_GET['q']);
			}
			
			try
			{
				$class = $routes[0];
				
				if (count ($routes) > 1)
				{
					$function = $routes[1];
				}
				else
				{
					$function = '';
				}
				
				$parameters = array ();
				
				// Put other parameters into array
				for ($i = 2, $x = 0; $i < count ($routes); $i++, $x++)
				{
					$parameters[$x] = $routes[$i];
				}
			}
			catch (Exception $e)
			{
				$class = '';
				$function = '';
				
				$parameters = array ();
			}
		}
		
		// Check for class and function
		if ( ($controller = $this->_loadController ($class)) !== null)
		{
			// Security for function
			if ($function == '')
			{
				$function = 'index';
			}
			
			// We are going to try calling main method with parameter count
			if (in_array ($function, get_class_methods ($controller)) === false)
			{
				$parameters = array ($function);
				$function = 'index';
			}
			
			try
			{
				call_user_func_array (array (&$controller, $function), $parameters);
			}
			catch (Exception $e)
			{
				$this->DisplayError (404);
			}
		}
		// Load default controller
		else if (empty ($class) === true)
		{
			// Check for class and function
			if ( ($controller = $this->_loadController ($this->config->Get('default_controller'))) !== null)
			{
				if (empty ($function) === true)
				{
					// Call index function of default controller
					$controller->index ();
				}
				else
				{
					call_user_func_array (array (&$controller, $function), $parameters);
				}
			}
		}
		else
		{
			$this->DisplayError (404);
		}
	}
	
	//
	// Function for generic error creation
	//

	public function DisplayError ($error, $description = '')
	{
		if (!$this->View ($error))
		{
			switch ($error)
			{
				default:
					echo ('<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
							<html><head>
							<title>404 Not Found</title>
							</head><body>
							<h1>Not Found</h1>
							<p>The requested URL /index.html was not found on this server.</p>
							<p>Additionally, a 404 Not Found
							error was encountered while trying to use an ErrorDocument to handle the request.</p>
							</body></html>
							');
					break;
			}
		}
	}

	//
	// Functions to use classes
	//
	
	public function Load ($classVariable, $className = '', $instance = true)
	{
		if ( empty ($className) === true)
		{
			$className = $classVariable;
		}
		
		if (isset ($this->modules[$classVariable]) === true)
		{
			return $this->modules[$classVariable];
		}
		else
		{
			if (class_exists ($className) === false)
			{
				if (file_exists (DEFAULT_LIBRARY . '/' . strtolower ($className) . EXTENSION) === true)
				{
					include_once (DEFAULT_LIBRARY . '/' . strtolower ($className) . EXTENSION);
				}
				else
				{
					return null;
				}
				
				if ( (class_exists ($className) === true) && ($instance === true) )
				{
					$this->modules[$classVariable] = new $className ();
					return $this->modules[$classVariable];
				}
				else
				{
					return null;
				}				
			}
			else
			{
				if ($instance === true)
				{
					$this->modules[$classVariable] = new $className ();
					return $this->modules[$classVariable];
				}
				else
				{
					return null;
				}
			}
		}
	}
	
	public final function View ($view, $data = array (), $return = false)
	{
		$templateModule = $this->_getModuleKey ('Template');
		$languageModule = $this->_getModuleKey ('Language');
		
		// We use template loading system if we have it
		if ($templateModule !== false)
		{
			// Language check
			if ($languageModule !== false)
			{
				$data = array_merge ($data, $this->Load ($languageModule)->Get ());
			}
			
			return ($this->Load ($templateModule)->Display ($view, $data, $return) );
		}
		else
		{
			ob_start();
			
			if (file_exists ($this->config->Get ('application_dir') . '/view/' . $view . '.html') === true)
			{
				$path = $this->config->Get ('application_dir') . '/view/' . $view . '.html';
			}
			else if (file_exists ($this->config->Get ('application_dir') . '/view/' . $view . EXTENSION) === true)
			{
				$path = $this->config->Get ('application_dir') . '/view/' . $view . EXTENSION;
			}
			else
			{
				return false;
			}

			extract ($data);

			// Fixes short open tags <?=, if server does not support it
			if (@ini_get ('short_open_tag') === false)
			{
				echo eval ('?>' . preg_replace("/;*\s*\?>/", "; ?>", str_replace ('<?=', '<?php echo ', file_get_contents ($path))));
			}
			else
			{
				include ($path);
			}
			
			$buffer = ob_get_contents ();
			@ob_end_clean ();
			
			// Language auto-replace
			if ($languageModule !== false)
			{
				if ($this->modules[$languageModule]->IsReady () === true)
				{
					$buffer = $this->modules[$languageModule]->Replace ($buffer);
				}
			}
			
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
	
	//
	// Function to end system execution
	//
	
	public function Destroy ()
	{
		die ();
	}
	
	//
	// Singleton function
	//
	
	public static function &GetInstance ()
	{
		return self::$instance;
	}
	
	// Returns  config variables
	public function GetConfig ($var)
	{
		return ($this->config->Get ($var));
	}
	
		
	private function _loadController ($controller)
	{
		// Load controller
		if ( (file_exists ($this->config->Get ('application_dir') . '/controller/' . strtolower ($controller) . EXTENSION) === true) && (class_exists ($controller) === false) )
		{
			include_once ($this->config->Get ('application_dir') . '/controller/' . strtolower ($controller) . EXTENSION);
		}
		
		// Create controller and return it
		if (class_exists ($controller) === true)
		{
			$return = new $controller();
			$return->Setup (&$this->modules, &$this->config);
			
			return $return;
		}
		else
		{
			return null;
		}
	}
	
		
	// Returns module key of desired class - used for template
	private function _getModuleKey ($className)
	{
		if (isset ($this->modules[$className]) === true)
		{
			if (get_class ($this->modules[$className]) == $className)
			{
				return $className;
			}
			else
			{
				foreach ($this->modules as $key => $value)
				{
					if (get_class ($this->modules[$key]) == $className)
					{
						return $key;
					}
				}
				
				return false;			
			}
		}
		else
		{
			return false;
		}
	}
}
?>