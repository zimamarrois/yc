<?php

/**
 * Main controller
 * 
 * @author flexphperia.net
 */
class Items extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $item_vos = filter_item_edit($this->items_model->get());
        
        //the same as in browse
        $item_types_ids_from_items = array_unique(array_column(array_column($item_vos, 'type'), 'id')); 
        
        //item types allowed to edit directly
        $item_types_ids = array_column(filter_item_type_edit($this->item_types_model->get(null)), 'id'); //by name
        
        $item_type_ids_sum = array_unique(array_merge ($item_types_ids_from_items, $item_types_ids));
        
        //we have to get it from model in one call to get propoer order, we cannot combine array of two calls to model
        //array of item types that are allowed to filter
        $filter_item_types = $this->item_types_model->get($item_type_ids_sum, true); 
        
        //array of item types that allowed to add by user
        $add_item_types = filter_item_type_add_delete($this->item_types_model->get(null, true)); 
        
        $items = array();
        foreach ($item_vos as $item_vo)
        {
            $items[] = array(
                'link' => site_url('items/edit/' . $item_vo->id),
                'icon' => $item_vo->icon,
                'text' => $item_vo->name,
                'details' => array('ID: ' . $item_vo->id . ' / '.lang('items_type').' '. $item_vo->type->name ),
                'data' => array('id' => $item_vo->id, 'type-id' => $item_vo->type->id),
                'delete_button' => can_item_type_add_delete( $item_vo->type->id)
            );
        }

        $data = array(
            'title' => lang('items_title'),
            'list_data' => array(
                'subtitle' => lang('items_count'),
                'count' => count($items),
                'items' => $items,
            ),
            'filter_item_types' => $filter_item_types,
            'add_item_types' => $add_item_types,
        );

        $this->load->view('parts/header', $data);
        $this->load->view('pages/items', $data);
        $this->load->view('parts/footer');
    }
    



    public function edit($item_id = 0, $item_type_id = null)
    {
        if ($item_id != 0 && !$this->validator->validate_id($item_id))
        {
            return $this->show_404();
        }
        
        if ($item_id == 0 && $item_type_id == null)
        {
            return $this->show_404();
        }
        
        if ($item_type_id != null && !$this->validator->validate_id($item_type_id))
        {
            return $this->show_404();
        }
        
        //editing 
        if ($item_id != 0)
        {
            if (!can_item_edit($item_id))
            {
                return $this->show_403();
            }
            
            $item_vo = $this->items_model->get(array($item_id))[$item_id];  

            if (!$item_vo)
            {
                //not found
                return $this->show_404();
            }
        }
        else
        {
            if (!can_item_type_add_delete($item_type_id))
            {
                return $this->show_403();
            }
            
            $this->load->model('item_types_model');
            $item_type = $this->item_types_model->get(array($item_type_id))[$item_type_id]; 
            
            //type not found
            if ($item_type == false)
            {
                return $this->show_404();
            }
            
            $fields = array();
            foreach ($item_type->field_types as $field_type_vo)
            {
                $f = new Field_vo(array('type' => $field_type_vo));
                $fields[] = $f;
            }

            $item_vo = new Item_vo(array(
                        'type' => $item_type
                        ));
            
            $item_vo->fields = $fields;
        }
        

        $data = array(
            'title' => lang('item_edit_title'),
            'add_mode' => $item_vo->id == 0,
            'item_vo' => $item_vo,
//            'event_types' => $event_types,
        );

        $this->load->view('parts/header', $data);
        $this->load->view('pages/item_edit', $data);
        $this->load->view('parts/footer');
    }

    public function delete()
    {
        //user admin cannot be deleted
        if (!$this->validator->validate_id($this->input->post('id')))
        {
            return $this->output->json_wrong_params();
        }
        
        $id = (int)$this->input->post('id');
        
        $curr_item_vo = $this->items_model->get(array($id))[$id];
        
        if (!$curr_item_vo)
        {
            return $this->output->json_not_found();
        }
        
        if (!can_item_type_add_delete($curr_item_vo->type->id))
        {
            return $this->output->json_forbidden();
        }
            

        $result = $this->items_model->delete($id);

        //error while deleting 
        if ($result === false)
        {
            return $this->output->json_not_found();
        }


        return $this->output->json_response(
            array(
                lang('admin_ajax_item_deleted'),
                self::run('items/index', '.item-list') //return part of other method
            )
        ); //return okay response
    }

    
    public function save()
    {       
        $old_item_vo = null;
        $item_type = null;
        
        if (!empty($this->input->post('id')))
        {
            $old_item_vo = $this->items_model->get(array($this->input->post('id')))[$this->input->post('id')];
            $item_type = $old_item_vo->type;
            
            if (!$old_item_vo)
            {
                return $this->output->json_not_found();
            }
            
            if (!can_item_edit($old_item_vo->id))
            {
                return $this->output->json_forbidden();
            }
            
//            $new_item_vo->copy_empty($new_item_vo, $old_item_vo);
        }
        else //new item
        {
            $this->load->model('item_types_model');
            $item_type = $this->item_types_model->get(array($this->input->post('type')['id']))[$this->input->post('type')['id']];
            
            if (!$item_type)
            {
                return $this->output->json_not_found();
            }
            
            if (!can_item_type_add_delete($item_type->id))
            {
                return $this->output->json_forbidden();
            }
//            $old_item_vo = $this->items_model->get(array($new_item_vo->id))[$new_item_vo->id];
        }
        
        
        if ( !$this->validator->validate_item($this->input->post(), $item_type, $old_item_vo) )
        {
            return $this->output->json_wrong_params();
        }
        
        $new_item_vo = new Item_vo($this->input->post());
                
                
        $updated_id = null;
        
        if (!$new_item_vo->id) //new 
        {      
            //will return id
            $result_db = $this->items_model->save($new_item_vo, $item_type);
            
            //not found
            //error while saving 
            if ($result_db === false)
            {
                return $this->output->json_response(lang('ajax_error_saving_data'), 2);
            }
            
            $updated_id = $result_db;
        }
        else //updating user
        {
            $this->items_model->save($new_item_vo, $item_type, $old_item_vo);
            
            $updated_id = $new_item_vo->id;
        }
        
        //re get from db
        $new_item_vo = $this->items_model->get(array($updated_id))[$updated_id];

        $return_data = array(
            lang('admin_ajax_item_saved'),
            $new_item_vo->to_send_array()
        ); 

        return $this->output->json_response($return_data); //return okay response, changed
    }
   
    

}
