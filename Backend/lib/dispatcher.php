<?php 
/**
 * Basic Dispatcher Object
 * @author Kemmotar
 *
 */


class Dispatcher extends MyObject
{
	
	private $__url = '';
	
	public function __construct()
	{
		$this->__processURL();
		if($this->checkOffice())
		{
		    die(); //This is fucked but seems to work with no downside.....
		}
	}
	
	private function checkOffice()
	{
	    $userAgent = isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:null;
	    if(is_null($userAgent) || strpos($userAgent, "ms-office") !== false)
	    {
	        return true;
	    }
	    return false;
	}

	public function dispatch()
	{	    
	    // We need to discover if it's a defined route, a controller action route or nothing
	    require_once APP_ROOT.DS.'config'.DS.'routes.php';
	    $hostparts = explode(".", $_SERVER["HTTP_HOST"]);
	    $routesData = isset($routes["site"])?$routes["site"]:null;
	    if(Configure::read("cmssd") == $hostparts[0])
	    {
	        if(isset($routes['admin']))
	        {
	            $routesData = $routes['admin'];
	        }
	    }
	    if(Configure::read("apisd") == $hostparts[0])
	    {
	        if(isset($routes['api']))
	        {
	            $routesData = $routes['api'];
	        }
	    }	
		$controllername = null;
		$actionname = null;
		$methodname = strtoupper($_SERVER['REQUEST_METHOD']);
		$params = array();
		if(!is_null($routesData) && is_array($routesData))
		{		   
			//we check if we have this route defined
			//we need to check if any url matches
			if($this->__url == '/')
			{
			    if(isset($routesData["/"]))
				{
				    if(isset($routesData["/"][$methodname]))
				    {
				        $controllername = $routesData["/"][$methodname]['controller'];
				        $actionname = $routesData["/"][$methodname]['action'];
				    }
				}else{
					$this->e404();
				}
			}else{
			    $urls = array_keys($routesData);
				$trimedurl = rtrim(strtolower($this->__url),'/');
				if(substr($trimedurl, 0, 1) != "/")
				{
					$trimedurl = "/".$trimedurl;
				}
				foreach ($urls as $route)
				{
					if($route == $trimedurl)
					{
					    if(isset($routesData[$route][$methodname]))
					    {
					        $controllername = $routesData[$route][$methodname]['controller'];
					        $actionname = $routesData[$route][$methodname]['action'];
					    }
						break;
					}elseif(substr($route,-1) == '*' && strpos($trimedurl,rtrim($route,'*')) === 0){
						$urlparts = explode("/",ltrim($this->__url,"/"));
						$routeparts = explode("/",rtrim(ltrim($route,"/"),"/*"));
						if(isset($routesData[$route][$methodname]))
						{
						    $controllername = $routesData[$route][$methodname]['controller'];
						    $actionname = $routesData[$route][$methodname]['action'];
						}
						$params = array_diff($urlparts,$routeparts);
					}elseif (substr($route,-1) == '*' && $trimedurl."/" == rtrim($route,'*')){
					    if(isset($routes[$route][$methodname]))
					    {
					        $controllername = $routesData[$route][$methodname]['controller'];
					        $actionname = $routesData[$route][$methodname]['action'];
					    }
						$params = array();
						break;
					}
				}
			}
			if (is_null($controllername) && is_null($actionname) && in_array('*', $urls))
			{
			    if(isset($routes['*']['GET']))
		        {
		            $controllername = $routesData['*']['GET']['controller'];
		            $actionname = $routesData['*']['GET']['action'];
		        }
				$params = explode("/",$this->__url);
			}
		}
		if(is_null($controllername) || is_null($actionname))
		{
			//we try to get it from the URL
			$urlparts = explode("/", $this->__url);
			if(count($urlparts) > 1)
			{
				$controllername = $urlparts[0];
				$actionname = $urlparts[1];
				unset($urlparts[0]);
				unset($urlparts[1]);
				$params = $urlparts;
			}
		}
		if(!is_null($controllername) && !is_null($actionname))
		{
			if(is_file(APP_ROOT.DS.'controllers'.DS.$controllername.'.php'))
			{
				require APP_ROOT.DS.'lib'.DS.'controller.php';
				require APP_ROOT.DS.'controllers'.DS.$controllername.'.php';
				if(class_exists($controllername))
				{
					$controller = new $controllername;
					if (method_exists($controller, $actionname))
					{
					    $_SESSION['lastpath'] = isset($_SESSION['actualpath'])?$_SESSION['actualpath']:"/";
					    $_SESSION['actualpath'] = $this->__url;
					    if(Configure::read('db-enable'))
						{
							$this->__initDB();
							$controller = $this->__loadModels($controller);
						}
						$controller = $this->__loadComponents($controller);
						$outinit = false;
						if(is_file(APP_ROOT.DS.'lib'.DS.'io'.DS.$controller->output.".php"))
						{
							require APP_ROOT.DS.'lib'.DS.'io.php';
							require APP_ROOT.DS.'lib'.DS.'io'.DS.$controller->output.".php";
							$outputc = ucwords($controller->output);
							if(class_exists($outputc))
							{					
								$output = new $outputc($controllername,$actionname);
								if(method_exists($output, 'output'))
								{
									$outinit = true;
								}
							}
						}
						if(!$outinit)
						{
							$this->error('Please select/install correct dispatcher');
						}
						$controller->outputer = $output;
						$controller->initialization();
						$controller->init();
						call_user_func_array(array($controller,$actionname), $params);
						//$controller->{ $methodname }();						
						unset($controller->dbo);
						$controller->outputer->layout = $controller->layout;
						$controller->outputer->output();
						return true;
					}
				}
			}
		}
		$this->e404();
	}
	
