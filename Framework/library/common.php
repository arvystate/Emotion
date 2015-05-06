<?php
/***************************************************************************
 *   Application          : Emotion                                        *
 *   -------------------------------------------------------------------   *
 *   Package              : @library                                       *
 *   File                 : common.php                                     *
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
 *   Common is a class that provides common functions used in most         *
 *   complex websites. Most functions do not fit in any perticular         *
 *   category.                                                             *
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
 * Common
 *
 * Common functions, such as class loading, file downloads and rerouting.
 *
 * @package	library
 * @require	true
 * @author	ArvYStaTe.net Team
 **/

class Common
{
	/**
	 * Redirect user to another URL
	 *
	 * @access	public
	 * @param	string	URL where to redirect
	 * @return	void
	 **/
	
	public function Redirect ($url)
	{
		header ("Location: {$url}");
		
		exit ();
	}
	
	/**
	 * Checks and verifies E-mail address
	 *
	 * @access	public
	 * @param	string	email address
	 * @return	boolean	returns true, if email address is valid, false otherwise
	 **/
	 
	public function VerifyEmail ($email)
	{
		if (!preg_match("/^[A-Z0-9._%-]+@[A-Z0-9][A-Z0-9.-]{0,61}[A-Z0-9]\.[A-Z]{2,6}$/i", $email))
		{
			return false;
		}
		else
		{
			return true;
		}
	}
	
	/**
	 * Scans directory for PHP files and includes them, expecting they are classes and no procedures
	 *
	 * This method is used to load user created classes, to load library, use Load methods.
	 *
	 * @access	public
	 * @param	string	directory of classes
	 * @param	boolean	true if inside directories should be scanned
	 * @return	int		number of loaded files
	 **/
	
	public function LoadClasses ($directory, $recursive = true)
	{
		// Will not load system related files again to avoid redefinitions, as it is managed by framework itself
		if ( ($directory === DEFAULT_LIBRARY) || ($directory === DEFAULT_DATABASE) || ($directory === DEFAULT_APPLICATION) || ($directory === DEFAULT_SYSTEM) )
		{
			return;
		}
		
		if (is_dir ($directory) === false)
		{
			return;
		}
		
		$files = @scandir ($directory);
		
		$count = 0;
		
		foreach ($files as $value)
		{
			$dirTest = str_replace ('.', '', $value);
			
			// Recursively load files in next directory
			if ( (is_dir ($directory . '/' . $value) === true) && (empty ($dirTest) === false) && ($recursive === true) )
			{
				$this->LoadClasses ($directory . '/' . $value);
			}
			
			// Check for correct extension
			if ('.' . pathinfo ($value, PATHINFO_EXTENSION) === EXTENSION)
			{
				include_once ($directory . '/' . $value);
				$count++;
			}
		}
		
		return $count;
	}
	
	/**
	 * Send a file to browser
	 *
	 * @access	public
	 * @param	string	local path to file
	 * @param	string	filename that will be presented to browser
	 * @return	void
	 **/
	 
