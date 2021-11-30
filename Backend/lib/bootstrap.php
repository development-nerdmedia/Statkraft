<?php
/**
 * Basic bootstrap file
 *
 * This file can be modified to include new bootstrap process
 * At the moment basically it loads the core configuration, do some basica checks
 * and include some other files that could be needed.
 *
 * @author Kemmotar (Jorge Escribens)
 * @package ALF (Ajax Light Framework)
 * @version 0.1 Beta
 * @since 01/14/2013
 *
 */
/*
 * Initialize Session
 */
session_start();
/*
 * Basic Object class
 */
require APP_ROOT.DS.'lib'.DS.'object.php';
/*
 * Configuration class
 */
require APP_ROOT.DS.'lib'.DS.'configure.php';
/*
 * User configuration options
*/
require APP_ROOT.DS.'config'.DS.'bootstrap.php';
foreach ($bootstrap as $value) {
	# code...
	if(file_exists(APP_ROOT.DS.'config'.DS.$value))
	{
		require_once APP_ROOT.DS.'config'.DS.$value;
	}
}
/*
 * Core configuration options
*/
require APP_ROOT.DS.'config'.DS.'core.php';
/*
 * Basic util functions
 */
require APP_ROOT.DS.'lib'.DS.'utils.php';
/*
 * Based on cofig options we ca load extra dependencies
 */
if(Configure::read('curl.load'))
{
	require APP_ROOT.DS.'lib'.DS.'curl.php';
}
/**
 * File Manager
 */
require APP_ROOT.DS.'lib'.DS.'file.php';
/*
 * Logger
 */
require APP_ROOT.DS.'lib'.DS.'log.php';

/*
 * Dispatcher
 */
require APP_ROOT.DS.'lib'.DS.'dispatcher.php';
?>