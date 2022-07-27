<?php
/**
 * Storage for validators
 *
 * @author flexphperia.net
 */
class Validator {
    
    public static $gallery_name_max_len = 70;
    
    //Hasło musi mieć minimalną długośc 6 znaków i zawierać przynajmniej jedną literę i cyfrę.
    protected $password_rule = 'regex_match[/^(?=.*[0-9]+.*)(?=.*[a-zA-ZáàâäãåçćéèêëęíìîïłñóòôöõśúùûüýÿæœżźÁÀÂÄÃÅÇĆÉÈÊËĘÍÌÎÏÑÓÒÔÖÕŚÚÙÛÜÝŸÆŒŻŹ]+.*)[0-9\p{L}\S]{6,}$/u]';
    protected $bool_rule =  'required|regex_match[/^[0-1]$/]';
    protected $filename_rule =  'regex_match[#^((?!\.\.|\/|\\\).)*$#]'; //does not contain "..", "/", "\"
    //letters, digists and dots, starts with letter, ends letter or digit
    protected $username_rule =  'required|min_length[3]|max_length[12]|regex_match[/^[a-z]{1}[a-z|0-9|\.]+[a-z|0-9]{1}$/]'; 



    public function __construct()
    {
        // Assign the CodeIgniter super-object
        $this->CI =& get_instance();

        $this->CI->load->library('form_validation');    
    }

    /**
     * Validates  id. 
     * 
     * @param string $id
     * @return boolean
     */
    public function validate_id($id)
    {
        return $this->CI->form_validation->is_natural_no_zero($id);
    }
    
    public function validate_browse_vars ($year, $month, $item_id)
    {
        $data = array(
            'year' => $year,
            'month' => $month,
            'item_id' => $item_id
        );
        
        $this->CI->form_validation->set_data($data);
        
        $val_needed = false;
        
        if (!empty($data['year']))
        {
            $this->CI->form_validation->set_rules('year', null, 'required|greater_than_equal_to[2000]|less_than_equal_to[2100]');
            $val_needed = true;
        }
        
        if (!empty($data['month']))
        {
            $this->CI->form_validation->set_rules('month', null, 'required|greater_than_equal_to[1]|less_than_equal_to[12]');
            $val_needed = true;
        }
        
        if (!empty($data['item_id']))
        {
            $this->CI->form_validation->set_rules('item_id', null, 'required|is_natural_no_zero');
            $val_needed = true;
        }
        
        
        if ( $val_needed && !$this->CI->form_validation->run() )
        {
            return false;
        }
        
        return true; 
    }
    
    
    public function validate_date_item_vars ($post_data)
    {
        $this->CI->form_validation->reset_validation();
        $this->CI->form_validation->set_data($post_data);
        
        $this->CI->form_validation->set_rules('year', null, 'required|greater_than_equal_to[2000]|less_than_equal_to[2100]');
        $this->CI->form_validation->set_rules('month', null, 'required|greater_than_equal_to[1]|less_than_equal_to[12]');
        $this->CI->form_validation->set_rules('day', null, 'required|greater_than_equal_to[1]|less_than_equal_to[31]'); //simply validation
        $this->CI->form_validation->set_rules('item_id', null, 'required|is_natural_no_zero');

        if ( !checkdate ($post_data['month'], $post_data['day'], $post_data['year']) || !$this->CI->form_validation->run() )
        {
            return false;
        }
        
        return true; 
    }
    
    
    public function validate_events_edit ($post_data, array $old_events, Item_type_vo $item_type_vo)
    {
        if ( !is_array($post_data['events']) )
        {
            return false;
        }
        
        
        if ( count($post_data['events']) == 0 )
        {
            return true;
        }
        

        $old_events_ids = array_column($old_events, 'id');
      
        if (!$this->validate_cal_events($post_data['events'], $item_type_vo ))
        {
            return false;
        }
        //validate events
        foreach ($post_data['events'] as $event)
        {
            if (!empty($event['id'])) //editing mode not adding
            {
                //check that earlier exactly tihs id exist
                if ( !in_array($event['id'], $old_events_ids) )
                {
                    return false;
                }
            }
        }

        
        return true; 
    }
    
