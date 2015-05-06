<?php
/***************************************************************************
 *   Application          : Emotion                                        *
 *   -------------------------------------------------------------------   *
 *   Package              : @library                                       *
 *   File                 : image.php                                      *
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
 *   Image manipulation class provides functions such as resize or crop.   *
 *   operations, as it will handle the low-level commands by itself.       *
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
 * Image
 *
 * Image manipulation functions, resizing, watermarking and similar.
 *
 * @package	library
 * @require	false
 * @author	ExpressionEngine Dev Team
 **/
 
class Image
{
	private $imageLibrary		= 'gd2';	// Can be:  imagemagick, netpbm, gd, gd2
	private $libraryPath		= '';
	private $dynamicOutput		= false;	// Whether to send to browser or write to disk
	private $sourceImage		= '';
	private $newImage			= '';
	private $width				= '';
	private $height				= '';
	private $quality			= '90';
	private $createThumb		= false;
	private $thumbMarker		= '_thumb';
	private $maintainRatio		= true;		// Whether to maintain aspect ratio when resizing or use hard values
	private $masterDim			= 'auto';	// auto, height, or width.  Determines what to use as the master dimension
	private $rotationAngle		= '';
	private $xAxis				= '';
	private $yAxis				= '';

	// Watermark Vars
	private $wmText				= '';			// Watermark text if graphic is not used
	private $wmType				= 'text';		// Type of watermarking.  Options:  text/overlay
	private $wmXTransp			= 4;
	private $wmYTransp			= 4;
	private $wmOverlayPath		= '';			// Watermark image path
	private $wmFontPath			= '';			// TT font
	private $wmFontSize			= 17;			// Font size (different versions of GD will either use points or pixels)
	private $wmVrtAlignment		= 'B';			// Vertical alignment:   T M B
	private $wmHorAlignment		= 'C';			// Horizontal alignment: L R C
	private $wmPadding			= 0;			// Padding around text
	private $wmHorOffset		= 0;			// Lets you push text to the right
	private $wmVrtOffset		= 0;			// Lets you push  text down
	private $wmFontColor		= '#ffffff';	// Text color
	private $wmShadowColor		= '';			// Dropshadow color
	private $wmShadowDistance	= 2;			// Dropshadow distance
	private $wmOpacity			= 50;			// Image opacity: 1 - 100  Only works with image

	// Private Vars
	private $sourceFolder		= '';
	private $destFolder			= '';
	private $mimeType			= '';
	private $origWidth			= '';
	private $origHeight			= '';
	private $imageType			= '';
	private $sizeStr			= '';
	private $fullSrcPath		= '';
	private $fullDstPath		= '';
	private $createFnc			= 'imagecreatetruecolor';
	private $copyFnc			= 'imagecopyresampled';
	private $wmUseDropShadow	= false;
	private $wmUseTruetype		= false;

	/**
	 * Initialize image properties
	 *
	 * Resets values in case this class is used in a loop
	 *
	 * @access	public
	 * @return	void
	 **/
	
	public function Clear ()
	{
		$props = array ('sourceFolder', 'destFolder', 'sourceImage', 'fullSrcPath', 'fullDstPath', 'newImage', 'imageType', 'sizeStr', 'quality', 'origWidth', 'origHeight', 'rotationAngle', 'xAxis', 'yAxis', 'createFnc', 'copyFnc', 'wmOverlayPath', 'wmUseTruetype', 'dynamicOutput', 'wmFontSize', 'wmText', 'wmVrtAlignment', 'wmHorAlignment', 'wmPadding', 'wmHorOffset', 'wmVrtOffset', 'wmFontColor', 'wmUseDropShadow', 'wmShadowColor', 'wmShadowDistance', 'wmOpacity');

		foreach ($props as $val)
		{
			$this->$val = '';
		}

		// special consideration for masterDim
		$this->masterDim = 'auto';
	}

	/**
	 * initialize image preferences
	 *
	 * @access	public
	 * @param	array
	 * @return	bool
	 **/
	
