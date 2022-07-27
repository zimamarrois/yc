<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Highly modified to support only runing other controller methods
 * 
 * Modular Extensions - HMVC
 *
 * @copyright	Copyright (c) 2015 Wiredesignz
 * @version 	5.5
 * 
 **/
class CI extends CI_Controller
{
	public static $APP;
	
	public function __construct() {
		
		/* assign the application instance */
		self::$APP = $this;
//		get_instance()

		parent::__construct();
	}
}

/* create the application object */
new CI;