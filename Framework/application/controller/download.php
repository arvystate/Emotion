<?php

class Download extends EmotionController
{
	public function index ()
	{
		$this->View ('header');

		$this->View ('download');

		$this->View ('footer');
	}

	public function release ($type = 'full')
	{
		switch ($type)
		{
			case 'database':
				$path = 'release/EmotionEngine_' . EMOTION_VERSION . '_database.zip';
				break;
			case 'library':
				$path = 'release/EmotionEngine_' . EMOTION_VERSION . '_library.zip';
				break;
			case 'system':
				$path = 'release/EmotionEngine_' . EMOTION_VERSION . '_system.zip';
				break;
			default:
				$path = 'release/EmotionEngine_' . EMOTION_VERSION . '.zip';

				$collection = $this->Load ('Database')->Select ("collections");

				$this->Load ('Database')->Update ("collections", "collection_id = '{$collection[0]['collection_id']}'", "downloads = downloads + 1");
				break;

		}

		$this->Load ('Common')->Download ($path);
	}

	public function package ($download = '')
	{
		/*$compiler = new EmotionCompiler ();
		$compiler->CreateFile ('test.zip', '1:1:;;1;mssql-mysql:Hehheh-Tetttat:application_dir = "dfgggg":1;0:1:1:1:1:1:0:0:0:php');

		return;*/

		$ipAddress = $this->Load ('Session')->IpAddress ();
		$packAddress = $ipAddress . ';' . $this->Load ('Session')->Get ('session_id');

		$time = time ();

		$collection = $this->Load ('Database')->Select ("collections");
		$collection = $collection[0];

		$errorMsg = array ();

		// Get the package information
		$package = $this->Load ('Database')->Select ("packages", "user LIKE '{$ipAddress}%' ORDER BY dw_timestamp DESC, timestamp DESC LIMIT 1");

		if (isset ($package[0]) === true)
		{
			// Finished check
			if ($package[0]['step'] != 6)
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
			// Package checks
			else if ($package[0]['timestamp'] < ($time - 3600) )
			{
				echo ('<h3>Your package has expired, you can no longer download it.</h3>');
			}
			else if ($package[0]['downloads'] >= 5)
			{
				echo ('<h3>You have exceeded your package download limit.</h3>');
			}

			// User requested the file itself!
			else if ($download == 'custom')
			{
				if ( ($package[0]['ready'] == 1) && ($package[0]['link'] != '') )
				{
					// Update downloads
					$update = "downloads = downloads + 1";

					// Update download time, if it was not yet
					if ($package[0]['dw_timestamp'] == 0)
					{
						$update .= ", dw_timestamp = '{$time}'";

						$package[0]['dw_timestamp'] = $time;

						// Send email...
						$email = substr ($package[0]['data'], 0, strpos ($package[0]['data'], ';'));
						$email = substr ($email, strrpos ($email, ':') + 1);
					}


					$this->Load ('Database')->Update ("packages", "package_id = '{$package[0]['package_id']}'", $update);
					$this->Load ('Database')->Update ("collections", "name='Emotion Engine'", "packages = packages + 1");

					// Send file to browser

					$this->Load ('Common')->Download ('temp/' . $package[0]['link'], 'EmotionEngine_' . EMOTION_VERSION . '_' . date ("j.M.Y", $package[0]['dw_timestamp']) . '.zip');
				}
				else
				{
					echo ('<h3>Your package is not ready yet.</h3>');
				}
			}
			// Just a getter for package status
			else
			{
				if ( ($package[0]['ready'] == 1) && ($package[0]['link'] != '') )
				{
					echo ('<h3><img src="/application/img/archive.png" /> <a href="/download/package/custom/">Emotion Engine ' . EMOTION_VERSION . ' Custom Package (.zip)</a></h3>');
				}
				else
				{
					// Start to generate a package, if we do not have it generated yet
					if ($package[0]['link'] == '')
					{
						// Create fake filename
						$link = $this->Load ('Text')->Random ('alnum', 25);

						while (file_exists ('temp/' . $link) === true)
						{
							$link = $this->Load ('Text')->Random ('alnum', 25);
						}

						$this->Load ('Database')->Update ("packages", "package_id = '{$package[0]['package_id']}'", "link = '{$link}'");

						$compiler = new EmotionCompiler ();
						$compiler->CreateFile ($link, $package[0]['data']);

						$this->Load ('Database')->Update ("packages", "package_id = '{$package[0]['package_id']}'", "ready = '1'");
					}

					echo ('<h3>Your download link is currently being generated.</h3>');
				}
			}
		}
		else
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
	}
}

?>