	public function Initialize ($props = array())
	{
		/*
		 * Convert array elements into class variables
		 **/
		if (count ($props) > 0)
		{
			foreach ($props as $key => $val)
			{
				$this->$key = $val;
			}
		}

		/*
		 * Is there a source image?
		 *
		 * If not, there's no reason to continue
		 *
		 **/
		if ($this->sourceImage == '')
		{
			return false;	
		}

		/*
		 * Is getimagesize() Available?
		 *
		 * We use it to determine the image properties (width/height).
		 * Note:  We need to figure out how to determine image
		 * properties using ImageMagick and NetPBM
		 *
		 **/
		if (function_exists ('getimagesize') === false)
		{
			return false;
		}

		$this->imageLibrary = strtolower ($this->imageLibrary);

		/*
		 * Set the full server path
		 *
		 * The source image may or may not contain a path.
		 * Either way, we'll try use realpath to generate the
		 * full server path in order to more reliably read it.
		 *
		 **/
		if ( (function_exists ('realpath') === true) && (@realpath ($this->sourceImage) !== false) )
		{
			$fullSourcePath = str_replace ("\\", "/", realpath ($this->sourceImage));
		}
		else
		{
			$fullSourcePath = $this->sourceImage;
		}

		$x = explode ('/', $fullSourcePath);
		$this->sourceImage = end ($x);
		$this->sourceFolder = str_replace ($this->sourceImage, '', $fullSourcePath);

		// Set the Image Properties
		if ($this->_getImageProperties ($this->sourceFolder . $this->sourceImage) === false)
		{
			return false;	
		}

		/*
		 * Assign the "new" image name/path
		 *
		 * If the user has set a "newImage" name it means
		 * we are making a copy of the source image. If not
		 * it means we are altering the original.  We'll
		 * set the destination filename and path accordingly.
		 *
		 **/
		if ($this->newImage == '')
		{
			$this->destImage = $this->sourceImage;
			$this->destFolder = $this->sourceFolder;
		}
		else
		{
			if (strpos ($this->newImage, '/') === false)
			{
				$this->destFolder = $this->sourceFolder;
				$this->destImage = $this->newImage;
			}
			else
			{
				if ( (function_exists('realpath') === true) && (@realpath ($this->newImage) !== false) )
				{
					$fullDestPath = str_replace ("\\", "/", realpath ($this->newImage));
				}
				else
				{
					$fullDestPath = $this->newImage;
				}

				// Is there a file name?
				if (!preg_match ("#\.(jpg|jpeg|gif|png)$#i", $fullDestPath))
				{
					$this->destFolder = $fullDestPath . '/';
					$this->destImage = $this->sourceImage;
				}
				else
				{
					$x = explode ('/', $fullDestPath);
					$this->destImage = end ($x);
					$this->destFolder = str_replace ($this->destImage, '', $fullDestPath);
				}
			}
		}

		/*
		 * Compile the finalized filenames/paths
		 *
		 * We'll create two master strings containing the
		 * full server path to the source image and the
		 * full server path to the destination image.
		 * We'll also split the destination image name
		 * so we can insert the thumbnail marker if needed.
		 *
		 **/
		
		if ( ($this->createThumb === false) || ($this->thumbMarker == '') )
		{
			$this->thumbMarker = '';
		}

		$xp	= $this->_explodeName ($this->destImage);

		$filename = $xp['name'];
		$fileExt = $xp['ext'];

		$this->fullSrcPath = $this->sourceFolder . $this->sourceImage;
		$this->fullDstPath = $this->destFolder . $filename . $this->thumbMarker . $fileExt;

		/*
		 * Should we maintain image proportions?
		 *
		 * When creating thumbs or copies, the target width/height
		 * might not be in correct proportion with the source
		 * image's width/height.  We'll recalculate it here.
		 *
		 **/
		 
		if ( ($this->maintainRatio === true )&& ($this->width != '') && ($this->height != '') )
		{
			$this->_imageReproportion();
		}

		/*
		 * Was a width and height specified?
		 *
		 * If the destination width/height was
		 * not submitted we will use the values
		 * from the actual file
		 *
		 **/
		if ($this->width == '')
		{
			$this->width = $this->origWidth;
		}

		if ($this->height == '')
		{
			$this->height = $this->origHeight;
		}

		// Set the quality
		$this->quality = trim (str_replace ("%", "", $this->quality));

		if ( ($this->quality == '') || ($this->quality == 0) || (is_numeric ($this->quality) === false) )
		{
			$this->quality = 90;
		}

		// Set the x/y coordinates
		$this->xAxis = ( ($this->xAxis == '') || (is_numeric ($this->xAxis) === false) ) ? 0 : $this->xAxis;
		$this->yAxis = ( ($this->yAxis == '') || (is_numeric ($this->yAxis) === false) ) ? 0 : $this->yAxis;

		// Watermark-related Stuff...
		if ($this->wmFontColor != '')
		{
			if (strlen ($this->wmFontColor) == 6)
			{
				$this->wmFontColor = '#' . $this->wmFontColor;
			}
		}

		if ($this->wmShadowColor != '')
		{
			if (strlen ($this->wmShadowColor) == 6)
			{
				$this->wmShadowColor = '#' . $this->wmShadowColor;
			}
		}

		if ($this->wmOverlayPath != '')
		{
			$this->wmOverlayPath = str_replace ("\\", "/", realpath ($this->wmOverlayPath));
		}

		if ($this->wmShadowColor != '')
		{
			$this->wmUseDropShadow = true;
		}

		if ($this->wmFontPath != '')
		{
			$this->wmUseTruetype = true;
		}

		return true;
	}

	/**
	 * Image Resize
	 *
	 * This is a wrapper function that chooses the proper
	 * resize function based on the protocol specified
	 *
	 * @access	public
	 * @return	bool
	 **/
	
