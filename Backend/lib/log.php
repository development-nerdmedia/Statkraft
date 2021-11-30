<?php

class Log
{
	private $path = null;

	private $fp = null;

	public function __construct()
	{
		$this->setLogPath();
	}

	public function setLogPath()
	{
		$this->path = Configure::read('log.path');
		if(is_null($this->path))
		{
			$this->path = APP_ROOT.DS.'logs'.DS;
		}
		if(!is_dir($this->path))
		{
			mkdir($this->path);
		}		
	}

	public function getHandler()
	{
		return $this->fp;
	}

	public function into($file)
	{
		$file = $file . ".log";
		if(!file_exists($this->path.$file))
		{
			touch($this->path.$file);
		}
		if(!is_resource($this->fp))
		{
			if(is_writable($this->path.$file))
			{
				$this->fp = fopen($this->path.$file, 'a+');
			}else{
				syslog(LOG_ERR, "Can't open ".$this->path.$file." for writing.");
			}
		}
		return $this;
	}

	public function log($data)
	{
		if(!is_string($data))
		{
			$data = json_encode($data);
		}
		if(is_resource($this->fp))
		{
			fwrite($this->fp, "[".date("d-m-Y H:i:s")."] " . $data."\n");
		}
		fclose($this->fp);
	}

}