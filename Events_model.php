<?php
/**
 * @author flexphperia.net
 */
class Events_model extends CI_Model 
{

    public function __construct()
    {
        parent::__construct();
    }
    
    
    public function get_for_month($year, $month, array $item_ids)
    {
        $events = array();
        
        //no items so no results
        if (empty($item_ids))
        {
            return $events;
        }
        
        $date = new DateTime();
        $from = $date->setDate($year, $month, 1)->format('Y-m-d'); 
        $to = $date->setDate($year, $month, $date->format('t'))->format('Y-m-d'); 


        $this->db->from('yc_calendar_events');

        //better for performance and indexing
        $this->db->where("date BETWEEN '$from' AND '$to'", null, false);
        $this->db->where_in('item_id', $item_ids);
        $this->db->order_by('date', 'ASC');
        $this->db->order_by('order', 'ASC');
        
        $query = $this->db->get();
        
        if ($query->num_rows() == 0)
        {
            return $events;
        }
        
        
        $needed_item_ids = array();
        $needed_event_ids = array();
        foreach ($query->result_array() as $row)
        {
            $php_date = strtotime( $row['date'] );
            
            $data = array(
                'id' => $row['id'],
                'order' => $row['order'],
                'year' => date('Y', $php_date),
                'month' => date('n', $php_date),
                'day' => date('j', $php_date),
                'is_main' => $row['is_main'],
                'description1' => $row['description1'],
                'description2' => $row['description2'],
                'item' => $row['item_id'], //store temporarry
                'type' => array( 'id' => $row['event_type_id']) //store temporarry
            );
            
            $events[] = new Cal_event_vo($data);
            
            $needed_item_ids[] = (int)$row['item_id'];
            $needed_event_ids[] = (int)$row['event_type_id'];
        }
        
        $needed_item_ids = array_unique($needed_item_ids);
        $needed_event_ids = array_unique($needed_event_ids);
        
        $this->load->model('event_types_model');
        $event_types = $this->event_types_model->get($needed_event_ids);
        
        $this->load->model('items_model');
        $items = $this->items_model->get($needed_item_ids);
        
//        $events = array();
        foreach ($events as $event_vo)
        {
            $event_vo->type = $event_types[$event_vo->type->id];
            $event_vo->item = $items[$event_vo->item];
        }

        return $events;
    }
    
    
    public function get_for_day($year, $month, $day, $item_id)
    {
        $res = $this->get_for_days( array(
                        array(
                            'year' => $year, 
                            'month' => $month, 
                            'day' => $day, 
                            'item_id' => $item_id
                        )
                    )
                );
        
        return !empty($res) ? $res[0]['events'] : array();
    }
    
    /**
     * Returns array of cal events $a[item_id][year][month][day] = events
     * 
     * @param type $cells
     * @return \Cal_event_vo
     */
    public function get_for_days($cells)
    {
        $dates = array();
        foreach ($cells as $cell)
        {
            $date = new DateTime();
            $date->setDate($cell['year'], $cell['month'], $cell['day']);   
            
            //collect dates needed by item id
            $dates[$cell['item_id']][] = $date->format('Y-m-d');
        }
        
//        var_dump($dates);
        $events = array();
        $needed_event_ids = array();
        //make it for each item id, collect all dates last order
        foreach (array_keys($dates) as $item_id)
        {
            $this->db->from('yc_calendar_events');
            $this->db->where('item_id', $item_id);
            $this->db->where_in('date', $dates[$item_id]);
            $this->db->order_by('order', 'ASC');
            
            
            
            $query = $this->db->get(); 
//            var_dump($dates);
            
            $dates[$item_id] = array(); //reset storage
            
            foreach ($query->result_array() as $row)
            {
                $php_date = strtotime( $row['date'] );

                $data = array(
                    'id' => $row['id'],
                    'order' => $row['order'],
                    'year' => date('Y', $php_date),
                    'month' => date('n', $php_date),
                    'day' => date('j', $php_date),
                    'is_main' => $row['is_main'],
                    'description1' => $row['description1'],
                    'description2' => $row['description2'],
                    'item' => $row['item_id'], //store temporarry
                    'type' => array('id' => $row['event_type_id']) //store temporarry
                );
                
                $ev = new Cal_event_vo($data);
                $events[] = $ev;
                $needed_event_ids[] = (int)$row['event_type_id'];
                
                //store in hash table
                $dates[$item_id][$ev->year][$ev->month][$ev->day][] = $ev;
            }
        }
        
        $needed_event_ids = array_unique($needed_event_ids);
        
        $this->load->model('event_types_model');
        $event_types = $this->event_types_model->get($needed_event_ids);
        
        $this->load->model('items_model');
        $items = $this->items_model->get(array_keys($dates));
        
        foreach ($events as $event_vo)
        {
            $event_vo->type = $event_types[$event_vo->type->id];
            $event_vo->item = $items[$event_vo->item];
        }

        //LOL
        $return = array();
        foreach ($dates as $item_id => $year_array)
        {
            foreach ($year_array as $year => $month_array)
            {
                foreach ($month_array as $month => $day_array)
                {
                    foreach ($day_array as $day => $events_array)
                    {
                        $return[] = array(
                            'item_id' => $item_id,
                            'year' => $year,
                            'month' => $month,
                            'day' => $day,
                            'events' => $events_array
                        );
                    }
                }
            }
        }
        
        

        return $return;
    }
    

