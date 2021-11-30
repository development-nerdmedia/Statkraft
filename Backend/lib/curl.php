<?php

class Curl {

		private $__ch;

        private $response;

        private $isError = false;

        private $errorMsg;

        private $options = [
        	CURLOPT_FOLLOWLOCATION => true,
        	CURLOPT_MAXREDIRS => 10,
			CURLOPT_HTTPHEADER => [],
            CURLOPT_RETURNTRANSFER =>true,
            CURLOPT_POSTFIELDS => [],
            CURLOPT_HEADER => false,
            CURLINFO_HEADER_OUT => false,
            CURLOPT_POST => false,
            CURLOPT_VERBOSE => false,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_URL => null,
            CURLOPT_STDERR => null
        ];

        private $debug = false;

        private $transferInfo = [];

        public function resetOptions()
        {
        	$this->options = [
	        	CURLOPT_FOLLOWLOCATION => true,
	        	CURLOPT_MAXREDIRS => 10,
				CURLOPT_HTTPHEADER => [],
	            CURLOPT_RETURNTRANSFER =>true,
	            CURLOPT_POSTFIELDS => [],
	            CURLOPT_HEADER => false,
	            CURLINFO_HEADER_OUT => false,
	            CURLOPT_POST => false,
	            CURLOPT_VERBOSE => false,
	            CURLOPT_CUSTOMREQUEST => 'GET',
	            CURLOPT_URL => null,
	            CURLOPT_STDERR => null
	        ];
        }

        public function getAllOptions()
        {
        	return $this->options;
        }

        public function setHeaders($value)
        {
        	if(is_array($value))
        	{
        		$this->options[CURLOPT_HTTPHEADER] = $value;        		
        	}else{
        		$this->options[CURLOPT_HTTPHEADER][] = $value;        	
        	}
        }

        public function followLocation($value)
        {
        	if(is_bool($value))
        	{
        		$this->options[CURLOPT_FOLLOWLOCATION] = $value;
        	}
        }

        public function getHeaders($value, $out = false)
        {
        	if(is_bool($value))
        	{
        		$this->options[CURLOPT_HEADER] = $value;
        		if(is_bool($out))
        		{
        			$this->options[CURLINFO_HEADER_OUT] = $value;
        		}
        	}
        }

        public function doPost($value = true)
        {
        	if(is_bool($value))
        	{
        		$this->options[CURLOPT_POST] =$value;
        		if($value)
        		{
        			$this->options[CURLOPT_CUSTOMREQUEST] = 'POST';
        		}
        	}
        }

        public function maxRedirs($value)
        {
        	if(is_numeric($value) && $value >= 0)
        	{
        		$this->options[CURLOPT_MAXREDIRS] = $value;
        	}
        }

        public function verbose($value = true)
        {
        	if(is_bool($value))
        	{
        		$this->options[CURLOPT_VERBOSE] = $value;
        	}
        }

        public function customRequest($method)
        {
        	if(!$this->options[CURLOPT_POST])
        	{
        		$this->options[CURLOPT_CUSTOMREQUEST] = $method;
        	}
        }

        public function setPostFields($value, $append = true)
        {
        	if(is_array($value) || !$append)
        	{
        		$this->options[CURLOPT_POSTFIELDS] = $value;
        	}else{        		
        		$this->options[CURLOPT_POSTFIELDS][] = $value;
        	}
        }

        public function setUrl($url)
        {
        	$this->options[CURLOPT_URL] = $url;
        }

		public function getUrl()
		{
			return $this->options[CURLOPT_URL];
		}        

        public function doDebug($value = true)
        {
        	if (is_bool($value)) {
        		$this->debug = true;
        	}
        }

        public function getDebug()
        {
        	return $this->debug;
        }

        public function setTransferInfo()
        {
			$this->transferInfo = curl_getinfo($this->__ch);
        }

        public function getTransferInfo()
        {
        	return $this->transferInfo;
        }

        private function setIsError($value,$getErrorMessage = true)
        {
        	if(is_bool($value))
        	{
        		$this->isError = $value;
        		if($getErrorMessage)
        		{
        			$this->setErrorMessage();
        		}
        	}
        }

        public function isError()
        {
        	return $this->isError;
        }

        private function setErrorMessage($message = null)
        {
        	if(is_null($message))
        	{
				$this->errorMsg = curl_error($this->__ch);
			}else{
				$this->errorMsg = $message;
			}
        }

        public function getErrorMessage()
        {
        	return $this->errorMsg;
        }

        public function logTo($value)
        {
        	if(is_resource($value))
        	{
        		$this->options[CURLOPT_STDERR] = $value;
        	}
        }

        private function setResponse()
        {
        	$this->response = curl_exec($this->__ch);
        	if($this->response === false)
        	{
        		$this->setIsError(true);
        	}
        	if($this->getDebug())
        	{
        		$this->setTransferInfo();
        	}
        }

        public function getResponse($decode = true)
        {
            $result = $this->response;
        	if($decode && !$this->getDebug())
        	{
        	    try{
        		  $result = json_decode($this->response);
        	    }catch (Exception $e){
        	        if(is_string($this->response))
        	        {
        	            $result = $this->response;
        	        }else{
        	            if(!is_null($result))
        	            {
        	                $this->setIsError(true, false);
        	                $this->setErrorMessage("Response is not a valid JSON.");
        	                $result = $e->getMessage();
        	           }else{
        	               $this->setIsError(true, false);
        	               $this->setErrorMessage("Response is not a valid JSON. Got Null at decoding");
        	               $result = "null";
        	           }
        	        }
        	    }
        	}
        	return $result;
        }

        public function call()
        {
        	$this->__ch = curl_init();
        	/*
        	 * If we are at debug mode we override some values
        	 */
        	if($this->getDebug())
        	{
	        	$this->getHeaders(true, true);
	        	$this->verbose();
	        }
        	curl_setopt_array($this->__ch, $this->options);
        	$this->setResponse();
        	curl_close($this->__ch);
        }
}