	private function __processURL()
	{
		if(isset($_SERVER['REQUEST_URI']))
		{
		    if(isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING']))
		    {
		        $this->__url = str_replace("?".$_SERVER['QUERY_STRING'], "", $_SERVER['REQUEST_URI']);
		    }else{
		        $this->__url = $_SERVER['REQUEST_URI'];
		    }
			if(Configure::read('baseurl'))
			{
			    $this->__url = str_replace(Configure::read('baseurl'), '', $this->__url);
			}
			if(Configure::read('api.only'))
			{
				$this->__url = str_replace(['/api/'.Configure::read('api.version','v1'), '/api'], '', $this->__url);
			}			
			if(substr($this->__url, -1) == "/")
			{
				$this->__url = substr($this->__url,0,-1);
			}
			if(empty($this->__url))
			{
				$this->__url = "/";
			}
		}else{
			$this->__url = "/";
		}
	}
	
	private function __initDB()
	{
		/*
		 * If Configureation says we must use Database we load the options and the driver
		*/
		if(Configure::read('db-enable'))
		{
			require APP_ROOT.DS.'config'.DS.'database.php';
			$dboptions = Configure::read('DB');
			if(!is_null($dboptions))
			{			
				foreach ($dboptions as $key => $value)
				{
					if(isset($value['driver']))
					{
					    if(strtolower(substr($value['driver'], 0, 3)) == 'pdo')
					    {
					        $driverparts = explode("=>", $value['driver']);
					        if(count($driverparts) > 1)
					        {
					            $value['driver'] = $driverparts[1];
					        }else{
					            $value['driver'] = $driverparts[0];
					        }
					        $driver = 'MyPdo';
					    }else{
					        $driver = $value['driver'];
					    }
					}else{
						$driver = 'mysql';
					}
					require_once APP_ROOT.DS.'lib'.DS.'db'.DS.strtolower($driver).".php";
					$this->{ 'dbo_'.$key } = new $driver($value); 
				}
			}
		}
	}
	
	private function __loadModels($controller)
	{
		require APP_ROOT.DS.'lib'.DS.'model.php';
		if(isset($controller->uses) && is_array($controller->uses))
		{
			foreach ($controller->uses as $model)
			{
				$modelpath = APP_ROOT.DS.'models'.DS.strtolower($model).'.php';
				if(is_file($modelpath))
				{
					require $modelpath;
					if(class_exists($model))						
					{
						$controller->{ $model } = new $model();
						$modeldbconf = (isset($controller->{ $model }->dbconfig))?'dbo_'.$controller->{ $model }->dbconfig:'dbo_default';
						$controller->{ $model }->setDBO(clone $this->{ $modeldbconf });
					}
				}
			}
		}
		return $controller;
	}
	
	private function __loadComponents($controller)
	{
	    if(isset($controller->components) && is_array($controller->components))
	    {
	        foreach ($controller->components as $component)
	        {
	            $componentpath = APP_ROOT.DS.'controllers'.DS.'components'.DS.strtolower($component).'.php';
	            if(is_file($componentpath))
	            {
	                require $componentpath;
	                if(class_exists($component))
	                {
	                    $controller->{ $component } = new $component();
	                    if(method_exists($controller->{ $component }, 'initialize'))
	                    {
	                        $controller->{ $component }->initialize();
	                    }
	                }
	            }else{
	                $componentpath = APP_ROOT.DS.'lib'.DS.'components'.DS.strtolower($component).'.php';
	                if(is_file($componentpath))
	                {
	                    require $componentpath;
	                    if(class_exists($component))
	                    {
	                        $controller->{ $component } = new $component();
	                        if(method_exists($controller->{ $component }, 'initialize'))
	                        {
	                            $controller->{ $component }->initialize();
	                        }
	                    }
	                }
	            }
	        }
	    }
	    return $controller;
	}
	
}

?>
