<?php
/*
 * @author flexphperia.net
 */

class Cal_renderer
{
    private $_week_day_format;
    
    public $settings_vo;
    

    public function __construct()
    {
        $this->CI = & get_instance();
        
        $this->settings_vo = $this->CI->settings_model->load();
        
        $this->_week_day_format = new IntlDateFormatter(lang('php_locale'), IntlDateFormatter::NONE,
                IntlDateFormatter::NONE, NULL, NULL, "cccccc");
    }

    public function calendar($year, $month, $events, $items = array(),  $is_editor = false, $show_all = false, $item_to_show = 0)
    {
        $this->_header($year, $month, $is_editor);

        $rows = $this->_prepare_rows($events, $items, $show_all, $item_to_show);
        $this->_rows($year, $month, $rows);

        $this->_end();
    }

    // month_offset allows to add or substract month
    public function get_month_data($year, $month_num)
    {
        $month_num = (int)$month_num; //ensure that is not have leading zeros
        
        $date_obj = DateTime::createFromFormat('!n', $month_num);

        $day_num = cal_days_in_month(CAL_GREGORIAN, $month_num, $year);

        $month = array(
            'num' => $month_num,
            'name' => get_month_name($month_num),
            'year' => $year,
            'days' => array(),
        );

        for ($i = 1; $i <= $day_num; $i++)
        {
            $date_obj = date_create_from_format('Y-n-j', $year . '-' . $month_num . '-' . $i);
            $week_day_num = $date_obj->format('N');
            $week_day_name = datefmt_format( $this->_week_day_format, mktime(0, 0, 0, $month_num, $i, $year));

            $now = new DateTime();
            // Setting the time to 0 will ensure the difference is measured only in days
            $now->setTime(0, 0, 0);
            $date_obj->setTime(0, 0, 0);

            $today = $now->diff($date_obj)->days === 0; // Today

            $day = array(
                'num' => $i,
                'week_num' => $week_day_num,
                'name' => $week_day_name,
                'date_string' => $date_obj->format(lang('date_format')), //format 
                'is_today' => $today,
            );

            $month['days'][$i] = $day;
        }

        return $month;
    }
    
    
    /**
     * Prepares rows rows from month events
     * @param type $events
     * @return type
     */
    private function _prepare_rows($events = array(), $items = array(), $show_all = false, $item_to_show = 0)
    {
        //row keys are item ids
        $rows = array();
        
        
        //find what item ids are used by events
        $item_ids = array_unique (array_column(array_column($events, 'item'), 'id'));

        //prefill rows table with items details etc if item is needed
        foreach ($items as $item_vo)
        {
            if ( in_array($item_vo->id, $item_ids) || $show_all || $item_to_show == $item_vo->id )
            {
                $rows[$item_vo->id] = array('item' => $item_vo, 'days' => array());
            }
        }
        //fill events
        foreach ($events as $event_vo)
        {
            $rows[$event_vo->item->id]['days'][$event_vo->day][] = $event_vo;
        }

        
        return array_values($rows);
    }

    private function _header($year, $month, $is_editor = false)
    {
        $month_data = $this->get_month_data($year, $month);
        // return;
        ?>
        <div class="cali <?php echo ($is_editor ? 'editor' : ''); ?> " data-year="<?php echo $year ?>" data-month="<?php echo $month ?>">
            <div class="header">
                <div class="name-column">
                    <div class="wrap"><?php echo $month_data['name'] ?> <?php echo $month_data['year'] ?></div>
                </div>
                <div class="days">
                    <?php foreach ($month_data['days'] as $day): ?>
                        <?php $this->_render_day_cell($day, array(), true); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php
    }

    function _rows( $year, $month, $rows = array())
    {
        if (count($rows) == 0)
        {
        ?>
            <p class="no-items">Brak elementów do wyświetlenia.</p>
        <?php
            return;
        }

        $month_data = $this->get_month_data($year, $month);

        $previous_row_item_type_id = null;

        foreach ($rows as $row_data)
        {

            $is_new_type = ($previous_row_item_type_id !== null && $row_data['item']->type->id != $previous_row_item_type_id) ? true : false;

            $previous_row_item_type_id = $row_data['item']->type->id;
            ?>

            <?php if ($is_new_type): ?>
                <div class="separator"></div>
            <?php endif; ?>

            <div class="item" data-item-id="<?php echo $row_data['item']->id ?>" data-type-id="<?php echo $previous_row_item_type_id; ?>">
                <div class="name-column">
                    <?php echo $this->_get_item($row_data['item']); ?>
                </div>
                <div class="days">
                    <?php foreach ($month_data['days'] as $day): ?>
                        <?php $this->_render_day_cell($day, isset($row_data['days'][$day['num']]) ? $row_data['days'][$day['num']] : array()) ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php
        }
    }

