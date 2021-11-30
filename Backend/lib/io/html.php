<?php 

class Html extends IO
{

	protected $_ext = 'etf';
	
	public $layout = '';
	
	private $__CSSList = [];
	
	private $__JSList = [];
	
	public function output()
	{
		if(is_file(APP_ROOT.DS.'views'.DS.$this->_folder.DS.$this->_file.".".$this->_ext))
		{
			ob_start();						
			extract($this->_data);
			require APP_ROOT.DS.'views'.DS.$this->_folder.DS.$this->_file.".".$this->_ext;
			$content_for_layout = ob_get_clean();
			if(!empty($this->layout) && is_file(APP_ROOT.DS.'views'.DS.'layouts'.DS.$this->layout.".".$this->_ext))
			{
				ob_start();
				require APP_ROOT.DS.'views'.DS.'layouts'.DS.$this->layout.".".$this->_ext;
				ob_end_flush();
			}else{
				echo $content_for_layout;				
			}
			return true;			
		}
		$this->e404();
	}	

	public function getRenderedView($viewName = null, $folder = null, $useDefaultLayout = false, $layout = null)
	{
	    if(is_null($folder))
	    {
	        $folder = $this->_folder;
	    }
	    if(is_null($viewName))
	    {
	        $viewName = $this->_file;
	    }
	    if(is_null($layout))
	    {
	        if($useDefaultLayout)
	        {
	           $layout = $this->layout;
	        }
	    }
	    var_dump(APP_ROOT.DS.'views'.DS.$folder.DS.$viewName.".".$this->_ext);
	    if(is_file(APP_ROOT.DS.'views'.DS.$folder.DS.$viewName.".".$this->_ext))
	    {
	        ob_start();
	        extract($this->_data);
	        require APP_ROOT.DS.'views'.DS.$folder.DS.$viewName.".".$this->_ext;
	        $content_for_layout = ob_get_clean();
	        if(!empty($layout) && is_file(APP_ROOT.DS.'views'.DS.'layouts'.DS.$layout.".".$this->_ext))
	        {
	            ob_start();
	            require APP_ROOT.DS.'views'.DS.'layouts'.DS.$layout.".".$this->_ext;
	            return ob_get_clean();
	        }else{
	            return $content_for_layout;
	        }
	    }
	    return null;
	}
	
	
	public function render($file)
	{
		if(is_file(APP_ROOT.DS.'views'.DS.$this->_folder.DS.$file.".".$this->_ext))
		{
			$this->_file = $file;
			return true;
		}
		return false;
	}
	
	public function renderElement($name,$variables = array())
	{
		if(is_file(APP_ROOT.DS.'views'.DS.'elements'.DS.$name.".".$this->_ext))
		{
			ob_start();
			extract($this->_data);
			if(!empty($variables))
			{
				extract($variables);
			}
			require APP_ROOT.DS.'views'.DS.'elements'.DS.$name.".".$this->_ext;
			return ob_get_clean();
		}
		return '';
	}
	
	public function existFlash()
	{
	    if(isset($_SESSION['flash']))
	    {
	        return true;
	    }else{
	        return false;
	    }
	}
	
	public function getFlash()
	{
	    $flashvalue = null;
	    if($this->existFlash())
	    {
	       $flashvalue = $_SESSION['flash'];
	       unset($_SESSION["flash"]);
	    }
	    return $flashvalue;
	}
	
	public function storeLastPost()
	{
	    if(!empty($_POST))
	    {
	       $_SESSION["lastpost"] = $_POST;
	    }
	}
	
	public function getLastValue($field, $default = null)
	{
	    if(isset($_SESSION["lastpost"]))
	    {
	        if(isset($_SESSION["lastpost"][$field]))
	        {
	            $default = $_SESSION["lastpost"][$field];
	            $_SESSION["lastpost"][$field] = null;
	            unset($_SESSION["lastpost"][$field]);
	        }
	    }
	    return $default;
	}
	
	public function addCSSFile($file)
	{
	    $this->__CSSList[] = $file;
	}
	
	public function getCSSList()
	{
	    return $this->__CSSList;
	}
	
	public function addJSFile($file)
	{
	    $this->__JSList[] = $file;
	}
	
	public function getJSList()
	{
	    return $this->__JSList;
	}
	
}