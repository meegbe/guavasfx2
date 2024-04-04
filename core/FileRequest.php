<?php

class FileRequest
{
    protected $files;

    public $name = null;
	public $size = null;
	public $tmp_name = null;
	public $type = null;
	public $extension = null;
	public $error = null;

    protected $imageQuality = 60;
	protected $imageWidth = false;
	protected $withImageOptimized = false;
	protected $isImage = false;
	protected $usingTmp = false;

    public function __construct($files = null)
    {
        $this->files = $files;
        if(is_array($files['name']) && sizeof($files['name']) > 0) {

        } else {
            $this->name = $files['name'];
            $this->size = $files['size'];
            $this->extension = $this->getExtension($files['name']);
            $this->tmp_name = $files['tmp_name'];
            $this->type = $files['type'];
            $this->error = $files['error'];
        }
        $this->withImageOptimized = false;
        return $this;
    }

    public function move($path, $filename = '', $index = -1) 
    {
		if ($this->error == UPLOAD_ERR_OK) {
            if($index > -1) {
				$name = $this->name[$index];
				$tmpName = $this->tmp_name[$index];
			} else {
				$name = $this->name;
				$tmpName = $this->tmp_name;
			}
            $filename = !empty($filename) ? $filename : $name;
			$pathDir = slashtrim($path) . '/';
            $pathFile = $pathDir .  $filename;
			if($this->isImage && $this->withImageOptimized) {
				$this->imageCompress($tmpName, $pathFile, $this->imageQuality, $this->imageWidth);
				return is_readable($pathFile);
			}
			if($this->usingTmp) {
				rename($tmpName, $pathFile);
				@unlink($tmpName);
			}else{
				move_uploaded_file($tmpName, $path . $filename);
			}
        }
        return false;
    }

    public function withImageOptimized($quality = 90, $imageWidth = false) 
    {
        if(in_array($this->extension,['jpg','jpeg','png'])) {
            $this->imageQuality = $quality;
            $this->imageWidth = $imageWidth;
            $this->withImageOptimized = true; 
			$this->isImage = true;
		}
		return $this;
	}

    /**
	 * Image Optimized
	 * 
	 * @author Roywae
	 * @param string $source
	 * @param string $destination       target 
	 * @param int $quality              range value 0-100 
	 * @return string $destination
	 */
	protected function imageCompress($source, $destination = '', $quality = 60, $imageWidth = false)
	{
		$image = null;
		$info = getimagesize($source);

		list($width,$height) = $info;
		$mime = $info['mime'];
		
		switch($mime) {
			case 'image/jpeg':
			case 'image/jpg':
				$image = imagecreatefromjpeg($source);
				break;
			case 'image/png':
				$image = imagecreatefrompng($source);
				break;
			default:
				return false;
		}

		if(empty($destination)) {
			$destination = $source;
		}

		$new_width = $width;
		$new_height = $height;
		if($imageWidth !== false) {
			if($width > $imageWidth) {
				$new_width = $imageWidth;
				$new_height = ($imageWidth / $width) * $height;
			}
		}

		$dst = imagecreatetruecolor($new_width, $new_height);
		imagecopyresampled($dst, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
		imagejpeg($dst, $destination, $quality);
		if($new_height > $new_width) {
			imagerotate($dst, 270, 0);
		}
		imagedestroy($image);
    	imagedestroy($dst);
		
		return $destination;
	}

    protected function getExtension($filename) 
    {
		return pathinfo($filename, PATHINFO_EXTENSION);
	}
}