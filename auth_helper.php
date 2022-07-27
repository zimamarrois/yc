<?php
/**
 * Checks user permission.
 * Pass array of permissions and operator to compare AND or OR
 * Pass simple permission name too without array
 * 
 * @param type $perms
 * @param type $operator
 * @return boolean
 */
function can($perms, $operator = 'or')
{
    $CI = & get_instance();
    
    if (is_array($perms))
    {
        $res = null;
        foreach ($perms as $perm)
        {
            $allowed = $CI->aauth->is_allowed($perm);
            
            if ($res === null)
            {
                $res = $allowed; 
                continue;
            }
            if ($operator == 'and')
            {
                $res = $res && $allowed;
                if (!$res) //bail early, do not check rest
                {
                    return false;
                } 
            }
            else{
                $res = $res || $allowed;
            }

        }
        return $res;
    }
    return $CI->aauth->is_allowed($perms);
}


function can_item_events_browse($id){
    $CI = & get_instance();
    
    $item_vo = $CI->items_model->get(array($id))[$id];
    if (!$item_vo)
    {
        return false;
    }
    $type_id = $item_vo->type->id;
    
    return  
            can_item_type_events_browse($type_id) ||
            can_item_type_events_desc1($type_id) ||
            can_item_type_events_desc2($type_id) ||
            can_item_type_events_edit($type_id) ||
    
            $CI->aauth->is_allowed('item_events_browse_id_'.$id) ||
            can_item_events_desc1($id) ||
            can_item_events_desc2($id) ||
            can_item_events_edit($id);
            
}


function can_item_events_desc1($id){
    $CI = & get_instance();
    
    $item_vo = $CI->items_model->get(array($id))[$id];
    if (!$item_vo)
    {
        return false;
    }
    $type_id = $item_vo->type->id;
    
    return $CI->aauth->is_allowed('item_events_desc1_id_'.$id) || 
            can_item_events_edit($id) ||
            
            can_item_type_events_desc1($type_id) ||
            can_item_type_events_edit($type_id);
}

function can_item_events_desc2($id){
    $CI = & get_instance();
    
    $item_vo = $CI->items_model->get(array($id))[$id];
    if (!$item_vo)
    {
        return false;
    }
    $type_id = $item_vo->type->id;
    
    return $CI->aauth->is_allowed('item_events_desc2_id_'.$id) || 
            can_item_events_edit($id) ||
            
            can_item_type_events_desc2($type_id) ||
            can_item_type_events_edit($type_id);
}


function can_item_events_edit($id){
    $CI = & get_instance();
    
    $item_vo = $CI->items_model->get(array($id))[$id];
    if (!$item_vo)
    {
        return false;
    }
    $type_id = $item_vo->type->id;
    
    return $CI->aauth->is_allowed('item_events_edit_id_'.$id) ||
           can_item_type_events_edit($type_id);
}

function can_item_edit($id){
    $CI = & get_instance();
    
    $item_vo = $CI->items_model->get(array($id))[$id];
    if (!$item_vo)
    {
        return false;
    }
    $type_id = $item_vo->type->id;
    return $CI->aauth->is_allowed('item_edit_id_'.$id) ||
           can_item_type_edit($type_id) ||
           can_item_type_add_delete($type_id);
}



function can_item_type_events_browse($id){
    $CI = & get_instance();
    
    return  $CI->aauth->is_allowed('item_type_events_browse_id_all') ||
            $CI->aauth->is_allowed('item_type_events_browse_id_'.$id) ||
            can_item_type_events_desc1($id) ||
            can_item_type_events_desc2($id) ||
            can_item_type_events_edit($id);
}

function can_item_type_events_desc1($id){
    $CI = & get_instance();
    
    return  $CI->aauth->is_allowed('item_type_events_desc1_id_all') || 
            $CI->aauth->is_allowed('item_type_events_desc1_id_'.$id) || 
            can_item_type_events_edit($id);
}

function can_item_type_events_desc2($id){
    $CI = & get_instance();
    
    return  $CI->aauth->is_allowed('item_type_events_desc2_id_all') || 
            $CI->aauth->is_allowed('item_type_events_desc2_id_'.$id) || 
            can_item_type_events_edit($id);
}


function can_item_type_events_edit($id){
    $CI = & get_instance();
    
    return $CI->aauth->is_allowed('item_type_events_edit_id_all') ||
           $CI->aauth->is_allowed('item_type_events_edit_id_'.$id); 
}

function can_item_type_edit($id){
    $CI = & get_instance();
    
    return  $CI->aauth->is_allowed('item_type_edit_id_all') ||  
            $CI->aauth->is_allowed('item_type_edit_id_'.$id) ||  
            can_item_type_add_delete($id);
}


function can_item_type_add_delete($id){
    $CI = & get_instance();
    
    return  $CI->aauth->is_allowed('item_type_add_delete_id_all') ||  
            $CI->aauth->is_allowed('item_type_add_delete_id_'.$id);
}


function filter_item_events_browse(array $items){
    
    /* @var $item_vo Item_vo */
    foreach ($items as $key => $item_vo)
    {
        if (!can_item_events_browse($item_vo->id))
        {
            unset($items[$key]);
        }
    }
    
    return $items;
}


function filter_item_edit(array $items){
    
    /* @var $item_vo Item_vo */
    foreach ($items as $key => $item_vo)
    {
        if (!can_item_edit($item_vo->id))
        {
            unset($items[$key]);
        }
    }
    
    return $items;
}

function filter_descriptions(array $events){
    
    /* @var $event_vo Cal_event_vo */
    foreach ($events as $event_vo)
    {
        if (!can_item_events_desc1($event_vo->item->id))
        {
            $event_vo->description1 = '';
        }
        
        if (!can_item_events_desc2($event_vo->item->id))
        {
            $event_vo->description2 = '';
        }
    }
}



function filter_item_type_events_browse(array $item_types){

    /* @var $item_type_vo Item_type_vo */
    foreach ($item_types as $key => $item_type_vo)
    {
        if (!can_item_type_events_browse($item_type_vo->id))
        {
            unset($item_types[$key]);
        }
    }
    
    return $item_types;
}


function filter_item_events_edit(array $items){
    
    /* @var $item_vo Item_vo */
    foreach ($items as $key => $item_vo)
    {
        if (!can_item_events_edit($item_vo->id))
        {
            unset($items[$key]);
        }
    }
    
    return $items;
}


function filter_item_type_events_edit(array $item_types){

    /* @var $item_type_vo Item_type_vo */
    foreach ($item_types as $key => $item_type_vo)
    {
        if (!can_item_type_events_edit($item_type_vo->id))
        {
            unset($item_types[$key]);
        }
    }
    
    return $item_types;
}


function filter_item_type_edit(array $item_types){

    /* @var $item_type_vo Item_type_vo */
    foreach ($item_types as $key => $item_type_vo)
    {
        if (!can_item_type_edit($item_type_vo->id))
        {
            unset($item_types[$key]);
        }
    }
    
    return $item_types;
}


function filter_item_type_add_delete(array $item_types){

    /* @var $item_type_vo Item_type_vo */
    foreach ($item_types as $key => $item_type_vo)
    {
        if (!can_item_type_add_delete($item_type_vo->id))
        {
            unset($item_types[$key]);
        }
    }
    
    return $item_types;
}



