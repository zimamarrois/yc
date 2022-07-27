<?php

/**
 * Main controller
 * 
 * @author flexphperia.net
 */
class Event_types extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('event_types_model');
        
    }

    public function index()
    {
        $this->html_auth_check(array('add_delete_event_types', 'edit_event_types'), 'or');
        
        $event_types =  $this->event_types_model->get();  
        
        $items = array();

        
        $can_delete_add = can('add_delete_event_types');
        
        foreach ($event_types as $event_type_vo)
        {
            $items[] = array(
                'link' => site_url('event_types/edit/' . $event_type_vo->id),
                'text' => $event_type_vo->name,
                'icon' => $event_type_vo->icon,
                'details' => array('ID: '. $event_type_vo->id),
                'delete_button' => $can_delete_add,
                'data' => array('id' => $event_type_vo->id)
            );
        }

        $data = array(
            'title' => lang('event_types_title'),
            'list_data' => array(
                'subtitle' => lang('event_types_count'),
                'count' => count($items),
                'items' => $items,
            ),
            'can_add' => $can_delete_add
        );

        $this->load->view('parts/header', $data);
        $this->load->view('pages/event_types', $data);
        $this->load->view('parts/footer');
    }

    public function edit($event_type_id = 0)
    {

        if ($event_type_id !== 0 && !$this->validator->validate_id($event_type_id))
        {
            return $this->show_404();
        }
        
        //editing 
        if ($event_type_id !== 0)
        {
            $this->html_auth_check(array('add_delete_event_types', 'edit_event_types'), 'or');
            
            $event_type_vo = $this->event_types_model->get(array($event_type_id))[$event_type_id];  

            if (!$event_type_vo)
            {
                //not found
                return $this->show_404();
            }
        }
        else
        {
            $this->html_auth_check('add_delete_event_types');
            $event_type_vo = new Event_type_vo();
        }

        $data = array(
            'title' => lang('event_type_edit_title'),
            'add_mode' => $event_type_vo->id == 0,
            'event_type_vo' => $event_type_vo,
        );

        $this->load->view('parts/header', $data);
        $this->load->view('pages/event_type_edit', $data);
        $this->load->view('parts/footer');
    }

    public function delete()
    {
        if (!can('add_delete_event_types'))
        {
            return $this->output->json_forbidden();
        }
        
        //user admin cannot be deleted
        if (!$this->validator->validate_id($this->input->post('id')))
        {
            return $this->output->json_wrong_params();
        }
        
        $id = (int)$this->input->post('id');
        
        $curr_event_type_vo = $this->event_types_model->get(array($id))[$id];
            
        //changed username so validate that is exist
        if (!$curr_event_type_vo)
        {
            return $this->output->json_not_found();
        }

        $result = $this->event_types_model->delete($id);

        //error while deleting 
        if ($result === false)
        {
            return $this->output->json_not_found();
        }

        return $this->output->json_response(
            array(
                lang('admin_ajax_event_type_deleted'),
                self::run('event_types/index', '.item-list') //return part of other method
            )
        ); //return okay response
    }
    
    public function save()
    {       
        if ( !$this->validator->validate_event_type($this->input->post()) )
        {
            return $this->output->json_wrong_params();
        }
        
        $event_type_vo = new Event_type_vo($this->input->post());
        
        $updated_id = null;
        
        if (!$event_type_vo->id) //new 
        {      
            if (!can('add_delete_event_types'))
            {
                return $this->output->json_forbidden();
            }
            
            
            //will return id
            $result_db = $this->event_types_model->save($event_type_vo);
            
            //not found
            //error while saving 
            if ($result_db === false)
            {
                return $this->output->json_response(lang('ajax_error_saving_data'), 2);
            }
            
            $updated_id = $result_db;
        }
        else //updating 
        {
            if (!can(array('add_delete_event_types', 'edit_event_types'), 'or'))
            {
                return $this->output->json_forbidden();
            }
            
            $curr_event_type_vo = $this->event_types_model->get(array($event_type_vo->id))[$event_type_vo->id];
            
            //changed username so validate that is exist
            if (!$curr_event_type_vo)
            {
                return $this->output->json_not_found();
            }
            
            $updated_id = $this->event_types_model->save($event_type_vo);
        }
        
        //re get from db
        $event_type_vo = $this->event_types_model->get(array($updated_id))[$updated_id];

        $return_data = array(
            lang('admin_ajax_event_type_saved'),
            $event_type_vo->to_send_array()
        ); 

        return $this->output->json_response($return_data); //return okay response, changed
    }
    
    
    
    
   

}