    public function add(array $cells, array $events)
    {
        $has_main = false;
        foreach ($events as $event_vo)
        {
            if ($event_vo->is_main)
            {
                $has_main = true;
                break;
            }
        }

        $dates = array();
        foreach ($cells as $cell)
        {
            $date = new DateTime();
            $date->setDate($cell['year'], $cell['month'], $cell['day']);   
            
            //create default order
            $dates[$cell['item_id']][$date->format('Y-m-d')] = array(
                'last_order' => 0
            );
        }
        
        
        //make it for each item id, collect all dates last order
        foreach (array_keys($dates) as $item_id)
        {
            $this->db->select('DATE_FORMAT(`date`,"%Y-%m-%d") as date, item_id, MAX(`order`) as last_order');
            $this->db->where('item_id', $item_id);
            $this->db->where_in('date', array_keys($dates[$item_id]));
            $this->db->from('yc_calendar_events');
            $this->db->group_by("date");
            
            $query = $this->db->get(); 
            
            foreach ($query->result_array() as $row)
            {
                $dates[$item_id][$row['date']]['last_order'] = (int)$row['last_order'];
            }
        }
//        Console::log($dates);

        $add_data = array();
        foreach ($dates as $item_id => $dat)
        {
            foreach ($dat as $date => $d)
            {
                $i = 1;
                foreach ($events as $event_vo)
                {
                    $arr = $event_vo->to_storage_array();
                    
                    $arr['date'] = $date;
                    $arr['item_id'] = $item_id;
                    $arr['event_type_id'] = $event_vo->type->id;
                    $arr['order']  = $d['last_order'] + $i;
                    $arr['is_main'] = $d['last_order'] == 0 && $i == 1 && !$has_main ? 1 : $event_vo->is_main; //if no previoues events on this day set is main event
                    
     
                    $add_data[] = $arr;

                    $i++;
                }
            }
        }
        
        //turn off all main events, main event will be set later
        if ($has_main)
        {
//                Console::log($dates);
            foreach (array_keys($dates) as $item_id)
            {
//                Console::log($dates[$item_id]);
             
                $this->db->where('item_id', $item_id);
                $this->db->where('is_main', 1);
                $this->db->where_in('date', array_keys($dates[$item_id]));
                $this->db->update('yc_calendar_events', array('is_main' => 0));

            }
//            Console::log($dat);


        }
        
        //now not earlier
        $this->db->insert_batch('yc_calendar_events', $add_data);
    }
    
    
    public function save_for_day($year, $month, $day, $item_id, array $events, array $old_events)
    {
        $events_to_update = array();
        $events_to_add = array();
        
        $order = 1;
        foreach ($events as $event_vo)
        {
            if ($event_vo->id) //updating
            {
                $events_to_update[] = $event_vo;
            }
            else{
                $events_to_add[] = $event_vo;
            }
            
            $event_vo->order = $order;
            $order++;
        }
        
        $event_ids_to_delete = array_diff(array_column($old_events, 'id'), array_column($events_to_update, 'id'));
        
        //delete whats needed to delete
        if (count($event_ids_to_delete))
        {
            $this->db->where_in('id', $event_ids_to_delete);
            $this->db->delete('yc_calendar_events');
        }
        
        if (count($events_to_add))
        {
            $data = array();
            foreach ($events_to_add as $event_vo)
            {
                $date = new DateTime();
                $date->setDate($event_vo->year, $event_vo->month, $event_vo->day);    
                
                $arr = $event_vo->to_storage_array();

                $arr['event_type_id'] = $event_vo->type->id;
                $arr['item_id'] = $item_id;
                $arr['date'] = $date->format('Y-m-d');
 
                $data[] = $arr;
            }
            $this->db->insert_batch('yc_calendar_events', $data);
        }
        
        
        if (count($events_to_update))
        {
            $data = array();
            foreach ($events_to_update as $event_vo)
            {
                $date = new DateTime();
                $date->setDate($event_vo->year, $event_vo->month, $event_vo->day);           

                $arr = $event_vo->to_storage_array();
                
                $arr['id'] = $event_vo->id;
                $arr['event_type_id'] = $event_vo->type->id; //can be changed

        
                $data[] = $arr;
            }
            
            $this->db->update_batch('yc_calendar_events', $data, 'id');

        }

    }
    
