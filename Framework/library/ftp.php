<?php
/***************************************************************************
 *   Application          : Emotion                                        *
 *   -------------------------------------------------------------------   *
 *   Package              : @library                                       *
 *   File                 : ftp.php                                        *
 *   Version              : 1.0.0                                          *
 *   Status               : Complete, Not tested                           *
 *   -------------------------------------------------------------------   *
 *   Begin                : Thursday, Aug 11, 2011                         *
 *   CopyRight            : (C) 2011 ArvYStaTe.net Team                    *
 *   E-Mail               : support@arvystate.net                          *
 *   Last edit            : Thursday, Aug 11, 2011                         *
 *                                                                         *
 *                                                                         *
 ***************************************************************************
 
 ***************************************************************************
 *   File description                                                      *
 *   -------------------------------------------------------------------   *
 *   FTP Server class, contains basic functionality to work with FTP       *
 *   servers, such as downloading and uploading files.                     *
 *   -------------------------------------------------------------------   *
 *                                                                         *
 ***************************************************************************
 
 ***************************************************************************
 *   Change log                                                            *
 *   -------------------------------------------------------------------   *
 *    + [08/11/11] - File created                                          *
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
 ****************************************************************************/

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
 * Ftp
 *
 * Basic FTP client functionality, to be used for file uploads and transfers.
 *
 * @package	library
 * @require	false
 * @author	ExpressionEngine Dev Team
 **/

class Ftp
{
	private $hostname	= '';
	private $username	= '';
	private $password	= '';
	private $port		= 21;
	private $passive	= true;
	private $connId		= false;

	/**
	 * Initialize preferences
	 *
	 * @access	private
	 * @param	array	config variables
	 * @return	void
	 **/
	 
	private function Initialize ($config = array())
	{
		foreach ($config as $key => $val)
		{
			if (isset ($this->$key))
			{
				$this->$key = $val;
			}
		}

		// Prep the hostname
		$this->hostname = preg_replace ('|.+?://|', '', $this->hostname);
	}

	/**
	 * FTP Connect
	 *
	 * @access	public
	 * @param	array	the connection values
	 * @return	bool	returns true if connection was successful
	 **/
	
	public function Connect ($config = array())
	{
		if (count ($config) > 0)
		{
			$this->Initialize ($config);
		}

		if ( ($this->connId = @ftp_connect ($this->hostname, $this->port)) === false)
		{
			return false;
		}

		if (!$this->_login())
		{
			return false;
		}

		// Set passive mode if needed
		if ($this->passive == true)
		{
			ftp_pasv ($this->connId, true);
		}

		return true;
	}
	
	/**
	 * Close the connection
	 *
	 * @access	public
	 * @return	bool	returns true if closing was successful
	 **/
	
	public function Close ()
	{
		if (!$this->IsConnected())
		{
			return false;
		}
		else
		{
			return @ftp_close ($this->connId);
		}
	}

	/**
	 * FTP Login
	 *
	 * @access	private
	 * @return	bool	returns true if login was successful
	 **/
	
	private function _login ()
	{
		return @ftp_login ($this->connId, $this->username, $this->password);
	}

	/**
	 * Validates the connection ID
	 *
	 * @access	public
	 * @return	bool	returns true if it is connected
	 **/
	
	public function IsConnected ()
	{
		if (!is_resource ($this->connId))
		{
			return false;
		}
		
		return true;
	}

	/**
	 * Change directory
	 *
	 * The second parameter lets us momentarily turn off debugging so that
	 * this function can be used to test for the existence of a folder
	 * without throwing an error.  There's no FTP equivalent to is_dir()
	 * so we do it by trying to change to a particular directory.
	 * Internally, this parameter is only used by the "mirror" function below.
	 *
	 * @access	public
	 * @param	string	path to change directory to
	 * @return	bool
	 **/
	 
	public function ChangeDir ($path = '')
	{
		if ( ($path == '') || (!$this->IsConnected()) )
		{
			return false;
		}

		$result = @ftp_chdir ($this->connId, $path);

		if ($result === false)
		{
			return false;
		}

		return true;
	}

