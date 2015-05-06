<?php

class Sample extends EmotionController
{
	public function index ()
	{
		$this->View ('404');
	}
	
	public function show ($sample = '')
	{
		$sample = str_replace ('.', '', $sample);
		
		$sample = explode ('_', $sample);
		
		$this->View ('header_no_menu');
		
		echo ('<div id="main_content">
					<div id="content">
						<div class="front_page">');
		
		if (substr ($sample[0], -1) != 's')
		{
			$sample[0] .= 's';
		}		
		
		if (file_exists ('application/sample/' . $sample[0] . '/' . $sample[1] . '_' . $sample[2] . '.php') === true)
		{
			$name = array_slice ($sample, 1);
			$name = ucwords (trim (implode (' ', $name) ) );
			
			$type = ucwords (substr ($sample[0], 0, -1));
			
			echo ('<h1>' . $name . ' ' . $type . '</h1>');
			echo ('<h4>To present the way current sample works, it was executed.</h4>');
			
			echo ('<code>');
			
			$this->run (implode ('_', $sample));
			
			echo ('</code>');
			
			echo ('<br /><br />');
			echo ('<h4>' . $type . ' sample source code is displayed below.</h4>');
			
			highlight_file ('application/sample/' . $sample[0] . '/' . $sample[1] . '_' . $sample[2] . '.php');
		}
		else
		{
			echo ('<h1>Sample not found</h1><h4>Apparently you are searching for samples. But, the one you want was either removed or is no longer availible. Try again.</h4>');
		}
		
		echo ('</div><!-- end .front_page -->
					</div><!-- end #content -->
				</div><!-- end #main_content -->');
		
		$this->View ('footer');
	}
	
	public function run ($sample = '')
	{
		$sample = str_replace ('.', '', $sample);
		
		$sample = explode ('_', $sample);

		if (substr ($sample[0], -1) != 's')
		{
			$sample[0] .= 's';
		}		
		
		if (file_exists ('application/sample/' . $sample[0] . '/' . $sample[1] . '_' . $sample[2] . '.php') === true)
		{
			// Include the stuff then virtualize routing stuff
			
			include_once ('application/sample/' . $sample[0] . '/' . $sample[1] . '_' . $sample[2] . '.php');
			
			$sampleName = array_slice ($sample, 1);
			$sampleName = ucwords (trim (implode ('_', $sampleName) ) );
			
			// Sandboxing the sample load
			if ($sample[0] == 'loaders')
			{
				$loader = new $sampleName ();
				$loader->StartLoad (array (), new EmotionConfiguration ());
			}
			else
			{
				$controller = new $sampleName ();
			
				$controller->Setup (array (), new EmotionConfiguration ());
				
				$routes = explode ('/', $this->Load ('Security')->Get ('q'));
			
				try
				{
					$class = $routes[2];
					
					if (count ($routes) > 1)
					{
						$function = $routes[3];
					}
					else
					{
						$function = '';
					}
					
					$parameters = array ();
					
					// Put other parameters into array
					for ($i = 4, $x = 0; $i < count ($routes); $i++, $x++)
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
				
				// Check for class and function
				if (isset ($controller) === true)
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
						$this->error ();
					}
				}
				else
				{
					$this->error ();
				}
			}
		}
		else
		{
			$this->error ();
		}
	}
	
	public function error ()
	{
		$this->View ('header_no_menu');
		
		echo ('<div id="main_content">
					<div id="content">
						<div class="front_page">');
		
		echo ('<h1>Sample not found</h1><h4>Apparently you are searching for samples. But, the one you want was either removed or is no longer availible. Try again.</h4>');
		
		echo ('</div><!-- end .front_page -->
				</div><!-- end #content -->
			</div><!-- end #main_content -->');
	
		$this->View ('footer');
	}
}

?>