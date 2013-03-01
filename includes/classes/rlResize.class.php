<?php

/******************************************************************************
 *	
 *	PROJECT: Flynax Classifieds Software
 *	VERSION: 4.1.0
 *	LICENSE: FL43K5653W2I - http://www.flynax.com/license-agreement.html
 *	PRODUCT: Real Estate Classifieds
 *	DOMAIN: avisos.com.bo
 *	FILE: RLRESIZE.CLASS.PHP
 *	
 *	The software is a commercial product delivered under single, non-exclusive,
 *	non-transferable license for one domain or IP address. Therefore distribution,
 *	sale or transfer of the file in whole or in part without permission of Flynax
 *	respective owners is considered to be illegal and breach of Flynax License End
 *	User Agreement.
 *	
 *	You are not allowed to remove this information from the file without permission
 *	of Flynax respective owners.
 *	
 *	Flynax Classifieds Software 2013 | All copyrights reserved.
 *	
 *	http://www.flynax.com/
 ******************************************************************************/

class rlResize {

	var $strOriginalImagePath;
	var $strResizedImagePath;
	var $arrOriginalDetails;
	var $arrResizedDetails;
	var $resOriginalImage;
	var $resResizedImage;
	var $boolProtect = true;
	
	/**
	* @var $gdVersion - gd version
	**/
	var $gdVersion;
	
	/**
	* @var $returnRes - return resu;t
	**/
	var $returnRes = false;
	
	/**
	* @var $rlValid - valid class object
	**/
	var $rlValid;
	
	/**
	* @var $watermark - allow watermark with resize oteration
	**/
	var $rlWatermark;

	function rlResize()
	{
		global $rlValid;
		
		$this -> rlValid = $rlValid;
		
		$_gd_info = gd_info();
		if ( !$_gd_info )
             return false;        
   
		preg_match( '/(\d)\.(\d)/', $_gd_info['GD Version'], $_match );
   
		$this -> gdVersion = $_match[1];
	}
	
	/*
	*
	*	@Method:		rlResize
	*	@Parameters:	5
	*	@Param-1:		strPath - String - The path to the image
	*	@Param-2:		strSavePath - String - The path to save the new image to
	*	@Param-3:		strType - String - The type of resize you want to perform
	*	@Param-4:		value - Number/Array - The resize dimensions
	*	@Param-5:		boolProect - Boolen - Protects the image so that it doesnt resize an image if its already smaller
	*	@Description:	Calls the RVJ_Pagination method so its php 4 compatible
	*
	*/
	function resize($strPath, $strSavePath, $strType = 'W', $value = '150', $boolProtect = true, $watermark = true)
	{
		//save the image/path details
		$this->strOriginalImagePath = $strPath;
		$this->strResizedImagePath = $strSavePath;
		$this->boolProtect = $boolProtect;
		$this->rlWatermark = $watermark;
		
		//get the image dimensions
		$this->arrOriginalDetails = getimagesize($this->strOriginalImagePath);
		$this->arrResizedDetails = $this->arrOriginalDetails;
		
		//create an image resouce to work with
		$this->resOriginalImage = $this->createImage($this->strOriginalImagePath);
														
		//select the image resize type
		switch(strtoupper($strType)){
			case 'P':
				$this->resizeToPercent($value);
				break;
			case 'H':
				$this->resizeToHeight($value);
				break;
			case 'C':
				$this->resizeToCustom($value);
				break;
			case 'W':
			default:
				$this->resizeToWidth($value);
				break;
		}
	}

	/*
	*
	*	@Method:		findResourceDetails
	*	@Parameters:	1
	*	@Param-1:		resImage - Resource - The image resource you want details on
	*	@Description:	Returns an array of details about the resource identifier that you pass it
	*
	*/
	function findResourceDetails($resImage){
		//check to see what image is being requested
		if($resImage==$this->resResizedImage){										
			//return new image details
			return $this->arrResizedDetails;
		}else{
			//return original image details
			return $this->arrOriginalDetails;
		}
	}

	/*
	*
	*	@Method:		updateNewDetails	
	*	@Parameters:	0
	*	@Description:	Updates the width and height values of the resized details array
	*
	*/
	function updateNewDetails(){
		$this->arrResizedDetails[0] = imagesx($this->resResizedImage);
		$this->arrResizedDetails[1] = imagesy($this->resResizedImage);
	}
		
	/*
	*
	*	@Method:		createImage
	*	@Parameters:	1
	*	@Param-1:		strImagePath - String - The path to the image
	*	@Description:	Created an image resource of the image path passed to it
	*
	*/
	function createImage($strImagePath){
		//get the image details
		$arrDetails = $this->findResourceDetails($strImagePath);
		
		//choose the correct function for the image type
		switch($arrDetails['mime']){
			case 'image/jpeg':
				return imagecreatefromjpeg($strImagePath);
				break;
			case 'image/png':
				return imagecreatefrompng($strImagePath);
				break;
			case 'image/gif':
				return imagecreatefromgif($strImagePath);
				break;
		}
	}
		
