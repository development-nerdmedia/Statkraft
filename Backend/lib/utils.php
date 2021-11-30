<?php 
/**
 * Basic set of utilitary functions.
 * 
 * 
 * @author Kemmotar (Jorge Escribens) 
 * @package ALF (Ajax Light Framework)
 * @version 0.1 Beta
 * @since 01/14/2013 * 
 * 
 */


function pr($data)
{
	if(Configure::read('debug') > 0)
	{
		echo "<pre>";
		print_r($data);
		echo "</pre>";
	}
}


function prd($data)
{
    if(Configure::read('debug') > 0)
    {        
        pr($data);
        die();
    }
}


function e($string)
{
	if(is_string($string))
	{
		echo $string."<br>\n";
	}	
}

function de($string)
{
	if(Configure::read('debug') > 0)
	{
		if(is_string($string))
		{
			echo $string."<br>\n";
		}
	}
}

function ed($string)
{
    e($string);
    die();
}

function ded($string)
{
    de($string);
    die();
}

function vd($var)
{
    var_dump($var);
}

function dvd($var)
{
    var_dump($var);
    die();
}

function stripslashes_deep($value) {
	if (is_array($value)) {
		$return = array_map('stripslashes_deep', $value);
		return $return;
	} else {
		$return = stripslashes($value);
		return $return ;
	}
}

function loadUtilFile($file)
{
	if(file_exists(APP_ROOT.DS.'utils'.DS.$file))
	{
		require_once APP_ROOT.DS.'utils'.DS.$file;
	}
}

function loadVendor($name, $loadfile = null)
{
    if(file_exists(APP_ROOT.DS.'vendor'.DS.$name.DS."autoload.php"))
    {
        require_once APP_ROOT.DS.'vendor'.DS.$name.DS."autoload.php";
    }else{
        if(is_array($loadfile))
        {
            foreach ($loadfile as $file)
            {
                if(file_exists(APP_ROOT.DS.'vendor'.DS.$name.DS.$file))
                {
                    require_once APP_ROOT.DS.'vendor'.DS.$name.DS.$file;
                }
            }
        }else{
            if(file_exists(APP_ROOT.DS.'vendor'.DS.$name.DS.$loadfile))
            {
                require_once APP_ROOT.DS.'vendor'.DS.$name.DS.$loadfile;
            }
        }
    }
}

?>