    public function validate_cal_events($events, Item_type_vo $item_type_vo, $require_main = true)
    {  
        $main_num = 0;
        //validate events
        foreach ($events as $event)
        {
            $needed_keys = Cal_event_vo::validation_keys();

            if ( !$this->_array_keys_match($needed_keys, $event) ) 
            {
                return false;
            }

            $this->CI->form_validation->reset_validation();
            $this->CI->form_validation->set_data($event);

            if (!empty($event['id'])) //editing
            {
                $this->CI->form_validation->set_rules('id', null, 'required|is_natural_no_zero');
            }
            
            $this->CI->form_validation->set_rules('is_main', null, $this->bool_rule);
            $this->CI->form_validation->set_rules('type[id]', null, 'required|is_natural_no_zero');

            if ( !$this->CI->form_validation->run() )
            {
                return false;
            }
            
            if ($item_type_vo->desc2_disabled && !empty($event['description2']))
            {
                return false;
            }
            

           
            //check that event type is allowed
            $found = false;
            foreach ($item_type_vo->event_types as $event_type_vo)
            {
 
                if ( $event['type']['id'] == $event_type_vo->id)
                {
                    $found = true;
                    break;
                }
            }
            
            //only when adding new event, validate that event type is allowed
            //we can edit old event that type now is not allowed
            if (empty($event['id']) && !$found)
            {
                return false;
            }
            
            //check that any has main 
            if ($event['is_main'])
            {
                $main_num++;
            }

        }
        
        if ($main_num > 1 || ($require_main && $main_num != 1))
        {
            return false;
        }

        return true; 
    }  
        
    public function validate_settings($post_data)
    {       
        $needed_keys = Settings_vo::validation_keys();
   
        //check that post data contain only needed keys nothing more
        if ( !$this->_array_keys_match($needed_keys, $post_data) ) 
        {
            return false;
        }
      
        $languages = array_keys($this->CI->config->item('yc_languages'));
        $languages[] = 'auto';

        //validate language selection
        if ( !in_array( $post_data['language'], $languages ) )
        {
            return false;
        }

        $this->CI->form_validation->set_data($post_data);
        $this->CI->form_validation->set_rules('show_empty_rows', null, $this->bool_rule);
        $this->CI->form_validation->set_rules('browse_month_num', null, 'required|is_natural_no_zero|greater_than_equal_to[1]|less_than_equal_to[3]');
        $this->CI->form_validation->set_rules('editor_month_num', null, 'required|is_natural_no_zero|greater_than_equal_to[1]|less_than_equal_to[3]');
        $this->CI->form_validation->set_rules('editor_hide_tips', null, $this->bool_rule);
        $this->CI->form_validation->set_rules('hide_login_button', null, $this->bool_rule);
        $this->CI->form_validation->set_rules('hide_header', null, $this->bool_rule);
        $this->CI->form_validation->set_rules('edit_one_only', null, $this->bool_rule);
        $this->CI->form_validation->set_rules('logo', null, 'required|'.$this->filename_rule); //validate relative paths etc does not contains
        $this->CI->form_validation->set_rules('title', null, 'required|min_length[3]|max_length[30]'); 
        
        if ( !$this->CI->form_validation->run() )
        {
            return false;
        }
        
        //when changed logo it will be in temp dir, if not it will be in uploads, one should return true
        if ( !file_exists(config_item('yc_temp_path').$post_data['logo']) && !file_exists(config_item('yc_uploads_path').$post_data['logo']) )
        {
            return false;
        }


        return true;
    }
    
    public function validate_user_perms($post_data)
    {       
        $needed_keys = User_perms_vo::validation_keys();
   
        //check that post data contain only needed keys nothing more
        if ( !$this->_array_keys_match($needed_keys, $post_data) ) 
        {
            return false;
        }

        $this->CI->form_validation->set_data($post_data);
        $this->CI->form_validation->set_rules('id', null, 'required|is_natural_no_zero');
        
        if ( !$this->CI->form_validation->run() )
        {
            return false;
        }
        
        if ( !is_array($post_data['groups']) )
        {
            return false;
        }
        
        //check that user id exists and its not 1 (admin)
        if ($post_data['id'] == 1 || !$this->CI->aauth->user_exist_by_id($post_data['id']))
        {
            return false;
        }
        
        //check that in not logged group, etc
        $have_notlogged = false;
        $group_ids = array();
        foreach ($post_data['groups'] as $group)
        {
            if ($group['id'] === '2')
            {
                $have_notlogged = true;
            }
            $group_ids[] = $group['id'];
        }
        
        if (!$have_notlogged)
        {
            return false;
        }
        
        //check that we do not have duplicates-
        if (count($group_ids) != count(array_unique($group_ids)))
        {
            return false;
        }
        
        //get groups and count them
        $this->CI->load->model('perms_model');
        $group_vos = $this->CI->perms_model->get_groups($group_ids);
        
        //will check too for duplicates of groups
        //array column on false do not add anything
        $group_num = count(array_column($group_vos, 'id'));


        if (count($group_ids) != $group_num)
        {
            return false;
        }

        
        //check that we do not have duplicates-
        if (count($post_data['perms']) != count(array_unique($post_data['perms'])))
        {
            return false;
        }
        
        $all_perms = $this->CI->aauth->list_perms();
        
        //check that we have passed permission that not exists
        if ( count(array_diff( $post_data['perms'], $all_perms)) )
        {
            return false;
        }
//        die;
        
        return true;
        
    }
    
