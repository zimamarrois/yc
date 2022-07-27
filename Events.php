<?php

/**
 * Main controller
 * 
 * @author flexphperia.net
 */
class Events extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        
        $this->load->model('events_model');
    }

    /**
     * 
     * @param type $year
     * @param type $month
     * @param type $day
     * @param type $item_id
     * @return type
     */
    public function get_for_day()
    {
        if (!$this->validator->validate_date_item_vars($this->input->post()))
        {
             return $this->output->json_wrong_params();
            //wrong data
        }
        
        if (!can_item_events_edit($this->input->post('item_id')))
        {
            return $this->output->json_forbidden();
        }
        
        $this->load->model('items_model');
        $item_vo = $this->items_model->get(array($this->input->post('item_id')))[$this->input->post('item_id')];

        if (!$item_vo)
        {
            //not found
            return $this->output->json_not_found();
        }
        
        $events = $this->events_model->get_for_day(
                    $this->input->post('year'), 
                    $this->input->post('month'), 
                    $this->input->post('day'), 
                    $this->input->post('item_id')
                );
        

        $ev = new Cal_event_vo(array(
            
            'is_main' => false,
            'type' => array(),
            'item' => $item_vo
        ));
       
        
        return $this->output->json_response(
                array(
                    'events' => $events,
                    'item_type' => $item_vo->type,
                    'event_template' => get_event($ev, true)
                )
                ); //return okay response, changed
    }
    
    
    //used when adding events for a day, returns item type and event template
    public function get_item_type()
    {
        //passed first item
        if (!can_item_events_edit($this->input->post('id'))) 
        {
            return $this->output->json_forbidden();
        }
       
        $this->load->model('items_model');
        $item_vo = $this->items_model->get(array($this->input->post('id')))[$this->input->post('id')];

        if (!$item_vo)
        {
            //not found
            return $this->output->json_not_found();
        }

        $ev = new Cal_event_vo(array(
            'is_main' => false,
            'type' => array(),
            'item' => $item_vo
        ));
       
        return $this->output->json_response(
                array(
                    'item_type' => $item_vo->type,
                    'event_template' => get_event($ev, true)
                )
                ); //return okay response, changed
    }
    
    
    public function delete_all()
    {
        //this data might be vary large so we take care to not exceed max_input_vars_limit
        $cells = json_decode($this->input->post('cells'), true); 

        //validate all dates etc,
        foreach ($cells as $cell)
        {
            //first validate response
            if (!$this->validator->validate_date_item_vars($cell))
            {
                 return $this->output->json_wrong_params();
                //wrong data
            }
            
            $item_id = $cell['item_id'];
            
            if (!can_item_events_edit($item_id))
            {
                return $this->output->json_forbidden();
            }
        }

        //do not validate item id, waste of time
        $this->events_model->delete_all( $cells );
        
        $this->load->library('cal_renderer');
        
        $return_data = array(
            lang('admin_ajax_events_deleted'),
            $this->cal_renderer->get_events_list(array()) //return empty events list
        ); 
        
        return $this->output->json_response($return_data); //return okay response, changed
    }
    
    
    public function add()
    {
        //this data might be vary large so we take care to not exceed max_input_vars_limit
        $cells = json_decode($this->input->post('days'), true); 
        
        //validate all dates etc,
        foreach ($cells as $cell)
        {
            //first validate response
            if (!$this->validator->validate_date_item_vars($cell))
            {
                 return $this->output->json_wrong_params();
                //wrong data
            }
        }
        
        $needed_items = array_unique(array_column($cells, 'item_id'));
        
        foreach ($needed_items as $item_id)
        {
            if (!can_item_events_edit($item_id))
            {
                return $this->output->json_forbidden();
            }
        }
        
        $this->load->model('items_model');
        $item_vos = $this->items_model->get($needed_items);

        if (in_array(false, $item_vos, true)) //if we have something not found
        {
            //not found
            return $this->output->json_not_found();
        }
        
        //validate for each found item vo that that every event type is allowed
        foreach ($item_vos as $item_vo)
        {
//            $item_vo = $item_vos[$cell['item_id']];
            
            if (!$this->validator->validate_cal_events($this->input->post('events'), $item_vo->type, false))
            {
                return $this->output->json_wrong_params();
                //wrong data
            } 
        }
        
        
        //make objects
        $events = array();
        foreach ($this->input->post('events') as $event)
        {
            $events[] = new Cal_event_vo($event);
        }

        $this->events_model->add($cells, $events);
        
        $saved_events = $this->events_model->get_for_days($cells);
        
        
        $this->load->library('cal_renderer');
        
        //iterate over array and render cells
        foreach ($saved_events as &$arr)
        {
            $arr['events'] = $this->cal_renderer->get_events_list($arr['events']);
        }
        
        $return_data = array(
            lang('admin_ajax_events_saved'),
            $saved_events
        ); 
        
        return $this->output->json_response($return_data); //return okay response, changed
    }
    
    
    public function save()
    {
        //first validate response
        if (!$this->validator->validate_date_item_vars($this->input->post()))
        {
             return $this->output->json_wrong_params();
            //wrong data
        }
        
        if (!can_item_events_edit($this->input->post('item_id')))
        {
            return $this->output->json_forbidden();
        }

        $this->load->model('items_model');
        $item_vo = $this->items_model->get(array($this->input->post('item_id')))[$this->input->post('item_id')];

        if (!$item_vo)
        {
            //not found
            return $this->output->json_not_found();
        }

        $this->load->model('events_model');
        $old_events = $this->events_model->get_for_day(
                    $this->input->post('year'), 
                    $this->input->post('month'), 
                    $this->input->post('day'), 
                    $this->input->post('item_id')
                );
        
        //validate events etc
        if ( !$this->validator->validate_events_edit($this->input->post(), $old_events, $item_vo->type) )
        {
             return $this->output->json_wrong_params();
            //wrong data
        }
        
        //make objects
        $events = array();
        foreach ($this->input->post('events') as $event)
        {
            $event['year'] = $this->input->post('year');
            $event['month'] = $this->input->post('month');
            $event['day'] = $this->input->post('day');
            $events[] = new Cal_event_vo($event);
        }

        
        $this->events_model->save_for_day(
                    $this->input->post('year'), 
                    $this->input->post('month'), 
                    $this->input->post('day'), 
                    $this->input->post('item_id'),
                    $events,
                    $old_events
                );
       
        $saved_events = $this->events_model->get_for_day(
                    $this->input->post('year'), 
                    $this->input->post('month'), 
                    $this->input->post('day'), 
                    $this->input->post('item_id')
                );
        
        $this->load->library('cal_renderer');
        
        $return_data = array(
            lang('admin_ajax_events_saved'),
            $this->cal_renderer->get_events_list($saved_events)
        ); 
        
        return $this->output->json_response($return_data); //return okay response, changed
    }

   
}
