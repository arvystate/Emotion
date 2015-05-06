<?php

class AppLoader extends EmotionLoader
{
	protected function CustomLoad ()
	{
		// Session init
		$this->Load ('Session')->Initialize ();
		
		$this->Load ('Debug')->SaveMessage ('Reading session data...');
		
		if (!$this->Load ('Session')->SessionRead ())
		{
			$this->Load ('Debug')->SaveMessage ('Session not created, proceed with creation...');
			
			if ($this->Load ('Session')->SessionCreate ())
			{
				$this->Load ('Debug')->SaveMessage ('Session successfully created. SID: ' . $this->Load ('Session')->Get ('session_id'));
			}
			else
			{
				$this->Load ('Debug')->SaveMessage ('Error creating session.');
			}
		}
		else
		{
			$this->Load ('Debug')->SaveMessage ('Session successfully read. SID: ' . $this->Load ('Session')->Get ('session_id'));
		}
		
		$this->Load ('Security')->SecureGlobals ();
		
		// Load user classes
		$this->Load ('Common')->LoadClasses ('application/lib');
		
		$this->Load ('Debug')->SaveMessage ('Loading database.');
		
		$this->Load ('Database')->PrepareDriver ('MySQL');
		
		$this->Load ('Debug')->SaveMessage ('Driver status: ' . $this->Load ('Database')->DriverStatus ());
		$this->Load ('Debug')->SaveMessage ('Connecting to database.');
		
		$this->Load ('Database')->SetPrefix ($this->Config ('database_prefix'));
		
		if ($this->Load ('Database')->Connect ($this->Config ('database_host'), $this->Config ('database_user'), $this->Config ('database_pass'), $this->Config ('database_name')))
		{
			$this->Load ('Debug')->SaveMessage ('Connection successful.');
			
			$this->Load ('Debug')->SaveMessage ('Connection status: ' . $this->Load ('Database')->DriverStatus ());
		
			//
			// Checking framework status.. -> Self-check
			//
			
			$this->Load ('Debug')->SaveMessage ('Checking framework file structure.');
			
			// Find collection
			$collections = $this->Load ('Database')->Select ("collections");
			
			if (count ($collections) == 0)
			{
				$this->Load ('Debug')->SaveMessage ('No collection found -> creating default EE collection, version: ' . EMOTION_VERSION);
				
				// We do not know download link yet
				$this->Load ('Database')->Insert ("collections", "name, version, revision, timestamp, downloads, packages", "'Emotion Engine', '" . EMOTION_VERSION . "', 0, " . time () . ", 0, 0");
				
				$this->Load ('Debug')->SaveMessage ('Default collection created.');
				
				// Get collections again
				$collections = $this->Load ('Database')->Select ("collections");
			}
			
			$this->Load ('Debug')->SaveMessage ('Scanning collection files. Process may take a while.');
			
			$files = $this->Load ('Database')->Select ("files", "collection_id = '{$collections[0]['collection_id']}'");
			
			// Construct local fileBase
			$localScanner =  new EmotionStructure ();
			
			$fileBase = $localScanner->GetFiles ();
			
			//
			// Db status:
			// 0 - new file
			// -1 - file found
			// array - details of file to be updated
			//
			
			for ($i = 0; $i < count ($files); $i++)
			{
				// Search array for the key
				if (array_key_exists ($files[$i]['filename'], $fileBase) === true)
				{
					// Check for hash comparison
					if ($files[$i]['hash'] === $fileBase[$files[$i]['filename']]['hash'])
					{
						$fileBase[$files[$i]['filename']]['db_status'] = -1;
					}
					else
					{
						$fileBase[$files[$i]['filename']]['db_status'] = $files[$i]['file_id'];
					}
				}
			}
			
			$update = false;
			
			// Commence file changes in database
			foreach ($fileBase as $key => $value)
			{
				// Insertion
				if ($value['db_status'] === 0)
				{
					$this->Load ('Database')->Insert ("files", "filename, path, version, revision, hash, timestamp, collection_id", "'{$key}', '{$value['path']}', '" . EMOTION_VERSION . "', 1, '{$value['hash']}', '{$value['modified']}', '{$collections[0]['collection_id']}'");
				
					$update = true;
					
					$this->Load ('Debug')->SaveMessage ('Inserted file: ' . $key);
				}
				else if ($value['db_status'] !== -1)
				{
					//$revision = intval ($value['db_status']['revision']) + 1;
					$this->Load ('Database')->Update ("files", "file_id='{$value['db_status']}'", "version = '" . EMOTION_VERSION . "', revision = revision + 1, hash = '{$value['hash']}', timestamp = '{$value['modified']}'");
				
					$update = true;
					
					$this->Load ('Debug')->SaveMessage ('Updated file: ' . $key . ' File ID: ' . $value['db_status']);
				}
			}
			
			// Something was updated, need to update collections
			if ($update === true)
			{
				$tempName = str_replace (' ', '', $collections[0]['name']);
				$tempName = $tempName . '_' . EMOTION_VERSION;
				
				// Cleaning up old files
				if (is_file ('release/' . $tempName . '.zip') === true)
				{
					unlink ('release/' . $tempName . '.zip');
				}
				
				if (is_file ('release/' . $tempName . '_database.zip') === true)
				{
					unlink ('release/' . $tempName . '_database.zip');
				}
				
				if (is_file ('release/' . $tempName . '_library.zip') === true)
				{
					unlink ('release/' . $tempName . '_library.zip');
				}
				
				if (is_file ('release/' . $tempName . '_system.zip') === true)
				{
					unlink ('release/' . $tempName . '_system.zip');
				}
				
				// Creating release ZIP files with php built in ZIP
				if (class_exists ('ZipArchive') === true)
				{
					$this->Load ('Debug')->SaveMessage ('Recreating download Zip archives using PHP ZipArchive system (faster) ...');
					
					//
					// Create release files
					//
					
					$zipMain = new ZipArchive ();
					$zipDatabase = new ZipArchive ();
					$zipLibrary = new ZipArchive ();
					$zipSystem = new ZipArchive ();
					
					// Opening zip files
					$opened = $zipMain->open ('release/' . $tempName . '.zip', ZIPARCHIVE::CREATE) && $zipDatabase->open ('release/' . $tempName . '_database.zip', ZIPARCHIVE::CREATE) && $zipLibrary->open ('release/' . $tempName . '_library.zip', ZIPARCHIVE::CREATE) && $zipSystem->open ('release/' . $tempName . '_system.zip', ZIPARCHIVE::CREATE);
					
					// Attempt to open new archives
					if ($opened === true)
					{
						$this->Load ('Debug')->SaveMessage ('Opened archive files.');
						
						foreach ($fileBase as $key => $value)
						{
							// Main release file
							$zipMain->addFile ($value['path'], $value['path']);
							
							// Database release file
							if ( ($this->Load ('Text')->StartsWith ($value['path'], 'database/') === true) || ($this->Load ('Text')->StartsWith ($key, 'database.') === true) )
							{
								$zipDatabase->addFile ($value['path'], $value['path']);
							}
							
							// Library release file
							if ($this->Load ('Text')->StartsWith ($value['path'], 'library/') === true)
							{
								$zipLibrary->addFile ($value['path'], $value['path']);
							}
							
							// System release file
							if ($this->Load ('Text')->StartsWith ($value['path'], 'system/') === true)
							{
								$zipSystem->addFile ($value['path'], $value['path']);
							}
						}
					}
					else
					{
						$this->Load ('Debug')->SaveMessage ('Unable to create archive files.');
					}
					
					$zipMain->close ();
					$zipDatabase->close ();
					$zipLibrary->close ();
					$zipSystem->close ();
				}
				else
				{
					$this->Load ('Debug')->SaveMessage ('Recreating download Zip archives using Emotion Engine system...');
					
					$this->Load ('zipMain', 'Zip');
					$this->Load ('zipDatabase', 'Zip');
					$this->Load ('zipLibrary', 'Zip');
					$this->Load ('zipSystem', 'Zip');
					
					$this->Load ('zipMain')->AddDirectory ('database');
					$this->Load ('zipMain')->AddDirectory ('library');
					$this->Load ('zipMain')->AddDirectory ('system');
					
					$this->Load ('zipDatabase')->AddDirectory ('database');
					$this->Load ('zipDatabase')->AddDirectory ('library');
					
					$this->Load ('zipLibrary')->AddDirectory ('library');
					
					$this->Load ('zipSystem')->AddDirectory ('system');
					
					foreach ($fileBase as $key => $value)
					{
						// Main release file
						$this->Load ('zipMain')->AddFile ($key, pathinfo ($value['path'], PATHINFO_DIRNAME) );
						
						// Database release file
						if ( ($this->Load ('Text')->StartsWith ($value['path'], 'database/') === true) || ($this->Load ('Text')->StartsWith ($key, 'database.') === true) )
						{
							$this->Load ('zipDatabase')->AddFile ($key, pathinfo ($value['path'], PATHINFO_DIRNAME) );
						}
						
						// Library release file
						if ($this->Load ('Text')->StartsWith ($value['path'], 'library/') === true)
						{
							$this->Load ('zipLibrary')->AddFile ($key, pathinfo ($value['path'], PATHINFO_DIRNAME) );
						}
						
						// System release file
						if ($this->Load ('Text')->StartsWith ($value['path'], 'system/') === true)
						{
							$this->Load ('zipSystem')->AddFile ($key, pathinfo ($value['path'], PATHINFO_DIRNAME) );
						}
					}
					
					$this->Load ('zipMain')->Save ('release/' . $tempName . '.zip');
					$this->Load ('zipDatabase')->Save ('release/' . $tempName . '_database.zip');
					$this->Load ('zipLibrary')->Save ('release/' . $tempName . '_library.zip');
					$this->Load ('zipSystem')->Save ('release/' . $tempName . '_system.zip');
				}
				
				$revision = intval ($collections[0]['revision']) + 1;
				$this->Load ('Database')->Update ("collections", "collection_id = '{$collections[0]['collection_id']}'", "version = '" . EMOTION_VERSION . "', revision = '{$revision}', timestamp = '" . time () . "'");
				$this->Load ('Debug')->SaveMessage ('Updated collection. Version: ' . EMOTION_VERSION . ' - ' . date ('M j, Y') . ' Revision: ' . $revision);
			}
			else
			{
				$this->Load ('Debug')->SaveMessage ('Nothing new to update.');
			}
		}
		else
		{
			$error = $this->Load ('Database')->GetErrorList ();
			$this->Load ('Debug')->SaveMessage ('Connection unsuccessful: ' . print_r ($error, true) );
		}
	}
}