	public function Resize ()
	{
		$protocol = '_imageProcess' . ucwords ($this->imageLibrary);

		if (preg_match ('/gd2$/i', $protocol))
		{
			$protocol = '_imageProcessGd';
		}

		return $this->$protocol ('resize');
	}

	/**
	 * Image Crop
	 *
	 * This is a wrapper function that chooses the proper
	 * cropping function based on the protocol specified
	 *
	 * @access	public
	 * @return	bool
	 **/
	
	public function Crop ()
	{
		$protocol = '_imageProcess' . ucwords ($this->imageLibrary);

		if (preg_match ('/gd2$/i', $protocol))
		{
			$protocol = '_imageProcessGd';
		}

		return $this->$protocol ('crop');
	}

	/**
	 * Image Rotate
	 *
	 * This is a wrapper function that chooses the proper
	 * rotation function based on the protocol specified
	 *
	 * @access	public
	 * @return	bool
	 **/
	
	public function Rotate ()
	{
		// Allowed rotation values
		$degs = array (90, 180, 270, 'vrt', 'hor');

		if ( ($this->rotationAngle == '') || (in_array ($this->rotationAngle, $degs) === false))
		{
			return false;	
		}

		// Reassign the width and height
		if ( ($this->rotationAngle == 90) || ($this->rotationAngle == 270) )
		{
			$this->width	= $this->origHeight;
			$this->height	= $this->origWidth;
		}
		else
		{
			$this->width	= $this->origWidth;
			$this->height	= $this->origHeight;
		}


		// Choose resizing public function
		if ( ($this->imageLibrary == 'imagemagick') || ($this->imageLibrary == 'netpbm') )
		{
			$protocol = '_imageProcess' . ucwords ($this->imageLibrary);

			return $this->$protocol ('rotate');
		}

		if ( ($this->rotationAngle == 'hor') || ($this->rotationAngle == 'vrt') )
		{
			return $this->_imageMirrorGd ();
		}
		else
		{
			return $this->image_rotate_gd();
		}
	}

	/**
	 * Image Process Using GD/GD2
	 *
	 * This function will resize or crop
	 *
	 * @access	private
	 * @param	string
	 * @return	bool
	 **/
	
	private function _imageProcessGd ($action = 'resize')
	{
		$v2_override = false;

		// If the target width/height match the source, && if the new file name is not equal to the old file name
		// we'll simply make a copy of the original with the new name... assuming dynamic rendering is off.
		if ($this->dynamicOutput === false)
		{
			if ( ($this->origWidth == $this->width) && ($this->origHeight == $this->height) )
			{
				if ($this->sourceImage != $this->newImage)
				{
					if (@copy ($this->fullSrcPath, $this->fullDstPath))
					{
						@chmod ($this->fullDstPath, FILE_WRITE_MODE);
					}
				}

				return true;
			}
		}

		// Let's set up our values based on the action
		if ($action == 'crop')
		{
			//  Reassign the source width/height if cropping
			$this->origWidth  = $this->width;
			$this->origHeight = $this->height;

			// GD 2.0 has a cropping bug so we'll test for it
			if ($this->_gdVersion() !== false)
			{
				$gdVersion = str_replace ('0', '', $this->_gdVersion ());
				$v2Override = ($gdVersion == 2) ? true : false;
			}
		}
		else
		{
			// If resizing the x/y axis must be zero
			$this->xAxis = 0;
			$this->yAxis = 0;
		}

		//  Create the image handle
		if ( ($srcImg = $this->_imageCreateGd ()) === false)
		{
			return false;
		}

		//  Create The Image
		//
		//  old conditional which users report cause problems with shared GD libs who report themselves as "2.0 or greater"
		//  it appears that this is no longer the issue that it was in 2004, so we've removed it, retaining it in the comment
		//  below should that ever prove inaccurate.
		//
		//  if ($this->imageLibrary == 'gd2' && function_exists('imagecreatetruecolor') && $v2_override == false)
		if ( ($this->imageLibrary == 'gd2') && (function_exists ('imagecreatetruecolor') === true) )
		{
			$create	= 'imagecreatetruecolor';
			$copy	= 'imagecopyresampled';
		}
		else
		{
			$create	= 'imagecreate';
			$copy	= 'imagecopyresized';
		}

		$dstImg = $create ($this->width, $this->height);

		if ($this->imageType == 3) // png we can actually preserve transparency
		{
			imagealphablending ($dstImg, false);
			imagesavealpha ($dstImg, true);
		}

		$copy ($dstImg, $srcImg, 0, 0, $this->xAxis, $this->yAxis, $this->width, $this->height, $this->origWidth, $this->origHeight);

		//  Show the image
		if ($this->dynamicOutput == true)
		{
			$this->_imageDisplayGd ($dstImg);
		}
		else
		{
			// Or save it
			if ($this->_imageSaveGd ($dstImg) === false)
			{
				return false;
			}
		}

		//  Kill the file handles
		imagedestroy ($dstImg);
		imagedestroy ($srcImg);

		// Set the file to 777
		@chmod ($this->fullDstPath, FILE_WRITE_MODE);

		return true;
	}