    private function _end()
    {
        ?>
        </div>
        <?php
    }

    private function _render_day_cell($day, $events, $with_date_string = false)
    {
    ?>
        <div 
            class="day week-num-<?php echo $day['week_num'] ?> <?php echo ($day['is_today'] ? 'today' : ''); ?>"
            <?php if($with_date_string): ?>
                data-date-string="<?php echo $day['date_string'] ?>"
            <?php endif; ?>
            >
            <div class="num"><?php echo $day['num'] ?></div>
            <div class="week-name"><?php echo $day['name'] ?></div>

            <?php echo $this->get_events_list($events); ?>

        </div>
    <?php
    }
    
    public function get_events_list(array $events)
    {
        $events_num = count($events);
        
        $events_all = array();
        $has_any_description = false;
        
        $r = '';
//        if ($events_num)
//        {
            foreach ($events as $event_vo)
            {
                $events_all[] = get_event($event_vo);
                
                $has_any_description = $has_any_description || $event_vo->has_any_description();
            }
            
            $r .= '<div class="events-list num-'.$events_num.' '.($has_any_description ? 'has-any-description' : '').'">';
            $r .= count($events_all) ? implode('', $events_all) : '';
            $r .= '</div>';
//        }
        
        return $r;
    }

    private function _get_item($item_vo)
    {
//        return '';
        $return = '<div class="name-wrap" >';
        $return .= get_ico($item_vo->icon);
        $return .= '<span class="name">' . htmlspecialchars($item_vo->name) . '</span>';
        $return .= '</div>';

        if (count($item_vo->fields))
        {
            $positions_html = array(
                'a' => '',
                'b' => '',
                'c' => '',
                'd' => ''
            );


            foreach ($item_vo->fields as $field_vo)
            {
                $pos = $field_vo->type->position;
                $positions_html[$pos] .= $this->_get_item_field($field_vo);
            }

            //if not empty
            if ( implode('', array_values($positions_html) ) )
            {
                $return .= '<div class="details">';

                if (!empty($positions_html['a']))
                {
                    $return .= '<div class="container-a">' . $positions_html['a'] . '</div>';
                }

                $has_b_c = !empty($positions_html['b']) && !empty($positions_html['c']);

                if ($has_b_c)
                {
                    $return .= '<div class="d-flex">';
                }

                if (!empty($positions_html['b']))
                {
                    $return .= '<div class="container-b">' . $positions_html['b'] . '</div>';
                }

                if (!empty($positions_html['c']))
                {
                    $return .= '<div class="container-c">' . $positions_html['c'] . '</div>';
                }

                if ($has_b_c)
                {
                    $return .= '</div>';
                }

                if (!empty($positions_html['d']))
                {
                    $return .= '<div class="container-d">' . $positions_html['d'] . '</div>';
                }
                $return .= '</div>';
            }
        }

        return $return;
    }

    private function _get_item_field($field_vo)
    {
        if (!$field_vo->value)
            return;
        
        $s = '<div class="field type-' . $field_vo->type->type . '">';

        
        $width = 100;
        switch ($field_vo->type->size)
        {
            case 's':
            {
                $width = 100;
                break;
            }
            case 'm':
            {
                $width = 150;
                break;
            }
            case 'l':
            {
                $width = 200;
                break;
            }
        }
        
        if ($field_vo->type->label_show)
        {
            $s .= '<div class="label">' . htmlspecialchars($field_vo->type->label) . '</div>';
        }

        switch ($field_vo->type->type)
        {
            case 'text':
            {
                $s .= '<div class="value" lang="en">' . htmlspecialchars($field_vo->value) . '</div>';
                break;
            }
            case 'image':
            {
                
                $s .= '<img src="' . base_url(config_item('yc_img_generator_url').'?dir=uploads&width='.$width.'&filename='. $field_vo->value) . '" class="value" style="width: '.$width.'px;"  />';
                break;
            }
            case 'link':
            {
                $s .= '<a href="' . htmlspecialchars($field_vo->value) . '" target="_blank" class="value">' . htmlspecialchars($field_vo->options['text']) . '</a>';
                break;
            }
        }

        $s .= '</div>';

        return $s;
    }

}
    