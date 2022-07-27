<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
 * Checks all app requirements
 *
 * @author flexphperia.net
 */
class Requirements{
    
    
    public function __construct()
    {
        // Assign the CodeIgniter super-object
        $this->CI =& get_instance();
    }
    
    /**
     * Checks all app requirements
     * @return string
     */
    public function check()
    {
        $failed = array();
        
        $storage_path = $this->CI->config->item('yc_storage_path');
        
        if (version_compare(PHP_VERSION, '7.2.0') < 0)
        {
            $failed[] = 'php';
        }
        
        if (!extension_loaded('gd') || !function_exists('gd_info'))
        {
            $failed[] = 'gd';
        }
       
        if (!is_writeable($storage_path.DIRECTORY_SEPARATOR.'uploads'))
        {
            $failed[] = 'uploads_dir';
        }
        
        if (!is_writeable($storage_path.DIRECTORY_SEPARATOR.'sessions'))
        {
            $failed[] = 'sessions_dir';
        }
        
        if (!is_writeable($storage_path.DIRECTORY_SEPARATOR.'temp'))
        {
            $failed[] = 'temp_dir';
        }
  
        return $failed;
    }
    
    
    
    
}
