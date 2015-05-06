<?php
/***************************************************************************
 *   Application          : Emotion                                        *
 *   -------------------------------------------------------------------   *
 *   Package              : @library                                       *
 *   File                 : zip.php                                        *
 *   Version              : 1.0.0                                          *
 *   Status               : Final                                          *
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
 *   Zip is a utility class that provides basic functionality for          *
 *   working with ZIP archives.                                            *
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
 * Zip
 *
 * Zip Archive functionality: opening, creating Zip Archives.
 *
 * @package	library
 * @require	false
 * @author	ArvYStaTe.net Team
 **/

class Zip
{

	public $compressedData = array ();
	public $centralDirectory = array (); // central directory
	public $endOfCentralDirectory = "\x50\x4b\x05\x06\x00\x00\x00\x00"; //end of Central directory record
	public $oldOffset = 0;

	/**
	 * Function to create the directory where the file(s) will be unzipped
	 *
	 * @access public
	 * @param	string	directory
	 * @return 	void
	 **/	
	
	public function AddDirectory ($directoryName)
	{
		$directoryName = str_replace ("\\", "/", $directoryName);
		$feedArrayRow = "\x50\x4b\x03\x04";
		$feedArrayRow .= "\x0a\x00";
		$feedArrayRow .= "\x00\x00";
		$feedArrayRow .= "\x00\x00";
		$feedArrayRow .= "\x00\x00\x00\x00";
		$feedArrayRow .= pack ("V", 0);
		$feedArrayRow .= pack ("V", 0);
		$feedArrayRow .= pack ("V", 0);
		$feedArrayRow .= pack ("v", strlen ($directoryName) );
		$feedArrayRow .= pack ("v", 0);
		$feedArrayRow .= $directoryName;
		$feedArrayRow .= pack ("V", 0);
		$feedArrayRow .= pack ("V", 0);
		$feedArrayRow .= pack ("V", 0);
		$this->compressedData[] = $feedArrayRow;
		$newOffset = strlen (implode ("", $this->compressedData));
		$addCentralRecord = "\x50\x4b\x01\x02";
		$addCentralRecord .= "\x00\x00";
		$addCentralRecord .= "\x0a\x00";
		$addCentralRecord .= "\x00\x00";
		$addCentralRecord .= "\x00\x00";
		$addCentralRecord .= "\x00\x00\x00\x00";
		$addCentralRecord .= pack ("V", 0);
		$addCentralRecord .= pack ("V", 0);
		$addCentralRecord .= pack ("V", 0);
		$addCentralRecord .= pack ("v", strlen ($directoryName) );
		$addCentralRecord .= pack ("v", 0);
		$addCentralRecord .= pack ("v", 0);
		$addCentralRecord .= pack ("v", 0);
		$addCentralRecord .= pack ("v", 0);
		$addCentralRecord .= pack ("V", 16);
		$addCentralRecord .= pack ("V", $this->oldOffset );
		$this->oldOffset = $newOffset;
		$addCentralRecord .= $directoryName;
		$this->centralDirectory[] = $addCentralRecord;
	}

	/**
	 * Function to add file(s) to the specified directory in the archive 
	 *
	 * @access	public
	 * @param	string	data
	 * @param	string	directory
	 * @return	void
	 **/	
	