    /**
     * Find where event type is used as main event, and if this day has other events than it sets as main first event from day.
     * If day dont have other events than its do nothing
     */
    public function replace_day_main_event($event_type_id)
    {
        //LIMIT is there to sort subquery before grouping (Maria db requires limit)
        $sql = 'SELECT id FROM
                (SELECT id, item_id, date
                    FROM yc_calendar_events
                    WHERE DATE IN(
                        SELECT date FROM yc_calendar_events
                        WHERE event_type_id = '.$this->db->escape($event_type_id).'  
                        AND is_main = 1
                    )
                    AND is_main = 0
                    AND event_type_id <> '.$this->db->escape($event_type_id).'
                    ORDER BY `order` ASC
                    LIMIT 99999999
                ) AS sub
                GROUP BY item_id, date';

        $query = $this->db->query($sql);
        
        $data = array();
        foreach ($query->result() as $row)
        {
            $data[] = array(
                'id' => $row->id,
                'is_main' => 1
            );
        }
        
        if (count($data))
        {
            $this->db->update_batch('yc_calendar_events', $data, 'id');
        }
    }
    
    
    public function delete_all(array $cells)
    {
        
        foreach ($cells as $cell)
        {
            $date = new DateTime();
            $date->setDate($cell['year'], $cell['month'], $cell['day']);           

            $this->db->where('date', $date->format('Y-m-d'));
            $this->db->where('item_id', $cell['item_id']);
//            $this->db->from('a');
            
//            var_dump($this->db->get_compiled_select());
            $this->db->delete('yc_calendar_events');
        }

    }
    
    
    /**
     * Deletes all events with specified type fo item types
     * 
     * @param type $event_type_id
     * @param type $item_type_id
     * @return type
     */
//    public function delete_type_for_item_type(array $event_type_ids, $item_type_id)
//    {
//        $sql ='DELETE yc_calendar_events
//                FROM yc_calendar_events
//                LEFT JOIN yc_items ON yc_calendar_events.item_id = yc_items.id
//                LEFT JOIN yc_item_types ON yc_items.item_type_id = yc_item_types.id
//                WHERE yc_item_types.id = '.$this->db->escape($item_type_id) .
//                ' AND yc_calendar_events.event_type_id IN ('.join(',',$event_type_ids). ')';
//        
////        Console::log($sql);
//        $query = $this->db->query($sql);
//    }
    
    
    
    
    
    
    

    
}
