<?php

/**
 * Main controller
 * 
 * @author flexphperia.net
 */
class Item_types extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('item_types_model');
        
    }

    public function index()
    {
        $this->html_auth_check(array('add_delete_item_types', 'edit_item_types'), 'or');
        
        $item_types = $this->item_types_model->get();  
        
        $items = array();

        $can_delete_add = can('add_delete_item_types');
        
        foreach ($item_types as $item_type_vo)
        {
            
             
            $items[] = array(
                'link' => site_url('item_types/edit/' . $item_type_vo->id),
                'text' => $item_type_vo->name,
                'details' => array('ID: '. $item_type_vo->id),
                'order_button' => true,
                'delete_button' => $can_delete_add,
                'data' => array('id' => $item_type_vo->id, 'order' => $item_type_vo->order),
                
            );
        }

        $data = array(
            'title' => lang('item_types_title'),
            'list_data' => array(
                'subtitle' => lang('item_types_count'),
                'count' => count($items),
                'items' => $items,
            ),
            'can_add' => $can_delete_add
            
        );

        $this->load->view('parts/header', $data);
        $this->load->view('pages/item_types', $data);
        $this->load->view('parts/footer');
    }
    

    
    
    public function update_order()
    {
        if (!can(array('edit_item_types', 'add_delete_item_types'), 'or'))
        {
            return $this->output->json_forbidden();
        }

        if ( !$this->validator->validate_item_types_orders($this->input->post()) )
        {
             return $this->output->json_wrong_params();
        }
        
        $this->item_types_model->update_orders($this->input->post('ids'), $this->input->post('orders'));  
        
        return $this->output->json_response(
                lang('admin_ajax_item_types_order')
        ); //return okay response
    }

    public function edit($item_type_id = 0)
    {
        if ($item_type_id !== 0 && !$this->validator->validate_id($item_type_id))
        {
            return $this->show_404();
        }
        
        //editing 
        if ($item_type_id !== 0)
        {
            $this->html_auth_check(array('add_delete_item_types', 'edit_item_types'), 'or');
            
            
            $item_type_vo = $this->item_types_model->get(array($item_type_id))[$item_type_id];  

            if (!$item_type_vo)
            {
                //not found
                return $this->show_404();
            }
        }
        else
        {
            $this->html_auth_check('add_delete_item_types');
            $item_type_vo = new Item_type_vo();
        }
        
        $this->load->model('event_types_model');
        $event_types = $this->event_types_model->get(); 
        
        $data = array(
            'title' => lang('item_type_edit_title'),
            'add_mode' => $item_type_vo->id == 0,
            'item_type_vo' => $item_type_vo,
            'event_types' => $event_types,
        );

        $this->load->view('parts/header', $data);
        $this->load->view('pages/item_type_edit', $data);
        $this->load->view('parts/footer');
    }

    public function delete()
    {
        if (!can('add_delete_item_types'))
        {
            return $this->output->json_forbidden();
        }
        
        //user admin cannot be deleted
        if (!$this->validator->validate_id($this->input->post('id')))
        {
            return $this->output->json_wrong_params();
        }
        
        $id = (int)$this->input->post('id');
        
        $curr_item_type_vo = $this->item_types_model->get(array($id))[$id];
            
        //changed username so validate that is exist
        if (!$curr_item_type_vo)
        {
            return $this->output->json_not_found();
        }
        

        $result = $this->item_types_model->delete($id);

        //error while deleting 
        if ($result === false)
        {
            return $this->output->json_not_found();
        }


        return $this->output->json_response(
            array(
                lang('admin_ajax_item_type_deleted'),
                self::run('item_types/index', '.item-list') //return part of other method
            )
        ); //return okay response
    }
    
    public function save()
    {       
        
        if ( !$this->validator->validate_item_type($this->input->post()) )
        {
            return $this->output->json_wrong_params();
        }
                
        $item_type_vo = new Item_type_vo($this->input->post());
        

        $updated_id = null;
        
        if (!$item_type_vo->id) //new 
        {      
            if (!can('add_delete_item_types'))
            {
                return $this->output->json_forbidden();
            }
            
            //will return id
            $result_db = $this->item_types_model->save($item_type_vo);
            
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
//            $this->html_auth_check(array('add_delete_item_types', 'edit_item_types'), 'or');
            if (!can(array('add_delete_item_types', 'edit_item_types'), 'or'))
            {
                return $this->output->json_forbidden();
            }
            
            $curr_item_type_vo = $this->item_types_model->get(array($item_type_vo->id))[$item_type_vo->id];
            
            if (!$curr_item_type_vo)
            {
                return $this->output->json_not_found();
            }
            
            $updated_id = $this->item_types_model->save($item_type_vo, $curr_item_type_vo);
        }

        //re get from db
        $item_type_vo = $this->item_types_model->get(array($updated_id))[$updated_id];

        $return_data = array(
            lang('admin_ajax_item_type_saved'),
            $item_type_vo->to_send_array()
        ); 

        return $this->output->json_response($return_data); //return okay response, changed
    }
    
   

}