	/**
	 * Image Process Using ImageMagick
	 *
	 * This function will resize, crop or rotate
	 *
	 * @access	private
	 * @param	string
	 * @return	bool
	 **/
	
	private function _imageProcessImagemagick ($action = 'resize')
	{
		//  Do we have a vaild library path?
		if ($this->libraryPath == '')
		{
			return false;
		}

		if (!preg_match ("/convert$/i", $this->libraryPath))
		{
			$this->libraryPath = rtrim ($this->libraryPath, '/') . '/';

			$this->libraryPath .= 'convert';
		}

		// Execute the command
		$cmd = $this->libraryPath . " -quality " . $this->quality;

		if ($action == 'crop')
		{
			$cmd .= " -crop " . $this->width . "x" . $this->height . "+" . $this->xAxis . "+" . $this->yAxis . " \"$this->fullSrcPath\" \"$this->fullDstPath\" 2>&1";
		}
		else if ($action == 'rotate')
		{
			switch ($this->rotationAngle)
			{
				case 'hor':
					$angle = '-flop';
					
					break;
				case 'vrt':
					$angle = '-flip';
					
					break;
				default	:
					$angle = '-rotate ' . $this->rotationAngle;
					
					break;
			}

			$cmd .= " " . $angle . " \"$this->fullSrcPath\" \"$this->fullDstPath\" 2>&1";
		}
		else  // Resize
		{
			$cmd .= " -resize " . $this->width . "x" . $this->height . " \"$this->fullSrcPath\" \"$this->fullDstPath\" 2>&1";
		}

		$retval = 1;

		@exec ($cmd, $output, $retval);

		//	Did it work?
		if ($retval > 0)
		{
			return false;
		}

		// Set the file to 777
		@chmod ($this->fullDstPath, FILE_WRITE_MODE);

		return true;
	}

	/**
	 * Image Process Using NetPBM
	 *
	 * This function will resize, crop or rotate
	 *
	 * @access	private
	 * @param	string
	 * @return	bool
	 **/
	
	private function _imageProcessNetpbm ($action = 'resize')
	{
		if ($this->libraryPath == '')
		{
			return false;
		}

		//  Build the resizing command
		switch ($this->imageType)
		{
			case 1:
				$cmdIn	= 'giftopnm';
				$cmdOut	= 'ppmtogif';
				
				break;
			case 2:
				$cmdIn	= 'jpegtopnm';
				$cmdOut	= 'ppmtojpeg';
				
				break;
			case 3:
				$cmdIn	= 'pngtopnm';
				$cmdOut	= 'ppmtopng';
				
				break;
		}

		if ($action == 'crop')
		{
			$cmdInner = 'pnmcut -left ' . $this->xAxis . ' -top ' . $this->yAxis . ' -width ' . $this->width . ' -height ' . $this->height;
		}
		else if ($action == 'rotate')
		{
			switch ($this->rotationAngle)
			{
				case 90:
					$angle = 'r270';
					
					break;
				case 18:
					$angle = 'r180';
					
					break;
				case 270:
					$angle = 'r90';
					
					break;
				case 'vrt':
					$angle = 'tb';
					
					break;
				case 'hor':
					$angle = 'lr';
					
					break;
			}

			$cmdInner = 'pnmflip -' . $angle . ' ';
		}
		else // Resize
		{
			$cmdInner = 'pnmscale -xysize ' . $this->width . ' ' . $this->height;
		}

		$cmd = $this->libraryPath . $cmdIn . ' ' . $this->fullSrcPath . ' | ' . $cmdInner . ' | ' . $cmdOut . ' > ' . $this->destFolder . 'netpbm.tmp';

		$retval = 1;

		@exec ($cmd, $output, $retval);

		//  Did it work?
		if ($retval > 0)
		{
			return false;
		}

		// With NetPBM we have to create a temporary image.
		// If you try manipulating the original it fails so
		// we have to rename the temp file.
		copy ($this->destFolder . 'netpbm.tmp', $this->fullDstPath);
		unlink ($this->destFolder . 'netpbm.tmp');
		@chmod ($this->fullDstPath, FILE_WRITE_MODE);

		return true;
	}
	
	/**
	 * Image Rotate Using GD
	 *
	 * @access	public
	 * @return	bool
	 **/
	