    public function validate_group($post_data)
    {       
        $needed_keys = Group_vo::validation_keys();
   
        //check that post data contain only needed keys nothing more
        if ( !$this->_array_keys_match($needed_keys, $post_data) ) 
        {
            return false;
        }

        $this->CI->form_validation->set_data($post_data);
        
        if (!empty($post_data['id'])) //editing
        {
            $this->CI->form_validation->set_rules('id', null, 'required|is_natural_no_zero');
        }
        $this->CI->form_validation->set_rules('name', null, 'required|min_length[3]|max_length[30]');
        
        if ( !$this->CI->form_validation->run() )
        {
            return false;
        }
 
        //check that we do not have duplicates-
        if (count($post_data['perms']) != count(array_unique($post_data['perms'])))
        {
            return false;
        }
//                    die;   
        $all_perms = $this->CI->aauth->list_perms();
        
        //check that we have passed permission that not exists
        if ( count(array_diff( $post_data['perms'], $all_perms)) )
        {
            return false;
        }
        
        return true;
        
        
        //below was validation that checks whenever proper relative things are checked
        //if we selected to allow view specified item type we do not want to checkboxes with items of this type should be selected
        //but when user have selected that he can edit item with id for example 1 of type 3 and we adding him to group that have access to edit all with type 3
        //we now have breake validation rules in thoery so leave it, JS will only prepare good data, no risk
    }
    
    public function validate_item($post_data, Item_type_vo $item_type_vo, Item_vo $old_item_vo = null)
    {       
        $needed_keys = Item_vo::validation_keys();
        
        //check that post data contain only needed keys nothing more
        if ( !$this->_array_keys_match($needed_keys, $post_data) ) 
        {
            return false;
        }
        $this->CI->form_validation->set_data($post_data);

        if (!empty($post_data['id'])) //editing
        {
            $this->CI->form_validation->set_rules('id', null, 'required|is_natural_no_zero');
        }

        $this->CI->form_validation->set_rules('type[id]', null, 'required|is_natural_no_zero');
        $this->CI->form_validation->set_rules('name', null, 'required|min_length[3]|max_length[38]');
        $this->CI->form_validation->set_rules('icon', null, 'required');

        
        if ( !$this->CI->form_validation->run() )
        {
            return false;
        }
              

        $this->CI->load->model('items_model');  
        if (!empty($post_data['id'])) //editing
        {
            //check that type id matches old item type id
            if ($post_data['type']['id'] != $old_item_vo->type->id )
            {
                return false;
            }
        }
      
        //no field types do not check anything more
        if (!count($item_type_vo->field_types) )
        {
            return true;
        }

        //check that number of send fields are equal to needed fields
        if (count($item_type_vo->field_types) != count($post_data['fields']) )
        {
            return false;
        }
        
        foreach ($item_type_vo->field_types as $key => $field_type_vo)
        {
            if (!self::validate_field($post_data['fields'][$key], $field_type_vo->type))
            {
                return false;
            }
            
            if (!empty($post_data['id'])) //editing mode nod adding
            {
                //check that we have returned fields with proper ids for that that was earlier exists and not
                if ($old_item_vo->fields[$key]->id != $post_data['fields'][$key]['id'])
                {
                    return false;
                }
            }
        }

        return true;
    }
    
    
    
    public function validate_item_types_orders($post_data)
    {       
        $needed_keys = array('ids', 'orders');
   
        //check that post data contain only needed keys nothing more
        if ( !$this->_array_keys_match($needed_keys, $post_data) ) 
        {
            return false;
        }
        
        if (!is_array($post_data['ids']) || !is_array($post_data['orders']) || (count($post_data['ids']) != count($post_data['orders'])) )
        {
            return false;
        }
        
        foreach ($post_data['ids'] as $id)
        {
            if (!self::validate_id($id))
            {
                return false;
            }
        }
        
        foreach ($post_data['orders'] as $order)
        {
            if (!self::validate_id($order)) //we can use the same validator
            {
                return false;
            }
        }


        return true;
    }
    
