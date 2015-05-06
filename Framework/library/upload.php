pl
<?php
/***************************************************************************
 *   Application          : Emotion                                        *
 *   -------------------------------------------------------------------   *
 *   Package              : @library                                       *
 *   File                 : upload.php                                     *
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
 *   Upload is a class that supports uploading of the files to server.     *
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
 * Upload
 *
 * Uploading files security and support functions.
 *
 * @package	library
 * @require	false
 * @author	ExpressionEngine Dev Team
 **/

class Upload
{

	private $maxSize				= 0;
	private $maxWidth				= 0;
	private $maxHeight				= 0;
	private $maxFilename			= 0;
	private $allowedTypes			= "";
	private $fileTemp				= "";
	private $fileName				= "";
	private $origName				= "";
	private $fileType				= "";
	private $fileSize				= "";
	private $fileExt				= "";
	private $uploadPath				= "";
	private $overwrite				= false;
	private $encryptName			= false;
	private $isImage				= false;
	private $imageWidth				= '';
	private $imageHeight			= '';
	private $imageType				= '';
	private $imageSizeStr			= '';
	private $errorMsg				= array ();
	private $mimes					= array ();
	private $removeSpaces			= true;
	private $tempPrefix				= "temp_file_";
	private $clientName				= '';

	private $filenameOverride		= '';

	/**
	 * Initialize preferences
	 *
	 * @access	public
	 * @param	array	configuration array
	 * @return	void
	 **/
	
	public function Initialize ($config = array ())
	{
		$defaults = array (
							'maxSize'			=> 0,
							'maxWidth'			=> 0,
							'maxHeight'		=> 0,
							'maxFilename'		=> 0,
							'allowedTypes'		=> "",
							'fileTemp'			=> "",
							'fileName'			=> "",
							'origName'			=> "",
							'fileType'			=> "",
							'fileSize'			=> "",
							'fileExt'			=> "",
							'uploadPath'		=> "",
							'overwrite'			=> false,
							'encryptName'		=> false,
							'isImage'			=> false,
							'imageWidth'		=> '',
							'imageHeight'		=> '',
							'imageType'		=> '',
							'imageSizeStr'	=> '',
							'errorMsg'			=> array(),
							'mimes'				=> array(),
							'removeSpaces'		=> true,
							'tempPrefix'		=> "temp_file_",
							'clientName'		=> ''
						);


		foreach ($defaults as $key => $val)
		{
			if (isset ($config[$key]) === true)
			{
				$method = 'set_' . $key;
				if (method_exists ($this, $method))
				{
					$this->$method ($config[$key]);
				}
				else
				{
					$this->$key = $config[$key];
				}
			}
			else
			{
				$this->$key = $val;
			}
		}

		// if a fileName was provided in the config, use it instead of the user input
		// supplied file name for all uploads until initialized again
		$this->filenameOverride = $this->fileName;
	}

	/**
	 * Perform the file upload
	 *
	 * @access	public
	 * @return	bool	returns true if upload was successful, false otherwise
	 **/
	