	private function _imageRotateGd ()
	{
		//  Create the image handle
		if ( ($srcImg = $this->_imageCreateGd ()) === false)
		{
			return false;
		}

		// Set the background color
		// This won't work with transparent PNG files so we are
		// going to have to figure out how to determine the color
		// of the alpha channel in a future release.

		$white	= imagecolorallocate ($srcImg, 255, 255, 255);

		//  Rotate it!
		$dstImg = imagerotate ($srcImg, $this->rotationAngle, $white);

		//  Save the Image
		if ($this->dynamicOutput == true)
		{
			$this->_imageDisplayGd ($dstImg);
		}
		else
		{
			// Or save it
			if (!$this->_imageSaveGd ($dstImg))
			{
				return false;
			}
		}

		//  Kill the file handles
		imagedestroy ($dstImg);
		imagedestroy ($srcImg);

		// Set the file to 777

		@chmod ($this->fullDstPath, FILE_WRITE_MODE);

		return true;
	}
	
	/**
	 * Create Mirror Image using GD
	 *
	 * This function will flip horizontal or vertical
	 *
	 * @access	private
	 * @return	bool
	 **/
	
	private function _imageMirrorGd ()
	{
		if ( ($srcImg = $this->_imageCreateGd()) === false)
		{
			return false;
		}

		$width  = $this->origWidth;
		$height = $this->origHeight;

		if ($this->rotationAngle == 'hor')
		{
			for ($i = 0; $i < $height; $i++)
			{
				$left  = 0;
				$right = $width - 1;

				while ($left < $right)
				{
					$cl = imagecolorat ($srcImg, $left, $i);
					$cr = imagecolorat ($srcImg, $right, $i);

					imagesetpixel ($srcImg, $left, $i, $cr);
					imagesetpixel ($srcImg, $right, $i, $cl);

					$left++;
					$right--;
				}
			}
		}
		else
		{
			for ($i = 0; $i < $width; $i++)
			{
				$top = 0;
				$bot = $height - 1;

				while ($top < $bot)
				{
					$ct = imagecolorat ($srcImg, $i, $top);
					$cb = imagecolorat ($srcImg, $i, $bot);

					imagesetpixel ($srcImg, $i, $top, $cb);
					imagesetpixel ($srcImg, $i, $bot, $ct);

					$top++;
					$bot--;
				}
			}
		}

		//  Show the image
		if ($this->dynamicOutput == true)
		{
			$this->_imageDisplayGd ($srcImg);
		}
		else
		{
			// Or save it
			if (!$this->_imageSaveGd ($srcImg))
			{
				return false;
			}
		}

		//  Kill the file handles
		imagedestroy ($srcImg);

		// Set the file to 777
		@chmod ($this->fullDstPath, FILE_WRITE_MODE);

		return true;
	}

	/**
	 * Image Watermark
	 *
	 * This is a wrapper function that chooses the type
	 * of watermarking based on the specified preference.
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 **/
	
	public function Watermark ()
	{
		if ($this->wmType == 'overlay')
		{
			return $this->OverlayWatermark();
		}
		else
		{
			return $this->TextWatermark();
		}
	}

	/**
	 * Watermark - Graphic Version
	 *
	 * @access	public
	 * @return	bool
	 **/
	
	public function OverlayWatermark ()
	{
		if (function_exists ('imagecolortransparent') === false)
		{
			return false;
		}

		//  Fetch source image properties
		$this->_getImageProperties ();

		//  Fetch watermark image properties
		$props			= $this->_getImageProperties ($this->wmOverlayPath, true);
		$wmImgType		= $props['imageType'];
		$wmWidth		= $props['width'];
		$wmHeight		= $props['height'];

		//  Create two image resources
		$wmImg  = $this->_imageCreateGd ($this->wmOverlayPath, $wmImgType);
		$srcImg = $this->_imageCreateGd ($this->fullSrcPath);

		// Reverse the offset if necessary
		// When the image is positioned at the bottom
		// we don't want the vertical offset to push it
		// further down.  We want the reverse, so we'll
		// invert the offset.  Same with the horizontal
		// offset when the image is at the right

		$this->wmVrtAlignment = strtoupper (substr ($this->wmVrtAlignment, 0, 1));
		$this->wmHorAlignment = strtoupper (substr ($this->wmHorAlignment, 0, 1));

		if ($this->wmVrtAlignment == 'B')
		{
			$this->wmVrtOffset = $this->wmVrtOffset * -1;
		}

		if ($this->wmHorAlignment == 'R')
		{
			$this->wmHorOffset = $this->wmHorOffset * -1;
		}

		//  Set the base x and y axis values
		$xAxis = $this->wmHorOffset + $this->wmPadding;
		$yAxis = $this->wmVrtOffset + $this->wmPadding;

		//  Set the vertical position
		switch ($this->wmVrtAlignment)
		{
			case 'T':
				break;
			case 'M':
				$yAxis += ($this->origHeight / 2) - ($wmHeight / 2);
				
				break;
			case 'B':
				$yAxis += $this->origHeight - $wmHeight;
				
				break;
		}

		//  Set the horizontal position
		switch ($this->wmHorAlignment)
		{
			case 'L':
				break;
			case 'C':
				$xAxis += ($this->origWidth / 2) - ($wmWidth / 2);
				
				break;
			case 'R':
				$xAxis += $this->origWidth - $wmWidth;
				
				break;
		}

		//  Build the finalized image
		if ( ($wmImgType == 3) && (function_exists ('imagealphablending') === true) )
		{
			@imagealphablending ($srcImg, true);
		}

		// Set RGB values for text and shadow
		$rgba = imagecolorat ($wmImg, $this->wmXTransp, $this->wmYTransp);
		$alpha = ($rgba & 0x7F000000) >> 24;

		// make a best guess as to whether we're dealing with an image with alpha transparency or no/binary transparency
		if ($alpha > 0)
		{
			// copy the image directly, the image's alpha transparency being the sole determinant of blending
			imagecopy ($srcImg, $wmImg, $xAxis, $yAxis, 0, 0, $wmWidth, $wmHeight);
		}
		else
		{
			// set our RGB value from above to be transparent and merge the images with the specified opacity
			imagecolortransparent ($wmImg, imagecolorat ($wmImg, $this->wmXTransp, $this->wmYTransp));
			imagecopymerge ($srcImg, $wmImg, $xAxis, $yAxis, 0, 0, $wmWidth, $wmHeight, $this->wm_opacity);
		}

		//  Output the image
		if ($this->dynamicOutput == true)
		{
			$this->_imageDisplayGd ($srcImg);
		}
		else
		{
			if (!$this->_imageSaveGd ($srcImg))
			{
				return false;
			}
		}

		imagedestroy ($srcImg);
		imagedestroy ($wmImg);

		return true;
	}

