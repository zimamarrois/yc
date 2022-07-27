<?php

/**
 * This is cal helper
 */
function image_move($temp_file = false, $upload_file_delete = false)
{
    $CI = & get_instance();

    if ($temp_file)
    {
        copy(config_item('yc_temp_path') . $temp_file,
                config_item('yc_uploads_path') . $temp_file);
    }

    $CI->load->library('image_operator');

    if ($upload_file_delete)
    {
        $CI->image_operator->delete_upload($upload_file_delete);
    }
    if ($temp_file)
    {
        $CI->image_operator->delete_temp($temp_file); //delete temp
    }

    $CI->image_operator->delete_old_temp(); //clear old cache, will clear cache files used earlier
}

function get_ico($name, $classes_string = '')
{
    return '<svg class="icon ' . $classes_string . '"><use xlink:href="#' . $name . '"></use></svg>';
}

function ico($name, $classes_string = '')
{
    echo get_ico($name, $classes_string);
}

/*
 * Returns month offset realtive to specified val
 */
function month_offset($year = 0, $month = 0, $offset = 0)
{
    $year = $year == 0 ? date('Y') : $year;
    $month = $month == 0 ? date('m') : $month;

    $date_obj = new DateTime($year . '-' . $month . '-01');
    $date_obj->setTime(0, 0, 0);

    $interval = new DateInterval('P' . abs($offset) . 'M');

    if ($offset > 0)
    {
        $date_obj->add($interval);
    }
    else if ($offset < 0)
    {
        $date_obj->sub($interval);
    }

    return array(
        'month' => (int)$date_obj->format('n'),
        'year' => (int)$date_obj->format('Y')
    );
}

/*
 * Returns full month name in current locale
 */
function get_month_name($month)
{
    $month_name_format = new IntlDateFormatter(lang('php_locale'), IntlDateFormatter::NONE,
            IntlDateFormatter::NONE, NULL, NULL, "LLLL");

    return datefmt_format($month_name_format, mktime(0, 0, 0, $month));
}



function get_event(Cal_event_vo $event_vo, $include_empty_desc = false)
{
    $classes = array('event');

    if ($event_vo->is_main)
    {
        $classes[] = 'main';
    }

    $return = '<div class="' . implode(' ', $classes) . '" >';


    $return .= get_ico($event_vo->type->icon);
    $return .= '<span class="name">' . htmlspecialchars($event_vo->type->name) . '</span>';
    if ($event_vo->is_main && !config_item('yc_hide_main_event_label'))
    {
        $return .= '<span class="badge badge-info">'.lang('cal_main_event').'</span>';
    }
    
   
    
    $desc1 = $event_vo->description1;
    $desc2 = $event_vo->item->type->desc2_disabled ? '' : $event_vo->description2;
    
    
    //label show and not empty
    $desc1Label = $event_vo->item->type->desc1_label_show && !empty($event_vo->item->type->desc1_label) ? $event_vo->item->type->desc1_label : '';
    $desc2Label = $event_vo->item->type->desc2_label_show && !empty($event_vo->item->type->desc2_label) ? $event_vo->item->type->desc2_label : '';
    
    $descriptions = '<span data-desc1 >';
    $descriptions .= '<span class="label" >'.$desc1Label.'</span>';
    $descriptions .= '<span class="value" >'.nl2br(htmlspecialchars($desc1)).'</span>';
    $descriptions .= '</span>';
    
    $descriptions .= '<span data-desc2 >';
    $descriptions .= '<span class="label" >'.$desc2Label.'</span>';
    $descriptions .= '<span class="value" >'.nl2br(htmlspecialchars($desc2)).'</span>';
    $descriptions .= '</span>';

    if ($include_empty_desc || $event_vo->has_any_description())
    {
        $classes = '';
        $classes .= $event_vo->has_any_description() ? 'has-any-desc ' : '';
        
        if (empty($desc1))
        {
            $classes .= 'desc1-empty ';
        }

        if (!empty($desc1Label))
        {
            $classes .= 'desc1-label ';
        }

        if (empty($desc2))
        {
            $classes .= 'desc2-empty ';
        }

        if (!empty($desc2Label))
        {
            $classes .= 'desc2-label ';
        }

        //do not doo this conditionally
        $return .= '<span class="description '.$classes.' ' .$event_vo->item->type->desc_show_how.'">' . $descriptions . '</span>';
    }
    

    $return .= '</div>';

    return $return;
}



function menu_active_class($section)
{
    $CI = & get_instance();
    
    $path = $CI->router->fetch_class().'/'.$CI->router->fetch_method();
    
    $d = array(
        'browse' => array('main/index'),
        'edit' => array('main/edit', 'main/edit_select'),
        'manage' => array(
            'items/index', 'items/edit', 
            'item_types/index', 'item_types/edit',
            'event_types/index', 'event_types/edit',
            'users/index', 'users/edit',
            'permissions/users', 'permissions/user_edit',
            'permissions/groups', 'permissions/group_edit',
            'settings/index'
            )
    );

    echo in_array($path, $d[$section]) ? 'active' : '';
    
}
