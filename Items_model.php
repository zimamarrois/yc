<?php

/**
 * @author flexphperia.net
 */
class Items_model extends MY_Model 
{
    public function __construct()
    {
        parent::__construct();
        
        //correct kache key
        $this->_cache_key .= 'items';
    }

    public function get($ids = null, $order_by_type_order = false)
    {
        $this->load_cache();
        
        $order_by = !$order_by_type_order ? 'name' : 'type_order';
        
        if (!empty($this->_storage))
        {
            
            return $this->_return_from_storage($ids, $order_by); 
        }
        
        $this->db->select('yc_items.id, yc_items.item_type_id, yc_items.name, yc_items.icon');
        $this->db->order_by('yc_items.name', 'ASC');
        $query = $this->db->get('yc_items');
        
        //prepare storage
        $this->_storage = array('name' => [], 'type_order' => []);

//        $result = array();
        if ($query->num_rows() != 0)
        {
            $needed_type_ids = array();
//            $ids_found = array();
            foreach ($query->result_array() as $row)
            {
                $type_id = $row['item_type_id'];

                unset($row['item_type_id']); //prevent to pass into constructor

                $item_vo = new Item_vo($row);
                $item_vo->type = $type_id; //store temporary

                $this->_storage['name'][$item_vo->id] = $item_vo;
                $needed_type_ids[] = $type_id;
//                $ids_found[] = $item_vo->id;
            }

            $this->load->model('item_types_model');
            $item_vos = $this->item_types_model->get($needed_type_ids);

            foreach ( $this->_storage['name'] as $item_vo)
            {
                $item_vo->type = $item_vos[$item_vo->type];

                //initialize in the same order as in field types
                foreach ($item_vo->type->field_types as $field_type_vo)
                {
                    $f = new Field_vo();
                    $f->type = $field_type_vo; //do not do this by cosntructor
                    //to preserve order
                    $item_vo->fields[$field_type_vo->id] = $f; //add empty field here
                }
            }

            //find item values etc
//            $this->db->where_in('item_id', $ids_found);
            $query = $this->db->get('yc_item_fields');

            foreach ($query->result_array() as $row)
            {
                //replace not empty fields
                $d = array(
                    'id' => $row['id'],
                    'type' =>  $this->_storage['name'][$row['item_id']]->fields[$row['item_type_field_id']]->type, //get type from stred earlier
                    'value' => $row['value'],
                    'options' => $row['options'] ? unserialize($row['options']) : ''
                );
                $field_vo = new Field_vo($d);

                 $this->_storage['name'][$row['item_id']]->fields[$row['item_type_field_id']] = $field_vo;
            }

            foreach ( $this->_storage['name'] as $item_vo)
            {
                $item_vo->fields = array_values($item_vo->fields); //reset array keys
            }
            
            //query by type order sort
            $this->db->select('yc_items.id');
            $this->db->join('yc_item_types', 'yc_items.item_type_id = yc_item_types.id', 'left');
            $this->db->order_by('yc_item_types.order', 'ASC');
            $this->db->order_by('yc_items.name', 'ASC');
            $query = $this->db->get('yc_items');
            
            
            foreach ($query->result_array() as $row)
            {
                $item_vo = $this->_storage['name'][$row['id']];
//                Console::log($item_vo->name);
                $this->_storage['type_order'][$item_vo->id] = $item_vo;
            }
            
        }

        $this->save_cache();
        
        return $this->_return_from_storage($ids, $order_by); 
    }

    public function delete(int $id)
    {
        $this->load->library('image_operator');
        //delete uploaded images
        $item_vo = $this->get(array($id))[$id];
        foreach ($item_vo->fields as $field_vo)
        {
            if ($field_vo->type->type == 'image' && !empty($field_vo->value))
            {
                $this->image_operator->delete_upload($field_vo->value);
            }
        }
        

        $this->db->where('id', $id);
        $this->db->delete('yc_items');
        
        $this->load->model('perms_model');
        $this->perms_model->delete_items_perms([$id]);
        
        $this->clear_all_cache(); 

        return true;
    }

