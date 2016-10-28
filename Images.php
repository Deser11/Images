<?php

interface Images {
	
    /**
     * Check if our image is smaller than required dimensions (or the same)
     *
     * @param string $image
     * @param int $requiredWidth
     * @param int $requiredHeight
     * @return bool
     */
    public function resizeOrNot($image, $requiredWidth, $requiredHeight);
	
    /**
     * Create new resized image
     *
     * @param string $image
     * @param int $requiredWidth
     * @param int $requiredHeight
     * @return string
     */
    public function resizeImage($image, $requiredWidth, $requiredHeight);
	
    /**
     * Check if our image is smaller than required dimensions (or the same)
     *
     * @param string $image
     * @param int $requiredWidth
     * @param int $requiredHeight
     * @return bool
     */
    public function cutOrNot($image, $requiredWidth, $requiredHeight);

    /**
     * Create new cutted image
     *
     * @param string $image
     * @param int $requiredWidth
     * @param int $requiredHeight
     * @return string
     */
    public function cutImage($image, $requiredWidth, $requiredHeight);
	
    /**
     * Save created image
     *
     * @param string $image
     * @param string $pathForNewImage
     * @return bool
     */
    public function saveImage($image, $pathForNewImage);
}

class ProcessImage implements Images {
	
    /**
     * @var string
     */
    private $image;
	
    /**
     * @var int
     */
    private $requiredWidth;
	
    /**
     * @var int
     */
    private $requiredHeight;
	
    /**
     * Check if our image is smaller than required dimensions
     *
     * @param string $image
     * @param int $requiredWidth
     * @param int $requiredHeight
     * @return bool
     */
    public function resizeOrNot($image, $requiredWidth, $requiredHeight) {
		 list($imageWidth, $imageHeight) = getimagesize($image);
		 if ($imageWidth <= $requiredWidth && $imageHeight <= $requiredHeight) {
		 	return false;
		 }
		 return true;
    }
	
    /**
     * Create new resized image
     *
     * @param string $image
     * @param int $requiredWidth
     * @param int $requiredHeight
     * @return string
     */
    public function resizeImage($image, $requiredWidth, $requiredHeight) {
		list($imageWidth, $imageHeight) = getimagesize($image);
		if ($requiredWidth === 0 &&  $requiredHeight === 0) {
			// It's already validated by resizeOrNot method -- !!
		} elseif ($requiredHeight === 0) {
			// Resize by width
			$percent = $requiredWidth/$imageWidth;
		} elseif ($requiredWidth === 0) {
			// Resize by height
			$percent = $requiredHeight/$imageHeight;

		} else {
			if (($requiredWidth/$imageWidth) <= ($requiredHeight / $imageHeight)) {
		    	$percent = $requiredHeight / $imageHeight;
		    } else {
		    	$percent = $requiredWidth / $imageWidth;
		    }
		}
		
		$destinationWidth = $imageWidth * $percent;
		$destinationHeight = $imageHeight * $percent;
    	// Prepare black image with required dimensions
		$destinationImage = imagecreatetruecolor($destinationWidth, $destinationHeight);
		$sourceImage = imagecreatefromjpeg($image);
		// Resize image
		imagecopyresampled($destinationImage, $sourceImage, 0, 0, 0, 0, $destinationWidth, $destinationHeight, $imageWidth, $imageHeight);
		// Save image in temp folder
		$explodedName = explode('.', $image);
		$extension = end($explodedName);
		$tempName = uniqid() . '.' . $extension;
		$tempPath = $_SERVER['DOCUMENT_ROOT'] . '/Images/temp/' . $tempName;
		imagejpeg($destinationImage, $tempPath, 100);
		return $tempPath;
    }

    /**
     * Check if our image is bigger than required dimensions
     *
     * @param string $image
     * @param int $requiredWidth
     * @param int $requiredHeight
     * @return bool
     */
    public function cutOrNot($image, $requiredWidth, $requiredHeight) {
    	if ($requiredWidth === 0 || $requiredHeight === 0) {
    		return false;
    	}
		 list($imageWidth, $imageHeight) = getimagesize($image);
		 if ($imageWidth <= $requiredWidth && $imageHeight <= $requiredHeight) {
		 	return false;
		 }
		 return true;
    }

    /**
     * Create new cutted image
     *
     * @param string $image
     * @param int $requiredWidth
     * @param int $requiredHeight
     * @return string
     */
    public function cutImage($image, $requiredWidth, $requiredHeight) {
    	list($imageWidth, $imageHeight) = getimagesize($image);
    	if ($imageWidth > $requiredWidth) {
			$cutX = ($imageWidth/2) - ($requiredWidth/2);
			$cutY = 0;
    	} else {
			$cutX = 0;
			$cutY = ($imageHeight/2) - ($requiredHeight/2);
    	}
		$destinationImage = imagecreatetruecolor($requiredWidth, $requiredHeight);
		$sourceImage = imagecreatefromjpeg($image);
		// Cut image
		imagecopyresampled($destinationImage, $sourceImage, 0, 0, $cutX, $cutY, $requiredWidth, $requiredHeight, $requiredWidth, $requiredHeight);
		// Save image in temp folder
		$explodedName = explode('.', $image);
		$extension = end($explodedName);
		$tempName = uniqid() . '.' . $extension;
		$tempPath = $_SERVER['DOCUMENT_ROOT'] . '/Images/temp/' . $tempName;
		imagejpeg($destinationImage, $tempPath, 100);
		if (file_exists($image)) {
			unlink($image);
		}
		return $tempPath;
    }
	
    /**
     * Save created image
     *
     * @param string $image
     * @param string $pathForNewImage
     * @return bool
     */
    public function saveImage($image, $pathForNewImage) {
    	$explodedName = explode('.', $image);
		$extension = end($explodedName);
		$tempName = uniqid() . '.' . $extension;
		$tempPath = $pathForNewImage . $tempName;
    	copy($image, $tempPath);
		if (file_exists($image)) {
			unlink($image);
		}
		return true;
    }
	
}

$image = new ProcessImage();
$imageForProcessing = 'http://img.ffffound.com/static-data/assets/6/51cc46900bf5fe574293d49c4d9939e0ebfc8ee3_m.jpg';

$resizeOrNot = $image->resizeOrNot($imageForProcessing, 200, 100);
if ($resizeOrNot === true) {
	$resizedImage = $image->resizeImage($imageForProcessing, 200, 100);
	$cutOrNot = $image->cutOrNot($resizedImage, 200, 100);
	if ($cutOrNot === true) {
		$cuttedImage = $image->cutImage($resizedImage, 200, 100);
		$save = $image->saveImage($cuttedImage, $_SERVER['DOCUMENT_ROOT'] . '/Images/upload/');
	} else {
		$save = $image->saveImage($resizedImage, $_SERVER['DOCUMENT_ROOT'] . '/Images/upload/');
	}
} else {
	$save = $image->saveImage($imageForProcessing, $_SERVER['DOCUMENT_ROOT'] . '/Images/upload/');
}