    public function validate_item_type($post_data)
    {       
        $needed_keys = Item_type_vo::validation_keys();
   
        //check that post data contain only needed keys nothing more
        if ( !$this->_array_keys_match($needed_keys, $post_data) ) 
        {
            return false;
        }
        
        $this->CI->form_validation->set_data($post_data);
//            die;
        if (!empty($post_data['id'])) //editing
        {
            $this->CI->form_validation->set_rules('id', null, 'required|is_natural_no_zero');
        }

        $this->CI->form_validation->set_rules('name', null, 'required|min_length[3]|max_length[30]');
        $this->CI->form_validation->set_rules('icons', null, 'required');
        $this->CI->form_validation->set_rules('desc1_label', null, 'min_length[3]|max_length[30]');
        $this->CI->form_validation->set_rules('desc1_label_show', null, $this->bool_rule);
        $this->CI->form_validation->set_rules('desc1_type', null, 'required|in_list[short,long]');
        $this->CI->form_validation->set_rules('desc2_label', null, 'min_length[3]|max_length[30]');
        $this->CI->form_validation->set_rules('desc2_label_show', null, $this->bool_rule);
        $this->CI->form_validation->set_rules('desc2_type', null, 'required|in_list[short,long]');
        $this->CI->form_validation->set_rules('desc2_disabled', null, $this->bool_rule);
        $this->CI->form_validation->set_rules('desc_show_how', null, 'required|in_list[separate,asone]');
        
        if ( !$this->CI->form_validation->run() )
        {
            return false;
        }
     
        if (!is_array( $post_data['field_types'] ) )
        {
            return false;
        }
        

            
        $field_ids = array();
        foreach ($post_data['field_types'] as $field_type)
        {
            if (!self::validate_field_type($field_type))
            {
                return false;
            }
            
            if (!empty($field_type['id']))
            {
                $field_ids[] = $field_type['id'];
            }
        }

        //new item but old fields, impossible
        if (count($field_ids) && empty($post_data['id']))
        {
            return false;
        }
        else if (count($field_ids) ) //check that fields with with ids, exists and are matched with item id
        {
            $this->CI->load->model('item_types_model');  
            if ( !$this->CI->item_types_model->fields_exists_for_item($field_ids, $post_data['id']))
            {
               return false;
            }
        }
     
        //event types is used when saving as storage with only ids of selected event types
        ///check it has no duplicated values
        if (!is_array( $post_data['event_types'] ) || count( $post_data['event_types'] ) == 0 )
        {
            return false;
        }
        
        $unique_ids = array();
        foreach ($post_data['event_types'] as $obj)
        {
            if (!isset($obj['id']) || !self::validate_id($obj['id']))
            {
                return false;
            }
            
            $unique_ids[$obj['id']] = true;
        }
    
        //found duplicates
        if (count($unique_ids) != count($post_data['event_types']))
        {
            return false;
        }

        //check that ids of events exists
        $this->CI->load->model('event_types_model');  
        $event_types = $this->CI->event_types_model->get(array_keys($unique_ids));   
        
        $count = 0;
        foreach ($event_types as $event_type_vo)
        {
            if ($event_type_vo)
            {
                $count++;
            }
        }
        //ensure that all event types are found in db
        if (count( $post_data['event_types'] ) != $count )
        {
            return false;
        }

        return true;
    }
    
    
    
    
    public function validate_field_type($post_data)
    {       
        $needed_keys = Field_type_vo::validation_keys();
   
        //check that post data contain only needed keys nothing more
        if ( !$this->_array_keys_match($needed_keys, $post_data) ) 
        {
            return false;
        }

        $this->CI->form_validation->reset_validation();
         
        $this->CI->form_validation->set_data($post_data);

        
        if (!empty($post_data['id'])) //editing
        {
            $this->CI->form_validation->set_rules('id', null, 'required|is_natural_no_zero');
        }
        
        
        $this->CI->form_validation->set_rules('type', null, 'required|in_list[text,image,link]');
        $this->CI->form_validation->set_rules('label', null, 'required|min_length[3]|max_length[20]');
        $this->CI->form_validation->set_rules('label_show', null, $this->bool_rule);
        $this->CI->form_validation->set_rules('allow_filtering', null, $this->bool_rule);
        $this->CI->form_validation->set_rules('position', null, 'required|in_list[a,b,c,d]');
        
        if ( $post_data['type'] == 'image' )
        {
            $this->CI->form_validation->set_rules('size', null, 'required|in_list[s,m,l]');
        }
        
    
        if ( !$this->CI->form_validation->run() )
        {
//            var_dump($this->CI->form_validation->error_array());
            return false;
        }
        
        
        if ( $post_data['type'] != 'text' && $post_data['allow_filtering'] == '1' )
        {
            return false;
        }
        
        if ( $post_data['type'] != 'image' && $post_data['size'] !== '' )
        {
            return false;
        }

        return true;
    }
    
