<?php

/***************************************************************************
 *   Application          : Emotion                                        *
 *   -------------------------------------------------------------------   *
 *   Package              : @library                                       *
 *   File                 : captcha.php                                    *
 *   Version              : 1.0.0                                          *
 *   Status               : Complete, Not tested                           *
 *   -------------------------------------------------------------------   *
 *   Begin                : Friday, November 14, 2008                      *
 *   CopyRight            : (C) 2008 ArvYStaTe.net Team                    *
 *   E-Mail               : support@arvystate.net                          *
 *   Last edit            : Friday, November 14, 2008                      *
 *                                                                         *
 *                                                                         *
 ***************************************************************************

 ***************************************************************************
 *   File description                                                      *
 *   -------------------------------------------------------------------   *
 *   Captcha is a class that provides basic captcha generation. Captcha    *
 *   stands for Completely Automated Public Turing test to tell            *
 *   Computers and Humans Apart. Based on configuration in this file and   *
 *   additional files and/or parameters class will generate a basic        *
 *   captcha. This functionality is experimental.                          *
 *   -------------------------------------------------------------------   *
 *                                                                         *
 ***************************************************************************
 
 ***************************************************************************
 *   Change log                                                            *
 *   -------------------------------------------------------------------   *
 *    + [11/14/2008] - File created                                        *
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
 * Captcha
 *
 * Automatically generated image to prevent macros and client-side scripts.
 *
 * @package	library
 * @require	false
 * @author	ArvYStaTe.net Team
 **/

class Captcha
{
	//
	// Private variables, configuration
	//
	
	// Image configuration
	private $imageWidth;
	private $imageHeight;
	private $imageType;
	private $imageCode;
	
	private $imageBgDir;
	private $imageFontDir;
	
	private $useBgFile = false;
	private $allowedExtensions = 'png,jpg,gif';
	
	// Colors
	private $imageBgColors = '#E3DAED, #E2AD21, #DA79EB, #FBF3A7';
	private $textColors = '#223300,#334400,#2322DD';
	private $lineColors = '#80BFFF';
	private $arcLineColors = '#8080FF, #FFFFFF, #000000';
	
	private $useMultiText = true;	
	
	// Text configuration
	private $textAngleMinimum = -20;
	private $textAngleMaximum = 20;
	private $textXStart = 8;
	private $textMinimumDistance = 8;
	private $textMaximumDistance = 15;
	
	private $textTransparencyPercentage = 15;
	private $useTransparentText = true;
	private $shadowText = false;
	
	// Font configuration
	private $useGdFont;
	private $fontSize = 15;
	private $gdFontSize = 14;
	
	// Line and arc configuration
	private $drawLines = true;
	private $lineDistance = 5;
	private $lineThickness = 1;
	private $drawAngledLines = false;
	private $drawLinesOverText = false;
	private $arcLineThrough = true;
	
	//
	// System variables, do NOT change!
	//

	private $im;
	private $bgimg;

	/**
	 * Creates Captcha based on parameters
	 *
	 * @access	public
	 * @param	integer	width in pixels
	 * @param	integer	height in pixels
	 * @param	string	text to be displayed on captcha (may be random string)
	 * @param	string	which type of the image should be generated: jpg, gif or png
	 * @param	string	font directory where there are .TTF fonts stored
	 * @param	string	background image directory
	 * @param	boolean	using gd fonts
	 * @return	void
	 **/
	
	public function Create ($width, $height, $code, $type, $fontDir, $imageDir = '', $useGdFont = false)
	{
		$this->imageWidth = $width;
		$this->imageHeight = $height;
		$this->imageCode = $code;
		
		// Selecting image type
		switch ($type)
		{
			case 'jpg':
				$this->imageType = 'jpg';
				break;
			case 'gif':
				$this->imageType = 'gif';
				break;
			default:
				$this->imageType = 'png';
				break;
		}
		
		$this->imageFontDir = $fontDir;
		$this->imageBgDir = $imageDir;
		$this->useGdFont = $useGdFont;

		// Creating image
		$this->_doImage ();
	}

	/**
	 * Generates Captcha based on current configuration
	 *
	 * @access	private
	 * @return	void
	 **/

