<?php 

class MyObject
{
	public function __construct()
	{
		// just a placeholder for future
	}
	
	public function e404()
	{
		e('<h1>404 - Not Found</h1>');
		e('The requested URL was not found on this server');
		$this->stop();
	}
	
	public function error($msg = array())
	{
		if(is_array($msg))
		{
			foreach ($msg as $m)
			{
				e($m);
			}
		}else{
			e($msg);
		}
		$this->stop();
	}
	
	public function stop($status = 0)
	{
		exit($status);
	}
}
?>