	/**
	 * Watermark - Text Version
	 *
	 * @access	public
	 * @return	bool
	 **/
	
	public function TextWatermark ()
	{
		if ( ($srcImg = $this->ImageCreateGd ()) === false)
		{
			return false;
		}

		if ( ($this->wmUseTruetype == true) && (file_exists ($this->wmFontPath) === false) )
		{
			return false;
		}

		//  Fetch source image properties
		$this->_getImageProperties ();

		// Set RGB values for text and shadow
		$this->wmFontColor = str_replace ('#', '', $this->wmFontColor);
		$this->wmShadowColor = str_replace ('#', '', $this->wmShadowColor);

		$R1 = hexdec (substr ($this->wmFontColor, 0, 2) );
		$G1 = hexdec (substr ($this->wmFontColor, 2, 2) );
		$B1 = hexdec (substr ($this->wmFontColor, 4, 2) );

		$R2 = hexdec (substr ($this->wmShadowColor, 0, 2) );
		$G2 = hexdec (substr ($this->wmShadowColor, 2, 2) );
		$B2 = hexdec (substr ($this->wmShadowColor, 4, 2) );

		$txtColor	= imagecolorclosest ($srcImg, $R1, $G1, $B1);
		$drpColor	= imagecolorclosest ($srcImg, $R2, $G2, $B2);

		// Reverse the vertical offset
		// When the image is positioned at the bottom
		// we don't want the vertical offset to push it
		// further down.  We want the reverse, so we'll
		// invert the offset.  Note: The horizontal
		// offset flips itself automatically

		if ($this->wmVrtAlignment == 'B')
		{
			$this->wmVrtOffset = $this->wmVrtOffset * -1;
		}

		if ($this->wmHorAlignment == 'R')
		{
			$this->wmHorOffset = $this->wmHorOffset * -1;
		}

		// Set font width and height
		// These are calculated differently depending on
		// whether we are using the true type font or not
		if ($this->wmUseTruetype == true)
		{
			if ($this->wmFontSize == '')
			{
				$this->wmFontSize = '17';
			}

			$fontWidth  = $this->wmFontSize - ($this->wmFontSize / 4);
			$fontHeight = $this->wmFontSize;
			$this->wmVrtOffset += $this->wmFontSize;
		}
		else
		{
			$fontWidth  = imagefontwidth ($this->wmFontSize);
			$fontHeight = imagefontheight ($this->wmFontSize);
		}

		// Set base X and Y axis values
		$xAxis = $this->wmHorOffset + $this->wmPadding;
		$yAxis = $this->wmVrtOffset + $this->wmPadding;

		// Set verticle alignment
		if ($this->wmUseDropShadow == false)
		{
			$this->wmShadowDistance = 0;
		}

		$this->wmVrtAlignment = strtoupper (substr ($this->wmVrtAlignment, 0, 1));
		$this->wmHorAlignment = strtoupper (substr ($this->wmHorAlignment, 0, 1));

		switch ($this->wmVrtAlignment)
		{
			case "T" :
				break;
			case "M":
				$yAxis += ($this->origHeight / 2) + ($fontHeight / 2);
				
				break;
			case "B":
				$yAxis += ($this->origHeight - $fontheight - $this->wmShadowDistance - ($fontHeight / 2));
				
				break;
		}

		$xShad = $xAxis + $this->wmShadowDistance;
		$yShad = $yAxis + $this->wmShadowDistance;

		// Set horizontal alignment
		switch ($this->wmHorAlignment)
		{
			case "L":
				break;
			case "R":
				if ($this->wmUseDropShadow)
				{
					$xShad += ($this->origWidth - $fontWidth * strlen ($this->wmText));
				}
				
				$xAxis += ($this->origWidth - $fontWidth * strlen ($this->wmText));
				
				break;
			case "C":
				if ($this->wmUseDropShadow)
				{
					$xShad += floor ( ($this->origWidth - $fontWidth * strlen ($this->wmText)) / 2);
				}
				
				$xAxis += floor(($this->origWidth - $fontWidth * strlen ($this->wmText))/2);
				
				break;
		}

		//  Add the text to the source image
		if ($this->wmUseTruetype === true)
		{
			if ($this->wmUseDropShadow)
			{
				imagettftext ($srcImg, $this->wmFontSize, 0, $xShad, $yShad, $drpColor, $this->wmFontPath, $this->wmText);
			}
			imagettftext ($srcImg, $this->wmFontSize, 0, $xAxis, $yAxis, $txtColor, $this->wmFontPath, $this->wmText);
		}
		else
		{
			if ($this->wmUseDropShadow)
			{
				imagestring ($srcImg, $this->wmFontSize, $xShad, $yShad, $this->wmText, $drpColor);
			}
			
			imagestring ($srcImg, $this->wmFontSize, $xAxis, $yAxis, $this->wmText, $txtColor);
		}

		//  Output the final image
		if ($this->dynamicOutput == true)
		{
			$this->_imageDisplayGd ($srcImg);
		}
		else
		{
			$this->_imageSaveGd ($srcImg);
		}

		imagedestroy ($srcImg);

		return true;
	}

