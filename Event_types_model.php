<?php
/**
 * @author flexphperia.net
 */
class Event_types_model extends MY_Model 
{

    
    public function __construct()
    {
        parent::__construct();
        
        $this->_cache_key .= 'event_types';
    }
    
    /**
     * Returns Event types array or specified item
     * Objects are always returned as references but array itself not. So changing objects will change source object but array adding etc not
     * 
     */    
    public function get(array $ids = null)
    {
        $this->load_cache();
        
        if ( empty($this->_storage) )
        {
            $this->db->order_by('name', 'ASC');
            $query = $this->db->get('yc_event_types');
            
            foreach ($query->result_array() as $row)
            {
                $event_type_vo = new Event_type_vo($row);

                $this->_storage[$event_type_vo->id] = $event_type_vo;
            }
            
            $this->save_cache();
        }
        
        return $this->_return_from_storage($ids); 
 
    }
    
    public function save(Event_type_vo $event_type_vo)
    {
        
        $id = null;
        
        if ($event_type_vo->id) //updating
        {
            $this->db->where('id', $event_type_vo->id);
            $this->db->update('yc_event_types', $event_type_vo->to_storage_array());
            
            $id = $event_type_vo->id;
        }
        else
        {
            $this->db->insert('yc_event_types', $event_type_vo->to_storage_array());
            
            $id = $this->db->insert_id();
        }

        $this->clear_all_cache();
        return $id;
    }
    
    public function delete(int $id)
    {
        
        $this->load->model('item_types_model');
        $this->load->model('events_model');
        
        $item_type_ids = $this->item_types_model->get_that_only_uses_event_type($id);
        
        foreach ($item_type_ids as $item_type_id)
        {
            //delete item types that have only one event type that uses
            $this->item_types_model->delete($item_type_id);
        }
        
        //Find on which day event type is used as main event, and if this day has other events than set as main first event from day.
        $this->events_model->replace_day_main_event($id);
        
        
        $this->db->where('id', $id);
        $this->db->delete('yc_event_types');

        $this->clear_all_cache();
        return true;
    }
    

    
    

    
    
    
    

    
}
