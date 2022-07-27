<?php

/**
 * Main controller
 * 
 * @author flexphperia.net
 */
class Permissions extends MY_Controller
{
    private $_items_storage;
    private $_item_types_storage;
        
    private $_collator;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->load->model('perms_model');
        $this->_collator = new Collator(lang('php_locale'));
    }

    public function groups()
    {
        $this->html_auth_check('edit_permissions');
        
        //get all
        $groups = $this->perms_model->get_groups();

        
        $items = array();

        foreach ($groups as $group_vo)
        {
            $details = array('ID: ' . $group_vo->id);
            $data = array('id' => $group_vo->id);
            if ($group_vo->id == 1)
            {
                $details[] = lang('perm_groups_built');
                $data['not-editable'] = lang('perm_groups_note_editable');
            }
            else if ($group_vo->id == 2)
            {
                $details[] = lang('perm_groups_built');
            }
            
            $items[] = array(
                'link' => site_url('permissions/group_edit/' . $group_vo->id),
                'text' => $group_vo->name,
                'details' => $details,
                'delete_button' => $group_vo->id != 1 && $group_vo->id != 2,
                'data' => $data
            );
        }

        $data = array(
            'title' => lang('perm_groups_title'),
            'list_data' => array(
                'subtitle' => lang('perm_groups_count'),
                'count' => count($items),
                'items' => $items,
            )
        );

        $this->load->view('parts/header', $data);
        $this->load->view('pages/perm_groups', $data);
        $this->load->view('parts/footer');
    }
    
    
    
    public function users()
    {
        $this->html_auth_check('edit_permissions');
        
        $users = $this->aauth->list_users();

        $items = array();
    
        foreach ($users as $user)
        {
            $data = array();
            $details = array('ID: ' . $user->id);
            if ($user->id == 1)
            {
                $data['not-editable'] = lang('perm_users_note_editable');
                $details[] = lang('perm_users_built');
            }
            
            $items[] = array(
                'link' => site_url('permissions/user_edit/' . $user->id),
                'text' => $user->username,
                'details' => $details,
                'data' => $data
            );
        }

        $data = array(
            'title' => lang('perm_users_title'),
            'list_data' => array(
                'subtitle' => lang('perm_users_count'),
                'count' => count($items),
                'items' => $items,
            )
        );
        
        $this->load->view('parts/header', $data);
        $this->load->view('pages/perm_users', $data);
        $this->load->view('parts/footer');
    }

    public function group_edit($group_id = 0)
    {
        $this->html_auth_check('edit_permissions');
        
        //group 1 is not editable admins
        if ($group_id !== 0 && !$this->validator->validate_id($group_id ) || $group_id == 1)
        {
            return $this->show_404();
        }
           
        //editing 
        if ($group_id !== 0)
        {
            $group_vo = $this->perms_model->get_groups(array($group_id))[$group_id];

            if (!$group_vo)
            {
                //not found
                return $this->show_404();
            }
        }
        else
        {
            $group_vo = new Group_vo();
        }
 
        $data = array(
            'title' => lang('perm_group_edit_title'),
            'add_mode' => $group_vo->id == 0,
            'group_vo' =>  $group_vo,
            'sections' => $this->_perms_sections()
        );
   

        $this->load->view('parts/header', $data);
        $this->load->view('pages/perm_group_edit', $data);
        $this->load->view('parts/footer');
    }
    
    public function user_edit($user_id = 0)
    {
        $this->html_auth_check('edit_permissions');
        //group 1 is not editable admins
        if ($user_id == 0 || !$this->validator->validate_id($user_id) || $user_id == 1)
        {
            return $this->show_404();
        }
        
        //editing user perms always, we cannoy add perms
        $user_perms_vo =  $this->perms_model->get_user($user_id);

        if (!$user_perms_vo)
        {
            //not found
            return $this->show_404();
        }

        
        $data = array(
            'title' => lang('perm_user_edit_title'),
            'add_mode' => $user_perms_vo->id == 0,
            'user_perms_vo' =>  $user_perms_vo,
            'groups' => $this->perms_model->get_groups(),
            'sections' => $this->_perms_sections()
        );

        $this->load->view('parts/header', $data);
        $this->load->view('pages/perm_user_edit', $data);
        $this->load->view('parts/footer');
    }
    
    public function user_save()
    {              
        if (!can('edit_permissions'))
        {
            return $this->output->json_forbidden();
        }
        
        if ( !$this->validator->validate_user_perms($this->input->post()) )
        {
            return $this->output->json_wrong_params();
        }
        
        
        $user_perms_vo = new User_perms_vo( $this->input->post() );
        
        $curr_user_perms_vo = $this->perms_model->get_user($user_perms_vo->id);
            
        if (!$curr_user_perms_vo)
        {
            return $this->output->json_not_found();
        }

            
        $this->perms_model->save_user($user_perms_vo, $curr_user_perms_vo);
        
        //re get from db
        $user_perms_vo = $this->perms_model->get_user($user_perms_vo->id);


        $return_data = array(
            lang('admin_ajax_user_perms_saved'),
            $user_perms_vo->to_send_array()
        ); 

        return $this->output->json_response($return_data); //return okay response, changed
    }
    

    public function group_delete()
    {
        if (!can('edit_permissions'))
        {
            return $this->output->json_forbidden();
        }
        
        //user admin cannot be deleted
        if (!$this->validator->validate_id($this->input->post('id')) || $this->input->post('id') == '1' || $this->input->post('id') == '2')
        {
            return $this->output->json_wrong_params();
        }

        $result = $this->aauth->delete_group($this->input->post('id'));

        //error while deleting 
        if ($result === false)
        {
            return $this->output->json_not_found();
        }


        return $this->output->json_response(
            array(
                lang('admin_ajax_group_deleted'),
                self::run('permissions/groups', '.item-list') //return part of other method
            )
        ); //return okay response
    }
  

    public function group_save()
    {       
        if (!can('edit_permissions'))
        {
            return $this->output->json_forbidden();
        }
        
        if ( !$this->validator->validate_group($this->input->post()) )
        {
            return $this->output->json_wrong_params();
        }
        
        $group_vo = new Group_vo( $this->input->post() );
        
        $updated_id = null;
        
        if (!$group_vo->id) //new 
        {      
            //will return id
            $result_db = $this->perms_model->save_group($group_vo);
            
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
            $curr_group_vo = $this->perms_model->get_groups(array($group_vo->id))[$group_vo->id];
            
            //changed username so validate that is exist
            if (!$curr_group_vo)
            {
                return $this->output->json_not_found();
            }
            
            //pass old group
            $updated_id = $this->perms_model->save_group($group_vo, $curr_group_vo);
        }
        
        //re get from db
        $group_vo = $this->perms_model->get_groups(array($updated_id))[$updated_id];

        $return_data = array(
            lang('admin_ajax_group_saved'),
            $group_vo->to_send_array()
        ); 

        return $this->output->json_response($return_data); //return okay response, changed
    }
    
    
    private function _perms_sections()
    {
        return  array(
            array(
                'name' => lang('perm_admin'),
                'items' => array(
                    array(
                        'name' => 'edit_item_types',
                        'label' => lang('perm_edit_item_types'),
                    ),
                    array(
                        'name' => 'add_delete_item_types',
                        'label' => lang('perm_add_delete_item_types'),
                    ),
                    array(
                        'name' => 'edit_event_types',
                        'label' => lang('perm_edit_event_types'),
                    ),
                    array(
                        'name' => 'add_delete_event_types',
                        'label' => lang('perm_add_delete_event_types'),
                    ),
                    array(
                        'name' => 'edit_users',
                        'label' => lang('perm_edit_users'),
                    ),
                    array(
                        'name' => 'add_delete_users',
                        'label' => lang('perm_add_delete_users'),
                    ),
                    array(
                        'name' => 'edit_permissions',
                        'label' => lang('perm_edit_permissions'),
                    ),
                    array(
                        'name' => 'edit_settings',
                        'label' => lang('perm_edit_settings'),
                    ),
                )
            ),
            array(
                'name' => lang('perm_item_types'),
                'items' => $this->_item_types_list('item_type')
            ),
            array(
                'name' => lang('perm_items'),
                'items' => $this->_items_list('item')
            ),

        );
    }
    
    
    
    private function _item_types_list($perm_prefix)
    {
        if (!$this->_item_types_storage) //only once
        {
            $this->load->model('item_types_model');
            $this->_item_types_storage = $this->item_types_model->get(null, true);
        }
        //find all items


        $item_types_perms = array(
            array(
                'name' => 'all',
                'perm_prefix' => $perm_prefix,
                'label' => lang('perm_all_types'),
                'is_complex' => true,
                'has_add' => true
            )
        );
        foreach ($this->_item_types_storage as $item_type_vo)
        {
            $item_types_perms[] = array(
                'name' => $item_type_vo->id,
                'perm_prefix' => $perm_prefix,
                'label' => htmlspecialchars($item_type_vo->name),
                'is_complex' => true,
                'has_add' => true
            );
        }
        
        
        return $item_types_perms;
    }
    
    
    
    private function _items_list($perm_prefix)
    {
        if (!$this->_items_storage) //only once
        {
            $this->load->model('items_model');
            $this->_items_storage = $this->items_model->get(); //by name
        }
        //find all items

        $items_perms = array();
        foreach ($this->_items_storage as $item_vo)
        {
            $items_perms[] = array(
                'name' => $item_vo->id,
                'perm_prefix' => $perm_prefix,
                'label' => htmlspecialchars($item_vo->name) . ' - <i>'.lang('perm_type').'</i> '. htmlspecialchars($item_vo->type->name),
                'dependend_id' => $item_vo->type->id,
                'is_complex' => true,
                'type_name' => $item_vo->type->name //store for sorter
            );
        }
        
        //now we have items sorted by name, additionally sort by type name
        usort ( $items_perms , array($this, '_sort_items_by_type') );
        
        return $items_perms;
    }
    
    
    private function _sort_items_by_type($arr1, $arr2)
    {
        return $this->_collator->compare($arr1['type_name'], $arr2['type_name']); 
//        return strcoll(mb_strtolower($vo1->name, 'UTF-8'), mb_strtolower($vo2->name, 'UTF-8'));
    }

}
