<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');


function startup()
{
    $CI =& get_instance();
    
    $CI->load->database();
    $CI->load->model('settings_model');

    $settings_vo = $CI->settings_model->load();
    
    //find language from browser if auto
    if ($settings_vo->language == 'auto')
    {   
        //server value might be empty
        $lang = strtolower(substr(@$_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));

        if ( !isset($CI->config->item('yc_languages')[$lang]) ) //not found in languages
        {
            $lang = 'en';
        }
    }
    else
    {
        $lang = $settings_vo->language;
    }

    //set lanaguage of app
    $lang = $CI->config->item('yc_languages')[$lang];
    $CI->config->set_item('language', $lang); //set into confic, core classes will load corect lang.
    $CI->lang->load(array('strings', 'validation'), $lang); //load langauge non standard files
    
    //store language name, for demo purposes,
    setlocale(LC_COLLATE,lang('php_locale')); //set locale for sorting

}





/**
 * Function used as hook to compress html output
 */
function compress()
{
    $CI =& get_instance();

    //minify only pages with html not ajax
    if ( !$CI->output->is_json ) 
    {
        $buffer = $CI->output->get_output();

        $new_buffer =  compress_html($buffer);

        // We are going to check if processing has working
        if ($new_buffer === null)
        {
            $new_buffer = $buffer;
        }

        $CI->output->set_output($new_buffer);
    }

    $CI->output->_display();
}
