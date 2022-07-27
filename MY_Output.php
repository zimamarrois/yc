<?php
/**
 * Default output
 *
 * @author flexphperia.net
 */
class MY_Output extends CI_Output{
    
    //json array prepared to return
//    public $json_data = array();
    
    public $is_json = false;

    public function __construct(){
        
        parent::__construct();
        
        //disable browser cache
        $this->set_header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT');
        $this->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->set_header('Pragma: no-cache');
        $this->set_header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
        
//        $this->get_header($header)
    }
    
    
    public function json_wrong_params()
    {
        return $this->json_response(lang('ajax_error_wrong_params'), 2);
    }
    
    
    public function json_not_found()
    {
        return $this->json_response(lang('ajax_error_resource_not_found'), 2);
    }
    
    
    public function json_forbidden()
    {
        return $this->json_response(lang('ajax_error_forbidden'), 3);
    }
    
    /**
     * Allowed codes of json response:
     * 1 - ok
     * 2 - error - return message to display in notify
     * 3 - not logged in / session expired
     * 
     * @param mix $data
     * @param int $code
     */
    public function json_response($data = null, $code = 1)
    {
        $this->is_json = true; //used by hook compress
        
        $return = array(
                'code' => $code,
                'data' => $data
             );
        //profiler was enabled, so we need his ouptus only to send via ajax
        if ($this->enable_profiler)
        {
            $this->enable_profiler(false); //disable it
            
            $CI =& get_instance();
            $CI->load->library('profiler');

            if ( ! empty($this->_profiler_sections))
            {
                $CI->profiler->set_sections($this->_profiler_sections);
            }
            $return['profiler'] = $CI->profiler->run();
        }

        
        $this->set_content_type('application/json')
             ->set_output(json_encode($return));

    }
}

