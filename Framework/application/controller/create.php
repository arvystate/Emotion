<?php

class Create extends EmotionController
{
	public function index ()
	{
		$this->View ('header_no_menu');
		
		$this->View ('create');
		
		$this->View ('footer');
	}
	
	public function wizard ($mode = '')
	{
		// Do information collection here.
		
		// Get stuff from database.
		
		$parser = new EmotionParser ();
		
		$ipAddress = $this->Load ('Session')->IpAddress ();
		$packAddress = $ipAddress . ';' . $this->Load ('Session')->Get ('session_id');
		
		$time = time ();
		
		$collection = $this->Load ('Database')->Select ("collections");
		$collection = $collection[0];
		
		$step = -1;
		
		$errorMsg = array ();
		
		// Check package based on client IP - last used
		$package = $this->Load ('Database')->Select ("packages", "user LIKE '{$ipAddress}%' ORDER BY dw_timestamp DESC, timestamp DESC LIMIT 1");
		
		// If we have it, we check if the package was created 24-hours ago
		if (isset ($package) === true)
		{
			// Package from other IP was set more than 24-hours ago, so the package is irrelevant
			if ( ($package[0]['dw_timestamp'] < ($time - 86400) ) && ($package[0]['dw_timestamp'] != 0) )
			{
				unset ($package);
			}
			// Package was not completed before browser was closed, start new one., since in last 24 hours it was not downloaded
			else if ($package[0]['timestamp'] < ($time - 3600) )
			{
				unset ($package);
			}
			// Check if sessionID matches
			else if ($package[0]['user'] != $packAddress)
			{
				unset ($package);
			}
		}
		
		// We handle the current package, meaning we have used the system in last 24-hours
		if (isset ($package) === true)
		{
			$package = $package[0];
			
			// We downloaded the package, but less than 1 hour ago, so we check if we allow to download it again (5 time limit)
			if ( ($package['dw_timestamp'] > ($time - 3600) ) && ($mode != 'download') && ($mode != 'review') )
			{
				$this->Load ('Common')->Redirect ('/create/wizard/download/');
			}
			// We did not download the package yet, now the big shit, we are in middle of creating...
			else if ($package['timestamp'] > ($time - 3600) )
			{
				//
				// Parsing data stored in database
				//
				
				// Handle data, if it is not created
				$data = explode (';', $package['data']);
				
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
				
				$stepInfo = $this->Load ('Security')->Post ('step_info');
				$updateData = $this->Load ('Security')->Post ('update_data');
				$update = false;
				
				//
				// Form was sent, we shall do something with our data
				//
				
				if (isset ($stepInfo) === true)
				{
					switch ($stepInfo)
					{
						case 1:
							$data[1][0] = intval ($this->Load ('Security')->Post ('license'));
							
							if ($data[1][0] == 0)
							{
								$errorMsg['license_error'] = true;
							}
							
							break;
							
						case 2:
							// Beginner mode is simple, we just choose the package
							if ($data[0][0] == 0)
							{
								if ( (count ($data[2]) > 0) && (is_numeric ($data[2][0]) === false) )
								{
									array_unshift ($data[2], intval ($this->Load ('Security')->Post ('website')) );
								}
								else
								{
									$data[2][0] = intval ($this->Load ('Security')->Post ('website'));
								}
							}
							// Advanced mode
							else
							{								
								if (is_numeric ($data[2][0]) === true)
								{
									$beginnerVal = intval ($data[2][0]);
								}
								
								$counter = 0;
								$data[2] = array ();
											
								// Get which classes user wants
								foreach ($this->Load ('Security')->Post () as $key => $value)
								{
									// Check if it starts with lib
									if (strpos ($key, 'lib_') === 0)
									{
										$data[2][$counter] = substr ($key, 4);
										$counter++;
									}
								}
								
								// Remember beginner setting!
								if (isset ($beginnerVal) === true)
								{
									array_unshift ($data[2], $beginnerVal);
								}
							}
							
							break;
							
						case 3:
							$counter = 0;
							
							if ( (!is_numeric ($data[2][0])) || ($data[2][0] > 0) )
							{
								$data[3][0] = '';
								
								// Get which DB user wants
								foreach ($this->Load ('Security')->Post () as $key => $value)
								{
									// Check if it starts with lib
									if (strpos ($key, 'db_') === 0)
									{
										$data[3][0] = $data[3][0] . '-' . substr ($key, 3);
										$counter++;
									}
								}
								// Cut away first minus
								$data[3][0] = substr ($data[3][0], 1);
							}
							
							// Then we store sample textbox for procedural
							
							$data[3][1] = $parser->ParseFiles ($this->Load ('Security')->Post ('access_points'));
														
							// And last the configuration
							$data[3][2] = $parser->ParseConfig ($this->Load ('Security')->Post ('config'));
							$data[3][3] = intval ($this->Load ('Security')->Post ('separate_config'));
						
							break;
						case 4:
							$data[4][0] = intval ($this->Load ('Security')->Post ('opt_debug'));	
							$data[4][1] = intval ($this->Load ('Security')->Post ('opt_htaccess'));
							
							$data[4][2] = intval ($this->Load ('Security')->Post ('sample_dirs'));
							$data[4][3] = intval ($this->Load ('Security')->Post ('sample_loaders'));
							$data[4][4] = intval ($this->Load ('Security')->Post ('sample_controllers'));
							$data[4][5] = intval ($this->Load ('Security')->Post ('sample_views'));
							
							$data[4][6] = intval ($this->Load ('Security')->Post ('opt_comment'));
							$data[4][7] = intval ($this->Load ('Security')->Post ('opt_shrink'));
							$data[4][8] = intval ($this->Load ('Security')->Post ('opt_compile'));
							
							$data[4][9] = $parser->ParseExtension ($this->Load ('Security')->Post ('opt_extension'));
						
							break;
						case 5:
							if ($package['step'] == 6)
							{
								$this->Load ('Common')->Redirect ('/create/wizard/download/');
							}
						
							break;

						// General form
						default:
							$data[0][0] = $this->Load ('Security')->Post ('user_mode');
							$data[0][1] = $this->Load ('Security')->Post ('architecture');
							$data[0][2] = $this->Load ('Security')->Post ('email');
							
							if ( ($this->Load ('Common')->VerifyEmail ($data[0][2]) === false) && (empty ($data[0][2]) === false) )
							{
								$errorMsg['email_error'] = true;
							}

							// Update form!
							if (intval ($data[0][0]) == 2)
							{
								$this->Load ('Common')->Redirect ('/create/wizard/update/');
							}

							break;
					}
				}
				//
				// Update stuff
				//
				else if (isset ($updateData) === true)
				{
					$rsa = new Crypt_RSA ();
					$rsa->setEncryptionMode (CRYPT_RSA_ENCRYPTION_PKCS1);
					
					$rsa->loadKey ('MIICXAIBAAKBgQCLpdkvl0NOk87U1qEI9oPgUiBTdFJxyUkNcSFTwHRx91QewojYHTLVjkqaw/ibe6kbrIn77lccqTZ7jvOXNJQ/egeWqvfQAleawQ6GTzLqjUtgMJ+3DkL8uDW3x4jYPrpmKsFhrr/2H+pEag/QUteu8sqPYuMqftyAUbkGDk6C4QIDAQABAoGAE0Frxct+X71dzaydSSvEr2UOSV5GvU3bTDEx6Tx7p031RBs1h1HaCpGzk8zl4M/tAiszs5CqY6ypczbDkTE80ktmrOz4DfNakWCGC7f1RQgF3Y3u9bxMnEohsTaVKqXE39n1QW0Q9ZUH/715f4gGDwLAlw17Vn7cWgmyhVLGiIkCQQDzNk5c3vdOCykg+8cIuqeTbuPMY4xKcY0IBzTB/g6t5UqbYvqv8FGv8WE09z8fXQ8ym7M5pas375EaHdwbONRHAkEAkv2KwZOBCIP8XmxCkh45CwqBFLqszBlpwYAstGp570auF4sapiFc1zvkZKwrs8iJjjjRhrcHg+JtX9rzaGPLlwJALlcPkURubHFV8dHnN3OREFdVkhD5nwmJrJjq5XeJxnpkv7yZNUKd3d8o/VWKsmghyCvAd2BogizkQcykro8iawJAFKlG48bUEFpdEvisstVEt2SWmE9VEqtPzj8yCXoeAaAxwGVxLYDrB+YHSI9a5T5+91rNON5pVARz01F3fWjpqQJBAMBdVKSkSSZ8t7BYI1Z4OPxZgt2TY4SZm23wy97oljsqyYicOTxBeMhgwqZ4U9usfFT07LXKpCCfRC7bH5D/LtA=', CRYPT_RSA_PRIVATE_FORMAT_PKCS1);
		
					// Decrypt using private key
					$data = $rsa->decrypt ( base64_decode ($updateData) );
									
					// Errorneous data, nothing!
					if (count (explode (';', $data)) != 5)
					{
						
						$update = true;
						
						$errorMsg['update_error'] = true;
					}
					else
					{
						// Update settings
						$this->Load ('Database')->Update ("packages", "package_id = '{$package['package_id']}'", "data = '{$data}', timestamp = '{$time}', step = '5'");
						
						$this->Load ('Common')->Redirect ('/create/wizard/review/');
					}
				}
				
				if ($update === false)
				{
					//
					// Store data into database
					//
					
					// Create data string
					$dataString = '';
					
					foreach ($data as $steps)
					{
						$dataString .= implode (':', $steps) . ';';
					}
					
					$dataString = substr ($dataString, 0, -1);
	
					// We have the last step stored, we have to check, if user is allowed to see requested step
					$urls = array ('general', 'license', 'feature', 'config', 'options', 'review', 'download');
					
					$nextPage = false;
					$step = $package['step'];
					
					// Move to next package, otherwise save
					if ( ($package['step'] == $this->Load ('Security')->Post ('step_info')) && (count ($errorMsg) === 0) )
					{
						$package['step'] += 1;
						$nextPage = true;
					}
					
					// Write into database
					$this->Load ('Database')->Update ("packages", "package_id = '{$package['package_id']}'", "data = '{$dataString}', timestamp = '{$time}', step = '{$package['step']}'");
					
					//
					// Routing user if necessary
					//
					
					// We are going to redirect to next page and we will take care of it all later, or
					// user was playing with our routes, so we will redirect him back to correct place :)
					if ( ($nextPage === true) || ($mode == '') )
					{
						$this->Load ('Common')->Redirect ('/create/wizard/' . $urls[$package['step']] . '/');
					}
	
					// Also another check, if the user is allowed to be on the current step
					
					$index = count ($urls);
					
					// Find which step was requested by user
					for ($i = 0; $i < count ($urls); $i++)
					{
						if ($urls[$i] == $mode)
						{
							$index = $i;
							break;
						}
					}
					
					// Check if user is allowed on step he requested
					if ($index <= $package['step'])
					{
						// We do not allow other pages because we dont want to recreate
						if ( ($package['step'] == 6) && ($index < 5) )
						{
							$this->Load ('Common')->Redirect ('/create/wizard/review/');
						}
						else
						{
							$mode = $urls[$index];
						}
					}
					// User is not allowed on current step, push him back
					else if ($mode != 'update')
					{
						$this->Load ('Common')->Redirect ('/create/wizard/' . $urls[$package['step']]);
					}
					
					//
					// After routing events (error-checks)
					//
					
					switch ($mode)
					{					
						case 'general':
						
							if ( (empty ($data[0][2]) === false) && ($this->Load ('Common')->VerifyEmail ($data[0][2]) === false) )
							{
								$errorMsg['email_error'] = true;
							}
							
							break;
						case 'options':
							$data[4][9] = $parser->ParseExtension ($this->Load ('Security')->Post ('opt_extension'));
						
							break;
					}
				}				
			}
			else if ($mode != 'unavailible')
			{
				$this->Load ('Common')->Redirect ('/create/wizard/unavailible/');
			}
			
			// If those two are not ok, it is automatically in 24-hour period and access is denied
			
		}
		//
		// No package, create and redirect user
		//
		
		else
		{
			// Package was downloaded more than 24-hours ago, we create a new package. Doesnt matter if its the same session.
			$this->Load ('Database')->Insert ("packages", "collection_id, user, step, data, dw_timestamp, downloads, link, timestamp", "'{$collection['collection_id']}', '{$packAddress}', 0, '', 0, 0, '', '{$time}'");
			
			$this->Load ('Common')->Redirect ('/create/wizard/general/');
		}

		$this->View ('header_no_menu');
		
		if ($mode != 'unavailible')
		{
			$this->View ('wizard_step_' . $mode, array ('data' => $data, 'msg' => $errorMsg, 'step' => $step));
			$this->View ('wizard', array ('mode' => $mode, 'step' => $step));
		}
		else
		{
			$this->View ('wizard_unavailible');
		}
		
		$this->View ('footer');
	}
}

?>