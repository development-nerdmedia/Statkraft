<?php

class Site extends Controller
{
    public $layout = '';
    
    public $uses = [
    ];
    
    public function index()
    {
        header("Location: https://www.statkraft.com.pe/conecta-con-el-futuro", false, 307);
        die();
    }

    public function iframetmp()
    {
        
    }
}

?>