	/**
	 * Create Image - GD
	 *
	 * This simply creates an image resource handle
	 * based on the type of image being processed
	 *
	 * @access	private
	 * @param	string
	 * @return	resource
	 **/
	
	private function _imageCreateGd ($path = '', $imageType = '')
	{
		if ($path == '')
		{
			$path = $this->fullSrcPath;
		}

		if ($imageType == '')
		{
			$imageType = $this->imageType;
		}

		switch ($imageType)
		{
			case 1:
				if (function_exists ('imagecreatefromgif') === false)
				{
					return false;
				}

				return imagecreatefromgif ($path);
				
				break;
			case 2:
				if (function_exists ('imagecreatefromjpeg') === false)
				{
					return false;
				}

				return imagecreatefromjpeg ($path);
				
				break;
			case 3:
				if (function_exists ('imagecreatefrompng') === false)
				{
					return false;
				}

				return imagecreatefrompng ($path);
				
				break;

		}

		return false;
	}

	/**
	 * Write image file to disk - GD
	 *
	 * Takes an image resource as input and writes the file
	 * to the specified destination
	 *
	 * @access	private
	 * @param	resource
	 * @return	bool
	 **/
	
	private function _imageSaveGd ($resource)
	{
		switch ($this->imageType)
		{
			case 1:
				if (function_exists ('imagegif') === false)
				{
					return false;
				}

				if (@imagegif ($resource, $this->fullDstPath) === false)
				{
					return false;
				}
				
				break;
			case 2:
				if (function_exists('imagejpeg') === false)
				{
					return false;
				}

				if (@imagejpeg ($resource, $this->fullDstPath, $this->quality) === false)
				{
					return false;
				}
				
				break;
			case 3:
				if (function_exists ('imagepng') === false)
				{
					return false;
				}

				if (@imagepng ($resource, $this->fullDstPath) === false)
				{
					return false;
				}
				
				break;
			default:
				return false;
				
				break;
		}

		return true;
	}

	/**
	 * Dynamically outputs an image
	 *
	 * @access	public
	 * @param	resource
	 * @return	void
	 **/
	
	public function ImageDisplayGd ($resource)
	{
		header ("Content-Disposition: filename={$this->sourceImage};");
		header ("Content-Type: {$this->mimeType}");
		header ('Content-Transfer-Encoding: binary');
		header ('Last-Modified: ' . gmdate ('D, d M Y H:i:s', time()) . ' GMT');

		switch ($this->imageType)
		{
			case 1:
				imagegif ($resource);
				
				break;
			case 2:
				imagejpeg ($resource, '', $this->quality);
				
				break;
			case 3:
				imagepng ($resource);
				
				break;
			default:
				echo 'Unable to display the image';
				
				break;
		}
	}

	/**
	 * Re-proportion Image Width/Height
	 *
	 * When creating thumbs, the desired width/height
	 * can end up warping the image due to an incorrect
	 * ratio between the full-sized image and the thumb.
	 *
	 * This function lets us re-proportion the width/height
	 * if users choose to maintain the aspect ratio when resizing.
	 *
	 * @access	private
	 * @return	void
	 **/
	