	private function _doImage ()
	{
		// Selecting background, based on if it is read from a file
		
		if ( ($this->imageBgDir != '') && ($this->useBgFile === true) )
		{
			// Random script selects if background will be from file or generated
			if (rand(0, 1))
			{
				$files = $this->getDirFiles ($this->imageBgDir, $this->allowedExtensions);
			
				if (count($files) > 0)
				{
					$this->bgimg = $this->imageBgDir . '/' . $files[rand(0, count ($files) - 1)];
				}
				else
				{
					$this->bgimg = '';
				}
			}
			// It will be generated
			else
			{
				$this->bgimg = '';
			}
		}
		
		$randomBgColor = $this->_getColors ($this->imageBgColors);
		
		$randomBgColor = $randomBgColor[rand(0, count ($randomBgColor) - 1)];
		
		// Transparent text
		if ( ($this->useTransparentText == true) || ($this->bgimg != '') )
		{
			$this->im = imagecreatetruecolor ($this->imageWidth, $this->imageHeight);
			$bgcolor = imagecolorallocate ($this->im, hexdec (substr ($randomBgColor, 1, 2)), hexdec (substr ($randomBgColor, 3, 2)), hexdec (substr ($randomBgColor, 5, 2)));
			imagefilledrectangle ($this->im, 0, 0, imagesx ($this->im), imagesy ($this->im), $bgcolor);
		}
		else
		{
			// No transparency
			$this->im = imagecreate ($this->imageWidth, $this->imageHeight);
			$bgcolor = imagecolorallocate ($this->im, hexdec (substr ($randomBgColor, 1, 2)), hexdec (substr ($randomBgColor, 3, 2)), hexdec (substr ($randomBgColor, 5, 2)));
		}

		if ($this->bgimg != '')
		{
			$this->_setBackground ();
		}

		if (!$this->drawLinesOverText && $this->drawLines)
		{
			$this->_drawLines ();
		}

		$this->_drawWord ();

		if ($this->arcLineThrough == true)
		{
			$this->_arcLines();
		}
		
		if ($this->drawLinesOverText && $this->drawLines)
		{
			$this->_drawLines ();
		}
	}

	/**
	 * Generates Captcha Background
	 *
	 * @access	private
	 * @return	void
	 **/

	private function _setBackground ()
	{
		$dat = @getimagesize ($this->bgimg);
	
		if ($dat == false)
		{
			return;
		}

		switch ($dat[2])
		{
			case 1:
				$newim = @imagecreatefromgif ($this->bgimg);
				break;
			case 2:
				$newim = @imagecreatefromjpeg ($this->bgimg);
				break;
			case 3:
				$newim = @imagecreatefrompng ($this->bgimg);
				break;
			case 15:
				$newim = @imagecreatefromwbmp ($this->bgimg);
				break;
			case 16:
				$newim = @imagecreatefromxbm ($this->bgimg);
				break;
			default:
				return;
		}

		if (!$newim)
		{
			return;
		}

		imagecopy ($this->im, $newim, 0, 0, 0, 0, $this->imageWidth, $this->imageHeight);
	}

	/**
	 * Generates random arch lines to make Captcha harder to crack
	 *
	 * @access	private
	 * @return void
	 **/
	 
	private function _arcLines ()
	{
		$colors = $this->_getColors ($this->arcLineColors);
		imagesetthickness ($this->im, 2);

		$color = $colors[rand(0, sizeof ($colors) - 1)];
		$lineColor = imagecolorallocate ($this->im, hexdec (substr ($color, 1, 2)), hexdec (substr ($color, 3, 2)), hexdec (substr ($color, 5, 2)));

		$xpos = $this->textXStart + ($this->fontSize * 2) + rand (-5, 5);
		$width = $this->imageWidth / 2.66 + rand(3, 10);
		$height = $this->fontSize * 2.14 - rand(3, 10);

		if (rand (0, 100) % 2 == 0)
		{
			$start = rand (0, 66);
			$ypos = $this->imageHeight / 2 - rand (5, 15);
			$xpos += rand (5, 15);
		}
		else
		{
			$start = rand (180, 246);
			$ypos = $this->imageHeight / 2 + rand (5, 15);
		}

		$end = $start + rand (75, 110);

		imagearc ($this->im, $xpos, $ypos, $width, $height, $start, $end, $lineColor);

		$color = $colors[rand (0, sizeof ($colors) - 1)];
		$lineColor = imagecolorallocate ($this->im, hexdec (substr ($color, 1, 2)), hexdec (substr ($color, 3, 2)), hexdec (substr ($color, 5, 2)));

		if (rand (1,75) % 2 == 0)
		{
			$start = rand (45, 111);
			$ypos = $this->imageHeight / 2 - rand (5, 15);
			$xpos += rand (5, 15);
		}
		else
		{
			$start = rand (200, 250);
			$ypos = $this->imageHeight / 2 + rand (5, 15);
		}

		$end = $start + rand (75, 100);

		imagearc ($this->im, $this->imageWidth * .75, $ypos, $width, $height, $start, $end, $lineColor);
	}