	public function Download ($path, $filename = '')
	{
		// File path
		if (!file_exists ($path))
		{
			die ('<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
					<html><head>
					<title>404 Not Found</title>
					</head><body>
					<h1>Not Found</h1>
					<p>The requested URL ' . $_SERVER['PHP_SELF'] . ' was not found on this server.</p>
					<p>Additionally, a 404 Not Found
					error was encountered while trying to use an ErrorDocument to handle the request.</p>
					</body></html>');
		}
		 
		// Extensions
		$allowedExt = array (
			'application/envoy' => 'evy',
			'application/fractals' => 'fif',
			'application/futuresplash' => 'spl',
			'application/hta' => 'hta',
			'application/internet-property-stream' => 'acx',
			'application/mac-binhex40' => 'hqx',
			'application/msword' => 'doc',
			'application/msword' => 'dot',
			'application/octet-stream' => 'bin',
			'application/octet-stream' => 'class',
			'application/octet-stream' => 'dms',
			'application/octet-stream' => 'exe',
			'application/octet-stream' => 'lha',
			'application/octet-stream' => 'lzh',
			'application/oda' => 'oda',
			'application/olescript' => 'axs',
			'application/pdf' => 'pdf',
			'application/pics-rules' => 'prf',
			'application/pkcs10' => 'p10',
			'application/pkix-crl' => 'crl',
			'application/postscript' => 'ai',
			'application/postscript' => 'eps',
			'application/postscript' => 'ps',
			'application/rtf' => 'rtf',
			'application/set-payment-initiation' => 'setpay',
			'application/set-registration-initiation' => 'setreg',
			'application/vnd.ms-excel' => 'xla',
			'application/vnd.ms-excel' => 'xlc',
			'application/vnd.ms-excel' => 'xlm',
			'application/vnd.ms-excel' => 'xls',
			'application/vnd.ms-excel' => 'xlt',
			'application/vnd.ms-excel' => 'xlw',
			'application/vnd.ms-outlook' => 'msg',
			'application/vnd.ms-pkicertstore' => 'sst',
			'application/vnd.ms-pkiseccat' => 'cat',
			'application/vnd.ms-pkistl' => 'stl',
			'application/vnd.ms-powerpoint' => 'pot',
			'application/vnd.ms-powerpoint' => 'pps',
			'application/vnd.ms-powerpoint' => 'ppt',
			'application/vnd.ms-project' => 'mpp',
			'application/vnd.ms-works' => 'wcm',
			'application/vnd.ms-works' => 'wdb',
			'application/vnd.ms-works' => 'wks',
			'application/vnd.ms-works' => 'wps',
			'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
			'application/winhlp' => 'hlp',
			'application/x-bcpio' => 'bcpio',
			'application/x-cdf' => 'cdf',
			'application/x-compress' => 'z',
			'application/x-compressed' => 'tgz',
			'application/x-cpio' => 'cpio',
			'application/x-csh' => 'csh',
			'application/x-director' => 'dcr',
			'application/x-director' => 'dir',
			'application/x-director' => 'dxr',
			'application/x-dvi' => 'dvi',
			'application/x-gtar' => 'gtar',
			'application/x-gzip' => 'gz',
			'application/x-hdf' => 'hdf',
			'application/x-internet-signup' => 'ins',
			'application/x-internet-signup' => 'isp',
			'application/x-iphone' => 'iii',
			'application/x-javascript' => 'js',
			'application/x-latex' => 'latex',
			'application/x-msaccess' => 'mdb',
			'application/x-mscardfile' => 'crd',
			'application/x-msclip' => 'clp',
			'application/x-msdownload' => 'dll',
			'application/x-msmediaview' => 'm13',
			'application/x-msmediaview' => 'm14',
			'application/x-msmediaview' => 'mvb',
			'application/x-msmetafile' => 'wmf',
			'application/x-msmoney' => 'mny',
			'application/x-mspublisher' => 'pub',
			'application/x-msschedule' => 'scd',
			'application/x-msterminal' => 'trm',
			'application/x-mswrite' => 'wri',
			'application/x-netcdf' => 'cdf',
			'application/x-netcdf' => 'nc',
			'application/x-perfmon' => 'pma',
			'application/x-perfmon' => 'pmc',
			'application/x-perfmon' => 'pml',
			'application/x-perfmon' => 'pmr',
			'application/x-perfmon' => 'pmw',
			'application/x-pkcs12' => 'p12',
			'application/x-pkcs12' => 'pfx',
			'application/x-pkcs7-certificates' => 'p7b',
			'application/x-pkcs7-certificates' => 'spc',
			'application/x-pkcs7-certreqresp' => 'p7r',
			'application/x-pkcs7-mime' => 'p7c',
			'application/x-pkcs7-mime' => 'p7m',
			'application/x-pkcs7-signature' => 'p7s',
			'application/x-rar-compressed' => 'rar',
			'application/x-sh' => 'sh',
			'application/x-shar' => 'shar',
			'application/x-shockwave-flash' => 'swf',
			'application/x-stuffit' => 'sit',
			'application/x-sv4cpio' => 'sv4cpio',
			'application/x-sv4crc' => 'sv4crc',
			'application/x-tar' => 'tar',
			'application/x-tcl' => 'tcl',
			'application/x-tex' => 'tex',
			'application/x-texinfo' => 'texi',
			'application/x-texinfo' => 'texinfo',
			'application/x-troff' => 'roff',
			'application/x-troff' => 't',
			'application/x-troff' => 'tr',
			'application/x-troff-man' => 'man',
			'application/x-troff-me' => 'me',
			'application/x-troff-ms' => 'ms',
			'application/x-ustar' => 'ustar',
			'application/x-wais-source' => 'src',
			'application/x-x509-ca-cert' => 'cer',
			'application/x-x509-ca-cert' => 'crt',
			'application/x-x509-ca-cert' => 'der',
			'application/ynd.ms-pkipko' => 'pko',
			'application/zip' => 'zip',
			'audio/basic' => 'au',
			'audio/basic' => 'snd',
			'audio/mid' => 'mid',
			'audio/mid' => 'rmi',
			'audio/mpeg' => 'mp3',
			'audio/x-aiff' => 'aif',
			'audio/x-aiff' => 'aifc',
			'audio/x-aiff' => 'aiff',
			'audio/x-mpegurl' => 'm3u',
			'audio/x-pn-realaudio' => 'ra',
			'audio/x-pn-realaudio' => 'ram',
			'audio/x-wav' => 'wav',
			'image/bmp' => 'bmp',
			'image/cis-cod' => 'cod',
			'image/gif' => 'gif',
			'image/ief' => 'ief',
			'image/jpeg' => 'jpe',
			'image/jpeg' => 'jpeg',
			'image/jpeg' => 'jpg',
			'image/pipeg' => 'jfif',
			'image/png' => 'png',
			'image/svg+xml' => 'svg',
			'image/tiff' => 'tif',
			'image/tiff' => 'tiff',
			'image/x-cmu-raster' => 'ras',
			'image/x-cmx' => 'cmx',
			'image/x-icon' => 'ico',
			'image/x-portable-anymap' => 'pnm',
			'image/x-portable-bitmap' => 'pbm',
			'image/x-portable-graymap' => 'pgm',
			'image/x-portable-pixmap' => 'ppm',
			'image/x-rgb' => 'rgb',
			'image/x-xbitmap' => 'xbm',
			'image/x-xpixmap' => 'xpm',
			'image/x-xwindowdump' => 'xwd',
			'message/rfc822' => 'mht',
			'message/rfc822' => 'mhtml',
			'message/rfc822' => 'nws',
			'text/css' => 'css',
			'text/h323' => '323',
			'text/html' => 'htm',
			'text/html' => 'html',
			'text/html' => 'stm',
			'text/iuls' => 'uls',
			'text/plain' => 'bas',
			'text/plain' => 'c',
			'text/plain' => 'h',
			'text/plain' => 'txt',
			'text/richtext' => 'rtx',
			'text/scriptlet' => 'sct',
			'text/tab-separated-values' => 'tsv',
			'text/webviewhtml' => 'htt',
			'text/x-component' => 'htc',
			'text/x-setext' => 'etx',
			'text/x-vcard' => 'vcf',
			'video/mpeg' => 'mp2',
			'video/mpeg' => 'mpa',
			'video/mpeg' => 'mpe',
			'video/mpeg' => 'mpeg',
			'video/mpeg' => 'mpg',
			'video/mpeg' => 'mpv2',
			'video/quicktime' => 'mov',
			'video/quicktime' => 'qt',
			'video/x-la-asf' => 'lsf',
			'video/x-la-asf' => 'lsx',
			'video/x-ms-asf' => 'asf',
			'video/x-ms-asf' => 'asr',
			'video/x-ms-asf' => 'asx',
			'video/x-msvideo' => 'avi',
			'video/x-sgi-movie' => 'movie',
			'x-world/x-vrml' => 'flr',
			'x-world/x-vrml' => 'vrml',
			'x-world/x-vrml' => 'wrl',
			'x-world/x-vrml' => 'wrz',
			'x-world/x-vrml' => 'xaf',
			'x-world/x-vrml' => 'xof',
		);
		
		set_time_limit (0);
		
		//
		// Get file extension
		//
		
		if ($filename != '')
		{
			$extension = pathinfo ($filename, PATHINFO_EXTENSION);
		}
		else
		{
			$extension = pathinfo ($path, PATHINFO_EXTENSION);
		}
		
		$found = false;
		
		//
		// Find mtype
		//
		
		foreach ($allowedExt as $key => $value)
		{
			if ($value == $extension)
			{
				$mtype = $key;
				$found = true;
				
				break;
			}
		}
		
		//
		// Get mime type
		//
		
		if ($found === false)
		{
			$mtype = '';
	
			// If mime type is not set, get from server settings
			if (function_exists ('mime_content_type'))
			{
				$mtype = mime_content_type ($path);
			}
			else if (function_exists ('finfo_file'))
			{
				$finfo = finfo_open (FILEINFO_MIME);
				$mtype = finfo_file ($finfo, $path);
	
				finfo_close ($finfo);  
			}
			
			if ($mtype == '')
			{
				$mtype = "application/force-download";
			}
		}
		
		//
		// Filesize
		//
		
		$fileSize = filesize ($path);
		
		if ($filename == '')
		{
			$filename = basename ($path);
		}
		
		//
		// Set headers
		//
		
		header ("Pragma: public");
		header ("Expires: 0");
		header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header ("Cache-Control: public");
		header ("Content-Description: File Transfer");
		header ("Content-Type: $mtype");
		header ("Content-Disposition: attachment; filename=\"{$filename}\"");
		header ("Content-Transfer-Encoding: binary");
		header ("Content-Length: " . $fileSize);
	 
		//
		// Download
		//
		
		$fileHandle = @fopen ($path, "rb");
	
		while (!feof ($fileHandle))
		{
			print (fread ($fileHandle, 1024 * 8));
			flush();
			
			if (connection_status () != 0)
			{
				break;
			}
		}
		
		@fclose ($fileHandle);
	 }
}