	public function Upload ($field = 'userfile')
	{
		// Is $_FILES[$field] set? If not, no reason to continue.
		if (isset ($_FILES[$field]) === false)
		{
			return false;
		}

		// Is the upload path valid?
		if ($this->_validateUploadPath () === false)
		{
			return false;
		}

		// Was the file able to be uploaded? If not, determine the reason why.
		if (!is_uploaded_file ($_FILES[$field]['tmp_name']))
		{
			return false;
		}


		// Set the uploaded data as class variables
		$this->fileTemp = $_FILES[$field]['tmp_name'];
		$this->fileSize = $_FILES[$field]['size'];
		$this->fileType = preg_replace ("/^(.+?);.*$/", "\\1", $_FILES[$field]['type']);
		$this->fileType = strtolower (trim (stripslashes ($this->fileType), '"'));
		$this->fileName = $this->_prepareFilename ($_FILES[$field]['name']);
		$this->fileExt	 = $this->_getExtension ($this->fileName);
		$this->clientName = $this->fileName;

		// Is the file type allowed to be uploaded?
		if ($this->_isAllowedFiletype () === false)
		{
			return false;
		}

		// if we're overriding, let's now make sure the new name and type is allowed
		if ($this->filenameOverride != '')
		{
			$this->fileName = $this->_prepareFilename ($this->filenameOverride);

			// If no extension was provided in the fileName config item, use the uploaded one
			if (strpos ($this->filenameOverride, '.') === false)
			{
				$this->fileName .= $this->fileExt;
			}

			// An extension was provided, lets have it!
			else
			{
				$this->fileExt = $this->get_extension($this->filenameOverride);
			}
		}

		// Convert the file size to kilobytes
		if ($this->fileSize > 0)
		{
			$this->fileSize = round ($this->fileSize / 1024, 2);
		}

		// Is the file size within the allowed maximum?
		if ($this->_isAllowedFilesize () === false)
		{
			return false;
		}

		// Are the image dimensions within the allowed size?
		// Note: This can fail if the server has an open_basdir restriction.
		if ($this->_isAllowedDimensions () === false)
		{
			return false;
		}

		// Sanitize the file name for security
		$this->fileName = $this->_cleanFilename ($this->fileName);

		// Truncate the file name if it's too long
		if ($this->maxFilename > 0)
		{
			$this->fileName = $this->_limitFilenameLength ($this->fileName, $this->maxFilename);
		}

		// Remove white spaces in the name
		if ($this->removeSpaces === true)
		{
			$this->fileName = preg_replace ("/\s+/", "_", $this->fileName);
		}

		/*
		 * Validate the file name
		 * This function appends an number onto the end of
		 * the file if one with the same name already exists.
		 * If it returns false there was a problem.
		 **/
		
		$this->origName = $this->fileName;

		if ($this->overwrite == false)
		{
			$this->fileName = $this->_setFilename ($this->uploadPath, $this->fileName);

			if ($this->fileName === false)
			{
				return false;
			}
		}

		/*
		 * Move the file to the final destination
		 * To deal with different server configurations
		 * we'll attempt to use copy() first.  If that fails
		 * we'll use move_uploaded_file().  One of the two should
		 * reliably work in most environments
		 **/
		
		if ( ! @copy($this->fileTemp, $this->uploadPath . $this->fileName))
		{
			if ( ! @move_uploaded_file ($this->fileTemp, $this->uploadPath.$this->fileName))
			{
				return false;
			}
		}

		/*
		 * Set the finalized image dimensions
		 * This sets the image width/height (assuming the
		 * file was an image).  We use this information
		 * in the "data" function.
		 **/
		
		$this->_setImageProperties ($this->uploadPath . $this->fileName);

		return true;
	}

	/**
	 * Finalized Data Array
	 *
	 * Returns an associative array containing all of the information
	 * related to the upload, allowing the developer easy access in one array.
	 *
	 * @access	public
	 * @return	array	returns array of upload information
	 **/
	
	public function Data ()
	{
		return array (
						'fileName'			=> $this->fileName,
						'fileType'			=> $this->fileType,
						'file_path'			=> $this->uploadPath,
						'full_path'			=> $this->uploadPath.$this->fileName,
						'raw_name'			=> str_replace ($this->fileExt, '', $this->fileName),
						'origName'			=> $this->origName,
						'clientName'		=> $this->clientName,
						'fileExt'			=> $this->fileExt,
						'fileSize'			=> $this->fileSize,
						'isImage'			=> $this->isImage(),
						'imageWidth'		=> $this->imageWidth,
						'imageHeight'		=> $this->imageHeight,
						'imageType'			=> $this->imageType,
						'imageSizeStr'		=> $this->imageSizeStr,
					);
	}

	/**
	 * Set Upload Path
	 *
	 * @access	public
	 * @param	string	upload path
	 * @return	void
	 **/
	
	public function SetUploadPath ($path)
	{
		// Make sure it has a trailing slash
		$this->uploadPath = rtrim ($path, '/') . '/';
	}

	/**
	 * Set the file name
	 *
	 * This function takes a filename/path as input and looks for the
	 * existence of a file with the same name. If found, it will append a
	 * number to the end of the filename to avoid overwriting a pre-existing file.
	 *
	 * @access	private
	 * @param	string	upload path
	 * @param	string	filename
	 * @return	string	non-existing filename
	 **/
	
	private function _setFilename ($path, $filename)
	{
		if ($this->encryptName == true)
		{
			mt_srand ();
			$filename = md5 (uniqid (mt_rand() ) ) . $this->fileExt;
		}

		if (!file_exists ($path . $filename))
		{
			return $filename;
		}

		$filename = str_replace ($this->fileExt, '', $filename);

		$newFilename = '';
		
		for ($i = 1; $i < 100; $i++)
		{
			if (file_exists ($path . $filename . $i . $this->fileExt) === false)
			{
				$newFilename = $filename . $i . $this->fileExt;
				break;
			}
		}

		if ($newFilename == '')
		{
			return false;
		}
		else
		{
			return $newFilename;
		}
	}