	/*
	*
	*	@Method:		saveImage
	*	@Parameters:	1
	*	@Param-1:		numQuality - Number - The quality to save the image at
	*	@Description:	Saves the resize image
	*
	*/
	function saveImage()
	{
		$numQuality = $GLOBALS['config']['img_quality'];
		
		// woretmark action
		if ( $GLOBALS['config']['watermark_using'] && $this -> rlWatermark )
		{
			if ( $GLOBALS['config']['watermark_type'] == 'image' )
			{
				$w_source = $GLOBALS['config']['watermark_image_url'];
				$watermark = imagecreatefrompng($w_source);
				
				if ( $watermark )
				{
					list($watermark_width,$watermark_height) = getimagesize($w_source);
					$image = $this->resResizedImage;
		
					$dest_x = $this->arrResizedDetails[0] - $watermark_width - 5;
					$dest_y = $this->arrResizedDetails[1] - $watermark_height - 5;
					
					//imagealphablending($image, false);
					imagesavealpha($this->resResizedImage, true);
					imagecopyresampled($this->resResizedImage, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height, $watermark_width, $watermark_height);
		
					/* clear memory */
					imagedestroy($watermark);
				}
			}
			else
			{
				$w_text = $GLOBALS['config']['watermark_text'];
				
				if (empty($w_text))
				{
					$w_text = $this -> rlValid -> getDomain( RL_URL_HOME );
				}
				
				$w_blank = round( strlen( $w_text ) * 6.5 );
				
				$watermark = imagecreatetruecolor($w_blank, 18);
				$bgc = imagecolortransparent($watermark, 0);
				$tc  = imagecolorallocate($watermark, 255, 255, 255);
				imagefilledrectangle($watermark, 0, 0, $w_blank, 18, $bgc);
				
				imagestring($watermark, 2, 5, 4, $w_text, $tc);
		
				$watermark_width = imagesx($watermark);  
				$watermark_height = imagesy($watermark); 
		
				$x = 5;
				$y = 5;
				
				$dest_x = $this->arrResizedDetails[0] - $watermark_width - $x;
				$dest_y = $this->arrResizedDetails[1] - $watermark_height - $y;
		
				imagecopymerge($this->resResizedImage, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height, 100);
				
				/* clear memory */
				imagedestroy($watermark);
				imagedestroy($bgc);
				imagedestroy($tc);
			}
		}
		
		switch($this->arrResizedDetails['mime']){
			case 'image/jpeg':
				$this -> returnRes = imagejpeg($this->resResizedImage, $this->strResizedImagePath, $numQuality);
				break;
			case 'image/png':
				$this -> returnRes = imagepng($this->resResizedImage, $this->strResizedImagePath);
				break;
			case 'image/gif':
				$this -> returnRes = imagegif($this->resResizedImage, $this->strResizedImagePath);
				break;
		}
	}
	
	/*
	*
	*	@Method:		showImage
	*	@Parameters:	1
	*	@Param-1:		resImage - Resource - The resource of the image you want to display
	*	@Description:	Displays the image resouce on the screen
	*
	*/
	function showImage($resImage){
		//get the image details
		$arrDetails = $this->findResourceDetails($resImage);
		
		//set the correct header for the image we are displaying
		header("Content-type: ".$arrDetails['mime']);
		
		switch($arrDetails['mime']){
			case 'image/jpeg':
				return imagejpeg($resImage);
				break;
			case 'image/png':
				return imagepng($resImage);
				break;
			case 'image/gif':
				return imagegif($resImage);
				break;
		}
	}
	
	/*
	*
	*	@Method:		destroyImage
	*	@Parameters:	1
	*	@Param-1:		resImage - Resource - The image resource you want to destroy
	*	@Description:	Destroys the image resource and so cleans things up
	*
	*/
	function destroyImage(){
		imagedestroy($this->resResizedImage);
		imagedestroy($this->resOriginalImage);
		
		unset($this->resResizedImage);
		unset($this->strResizedImagePath);
		unset($this->resOriginalImage);
		unset($this->strOriginalImagePath);
	}
	
