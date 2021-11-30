<?php 
/**
 * Configuration Class
 */

class Configure extends MyObject
{
	private static $__options = array();
	
	
	public static function write($key,$value = '')
	{
		if(is_array($key))
		{
			self::$__options = array_merge(self::$__options,$key);
		}else{
			$keyparts = explode(".",$key);
			if(count($keyparts) > 1)
			{
				$keyn = array_shift($keyparts);
				if(!isset(self::$__options[$keyn]))
				{
					self::$__options[$keyn] = [];
				}
				self::$__options[$keyn] = self::__write(implode(".", $keyparts),$value, self::$__options[$keyn]);
			}else{
				self::$__options[$key] = $value;
			}
		}
	}
	
	public static function read($key = null, $default = null)
	{
		if(is_null($key))
		{
			return self::$__options;
		}else{
			$keyparts = explode('.',$key);
			if(count($keyparts) > 1)
			{
				return self::__read($key, self::$__options, $default);
			}else{
				return isset(self::$__options[$key])?self::$__options[$key]:$default;
			}
		}
	}
	
	private static function __write($key,$value,$options)
	{
		$keyparts = explode(".",$key);
		if(count($keyparts) > 1)
		{
			$keyn = array_shift($keyparts);
			if(!isset($options[$keyn]))
			{
			    $options[$keyn] = [];
			}
			$options[$keyn] = self::__write(implode(".", $keyparts),$value, $options[$keyn]);
		}else{
			$options[$key] = $value;
		}
		return $options;
	}
	
	private static function __read($key,$child,$default)
	{
		$keyparts = explode(".",$key);
		if(count($keyparts) > 1)
		{
			if(isset($child[$keyparts[0]]))
			{
				$keyn = array_shift($keyparts);
				return self::__read(implode(".",$keyparts), $child[$keyn], $default);
			}else{
				return $default;
			}
		}else{
			return isset($child[$key])?$child[$key]:$default;
		}
	}
}

?>