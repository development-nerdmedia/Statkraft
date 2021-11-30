<?php

class Admin extends Controller
{
    public $layout = 'admin';
    
    public $components = ['Auth'];
    
    public function login()
    {
        if($this->Auth->verify())
        {
            $this->redirect("/dashboard");
        }
    }

    public function logout()
    {
        $this->Auth->logout();
        $this->redirect("/");
    }
    
    
    public function verify()
    {
        if($this->getinput('Username') == 'admin' && $this->getinput('Password') == 'st4tkr4ft@')
        {
            $this->Auth->login();
            $this->redirect("/dashboard");
        }else{
            $this->redirect("/");
        }
    }
    
    public function dashboard()
    {
        $this->isLogged();
    }
    
    private function isLogged()
    {
        if(!$this->Auth->verify())
        {
            $this->logout();
        }
    }
    
}

?>