	/**
	 * Set Allowed File Types
	 *
	 * @access	public
	 * @param	string	string of allowed types divided by |
	 * @return	void
	 **/
	
	public function SetAllowedTypes ($types)
	{
		if ( (is_array ($types) === false) && ($types == '*') )
		{
			$this->allowedTypes = '*';
			return;
		}
		
		$this->allowedTypes = explode ('|', $types);
	}

	/**
	 * Set Image Properties
	 *
	 * Uses GD to determine the width/height/type of image
	 *
	 * @access	private
	 * @param	string	path to image
	 * @return	void
	 **/
	
	private function _setImageProperties ($path = '')
	{
		if ($this->_isImage () === false)
		{
			return;
		}

		if (function_exists ('getimagesize') === true )
		{
			if ( ($D = @getimagesize ($path)) !== false)
			{
				$types = array (1 => 'gif', 2 => 'jpeg', 3 => 'png');

				$this->imageWidth		= $D['0'];
				$this->imageHeight		= $D['1'];
				$this->imageType		= (isset ($types[$D['2']]) === false) ? 'unknown' : $types[$D['2']];
				$this->imageSizeStr		= $D['3'];  // string containing height and width
			}
		}
	}

	/**
	 * Validate the image
	 *
	 * @access	private
	 * @return	bool	returns true if upload was an image
	 **/
	
	private function _isImage ()
	{
		// IE will sometimes return odd mime-types during upload, so here we just standardize all
		// jpegs or pngs to the same file type.

		$png_mimes  = array ('image/x-png');
		$jpeg_mimes = array ('image/jpg', 'image/jpe', 'image/jpeg', 'image/pjpeg');

		if (in_array ($this->fileType, $png_mimes))
		{
			$this->fileType = 'image/png';
		}

		if (in_array ($this->fileType, $jpeg_mimes))
		{
			$this->fileType = 'image/jpeg';
		}

		$img_mimes = array (
							'image/gif',
							'image/jpeg',
							'image/png',
						);

		return (in_array ($this->fileType, $img_mimes, true)) ? true : false;
	}

	/**
	 * Verify that the filetype is allowed
	 *
	 * @access	private
	 * @return	bool	returns true if filetype is allowed
	 **/
	
	private function _isAllowedFiletype ()
	{
		if ($this->allowedTypes == '*')
		{
			return true;
		}

		if ( (count ($this->allowedTypes) == 0) || (is_array ($this->allowedTypes) === false) )
		{
			return false;
		}

		$ext = strtolower (ltrim ($this->fileExt, '.'));

		if (in_array ($ext, $this->allowedTypes) === false)
		{
			return false;
		}

		// Images get some additional checks
		$imageTypes = array ('gif', 'jpg', 'jpeg', 'png', 'jpe');

		if (in_array ($ext, $imageTypes) === true)
		{
			if (getimagesize ($this->fileTemp) === false)
			{
				return false;
			}
		}

		return false;
	}

	/**
	 * Verify that the file is within the allowed size
	 *
	 * @access	public
	 * @return	bool	returns true if file is allowed size
	 **/
	
