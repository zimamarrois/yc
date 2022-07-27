<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/** load the CI class for Modular Extensions **/
require dirname(__FILE__).'/Base.php';

/**
 * Highly modified to support only runing other controller methods
 * 
 * Modular Extensions - HMVC
 *
 * @copyright	Copyright (c) 2015 Wiredesignz
 * @version 	5.5
 * 
 **/
class MX_Controller  
{
   
        //registry that holds all instantied controllers
        public static $controller_registry;

        /**
        * Runs other controller method and returns output 
        */
        public static function run($path, $return_selector = null) 
        {	
            $method = 'index';

            $pos = strrpos($path, '/');

            $controller = substr($path, 0, $pos !== false ? $pos : 99);

            if ($pos)
            {
                $method = substr($path, $pos + 1);	
            }


            $class = ucfirst($controller);

            if ( !isset(self::$controller_registry[$class]) )
            {
                require_once(APPPATH.'controllers/'.$class.'.php');
                self::$controller_registry[$class] = new $class;
            }

            $controller_instance = self::$controller_registry[$class];

            ob_start();
            $args = func_get_args();
            $output = call_user_func_array(array($controller_instance, $method), array_slice($args, 1));
            $buffer = ob_get_clean();
            
            if ($return_selector)
            {
                $buffer = self::get_string_between($buffer, '<!--start="'.$return_selector.'"-->', '<!--end="'.$return_selector.'"-->');
            }
           
            return ($output !== NULL) ? $output : $buffer;
        }
        
        private static function get_string_between($string, $start, $end)
        {
            $ini = strpos($string, $start);
            if ($ini == 0) return '';
            $len = strpos($string, $end, $ini) - $ini + strlen($end);
            return substr($string, $ini, $len);
        }

	
	public function __construct() 
	{
//            var_dump()
            $class = get_class($this);
            log_message('debug', $class." MX_Controller Initialized");
            self::$controller_registry[$class] = $this;	
            
            /* copy a loader instance and initialize */
//            $this->load = clone load_class('Loader');
            $this->load = load_class('Loader');
            $this->load->initialize($this);	

	}
	
	public function __get($class) 
	{
            return CI::$APP->$class;
	}
}