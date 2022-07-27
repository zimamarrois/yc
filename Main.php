<?php

/**
 * Main controller
 * 
 * @author flexphperia.net
 */
class Main extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index($year = 0, $month = 0, $item_id = 0)
    {
        if (!$this->validator->validate_browse_vars($year, $month, $item_id))
        {
            return $this->show_404();
            //wrong data
        }

        if (!empty($item_id) &&  !can_item_events_browse($item_id)) //not allowed so reset this item_id to show
        {
            $item_id = 0;
        }
        
        if (!empty($item_id))
        {
            $item_vo = $this->items_model->get(array($item_id))[$item_id];

            if (!$item_vo)
            {
                //not found
                return $this->show_404();
            }
        }
        

        $filter_data = array();
                
        //1. load items and remove that browse is not allowed
        //2. find item type ids used in those items
        //3. get all item types and remove that we do not have alllowed to browse
        //4. add item types found in items to that that we have allowed browsing
        //5. find filter data only for specified types and filtered for allowed items
        
        $items = filter_item_events_browse($this->items_model->get(null, true)); //sort by type order
        $item_types_ids_from_items = array_unique(array_column(array_column($items, 'type'), 'id')); 
        
        //item types allowed to browse directly
        $item_types_ids = array_column(filter_item_type_events_browse($this->item_types_model->get(null)), 'id'); //by name
        
        $item_type_ids_sum = array_unique(array_merge ($item_types_ids_from_items, $item_types_ids));
        
        //we have to get it from model in one call to get propoer order, we cannot combine array of two calls to model
        $item_types = $this->item_types_model->get($item_type_ids_sum, true); 
        
      
        //if we have not allowed items so items not realy exists, do not need data
        if (!$this->settings_vo->disable_filtering && count($items) > 0)
        {
            $filter_data = $this->items_model->get_filter_data($item_types, array_column($items, 'id'));
        }


        $m_num = $this->settings_vo->browse_month_num;
        
        $m_data = month_offset($year, $month, 0);
        
        $next_month_data = month_offset($year, $month, $m_num );
        $prev_month_data = month_offset($year, $month, -$m_num);

        $year_prev = $prev_month_data['year'];
        $month_prev = $prev_month_data['month'];

        $year_next = $next_month_data['year'];
        $month_next = $next_month_data['month'];

        $this->load->library('cal_renderer');

        $data = array(
            'title' => lang('browse_title'),
            'year' => $m_data['year'],
            'month' => $m_data['month'],
            'year_prev' => $year_prev,
            'month_prev' => $month_prev,
            'year_next' => $year_next,
            'month_next' => $month_next,
            'item_types' => $item_types, 
            'filter_data' => $filter_data,
            'calendars_html' => $this->_browse_calendars($year, $month, $items, $item_id)
        ); 
        
        $this->load->view('parts/header', $data);
        $this->load->view('pages/browse', $data);
        $this->load->view('parts/footer');
    }

    public function filter()
    {       
        if ($this->settings_vo->disable_filtering)
        {
            return $this->output->json_not_found();
        }
        
        $post = $this->input->post();
        
        if (!$this->validator->validate_browse_vars($post['year'], $post['month'], 0))
        {
            return $this->output->json_wrong_params();//wrong data
        }
     
        $year = $post['year'];
        $month = $post['month'];
   

        if ($post['item_type_id'] == 'all')
        {
            $items = filter_item_events_browse($this->items_model->get(null, true)); //sort by type order
//            $items = $this->items_model->get(null, true);
        }
        else
        {
            //load only needed
            $items = filter_item_events_browse($this->items_model->find($post['item_type_id'], $post['field_type_id'], $post['value']));
        }

        $return_data = $this->_browse_calendars($year, $month, $items); 

        return $this->output->json_response($return_data); //return okay response, changed
    }
    

    
    public function edit($year = 0, $month = 0, $item_id = 0)
    {
        if (!$this->validator->validate_browse_vars($year, $month, $item_id))
        {
            return $this->show_404();
            //wrong data
        }
        
        if (!empty($item_id) &&  !can_item_events_edit($item_id)) //not allowed so reset this item_id to show
        {
            $item_id = 0;
        }

        if (!empty($item_id))
        {
            $item_vo = $this->items_model->get(array($item_id))[$item_id];

            if (!$item_vo)
            {
                //not found
                return $this->show_404();
            }
        }
        

        //get all or one if one requested 
        $items = filter_item_events_edit($this->items_model->get($item_id ? array($item_id) : null, true));
        
        $this->load->model('events_model');


        for ($i = 0; $i < $this->settings_vo->editor_month_num; $i++)
        {
            $m_data = month_offset($year, $month, $i);

            $calendar_data[] = array(
                'year' => $m_data['year'],
                'month' => $m_data['month'],
                'events' => $this->events_model->get_for_month($m_data['year'], $m_data['month'], array_keys($items)),
                'items' => $items
            );
            
//            Console::log($m_data['month']);
        }
        $next_month_data = month_offset($calendar_data[count($calendar_data)-1]['year'], $calendar_data[count($calendar_data)-1]['month'], 1);
        $prev_month_data = month_offset($calendar_data[0]['year'], $calendar_data[0]['month'], -$this->settings_vo->editor_month_num);

        $year_prev = $prev_month_data['year'];
        $month_prev = $prev_month_data['month'];

        $year_next = $next_month_data['year'];
        $month_next = $next_month_data['month'];

        

        $this->load->library('cal_renderer');

        $data = array(
            'title' => lang('edit_title'),
            'cal_renderer' => $this->cal_renderer,
            'cal_data' => $calendar_data,
            'items' => $items, //pass all items
            'year' => $calendar_data[0]['year'],
            'month' => $calendar_data[0]['month'],
            'year_prev' => $year_prev,
            'month_prev' => $month_prev,
            'year_next' => $year_next,
            'month_next' => $month_next,
            'item_id' => $item_id
        );

        $this->load->view('parts/header', $data);
        $this->load->view('pages/edit', $data);
        $this->load->view('parts/footer');
    }

    public function edit_select()
    {
        $items = filter_item_events_edit($this->items_model->get());
        
        //the same as in browse
        $item_types_ids_from_items = array_unique(array_column(array_column($items, 'type'), 'id')); 
        
        //item types allowed to browse directly
        $item_types_ids = array_column(filter_item_type_events_edit($this->item_types_model->get(null)), 'id'); //by name
        
        $item_type_ids_sum = array_unique(array_merge ($item_types_ids_from_items, $item_types_ids));
        
        //we have to get it from model in one call to get propoer order, we cannot combine array of two calls to model
        $item_types = $this->item_types_model->get($item_type_ids_sum, true); 


        $items_to_show = array();
        foreach ($items as $item_vo)
        {
            $items_to_show[] = array(
                'link' => site_url('edit/0/0/' . $item_vo->id),
                'icon' => $item_vo->icon,
                'text' => $item_vo->name,
                'details' => array(lang('edit_select_type') . ' ' . $item_vo->type->name),
                'data' => array('type-id' => $item_vo->type->id),
                'no_buttons' => true
            );
        }

        $data = array(
            'title' => lang('edit_select_title'),
            'list_data' => array(
                'subtitle' => lang('edit_select_count'),
                'count' => count($items_to_show),
                'items' => $items_to_show,
            ),
            'item_types' => $item_types
        );

        $this->load->view('parts/header', $data);
        $this->load->view('pages/edit_select', $data);
        $this->load->view('parts/footer');
    }

    public function login()
    {
        //when logged in redirect to home screen directly
        if ($this->aauth->is_loggedin())
        {
            redirect('');
        }

        $wrong_pass = false;
        $username = $this->input->post('login');
        $remember_me = $this->input->post('remember_me') ? true : false;

        if (!empty($this->input->post()))
        {
            //try to admin_login
            if (!$this->aauth->login($this->input->post('login'), $this->input->post('password'), $remember_me))
            {
                $wrong_pass = true;
            } 
            else
            {
                redirect('');
            }
        }

        $data = array(
            'title' => lang('login_title'),
            'wrong_pass' => $wrong_pass,
            'username' => $username,
            'remember_me' => $remember_me,
        );

        $this->load->view('parts/header', $data);
        $this->load->view('pages/login', $data);
        $this->load->view('parts/footer');
    }

    public function logout($only_display = null)
    {
        //firstly logout and redirect to itself to show logged out message
        if (!$only_display)
        {
            $this->aauth->logout();
            redirect('logout/1');
        }

        $data = array(
            'title' => lang('logout_title')
        );

        $this->load->view('parts/header', $data);
        $this->load->view('pages/logout');
        $this->load->view('parts/footer');
    }
    
    
    /**
     * Prepares html with browse calendars
     * 
     * @param type $year
     * @param type $month
     * @param type $items
     * @param type $item_to_show
     * @return type
     */    
    private function _browse_calendars($year, $month, $items, $item_to_show = null)
    {
        $this->load->model('events_model');

        for ($i = 0; $i < $this->settings_vo->browse_month_num; $i++)
        {
            $m_data = month_offset($year, $month, $i);
            
            $events = $this->events_model->get_for_month($m_data['year'], $m_data['month'], array_keys($items));
            

            
            filter_descriptions($events);

            $calendar_data[] = array(
                'year' => $m_data['year'],
                'month' => $m_data['month'],
                //get events only for allowed items
                'events' => $events
            );
        }

        $this->load->library('cal_renderer');

        $data = array(
            'cal_renderer' => $this->cal_renderer,
            'cal_data' => $calendar_data,
            'items' => $items, //pass all items
            'item_to_show' => $item_to_show ? $item_to_show : false,
        ); 
        
        return $this->load->view('parts/cal_months', $data, true); 
    }

}