	/*
	*
	*	@Method:		_resize
	*	@Parameters:	2
	*	@Param-1:		numWidth - Number - The width of the image in pixels
	*	@Param-2:		numHeight - Number - The height of the image in pixes
	*	@Description:	Resizes the image by creatin a new canvas and copying the image over onto it. DONT CALL THIS METHOD DIRECTLY - USE THE METHODS BELOW
	*
	*/
	function _resize($numWidth, $numHeight){
		global $config;
		
		switch ($this->arrOriginalDetails['mime']){
			case 'image/gif':
				//GIF image
				$this->resResizedImage = imagecreate($numWidth, $numHeight);
				break;
				
			case 'image/png':
				//PNG image
				$this->resResizedImage = imagecreatetruecolor($numWidth, $numHeight);
				imagealphablending($this->resResizedImage, false);
				imagesavealpha($this->resResizedImage, true);
				
				break;
				
			default:
				//JPG image
				$this->resResizedImage = imagecreatetruecolor($numWidth, $numHeight);
				
				break;
		}
		
		/* create white background */
		$white = imagecolorallocate($this->resResizedImage, 255, 255, 255);
		imagefill($this->resResizedImage, 0, 0, $white);
		
		//update the image size details
		$this -> updateNewDetails();

		$resize_method = function_exists('imagecopyresampled') ? 'imagecopyresampled' : 'imagecopyresized';
		
		//do the actual image resize
		if ( !$config['img_crop_module'] && !$this -> boolProtect )
		{
			if ( ($this->arrOriginalDetails[0] / $this->arrOriginalDetails[1]) >= ($numWidth / $numHeight) )
			{
			    // by width
			    $nw = $numWidth;
			    $nh = $this->arrOriginalDetails[1] * ($numWidth / $this->arrOriginalDetails[0]);
			    $nx = 0;
			    $ny = round(abs($numHeight - $nh) / 2);
			}
			else
			{
			    // by height
			    $nw = $this->arrOriginalDetails[0] * ($numHeight / $this->arrOriginalDetails[1]);
			    $nh = $numHeight;//???
			    $nx = round(abs($numWidth - $nw) / 2);
			    $ny = 0;
			}
			
			$resize_method($this->resResizedImage, $this->resOriginalImage, $nx, $ny, 0, 0, $nw, $nh, $this->arrOriginalDetails[0], $this->arrOriginalDetails[1]);
		}
		else
		{
			$resize_method($this->resResizedImage, $this->resOriginalImage, 0, 0, 0, 0, $numWidth, $numHeight, $this->arrOriginalDetails[0], $this->arrOriginalDetails[1]);
		}
		

		//saves the image
		$this->saveImage();
		
		$this -> destroyImage();
	}

	/*
	*
	*	@Method:		_imageProtect
	*	@Parameters:	2
	*	@Param-1:		numWidth - Number - The width of the image in pixels
	*	@Param-2:		numHeight - Number - The height of the image in pixes
	*	@Description:	Checks to see if we should allow the resize to take place or not depending on the size the image will be resized to
	*
	*/	
	function _imageProtect($numWidth, $numHeight){
		if($this->boolProtect AND ($numWidth > $this->arrOriginalDetails[0] OR $numHeight > $this->arrOriginalDetails[1])){
			return 0;
		}
		
		return 1;
	}
	
	/*
	*
	*	@Method:		resizeToWidth
	*	@Parameters:	1
	*	@Param-1:		numWidth - Number - The width to resize to in pixels
	*	@Description:	Works out the height value to go with the width value passed, then calls the resize method.
	*
	*/
	function resizeToWidth($numWidth){
		$numHeight=(int)(($numWidth*$this->arrOriginalDetails[1])/$this->arrOriginalDetails[0]);
		$this->_resize($numWidth, $numHeight);	
	}

	/*
	*
	*	@Method:		resizeToHeight
	*	@Parameters:	1
	*	@Param-1:		numHeight - Number - The height to resize to in pixels
	*	@Description:	Works out the width value to go with the height value passed, then calls the resize method.
	*
	*/
	function resizeToHeight($numHeight){
		$numWidth=(int)(($numHeight*$this->arrOriginalDetails[0])/$this->arrOriginalDetails[1]);
		$this->_resize($numWidth, $numHeight);	
	}
	
	/*
	*
	*	@Method:		resizeToPercent
	*	@Parameters:	1
	*	@Param-1:		numPercent - Number - The percentage you want to resize to
	*	@Description:	Works out the width and height value to go with the percent value passed, then calls the resize method.
	*
	*/
	function resizeToPercent($numPercent){
		$numWidth = (int)(($this->arrOriginalDetails[0]/100)*$numPercent);
		$numHeight = (int)(($this->arrOriginalDetails[1]/100)*$numPercent);
		$this->_resize($numWidth, $numHeight);	
	}

	/*
	*
	*	@Method:		resizeToCustom
	*	@Parameters:	1
	*	@Param-1:		size - Number/Array - Either a number of array of numbers for the width and height in pixels
	*	@Description:	Checks to see if array was passed and calls the resize method with the correct values.
	*
	*/
	function resizeToCustom($size)
	{		
		if(is_array($size))
		{
			// current image params
			$_photo_width = $this->arrOriginalDetails[0];
			$_photo_height = $this->arrOriginalDetails[1];
			
			// new image params
			$img_width = (int)$size[0];
			$img_height = (int)$size[1];
			
			$this->_resize($img_width, $img_height);
		}
		else 
		{
			$this->resizeToWidth($size);
		}
	}
	
}

?>