    public function save(Item_vo $item_vo, Item_type_vo $item_type_vo, Item_vo $old_item_vo = null)
    {
        if (!empty($item_vo->id))
        {
            $id = $item_vo->id;

            $this->db->where('id', $item_vo->id);
            $this->db->update('yc_items', $item_vo->to_storage_array());

            $add_data = array();
            $update_data = array();
            foreach ($item_vo->fields as $key => $field_vo)
            {
                if ($field_vo->id) //updating
                {
                    $update_data[] = array(
                        'id' => $field_vo->id,
                        'value' => $field_vo->value,
                        'options' => $field_vo->options ? serialize($field_vo->options) : ''
                    );

                    //image and changed
                    if ($item_type_vo->field_types[$key]->type == 'image' && $old_item_vo->fields[$key]->value != $field_vo->value)
                    {
                        image_move($field_vo->value, $old_item_vo->fields[$key]->value);
                    }
                }
                else if (!empty($field_vo->value)) //adding new
                {
                    $add_data[] = array(
                        'item_id' => $item_vo->id,
                        'item_type_field_id' => $field_vo->type->id,
                        'value' => $field_vo->value,
                        'options' => $field_vo->options ? serialize($field_vo->options) : ''
                    );


                    if ($item_type_vo->field_types[$key]->type == 'image')
                    {
                        image_move($field_vo->value);
                    }
                }
            }

            if (count($update_data))
            {
                $this->db->update_batch('yc_item_fields', $update_data, 'id');
            }

            if (count($add_data))
            {
                $this->db->insert_batch('yc_item_fields', $add_data);
            }
        }
        else
        {
            $data = $item_vo->to_storage_array();

            unset($data['id']);
            $data['item_type_id'] = $item_type_vo->id;

            $this->db->insert('yc_items', $data);
            $id = $this->db->insert_id();

            $add_data = array();
            foreach ($item_vo->fields as $key => $field_vo)
            {
                if (empty($field_vo->value)) //skip empty
                {
                    continue;
                }

                $add_data[] = array(
                    'item_id' => $id,
                    'item_type_field_id' => $field_vo->type->id,
                    'value' => $field_vo->value,
                    'options' => $field_vo->options ? serialize($field_vo->options) : ''
                );

                if ($item_type_vo->field_types[$key]->type == 'image')
                {
                    image_move($field_vo->value);
                }
            }

            if (count($add_data))
            {
                $this->db->insert_batch('yc_item_fields', $add_data);
            }
            
            $this->load->model('perms_model');
            $this->perms_model->create_item_perms($id);

        }

        
        $this->clear_all_cache(); 
        return $id;
    }

    /**
     * Replaces specified icons for all items with specified type
     * 
     * @param array $icons
     * @param type $to_icon
     * @param type $item_type_id
     */
    public function replace_icons(array $icons, $to_icon, $item_type_id)
    {
        $data = array(
            'icon' => $to_icon 
        );
        
        $this->db->where('item_type_id', $item_type_id);
        $this->db->where_in('icon', $icons);
        $this->db->update('yc_items', $data);
        
        $this->clear_all_cache(); 
    }
    
    
    public function get_filter_data(array $item_types, array $allowed_item_ids)
    {       
        $store = array();
        //collect all field types that allows filtering
        foreach ($item_types as $item_type_vo)
        {
            $store[$item_type_vo->id] = array();
            foreach ($item_type_vo->field_types as $field_type_vo)
            {
                if ($field_type_vo->allow_filtering)
                {
                    $values = $this->_get_unique_values_for_field($field_type_vo->id, $allowed_item_ids);
                    if (!empty($values))
                    {
                        $store[$item_type_vo->id][$field_type_vo->id] = $values;
                    }
                }
            }
        }

        $ret = array();
        foreach ($store as $item_type_id => $fields_array)
        {
            if (!empty($fields_array) )
            {
                $item_type_vo = $item_types[$item_type_id];
                $ret[$item_type_id]['item_type'] = $item_type_vo;

                foreach ($fields_array as $field_type_id => $values)
                {
                    $field_type_vo = $item_type_vo->field_types[array_search($field_type_id, array_column($item_type_vo->field_types, 'id'))];
                    
                    $ret[$item_type_id]['fields'][] = array(
                        'field_type' => $field_type_vo,
                        'values' => $values                       
                    );
                }
            }
        }

        return array_values($ret);

    }
    