	public function _imageReproportion ()
	{
		if ( (is_numeric ($this->width) === false) || (is_numeric ($this->height) === false) || ($this->width == 0) || ($this->height == 0) )
		{
			return;
		}


		if ( (is_numeric ($this->origWidth) === false) || (is_numeric ($this->origHeight) === false) || ($this->origWidth == 0) || ($this->origHeight == 0) )
		{
			return;
		}

		$newWidth	= ceil ($this->origWidth * $this->height / $this->origHeight);
		$newHeight	= ceil ($this->width * $this->origHeight / $this->origWidth);

		$ratio = ( ($this->origHeight / $this->origWidth) - ($this->height / $this->width));

		if ( ($this->masterDim != 'width') && ($this->masterDim != 'height') )
		{
			$this->masterDim = ($ratio < 0) ? 'width' : 'height';
		}

		if ( ($this->width != $newWidth) && ($this->height != $newHeight))
		{
			if ($this->masterDim == 'height')
			{
				$this->width = $newWidth;
			}
			else
			{
				$this->height = $newHeight;
			}
		}
	}

	/**
	 * Get image properties
	 *
	 * A public function that gets info about the file
	 *
	 * @access	private
	 * @param	string
	 * @return	mixed
	 **/
	
	public function _getImageProperties ($path = '', $return = false)
	{
		// For now we require GD but we should
		// find a way to determine this using IM or NetPBM

		if ($path == '')
		{
			$path = $this->fullSrcPath;
		}

		if (file_exists ($path) === false)
		{
			return false;
		}

		$vals = @getimagesize ($path);

		$types = array (1 => 'gif', 2 => 'jpeg', 3 => 'png');

		$mime = (isset ($types[$vals['2']])) ? 'image/' . $types[$vals['2']] : 'image/jpg';

		if ($return == true)
		{
			$v['width']			= $vals['0'];
			$v['height']		= $vals['1'];
			$v['imageType']		= $vals['2'];
			$v['sizeStr']		= $vals['3'];
			$v['mimeType']		= $mime;

			return $v;
		}

		$this->origWidth	= $vals['0'];
		$this->origHeight	= $vals['1'];
		$this->imageType	= $vals['2'];
		$this->sizeStr		= $vals['3'];
		$this->mimeType	= $mime;

		return true;
	}

	/**
	 * Size calculator
	 *
	 * This function takes a known width x height and
	 * recalculates it to a new size.  Only one
	 * new variable needs to be known
	 *
	 *	$props = array(
	 *					'width'			=> $width,
	 *					'height'		=> $height,
	 *					'new_width'		=> 40,
	 *					'new_height'	=> ''
	 *				  );
	 *
	 * @access	public
	 * @param	array
	 * @return	array
	 **/
	
	private function _sizeCalculator ($vals)
	{
		if (is_array ($vals) === false)
		{
			return;
		}

		$allowed = array ('newWidth', 'newHeight', 'width', 'height');

		foreach ($allowed as $item)
		{
			if ( (isset ($vals[$item]) === false) || ($vals[$item] == '') )
			{
				$vals[$item] = 0;
			}
		}

		if ( ($vals['width'] == 0) || ($vals['height'] == 0) )
		{
			return $vals;
		}

		if ($vals['newWidth'] == 0)
		{
			$vals['newWidth'] = ceil ($vals['width'] * $vals['newHeight'] / $vals['height']);
		}
		else if ($vals['newHeight'] == 0)
		{
			$vals['newHeight'] = ceil ($vals['newWidth'] * $vals['height'] / $vals['width']);
		}

		return $vals;
	}

	/**
	 * Explode sourceImage
	 *
	 * This is a helper function that extracts the extension
	 * from the sourceImage.  This public function lets us deal with
	 * sourceImages with multiple periods, like:  my.cool.jpg
	 * It returns an associative array with two elements:
	 * $array['ext']  = '.jpg';
	 * $array['name'] = 'my.cool';
	 *
	 * @access	private
	 * @param	array
	 * @return	array
	 **/
	
	private function _explodeName ($sourceImage)
	{
		$ext = strrchr ($sourceImage, '.');
		$name = ($ext === false) ? $sourceImage : substr ($sourceImage, 0, -strlen ($ext));

		return array ('ext' => $ext, 'name' => $name);
	}
	
	/**
	 * Is GD Installed?
	 *
	 * @access	private
	 * @return	bool
	 **/
	 
	private function _gdLoaded ()
	{
		if (extension_loaded ('gd') === false)
		{
			if (dl ('gd.so') === false)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Get GD version
	 *
	 * @access	private
	 * @return	mixed
	 **/
	
	private function _gdVersion ()
	{
		if (function_exists ('gd_info') === true)
		{
			$gdVersion = @gd_info ();
			$gdVersion = preg_replace ("/\D/", "", $gdVersion['GD Version']);

			return $gdVersion;
		}

		return false;
	}
}