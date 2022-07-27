<?php
/**
 * @author flexphperia.net
 */
class Item_types_model extends MY_Model 
{
    
    public function __construct()
    {
        parent::__construct();
        
        $this->_cache_key .= 'item_types';
    }
    
    public function get(array $ids = null, $order_by_name = false)
    {
        $this->load_cache();
        
        $order_by = $order_by_name ? 'name' : 'order';
        
        if (!empty($this->_storage))
        {
            return $this->_return_from_storage($ids, $order_by); 
        }
        
        //prepare storage
        $this->_storage = array('name' => [], 'order' => []);
        
        //first query sorted by order field
        $this->db->order_by('order', 'ASC');
        $query = $this->db->get('yc_item_types');

//        $result = array();
        if ($query->num_rows() != 0)
        {
            $found_item_type_ids = array();
            foreach ($query->result_array() as $row)
            {
                $item_type_vo = new Item_type_vo($row);

                $this->_storage['order'][$item_type_vo->id] = $item_type_vo;
                $found_item_type_ids[] = $item_type_vo->id; //store what is found to iterate later
            }

            //fill field 
            $this->db->order_by('order', 'ASC');
            $query = $this->db->get('yc_item_type_field_types');

            foreach ($query->result_array() as $row)
            {
                $item_type_id = $row['item_type_id'];
                unset($row['item_type_id']); //unset coz its not needed in vo
                unset($row['order']); //unset coz its not needed in vo

                $field_type_vo = new Field_type_vo($row);
                $this->_storage['order'][$item_type_id]->field_types[] = $field_type_vo;
            }

            //fill event types
//            $this->db->order_by('name', 'ASC');
            $this->db->join('yc_event_types', 'yc_item_type_event_types.event_type_id = yc_event_types.id', 'left');
            $this->db->order_by('yc_event_types.name', 'ASC');
            $query = $this->db->get('yc_item_type_event_types');

            //find what event types are needed
            $needed_event_types = array();
            foreach ($query->result_array() as $row)
            {
                //store temporary in item type object what id of event type it needs
                $this->_storage['order'][$row['item_type_id']]->event_types[] = $row['event_type_id'];
                $needed_event_types[$row['event_type_id']] = true; //collect unique event type event ids
            }
            
            $this->load->model('event_types_model');
            $item_type_events = $this->event_types_model->get(array_keys($needed_event_types));
            
            //loop thru found  item type ids to fill its data
            foreach ($found_item_type_ids as $item_type_id)
            {
                foreach ($this->_storage['order'][$item_type_id]->event_types as $key => $event_type_id)
                {
                    $this->_storage['order'][$item_type_id]->event_types[$key] = $item_type_events[(int)$event_type_id];
                }
            }

            //query by name order
            $this->db->select('id');
            $this->db->order_by('name', 'ASC');
            $query = $this->db->get('yc_item_types');
            
            foreach ($query->result_array() as $row)
            {
                $item_type_vo = $this->_storage['order'][$row['id']];
                $this->_storage['name'][$item_type_vo->id] = $item_type_vo;
            }
        }
        
        $this->save_cache();
        
        return $this->_return_from_storage($ids, $order_by); 
    }

    public function update_orders(array $ids, array $orders)
    {
        
        $data = array();
        
        foreach ($ids as $key => $id)
        {
            $data[] = array(
                'id' => $id,
                'order'=> $orders[$key] //orders array len is the same as ids
            );
        }
        
        $this->db->update_batch('yc_item_types', $data, 'id');
        
         $this->clear_all_cache(); 
    }
    
    /**
     * Returns ids of item types that uses specified event type as one and only event allowed
     * 
     * @param int $id
     * @return type
     */
    public function get_that_only_uses_event_type(int $id)
    {            
        $sql ='SELECT item_type_id as id
                FROM 
                (SELECT item_type_id, event_type_id 
                    FROM yc_item_type_event_types
                    GROUP BY `item_type_id`
                    HAVING COUNT(event_type_id) = 1) as f
                WHERE event_type_id = '.$this->db->escape($id);
        
        $query = $this->db->query($sql);
        
        $ids = array();
        foreach ($query->result() as $row)
        {
            $ids[] = $row->id;
        }
        
        
        return $ids;
    }
    
    
    public function get_last_order()
    {            
        $sql ='SELECT IFNULL(MAX(`order`), 0) as last FROM yc_item_types';
        
        $query = $this->db->query($sql);
        
        return (int)$query->row()->last;
    }
    
    
    
    public function fields_exists_for_item($field_ids, $item_id)
    {            
        $this->db->where_in('id', $field_ids);
        $this->db->where('item_type_id', $item_id);
        $query = $this->db->get('yc_item_type_field_types');
        
        
        return  $query->num_rows() == count($field_ids);
    }
    
    public function delete(int $id)
    {
        $item_type_vo = $this->get(array($id))[$id];
        
        foreach ($item_type_vo->field_types as $field_type_vo)
        {
            if ($field_type_vo->type == 'image')
            {
                $this->items_model->delete_field_images($field_type_vo->id); //delete uploaded images for image fields
            }
        }

        //make it first, inno db will remove items 
        $this->items_model->delete_permissions_for_item_types($id);

        $this->db->where('id', $id);
        $this->db->delete('yc_item_types');

        $this->load->model('perms_model');
        $this->perms_model->delete_item_type_perms($id);
  
        $this->clear_all_cache(); 
        return true;
    }
    