    /**
     * Finds items that matches criteria and returns sorted by type order
     * 
     * @param type $item_type_id
     * @param type $field_type_id
     * @param type $value
     * @return type
     */
    public function find($item_type_id, $field_type_id = 0, $value = null)
    {
        $value = $value == 'all' ? '' : $value;
//        if (!$item_type_id && $item_type_id)
        
        $this->db->select('yc_items.id');
        $this->db->where('yc_items.item_type_id', $item_type_id);
        
        if ($field_type_id && !empty($value))
        {
            $this->db->join('yc_item_fields', 'yc_items.id = yc_item_fields.item_id', 'left');
            
            $this->db->where('yc_item_fields.item_type_field_id', $field_type_id);
            $this->db->where('yc_item_fields.value', $value);
        }
        
        $query = $this->db->get('yc_items');
        
        if ($query->num_rows() == 0)
        {
            return array();
        }
        
        $ids = array();
        foreach ($query->result() as $row)
        {
            $ids[] = $row->id;
        }

        return $this->get($ids, true);
    }
    
    
    /**
     * Deletes upload images for specified item type field
     * 
     * @param type $item_type_field_id
     */
    public function delete_field_images($item_type_field_id)
    {
        $this->reset_storage();
        
        $this->db->select('value');
        $this->db->where('item_type_field_id', $item_type_field_id);    
        
        $query = $this->db->get('yc_item_fields');

        $this->load->library('image_operator');
        
        foreach ($query->result() as $row)
        {
            if (!empty($row->value))
            {
                $this->image_operator->delete_upload($row->value);
            }
        }
    }
    
    /*
     * Deletes all permissions of items for specified type, used when deleting item type, so items of this type will be deleted automatically
     */
    public function delete_permissions_for_item_types($item_type_id)
    {
        $this->reset_storage();
        $items = $this->get();
        
        $a = array();
        foreach ($items as $item_vo)
        {
            if ($item_vo->type->id == $item_type_id){
                $a[] = $item_vo->id;
            }
        }
        
        $this->load->model('perms_model');
        $this->perms_model->delete_items_perms($a);
    }
    
    
    private function _get_unique_values_for_field($field_type_id, array $allowed_item_ids)
    {
        $a = array();
        
        $this->db->select('DISTINCT(value)');
        $this->db->where('item_type_field_id', $field_type_id);
        $this->db->where_in('item_id', $allowed_item_ids);
        $this->db->order_by('value', 'ASC');
        
        
//        $sql = 'SELECT DISTINCT(value)
//                FROM yc_item_fields
//                WHERE item_type_field_id = '.$this->db->escape($field_type_id).'
//                AND item_id = '.$this->db->escape($field_type_id).'
//                ORDER BY value';

//        $query = $this->db->query($sql);
        
        $query = $this->db->get('yc_item_fields');
        
        foreach ($query->result() as $row)
        {
            if (!empty($row->value))
            {
             $a[] = $row->value;
            }
        }
        
//        var_dump($a);
//        die;
        
        return $a;
    }
    
    
    protected function _return_from_storage($ids = null, $order_by = 'name')
    {
        $ret = array();
        
        if ($ids === null)
        {
            return $this->_storage[$order_by];
        }
        else
        {
            //preserve order from storage
            $what_rem = array_diff(array_keys($this->_storage[$order_by]), $ids);
            $ids_found = array_diff(array_keys($this->_storage[$order_by]), $what_rem);
            $ids_not_found = array_diff($ids, $ids_found);
            
//            var_dump($ids);
            foreach ($ids_found as $id)
            {
                $ret[$id] = $this->_storage[$order_by][$id];
            }    
            
            foreach ($ids_not_found as $id)
            {
                $ret[$id] = false;
            }    
        }
        
        return $ret;
    }
 
    
//    
//    public function check_type_id_matches(int $item_id, int $item_type_id)
//    {
//        $this->db->where('id', $item_id);
//        $this->db->where('item_type_id', $item_type_id);
//        $query = $this->db->get('yc_items');
//
//        return $query->num_rows() == 1;
//    }

}