	public function AddFile ($data, $directoryName)
	{
		$directoryName = str_replace ("\\", "/", $directoryName);
		$feedArrayRow = "\x50\x4b\x03\x04";
		$feedArrayRow .= "\x14\x00";
		$feedArrayRow .= "\x00\x00";
		$feedArrayRow .= "\x08\x00";
		$feedArrayRow .= "\x00\x00\x00\x00";
		$uncompressedLength = strlen ($data);
		$compression = crc32 ($data);
		$gzCompressedData = gzcompress ($data);
		$gzCompressedData = substr ( substr ($gzCompressedData, 0, strlen ($gzCompressedData) - 4), 2);
		$compressedLength = strlen ($gzCompressedData);
		$feedArrayRow .= pack ("V", $compression);
		$feedArrayRow .= pack ("V", $compressedLength);
		$feedArrayRow .= pack ("V", $uncompressedLength);
		$feedArrayRow .= pack ("v", strlen ($directoryName) );
		$feedArrayRow .= pack ("v", 0 );
		$feedArrayRow .= $directoryName;
		$feedArrayRow .= $gzCompressedData;
		$feedArrayRow .= pack ("V", $compression);
		$feedArrayRow .= pack ("V", $compressedLength);
		$feedArrayRow .= pack ("V", $uncompressedLength);
		$this->compressedData[] = $feedArrayRow;
		$newOffset = strlen (implode ("", $this->compressedData));
		$addCentralRecord = "\x50\x4b\x01\x02";
		$addCentralRecord .= "\x00\x00";
		$addCentralRecord .= "\x14\x00";
		$addCentralRecord .= "\x00\x00";
		$addCentralRecord .= "\x08\x00";
		$addCentralRecord .= "\x00\x00\x00\x00";
		$addCentralRecord .= pack ("V", $compression);
		$addCentralRecord .= pack ("V", $compressedLength);
		$addCentralRecord .= pack ("V", $uncompressedLength);
		$addCentralRecord .= pack ("v", strlen ($directoryName) );
		$addCentralRecord .= pack ("v", 0);
		$addCentralRecord .= pack ("v", 0);
		$addCentralRecord .= pack ("v", 0);
		$addCentralRecord .= pack ("v", 0);
		$addCentralRecord .= pack ("V", 32);
		$addCentralRecord .= pack ("V", $this->oldOffset );
		$this->oldOffset = $newOffset;
		$addCentralRecord .= $directoryName;
		$this->centralDirectory[] = $addCentralRecord;
	}
	
	/**
	 * Function to store zip file
	 *
	 * @access public
	 * @param	string	path with filename on filesystem
	 * @param	boolean	default true, if data should be cleaned after saving
	 * @return	boolean	return true on success, false otherwise
	 **/
	 
	 public function Save ($path, $clean = true)
	 {
		 if (!file_exists ($path))
		 {
			 file_put_contents ($path, $this->GetZippedfile ());
			 
			 if ($clean === true)
			 {
				 $this->Clean ();
			 }
			 
			 return true;
		 }
		 
		 return false;
	 }

	/**
	 * Function to return the zip file
	 *
	 * @access	public
	 * @return	zipfile (archive)
	 **/
	
	public function GetZippedfile ()
	{
		$data = implode ("", $this->compressedData);
		$controlDirectory = implode ("", $this->centralDirectory);
		return
		$data .
		$controlDirectory .
		$this->endOfCentralDirectory . 
		pack ("v", sizeof ($this->centralDirectory)) .
		pack ("v", sizeof ($this->centralDirectory)) .
		pack ("V", strlen ($controlDirectory)) .
		pack ("V", strlen ($data)) .
		"\x00\x00";
	}

	/**
	 * Function to force the download of the archive as soon as it is created
	 *
	 * @access	public
	 * @param	string 	name of the created archive file
	 * @return	void
	 **/
	
	public function ForceDownload ($archiveName)
	{
		if (ini_get ('zlib.output_compression'))
		{
			ini_set ('zlib.output_compression', 'Off');
		}

		// Security checks
		if ($archiveName == "" )
		{
			return;
		}
		elseif (!file_exists ( $archiveName ) )
		{
			return;
		}

		header ("Pragma: public");
		header ("Expires: 0");
		header ("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header ("Cache-Control: private", false);
		header ("Content-Type: application/zip");
		header ("Content-Disposition: attachment; filename=" . basename ($archiveName).";" );
		header ("Content-Transfer-Encoding: binary");
		header ("Content-Length: " . filesize ($archiveName));
		
		$fileHandle = @fopen ($archiveName, "rb");
	
		while (!feof ($fileHandle))
		{
			print (fread ($fileHandle, 1024 * 8));
			flush();
			
			if (connection_status () != 0)
			{
				@fclose ($fileHandle);
				die ();
			}
			
			@fclose ($file);
		}
		
		unlink ($archiveName);
	}
	
	/**
	 * Function cleans variables and deletes file contents
	 *
	 * @access	public
	 * @return	void
	 **/
	
	public function Clean ()
	{
		$this->compressedData = array ();
		$this->centralDirectory = array (); // central directory
		$this->endOfCentralDirectory = "\x50\x4b\x05\x06\x00\x00\x00\x00"; //end of Central directory record
		$this->oldOffset = 0;
	}
}
?>