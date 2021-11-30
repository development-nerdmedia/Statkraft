<?php

class Controller extends MyObject
{
    public $output = 'config';

    public $outputer = null;
    
    protected $input = [];

    private  $files = [];
    
    private $curl = null;

    private $log = null;

    public function __construct()
    {
        parent::__construct();
        if(Configure::read('curl.load'))
        {
                $this->curl = new Curl();
        }
        if($this->output == 'config')
        {
            $this->output = Configure::read('output', 'json');
        }
    }

    protected function getLogger()
    {
        if(is_null($this->log))
        {
            $this->log = new Log();
        }
        return $this->log;
    }

    public function getOutputer()
    {
        return $this->outputer;
    }
    
    protected function getCurlClient()
    {
        return $this->curl;
    }

    protected function set($value, $key = null)
    {
        $this->outputer->set($value, $key);
    }

    protected function flash($value)
    {
        $_SESSION['flash'] = $value;
    }
    
    protected function getFlash()
    {
        $flashData = null;
        if(isset($_SESSION['flash']))
        {
            $flashData = $_SESSION['flash'];
            unset($_SESSION['flash']);
        }
        return $flashData; 
    }
    
    protected function addheader($header)
    {
        $this->outputer->addheder($header);
    }
    
    private function populateInput()
    {
        $this->populateFromPost();
        $this->populateFromGet();
        $this->populateFromFiles();
        $this->getFromJson();
    }
    
    private function getFromJson()
    {
        try{
            $inputJSON = file_get_contents('php://input');
            $this->input['json'] = json_decode($inputJSON, TRUE);
        }catch (Exception $e){
            //must have a catch
        }
    }
    
    private function populateFromPost()
    {
        if(isset($_POST) && is_array($_POST))
        {
            $this->input['post'] = $_POST;
            if(method_exists($this->outputer, "storeLastPost"))
            {
                $this->outputer->storeLastPost();
            }
        }
    }

    private function populateFromGet()
    {
        if(isset($_GET) && is_array($_GET))
        {
            $this->input['get'] = $_GET;
        }
    }
    
    private function populateFromFiles()
    {
        if(isset($_FILES) && is_array($_FILES))
        {
            $validFiles = [];
            foreach($_FILES as $key => $value)
            {
                if($value['size'] > 0 && $value['error'] == 0)
                {
                    $validFiles[$key] = $value;
                }
            }
            $this->files = $validFiles;
        }
    }
    
    
    protected function getinput($name, $default = null)
    {
        if(isset($this->input['post']) && isset($this->input['post'][$name]))
        {
            return $this->input['post'][$name];
        }elseif(isset($this->input['get']) && isset($this->input['get'][$name])){
            return $this->input['get'][$name];
        }elseif(isset($this->input['json']) && isset($this->input['json'][$name])){
            return $this->input['json'][$name];
        }else{
            return $default;
        }
    }
    
    protected function haveFiles()
    {
        return count($this->files)==0?false:true;
    }
    
    protected function getFiles($field = null)
    {
        if(is_null($field))
        {
            return $this->files;
        }else{
            if(isset($this->files[$field]))
            {
                return $this->files[$field];
            }else{
                return null;
            }
        }
    }
    
    protected function redirect($url, $statusCode = 303)
    {
        $_SESSION['lastpost'] = null;
        unset($_SESSION['lastpost']);
        session_write_close();
        header('Location: ' . $url, true, $statusCode);
        die();
    }
    
    protected function referrer()
    {
        return isset($_SESSION['lastpath'])?$_SESSION['lastpath']:"/";
    }

    public function initialization()
    {
        $this->populateInput();        
    }
    
    public function init()
    {
        //must be overriden
    }
}