	/**
	 * Generates horizontal and vertical lines to make Captcha harder to crack
	 *
	 * @access	private
	 * @return	void
	 **/
	
	private function _drawLines ()
	{
		$randomLineColor = $this->_getColors ($this->lineColors);
		
		$randomLineColor = $randomLineColor[rand(0, count ($randomLineColor) - 1)];
		
		$lineColor = imagecolorallocate ($this->im, hexdec (substr ($randomLineColor, 1, 2)), hexdec (substr ($randomLineColor, 3, 2)), hexdec (substr ($randomLineColor, 5, 2)));
		imagesetthickness ($this->im, $this->lineThickness);

		// Vertical lines
		for ($x = 1; $x < $this->imageWidth; $x += $this->lineDistance)
		{
			imageline ($this->im, $x, 0, $x, $this->imageHeight, $lineColor);
		}

		// Horizontal lines
		for ($y = 11; $y < $this->imageHeight; $y += $this->lineDistance)
		{
			imageline ($this->im, 0, $y, $this->imageWidth, $y, $lineColor);
		}

		if ($this->drawAngledLines == true)
		{
			for ($x = -($this->imageHeight); $x < $this->imageWidth; $x += $this->lineDistance)
			{
				imageline ($this->im, $x, 0, $x + $this->imageHeight, $this->imageHeight, $lineColor);
			}

			for ($x = $this->imageWidth + $this->imageHeight; $x > 0; $x -= $this->lineDistance)
			{
				imageline ($this->im, $x, 0, $x - $this->imageHeight, $this->imageHeight, $lineColor);
			}
		}
	}

	/**
	 * Generates word on Captcha
	 *
	 * @access	public
	 * @return	void
	 **/
	
	private function _drawWord ()
	{
		$textColors = $this->_getColors ($this->textColors);
		$textColor = $textColors[rand(0, count ($textColors) - 1)];
		
		//
		// Using GD font
		//
		
		if ($this->useGdFont == true)
		{
			$files = $this->_getDirFiles ($this->imageFontDir, 'gdf');
			
			if (count($files) == 0)
			{
				die ();
			}
			else
			{
				$fontFile = $this->imageFontDir . '/' . $files[rand(0, count ($files) - 1)];
				unset ($files);
			}
		
			// Font file is a file
			if (!is_int($font_file))
			{
				$font = @imageloadfont ($fontFile);
				if ($font == false)
				{
					die ();
					return;
				}
			}
			// GD font is already identified
			else
			{
				$font = $fontFile;
			}
			
			$color = imagecolorallocate ($this->im, hexdec (substr ($textColor, 1, 2)), hexdec (substr ($text_color, 3, 2)), hexdec (substr ($textColor, 5, 2)));
			imagestring ($this->im, $font, $this->textXStart, ($this->imageHeight / 2) - ($this->gdFontSize / 2), $this->imageCode, $color);
		} 
		
		//
		// Using TTF fonts
		//
		else
		{
			if ($this->useTransparentText == true)
			{
				$alpha = intval ($this->textTransparencyPercentage / 100 * 127);
				$fontColor = imagecolorallocatealpha ($this->im, hexdec (substr ($textColor, 1, 2)), hexdec (substr ($textColor, 3, 2)), hexdec (substr ($textColor, 5, 2)), $alpha);
			}
			else
			{
				$fontColor = imagecolorallocate ($this->im, hexdec (substr ($textColor, 1, 2)), hexdec (substr ($textColor, 3, 2)), hexdec (substr ($textColor, 5, 2)));
			}

			$x = $this->textXStart;
			$strlen = strlen ($this->imageCode);
			$yMin = ($this->imageHeight / 2) + ($this->fontSize / 2) - 2;
			$yMax = ($this->imageHeight / 2) + ($this->fontSize / 2) + 2;
			$colors = $textColors;

			$files = $this->_getDirFiles ($this->imageFontDir, 'ttf');
			
			if (count ($files) == 0)
			{
				die ();
			}

			for ($i = 0; $i < $strlen; $i++)
			{
				$angle = rand ($this->textAngleMinimum, $this->textAngleMaximum);
				$y = rand ($yMin, $yMax);
				
				if ($this->useMultiText == true)
				{
					$idx = rand (0, count ($colors) - 1);
					$r = substr ($colors[$idx], 1, 2);
					$g = substr ($colors[$idx], 3, 2);
					$b = substr ($colors[$idx], 5, 2);
					
					if ($this->useTransparentText == true)
					{
						$font_color = imagecolorallocatealpha ($this->im, "0x$r", "0x$g", "0x$b", $alpha);
					}
					else
					{
						$font_color = imagecolorallocate ($this->im, "0x$r", "0x$g", "0x$b");
					}
				}
				
				imagettftext ($this->im, $this->fontSize, $angle, $x, $y, $fontColor, $this->imageFontDir . '/' . $files[rand(0, count($files) - 1)], $this->imageCode{$i});
				
				if ($this->shadowText == true)
				{
					imagettftext ($this->im, $this->fontSize, $angle, $x + 2, $y + 2, $fontColor, $this->imageFontDir . '/' . $files[rand(0, count ($files) - 1)], $this->imageCode{$i});
				}
				
				$x += rand ($this->textMinimumDistance, $this->textMaximumDistance);
			}
		}
	}

