<?php 

class IO extends MyObject
{
	protected $_folder = '';
	
	protected $_file = '';
	
	protected $_data = array();
	
	public function __construct($folder = '',$file = '')
	{
		if(!empty($folder))
		{
			$this->_folder = $folder;
		}
		if(!empty($file))
		{
			$this->_file = $file;
		}
	}
	
	public function set($value, $key = null)
	{
		if(is_null($key))
		{
			$this->_data = $value;
		}else{
			$this->_data[$key] = $value;
		}
	}
	
	public function transfer($obj,$obj2)
	{
		$obj->_folder = $obj2->_folder;
		$obj->_file = $obj2->_file;
		$obj->_data = $obj2->_data;
		return $obj;
	}
	
}
?>