	/**
	 * Create a directory
	 *
	 * @access	public
	 * @param	string	directory path
	 * @param	integer	directory permissions
	 * @return	bool
	 **/
	
	public function MakeDir ($path = '', $permissions = null)
	{
		if ( ($path == '') || (!$this->IsConnected()) )
		{
			return false;
		}

		$result = @ftp_mkdir ($this->connId, $path);

		if ($result === false)
		{
			return false;
		}

		// Set file permissions if needed
		if (!is_null ($permissions))
		{
			$this->Chmod ($path, (int)$permissions);
		}

		return true;
	}

	/**
	 * Upload a file to the server
	 *
	 * @access	public
	 * @param	string	local path to file
	 * @param	string	remote path to file
	 * @param	string	mode
	 * @param	integer	file permissions
	 * @return	bool	returns true, if upload was successful
	 **/
	
	public function Upload ($localPath, $remotePath, $mode = 'auto', $permissions = null)
	{
		if (!$this->IsConnected())
		{
			return false;
		}

		if (!file_exists ($localPath))
		{
			return false;
		}

		// Set the mode if not specified
		if ($mode == 'auto')
		{
			// Get the file extension so we can set the upload type
			$ext = $this->GetExtension ($locpath);
			$mode = $this->SetType ($ext);
		}

		$mode = ($mode == 'ascii') ? FTP_ASCII : FTP_BINARY;

		$result = @ftp_put ($this->connId, $remotePath, $localPath, $mode);

		if ($result === false)
		{
			return false;
		}

		// Set file permissions if needed
		if (!is_null ($permissions))
		{
			$this->Chmod ($remotePath, (int)$permissions);
		}

		return true;
	}

	/**
	 * Download a file from a remote server to the local server
	 *
	 * @access	public
	 * @param	string	remote path to file
	 * @param	string	local path to file
	 * @param	string	mode
	 * @return	bool	returns true, if download was successful
	 **/
	
	public function Download ($remotePath, $localPath, $mode = 'auto')
	{
		if (!$this->IsConnected())
		{
			return false;
		}

		// Set the mode if not specified
		if ($mode == 'auto')
		{
			// Get the file extension so we can set the upload type
			$ext = $this->GetExtension ($remotePath);
			$mode = $this->SetType ($ext);
		}

		$mode = ($mode == 'ascii') ? FTP_ASCII : FTP_BINARY;

		$result = @ftp_get ($this->connId, $localPath, $remotePath, $mode);

		if ($result === false)
		{
			return false;
		}

		return true;
	}

	/**
	 * Rename (or move) a file
	 *
	 * @access	public
	 * @param	string	old file
	 * @param	string	new file
	 * @return	bool	returns true if rename was a success
	 **/
	
	public function Rename ($oldFile, $newFile)
	{
		if (!$this->IsConnected())
		{
			return false;
		}

		$result = @ftp_rename ($this->connId, $oldFile, $newFile);

		if ($result === false)
		{
			return false;
		}

		return true;
	}

	/**
	 * Move a file
	 *
	 * @access	public
	 * @param	string	old file
	 * @param	string	new file
	 * @return	bool	returns true if move was a success
	 **/
	public function Move ($oldFile, $newFile)
	{
		return $this->Rename ($oldFile, $newFfile);
	}

	/**
	 * Delete file
	 *
	 * @access	public
	 * @param	string	file path
	 * @return	bool
	 **/
	
	public function DeleteFile ($filePath)
	{
		if (!$this->IsConnected())
		{
			return false;
		}
		
		$result = @ftp_delete ($this->connId, $filePath);

		if ($result === false)
		{
			return false;
		}

		return true;
	}

	/**
	 * Delete a folder and recursively delete everything (including sub-folders)
	 * containted within it.
	 *
	 * @access	public
	 * @param	string	directory path
	 * @return	bool	returns true on success
	 **/
	
