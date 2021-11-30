<?php 


class Model extends MyObject
{
	
	private $__dbo = null;
	
	public function __construct()
	{
		
	}
	
	public function __call($name, array $args)
	{		
		return call_user_func_array(array($this->__dbo, $name),$args);
	}
	
	public function setDBO($dbo)
	{
		$this->__dbo = $dbo;
		$this->__dbo->model = $this;
	}

	public function getTable()
	{
		return $this->table;
	}
}

?>