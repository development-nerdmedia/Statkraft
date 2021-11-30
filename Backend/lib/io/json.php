<?php 

class Json extends IO
{
	private $__headers = array(
				'Content-Type:application/json'
			); 
	
	
	public function addheader($header)
	{
		$this->__headers[] = $header;
	}
	
	public function output()
	{
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
		if(Configure::read('debug') == 0)
		{
			foreach ($this->__headers as $header)
			{
				header($header);
			}
		}else{				
			header('Content-Type:application/json');
		}
		echo $this->json_encode($this->_data);
	}
	
	private function json_encode($data)
	{
		if(function_exists('json_encode'))
		{
			return json_encode($data);
		}else{
			/* by default we don't tolerate ' as string delimiters
			 if you need this, then simply change the comments on
			the following lines: */
			
			// $matchString = '/(".*?(?<!\\\\)"|\'.*?(?<!\\\\)\')/';
			$matchString = '/".*?(?<!\\\\)"/';
			
			// safety / validity test
			$t = preg_replace( $matchString, '', $json );
			$t = preg_replace( '/[,:{}\[\]0-9.\-+Eaeflnr-u \n\r\t]/', '', $t );
			if ($t != '') {
				return null;
			}
			
			// build to/from hashes for all strings in the structure
			$s2m = array();
			$m2s = array();
			preg_match_all( $matchString, $json, $m );
			foreach ($m[0] as $s) {
				$hash       = '"' . md5( $s ) . '"';
				$s2m[$s]    = $hash;
				$m2s[$hash] = str_replace( '$', '\$', $s );  // prevent $ magic
			}
			
			// hide the strings
			$json = strtr( $json, $s2m );
			
			// convert JS notation to PHP notation
			$a = ($assoc) ? '' : '(object) ';
			$json = strtr( $json,
					array(
							':' => '=>',
							'[' => 'array(',
							'{' => "{$a}array(",
							']' => ')',
							'}' => ')'
							)
							);
			
			// remove leading zeros to prevent incorrect type casting
			$json = preg_replace( '~([\s\(,>])(-?)0~', '$1$2', $json );
			
			// return the strings
			$json = strtr( $json, $m2s );
			
			/* "eval" string and return results.
			 As there is no try statement in PHP4, the trick here
			is to suppress any parser errors while a function is
			built and then run the function if it got made. */
			$f = @create_function( '', "return {$json};" );
			$r = ($f) ? $f() : null;
			
			// free mem (shouldn't really be needed, but it's polite)
			unset( $s2m ); unset( $m2s ); unset( $f );
			
			return $r;			
		}
	}
	
}
?>