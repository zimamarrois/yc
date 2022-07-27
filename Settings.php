<?php
/**
 * App settings controller
 * @author flexphperia.net
 */
class Settings extends MY_Controller {
    
    public function __construct() {
        parent::__construct();


    }

        
    public function index() 
    {   
        $this->html_auth_check('edit_settings');

        $data = array(
            'title' => lang('settings_title')
        );

        $this->load->view('parts/header', $data);
        $this->load->view('pages/settings');
        $this->load->view('parts/footer', $data);
    }

    /**
     * Saves app settings
     * 
     * @return json
     */
    public function save()
    {       
        $this->json_auth_check('edit_settings');
        
        if ( !$this->validator->validate_settings($this->input->post()) )
        {
            return $this->output->json_wrong_params();
        }
        
        $settings_vo = new Settings_vo($this->input->post());
        $settings_vo->last_version_check = $this->settings_vo->last_version_check; //copy old value
        
        $this->settings_model->save($settings_vo);
        
        //if logo has changed move from temp to uploads
        if ($this->settings_vo->logo != $settings_vo->logo)
        {
            image_move($settings_vo->logo, $this->settings_vo->logo);
        }
        
//        Console::log('tratat');
        //retrieve from db
        
        $s_array = $this->settings_vo = $this->settings_model->load()->to_send_array();
        
        $return_data = array(
            lang('admin_ajax_settings_saved'), 
            $s_array //return settngs array
        ); 

        return $this->output->json_response($return_data); //return okay response, changed
    }

    

}