    /**
     * Saves changes, too many logic
     * 
     * @param Item_type_vo $item_type_vo
     * @param Item_type_vo $curr_item_type_vo - used only when updating
     * @return type
     */
    public function save(Item_type_vo $item_type_vo, Item_type_vo $curr_item_type_vo = null)
    {

        $id = null;
        
        if ($item_type_vo->id) //updating
        {
//            var_dump($item_type_vo->id);
            $id = $item_type_vo->id;
            
            $this->db->where('id', $item_type_vo->id);
            $this->db->update('yc_item_types', $item_type_vo->to_storage_array());
            
            $old_icons = explode(',', $curr_item_type_vo->icons);
            $new_icons = explode(',', $item_type_vo->icons);
            
            $deleted_icons = array_values(array_diff($old_icons, $new_icons));

            //replace deleted icons in items 
            if (count($deleted_icons))
            {
                $this->load->model('items_model');
                $this->items_model->replace_icons($deleted_icons, $new_icons[0], $item_type_vo->id);
            }  
            
            //check what events are missing in new item type
            $had_event_types = array();
            foreach ($curr_item_type_vo->event_types as $event_type_vo)
            {
                $had_event_types[] = $event_type_vo->id;
            }
            
            //check what events are missing in new item type
            $have_event_types = array();
            foreach ($item_type_vo->event_types as $event_type_vo)
            {
                $have_event_types[] = $event_type_vo->id;
            }
            
            //find what to delete and what to add
            $events_to_delete = array_diff($had_event_types, $have_event_types);
            $events_to_add = array_diff($have_event_types, $had_event_types);
            
            if (count($events_to_delete))
            {
                $this->db->where_in('event_type_id', $events_to_delete);
                $this->db->where('item_type_id', $id);
                $this->db->delete('yc_item_type_event_types');
                
                //deleting all calendar events that uses this type in item NOT USED
//                $this->load->model('events_model');
//                $this->events_model->delete_type_for_item_type($events_to_delete,$id);
            }

            if (count($events_to_add))
            {
                $data = array();
                foreach ($events_to_add as $event_to_add)
                {
                    $data[] = array(
                        'event_type_id' => $event_to_add,
                        'item_type_id' => $id
                    );
                }
                $this->db->insert_batch('yc_item_type_event_types', $data);
            }
            
            //update order of fields and what to 
            $had_field_types = array();
            foreach ($curr_item_type_vo->field_types as $field_type_vo)
            {
                $had_field_types[] = $field_type_vo->id;
            }
            
            //check what fields are missing in new item type
            $have_field_types = array();
            $new_field_types = array();
            $update_field_types = array();
            
            $order = 1;
            foreach ($item_type_vo->field_types as $field_type_vo)
            {
                $field_type_vo->order = $order;
                
                if ($field_type_vo->id !=0)
                {
                    $have_field_types[] = $field_type_vo->id;
                    $update_field_types[] = $field_type_vo;
                }
                else
                {
                    $new_field_types[] = $field_type_vo;
                }
                
                $order++;
            }
            
            //find what to delete and what to add
            $fields_to_delete = array_diff($had_field_types, $have_field_types);
            
            if (count($fields_to_delete))
            {
                foreach ($fields_to_delete as $field_id)
                {
                    //search for field type vo
                    $idx = array_search($field_id, array_column($curr_item_type_vo->field_types, 'id'));
                    $field_type_vo = $curr_item_type_vo->field_types[$idx];             

                    if ($field_type_vo->type == 'image')
                    {
                        $this->items_model->delete_field_images($field_id); //delete uploaded images before deleting field 
                    }
                }
                
                $this->db->where_in('id', $fields_to_delete);
                $this->db->where('item_type_id', $id);
                $this->db->delete('yc_item_type_field_types');
            }
            

            //update order or someting in field typeses
            if (count($update_field_types))
            {
                $data = array();
                foreach ($update_field_types as $field_type_vo)
                {
                    $a = $field_type_vo->to_storage_array();
                    unset($a['type']); //never allow updating type
                    $data[] = $a;
                }
                $this->db->update_batch('yc_item_type_field_types', $data, 'id');
            }
            
            //add new fields
            if (count($new_field_types))
            {
                $data = array();
                foreach ($new_field_types as $field_type_vo)
                {
                    $a = $field_type_vo->to_storage_array();
                    $a['item_type_id'] = $id;
                    $data[] = $a;
                }
                $this->db->insert_batch('yc_item_type_field_types', $data);
            }
            
        }
        else //new adding
        {
            $data = $item_type_vo->to_storage_array();
            $data['order'] = $this->get_last_order() + 1;
            
            $this->db->insert('yc_item_types', $data);
            $id = $this->db->insert_id();
            
            //add event types
            $data = array();
            foreach ($item_type_vo->event_types as $event_type_vo)
            {
                $data[] = array(
                    'item_type_id' => $id,
                    'event_type_id' => $event_type_vo->id
                );

            }
            $this->db->insert_batch('yc_item_type_event_types', $data);
            
            
            if (count($item_type_vo->field_types))
            {
                $data = array();
                $i= 0;
                foreach ($item_type_vo->field_types as $field_type_vo)
                {
                    $a = $field_type_vo->to_storage_array();
                    $a['item_type_id'] = $id;
                    $a['order'] = ++$i;
                    $data[] = $a;
                }
                $this->db->insert_batch('yc_item_type_field_types', $data);
            }
            
            $this->load->model('perms_model');
            $this->perms_model->create_item_type_perms($id);


        }
        
        $this->clear_all_cache(); 

        return $id;
    }

    
    protected function _return_from_storage($ids = null, $order_by = 'order')
    {
        $ret = array();
        
        if ($ids === null)
        {
            return $this->_storage[$order_by];
        }
        else
        {
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
    

    
    
    
}