	public function DeleteDir ($filePath)
	{
		if (!$this->IsConnected())
		{
			return false;
		}
		
		// Add a trailing slash to the file path if needed
		$filePath = preg_replace ("/(.+?)\/*$/", "\\1/",  $filePath);

		$list = $this->ListFiles ($filePath);

		if ( ($list !== false) && (count ($list) > 0) )
		{
			foreach ($list as $item)
			{
				// If we can't delete the item it's probaly a folder so
				// we'll recursively call delete_dir()
				if (!@ftp_delete ($this->connId, $item))
				{
					$this->DeleteDir ($item);
				}
			}
		}

		$result = @ftp_rmdir ($this->connId, $filePath);

		if ($result === false)
		{
			return false;
		}

		return true;
	}
	
	/**
	 * Set file permissions
	 *
	 * @access	public
	 * @param	string	the file path
	 * @param	string	the permissions
	 * @return	bool
	 **/
	
	public function Chmod ($path, $perm)
	{
		if (!$this->IsConnected())
		{
			return false;
		}

		// Permissions can only be set when running PHP 5
		if (!function_exists ('ftp_chmod'))
		{
			return false;
		}

		$result = @ftp_chmod ($this->connId, $perm, $path);

		if ($result === false)
		{
			return false;
		}

		return true;
	}

	/**
	 * FTP List files in the specified directory
	 *
	 * @access	public
	 * @return	array	list of files
	 **/
	 
	public function ListFiles ($path = '.')
	{
		if (!$this->IsConnected())
		{
			return false;
		}

		return ftp_nlist ($this->connId, $path);
	}

	/**
	 * Read a directory and recreate it remotely
	 *
	 * This function recursively reads a folder and everything it contains (including
	 * sub-folders) and creates a mirror via FTP based on it.  Whatever the directory structure
	 * of the original file path will be recreated on the server.
	 *
	 * @access	public
	 * @param	string	path to source with trailing slash
	 * @param	string	path to destination - include the base folder with trailing slash
	 * @return	bool	returns true on success
	 **/
	
	public function Mirror ($localPath, $remotePath)
	{
		if (!$this->IsConnected())
		{
			return false;
		}

		// Open the local file path
		if ($fp = @opendir ($localPath))
		{
			// Attempt to open the remote file path.
			if (!$this->ChangeDir ($remotePath))
			{
				// If it doesn't exist we'll attempt to create the direcotory
				if ( (!$this->MakeDir($rempath)) || (!$this->ChangeDir($remotePath)) )
				{
					return false;
				}
			}

			// Recursively read the local directory
			while (($file = readdir ($fp)) !== false)
			{
				if (@is_dir ($locaPath . $file) && substr ($file, 0, 1) != '.')
				{
					$this->Mirror ($localPath . $file . "/", $remotePath . $file . "/");
				}
				elseif (substr ($file, 0, 1) != ".")
				{
					// Get the file extension so we can se the upload type
					$ext = $this->GetExtension ($file);
					$mode = $this->SetType ($ext);

					$this->Upload ($localPath . $file, $remotePath . $file, $mode);
				}
			}
			
			return true;
		}

		return false;
	}

	/**
	 * Extract the file extension
	 *
	 * @access	private
	 * @param	string	filename
	 * @return	string	extension
	 **/
	 
	public function GetExtension ($filename)
	{
		if (strpos ($filename, '.') === false)
		{
			return 'txt';
		}

		$x = explode ('.', $filename);
		return end ($x);
	}

	/**
	 * Set the upload type
	 *
	 * @access	private
	 * @param	string	extension
	 * @return	string	type
	 **/
	
	public function SetType ($ext)
	{
		$text_types = array(
							'txt',
							'text',
							'php',
							'phps',
							'php4',
							'js',
							'css',
							'htm',
							'html',
							'phtml',
							'shtml',
							'log',
							'xml'
							);


		return (in_array ($ext, $text_types)) ? 'ascii' : 'binary';
	}
}