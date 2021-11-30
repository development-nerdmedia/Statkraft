<?php 

class FileManager
{
    private $__uploadfile = null;
    
    private $__uploadsFolder = null;
        
    public function __construct()
    {
        $this->__uploadsFolder = Configure::read("uploadfolder");
        if(is_null($this->__uploadsFolder))        
        {            
            $this->__uploadsFolder = APP_ROOT.DS."storage".DS;
        }
        if(!is_dir($this->__uploadsFolder))
        {
            if(!mkdir($this->__uploadsFolder))
            {
                $log = new Log();
                $log->into("file")->log("Error creating folder");
            }
        }
    }
    
    public function setUploadFile(array $uploadFile) : bool
    {
        if(isset($uploadFile['size']) && $uploadFile['size'] > 0 && is_file($uploadFile['tmp_name']))
        {
            $this->__uploadfile = $uploadFile;
            return true;
        }
        return false;
    }
    
    public function saveUpload($name = null, $subfolder = null) : ?string
    {
        if(!is_array($this->__uploadfile))
        {
            return null;
        }
        if(is_null($name))
        {
            $name = $this->__uploadfile['name'];
        }
        $ok = true;
        if(!is_null($subfolder))
        {
            if(substr($subfolder, -1) != "/")
            {
                $subfolder .= "/";
            }
            $subfolder = ltrim($subfolder, "/");
            $name = $subfolder.$name;
            if(!is_dir($this->__uploadsFolder.$subfolder))
            {
                if(!mkdir($this->__uploadsFolder.$subfolder, 0777, true))
                {
                   $ok = false;
                   $name = null;
                }
            }
        }
        if($ok)
        {
            if(!move_uploaded_file($this->__uploadfile['tmp_name'], $this->__uploadsFolder.$name))
            {
                return null;
            }
        }        
        return $name;
    }
    
    public function verifyMimeImage() : bool
    {
        $supposedMimeType = $this->minimime($this->__uploadfile['tmp_name']);
        if($supposedMimeType)
        {
            //Since it's an image let's try to use it :D
            $ext = strtolower(substr($this->__uploadfile['name'], strrpos($this->__uploadfile['name'], ".") + 1));
            switch ($ext)
            {
                case 'bmp':
                    $im = @imagecreatefrombmp($this->__uploadfile['tmp_name']);
                    break;
                case 'jpeg':
                case 'jpg':
                    $im = @imagecreatefromjpeg($this->__uploadfile['tmp_name']);
                    break;
                case 'png':
                    $im = @imagecreatefrompng($this->__uploadfile['tmp_name']);
                    break;
                case 'gif';
                    $im = @imagecreatefromgif($this->__uploadfile['tmp_name']);
                    break;
                default:
                    $im = false;
            }
            if($im)
            {
                imagedestroy($im);
                return true;
            }
        }
        return false;
    }
    
    public function bulkDelete($list) : bool
    {
        $ok = true;
        foreach ($list as $file)
        {
            if(!unlink($this->__uploadsFolder.$file))
            {
                $ok = false;
            }
        }
        return $ok;
    }
    
    public function getBase64UploadedImage($path, $absolute = true) : ?string
    {
        if(!$absolute)
        {
            $path = $this->__uploadsFolder.$path;
        }
        if(is_file($path))
        {
            // Get the image and convert into string
            $img = file_get_contents($path);
            
            // Encode the image string data into base64
            $data = base64_encode($img);
            
            // Display the output
            return $data;
        } else {
            return null;
        }
    }
    
    public function getUploadFolder() : ?string
    {
        return $this->__uploadsFolder;
    }
    
    
    public function minimime($fname) : string
    {
        $fh=fopen($fname,'rb');
        if ($fh) 
        {
            $bytes6=fread($fh,6);
            fclose($fh);
            if ($bytes6===false) return false;
            if (substr($bytes6,0,3)=="\xff\xd8\xff") return 'image/jpeg';
            if ($bytes6=="\x89PNG\x0d\x0a") return 'image/png';
            if ($bytes6=="GIF87a" || $bytes6=="GIF89a") return 'image/gif';
            return 'application/octet-stream';
        }
        return false;
    }
}

?>