	private function _isAllowedFilesize ()
	{
		if ( ($this->maxSize != 0) && ($this->fileSize > $this->maxSize) )
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	/**
	 * Verify that the image is within the allowed width/height
	 *
	 * @access	public
	 * @return	bool	returns true if image is allowed dimensions
	 **/
	
	private function _isAllowedDimensions ()
	{
		if ($this->_isImage () === false)
		{
			return true;
		}

		if (function_exists ('getimagesize') === true)
		{
			$D = @getimagesize ($this->fileTemp);

			if ( ($this->maxWidth > 0) && $D['0'] > $this->maxWidth)
			{
				return false;
			}

			if ( ($this->maxHeight > 0) && $D['1'] > $this->maxHeight)
			{
				return false;
			}

			return true;
		}

		return true;
	}

	/**
	 * Validate Upload Path
	 *
	 * Verifies that it is a valid upload path with proper permissions.
	 *
	 * @access	private
	 * @return	bool	returns true if upload path is valid
	 **/
	
	private function _validateUploadPath ()
	{
		if ($this->uploadPath == '')
		{
			return false;
		}

		if ( (function_exists ('realpath') === true) && (@realpath ($this->uploadPath) !== false) )
		{
			$this->uploadPath = str_replace ("\\", "/", realpath ($this->uploadPath));
		}

		if (@is_dir ($this->uploadPath) === false)
		{
			return false;
		}

		if ($this->_isReallyWritable ($this->uploadPath) === false)
		{
			return false;
		}

		$this->uploadPath = preg_replace ("/(.+?)\/*$/", "\\1/",  $this->uploadPath);
		return true;
	}
	
	/**
	 * Tests for file writability
	 *
	 * is_writable() returns TRUE on Windows servers when you really can't write to
	 * the file, based on the read-only attribute.  is_writable() is also unreliable
	 * on Unix servers if safe_mode is on.
	 *
	 * @access	private
	 * @return	void
	 **/
	
	private function _isReallyWritable ($file)
	{
		// If we're on a Unix server with safe_mode off we call is_writable
		if ( (DIRECTORY_SEPARATOR == '/') && (@ini_get("safe_mode") === false) )
		{
			return is_writable ($file);
		}

		// For windows servers and safe_mode "on" installations we'll actually
		// write a file then read it.  Bah...
		if (is_dir ($file) === true)
		{
			$file = rtrim ($file, '/') . '/' . md5 (mt_rand (1, 100) . mt_rand (1, 100) );

			if ( ($fp = @fopen ($file, FOPEN_WRITE_CREATE)) === false)
			{
				return false;
			}

			fclose ($fp);
			@chmod ($file, DIR_WRITE_MODE);
			@unlink ($file);
			
			return true;
		}
		else if ( (is_file ($file) === false) || ( ($fp = @fopen ($file, FOPEN_WRITE_CREATE)) === false) )
		{
			return false;
		}

		fclose ($fp);
		
		return true;
	}

	/**
	 * Extract the file extension
	 *
	 * @access	private
	 * @param	string	filename
	 * @return	string	extension
	 **/
	
	private function _getExtension ($filename)
	{
		$x = explode ('.', $filename);
		return '.' . end ($x);
	}

	/**
	 * Clean the file name for security
	 *
	 * @access	private
	 * @param	string	filename
	 * @return	string	cleaned filename
	 **/
	
	private function _cleanFileName ($filename)
	{
		$bad = array ("<!--",
						"-->",
						"'",
						"<",
						">",
						'"',
						'&',
						'$',
						'=',
						';',
						'?',
						'/',
						"%20",
						"%22",
						"%3c",		// <
						"%253c",	// <
						"%3e",		// >
						"%0e",		// >
						"%28",		// (
						"%29",		// )
						"%2528",	// (
						"%26",		// &
						"%24",		// $
						"%3f",		// ?
						"%3b",		// ;
						"%3d"		// =
					);

		$filename = str_replace ($bad, '', $filename);

		return stripslashes ($filename);
	}

	/**
	 * Limit the File Name Length
	 *
	 * @access	private
	 * @param	string	filename
	 * @param	integer	maximum length
	 * @return	string	limited filename
	 **/
	
	private function _limitFilenameLength ($filename, $length)
	{
		if (strlen ($filename) < $length)
		{
			return $filename;
		}

		$ext = '';
		
		if (strpos ($filename, '.') !== false)
		{
			$parts		= explode ('.', $filename);
			$ext		= '.' . array_pop ($parts);
			$filename	= implode ('.', $parts);
		}

		return substr ($filename, 0, ($length - strlen ($ext) ) ) . $ext;
	}

	/**
	 * Prep Filename
	 *
	 * Prevents possible script execution from Apache's handling of files multiple extensions
	 * http://httpd.apache.org/docs/1.3/mod/mod_mime.html#multipleext
	 *
	 * @access	private
	 * @param	string	filename
	 * @return	string	parsed filename
	 **/
	
	private function _prepFilename ($filename)
	{
		if ( (strpos ($filename, '.') === false) || ($this->allowedTypes == '*') )
		{
			return $filename;
		}

		$parts		= explode ('.', $filename);
		$ext		= array_pop ($parts);
		$filename	= array_shift ($parts);

		foreach ($parts as $part)
		{
			if (in_array (strtolower ($part), $this->allowedTypes) === false)
			{
				$filename .= '.' . $part . '_';
			}
			else
			{
				$filename .= '.'.$part;
			}
		}

		$filename .= '.'.$ext;

		return $filename;
	}
}