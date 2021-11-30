<?php 

class Auth {
    
    public function verify()
    {
        if(!isset($_SESSION['logged']))
        {
            return false;
        }
        if(!$_SESSION['logged'])
        {
            return false;
        }else{
            if(isset($_SESSION['lastaction']))
            {
                if(time() - $_SESSION['lastaction'] > 3600)
                {
                    return false;
                }
            }else{
                return false;
            }
        }
        $_SESSION['lastaction'] = time();
        return true;
    }
    
    public function login()
    {
        $_SESSION['logged'] = true;
        $_SESSION['lastaction'] = time();        
    }
    
    public function logout()
    {
        if(isset($_SESSION['logged']))
        {
            unset($_SESSION['logged']);
            unset($_SESSION['lastaction']);
        }        
    }
    
}

?>