    public function validate_field($post_data, $type)
    {       
        $needed_keys = Field_vo::validation_keys();
   
//        //check that post data contain only needed keys nothing more
        if ( !$this->_array_keys_match($needed_keys, $post_data) ) 
        {
            return false;
        }

        $this->CI->form_validation->reset_validation();
        $this->CI->form_validation->set_data($post_data);

        $val_required = false;
        if (!empty($post_data['id'])) //editing
        {
            $this->CI->form_validation->set_rules('id', null, 'required|is_natural_no_zero');
        }

        //validate image if its provided that it does not contain relativenes
        if ( $type == 'image' && !empty($post_data['value']) )
        {
            $this->CI->form_validation->set_rules('value', null, $this->filename_rule);
        }
        
        if ( $val_required && !$this->CI->form_validation->run() )
        {
            return false;
        }
        
        
        if ( !is_array($post_data['options']) )
        {
            return false;
        }
        
        
        if ( $type == 'link' && count($post_data['options']) != 1 )
        {
            return false;
        }
        else if ($type != 'link' && count($post_data['options']) != 0) //only 
        {
            return false;
        }

        return true;
    }
    
    
    public function validate_event_type($post_data)
    {       
        $needed_keys = Event_type_vo::validation_keys();
   
        //check that post data contain only needed keys nothing more
        if ( !$this->_array_keys_match($needed_keys, $post_data) ) 
        {
            return false;
        }
        
        $this->CI->form_validation->set_data($post_data);
        
        if (!empty($post_data['id'])) //editing
        {
            $this->CI->form_validation->set_rules('id', null, 'required|is_natural_no_zero');
        }

        $this->CI->form_validation->set_rules('name', null, 'required|min_length[3]|max_length[30]');
        $this->CI->form_validation->set_rules('icon', null, 'required');
        
        if ( !$this->CI->form_validation->run() )
        {
            return false;
        }

        return true;
    }
    
    
    
    public function validate_user_edit($post_data)
    {       
        $needed_keys = User_edit_vo::validation_keys();
     
        //check that post data contain only needed keys nothing more
        if ( !$this->_array_keys_match($needed_keys, $post_data) ) 
        {
            return false;
        }

        $this->CI->form_validation->set_data($post_data);
        $username_rule = $this->username_rule;
        
        if (!empty($post_data['id'])) //editing user
        {
            $this->CI->form_validation->set_rules('id', null, 'required|is_natural_no_zero');
            
            //if we editing admin we cannot change its login
            if ($post_data['id'] == '1')
            {
                $username_rule .= '|in_list[admin]';
            }
        }
         
        $this->CI->form_validation->set_rules('username', null, $username_rule);
        $this->CI->form_validation->set_rules('pass_change', null, $this->bool_rule);
        
        if ( empty($post_data['id']) || (!empty($post_data['id']) && $post_data['pass_change'] === '1') )
        {
            $this->CI->form_validation->set_rules('password', null, $this->password_rule);
        }
        
        if ( !$this->CI->form_validation->run() )
        {
            return false;
        }

        
        return true;
    }
    
    public function validate_pass_change(Pass_change_vo $vo)
    {
        $this->CI->form_validation->set_data($vo->to_array());
        $this->CI->form_validation->set_rules('old_password', null, $this->password_rule);
        $this->CI->form_validation->set_rules('new_password1', null, $this->password_rule);
        $this->CI->form_validation->set_rules('new_password2', null, $this->password_rule);
        
        if ( !$this->CI->form_validation->run() )
        {
            return false;
        }

        if ($vo->new_password1 !== $vo->new_password2)
            return false;

        return true;
    }
    
    
    private function _has_value_starts_with($array, $startString)
    {
        foreach ($array as $value)
        {
            if ($this->_starts_with($value, $startString))
            {
                return true;
            }
        }

        return false;
    }
    
    private function _starts_with ($string, $startString) 
    { 
        $len = strlen($startString); 
        return (substr($string, 0, $len) === $startString); 
    } 

    //check that post data contain only needed keys
    private function _array_keys_match(array $keys, $arr) 
    {        
        return (count($keys) == count( array_keys((array)$arr) ) && !array_diff( $keys, array_keys((array)$arr) ));
    }
    

}
