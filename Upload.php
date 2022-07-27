<?php
/**
 * @author flexphperia.net
 */
class Upload extends MY_Controller {
    
    public function __construct() {
        parent::__construct();
    }


    public function logo()
    {
        $this->_upload('jpg|png|svg');
    }
    
    
    public function field()
    {
        $this->_upload('jpg');
    }
    
    
    function _upload($allowed_types)
    {
        $config = array();

        $config['upload_path'] = $this->config->item('yc_temp_path');
        $config['allowed_types'] = $allowed_types;
        $config['file_ext_tolower'] = true;

        $this->load->library('upload');

        $org_filename = $_FILES['file']['name'];
        //add random string to filename
        $_FILES['file']['name'] = convert_accented_characters(pathinfo($org_filename, PATHINFO_FILENAME)) .'-'. mt_rand(100000, 999999) .'.'.pathinfo($org_filename, PATHINFO_EXTENSION);

        
        
        $this->upload->initialize($config);
        $result = $this->upload->do_upload('file');

        if (!$result)
        {
            $this->output
                ->set_status_header(500)
                ->set_output($this->upload->display_errors('', ''));   
        }
           
        $return_data = $this->upload->data('file_name'); 

        return $this->output->json_response($return_data); //return okay response, changed
        //return nothing if ok, 200 code 
    }

    

}