	/**
	 * Outputs Captcha to browser
	 *
	 * @access	public
	 * @return	void
	 **/
	
	public function Show ()
	{
		if ($this->im != null)
		{
			header ("Expires: Sun, 1 Jan 2000 12:00:00 GMT");
			header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
			header ("Cache-Control: no-store, no-cache, must-revalidate");
			header ("Cache-Control: post-check=0, pre-check=0", false);
			header ("Pragma: no-cache");
			
			switch ($this->imageType)
			{
				case 'jpg':
					header ("Content-Type: image/jpeg");
					imagejpeg ($this->im, null, 90);
					break;
				
				case 'gif':
					header ("Content-Type: image/gif");
					imagegif ($this->im);
					break;
				
				default:
					header("Content-Type: image/png");
					imagepng ($this->im);
					break;
			}
			
			imagedestroy ($this->im);
			
			$this->im = null;
		}
	}
	
	/**
	 * Finds HTML colors in string and returns them as array
	 *
	 * @access	private
	 * @return	array	array of HTML colors with # prefix
	 **/
	
 	private function _getColors ($colorString)
	{
		$colors = array ();
		
		// Removing spaces and #
		$colorString = str_replace (' ', '', $colorString);
		$colorString = str_replace ('#', '', $colorString);
		
		// Diving colors by comma
		$colorArray = explode (',', $colorString);
		
		// Looping through whole color array
		for ($i = 0, $x = 0; $i < count ($colorArray); $i++)
		{
			// Only uppercase results are valid
			$colorArray[$i] = strtoupper ($colorArray[$i]);
		
			// Selecting only colors that match HTML colors
			if (preg_match ("/^[A-F0-9]{6}$/", $colorArray[$i]) !== false)
			{
				$colors[$x] = '#' . $colorArray[$i];
				$x++;
			}
		}
		
		return $colors;
	}
	
	/**
	 * Scans directory and returns array of files matching the extension
	 *
	 * @access	private
	 * @return	array	array of files matching selected extension
	 **/
	
	private function _getDirFiles ($directory, $extensions)
	{
		// Directory does not exist
		if (is_dir ($directory) === false)
		{
			return;
		}
		
		// Getting all extensions
		$extensions = str_replace (' ', '', $extensions);
		$extensions = explode (',', $extensions);
		
		// No extensions sent
		if (count ($extensions) == 0)
		{
			return;
		}		
		
		for ($i = 0; $i < count ($extensions); $i++)
		{
			$extensions[$i] = strtolower ($extensions[$i]);
		}
				
		$files = array ();
		
		// Scanning directory
		$scan = scandir ($directory);
		
		// Skipping . and .. directories and scanning files
		for ($i = 2, $x = 0; $i < count ($scan); $i++)
		{
			// Saving only files with selected extension
			if (in_array (strtolower (substr ($scan[$i], (strlen ($scan[$i]) - 3), 3)), $extensions) === true)
			{
				$files[$x] = $scan[$i];
				$x++;
			}
		}
		
		return $files;
	}
}
?>