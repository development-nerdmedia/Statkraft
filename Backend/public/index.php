<?php
/**
 * This is the Index file
 * Do not modify under any reason.
 * All configuration must be done in the config files.
 * Nothing to do here.
 * GET OUT!
 */
if(!defined('APP_ROOT'))
{
        define ('APP_ROOT',str_replace("/public", '', dirname(__FILE__)));
}
if(!defined('DS'))
{
        define ('DS',DIRECTORY_SEPARATOR);
}
if(is_file(APP_ROOT.DS.'lib'.DS.'bootstrap.php'))
{
        require APP_ROOT.DS.'lib/bootstrap.php';
        if(class_exists('dispatcher'))
        {
                $dp = new Dispatcher();
                $dp->Dispatch();
        }
}

?>