<?php

/**
 * @author flexphperia.net
 */
class Perms_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function get_groups(array $ids = null)
    {       
        $groups_rows = array();
        
        if ($ids === null) //we want all
        {
            $groups_rows = $this->aauth->list_groups();
        }
        else
        {
            foreach ($ids as $id)
            {
                $group_r = $this->aauth->get_group($id);
                if ($group_r)
                {
                    $groups_rows[] = $group_r;
                }
            }
        }
        
        $result = array();
        foreach ($groups_rows as $group_row)
        {
            $group_vo = new Group_vo(array('id' => $group_row->id, 'name' => $group_row->name));

            if ($group_vo->id == 1)
            {
                $group_vo->name = lang('perm_groups_admins');
            }
            else if ($group_vo->id == 2)
            {
                $group_vo->name = lang('perm_groups_notlogged');
            }

            $group_perms = $this->aauth->list_group_perms($group_vo->id);

            foreach ($group_perms as $perm)
            {
                $group_vo->perms[] = $perm;
            }

            $result[$group_vo->id] = $group_vo;
        }
        
//        $result['num_rows'] = count($groups_rows);
        
        if ($ids !== null)
        {
            //find what records was not found and attach false to its key in return array
            foreach (array_diff($ids, array_keys($result)) as $not_found_id)
            {
                $result[$not_found_id] = false;
            }
        }

        return $result;
    }
    
    
    public function get_user($id)
    {       
        $user = $this->aauth->get_user($id);

        if (!$user)
        {
            //not found
            return false;
        }
        
        $user_perms_vo = new User_perms_vo(array('id' => $id, 'username' => $user->username));
        
        $user_perms = $this->aauth->get_user_perms($user_perms_vo->id);

        foreach ($user_perms as $perm)
        {
            $user_perms_vo->perms[] = $perm;
        }
        
        $group_ids = $this->aauth->get_user_group_ids($user_perms_vo->id);
        
//        $group_ids = array();
//        foreach ($groups as $group_row)
//        {
//            $group_ids[] = (int)$group_row->id;
//        }
        
         //minimum one group not logged
        $group_vos = $this->get_groups($group_ids);
     
        foreach ($group_vos as $group_vo)
        {
             $user_perms_vo->groups[] = $group_vo;
        }
        
        return $user_perms_vo;
    }


    public function save_group(Group_vo $group_vo, Group_vo $old_group_vo = null)
    {
        if (!empty($group_vo->id))
        {
            $id = $group_vo->id;

            //updates name if neeed
            if ($group_vo->id != 2) //do not update notlogged groups name
            {
                $this->aauth->update_group($group_vo->id, $group_vo->name);
            }
            
            $perms_to_del = array_diff($old_group_vo->perms, $group_vo->perms);
            $perms_to_add = array_diff($group_vo->perms, $old_group_vo->perms);
            
            foreach ($perms_to_add as $perm)
            {
                $this->aauth->allow_group($group_vo->id, $perm);
            }
            
            foreach ($perms_to_del as $perm)
            {
                $this->aauth->deny_group($group_vo->id, $perm);
            }
        }
        else
        {
            $id = $this->aauth->create_group( $group_vo->name );

            $perms_to_add = $group_vo->perms;
            
            foreach ($perms_to_add as $perm)
            {
                $this->aauth->allow_group($id, $perm);
            }
        }

        return $id;
    }
    
    
    public function save_user(User_perms_vo $user_perms_vo, User_perms_vo $old_user_perms_vo)
    {
        //find what groups to add
        $old_group_ids = array();
        $new_group_ids = array();
        foreach ($old_user_perms_vo->groups as $group_vo)
        {
            $old_group_ids[] = $group_vo->id;
        }
        foreach ($user_perms_vo->groups as $group_vo)
        {
            $new_group_ids[] = $group_vo->id;
        }
        
        $groups_to_del = array_diff($old_group_ids, $new_group_ids);
        $groups_to_add = array_diff($new_group_ids, $old_group_ids);
        
        foreach ($groups_to_add as $id)
        {
            $this->aauth->add_member($user_perms_vo->id, $id);
        }

        foreach ($groups_to_del as $id)
        {
            $this->aauth->remove_member($user_perms_vo->id, $id);
        }
        
        //find what permissions to change
        $perms_to_del = array_diff($old_user_perms_vo->perms, $user_perms_vo->perms);
        $perms_to_add = array_diff($user_perms_vo->perms, $old_user_perms_vo->perms);
//        var_dump($perms_to_add);
//        die;

        foreach ($perms_to_add as $perm)
        {
            $this->aauth->allow_user($user_perms_vo->id, $perm);
        }
        

        foreach ($perms_to_del as $perm)
        {
            $this->aauth->deny_user($user_perms_vo->id, $perm);
        }
    }
    
    public function create_item_perms($item_id)
    {
        $this->aauth->create_perm('item_events_browse_id_'.$item_id);
        $this->aauth->create_perm('item_events_desc1_id_'.$item_id);
        $this->aauth->create_perm('item_events_desc2_id_'.$item_id);
        $this->aauth->create_perm('item_events_edit_id_'.$item_id);
        $this->aauth->create_perm('item_edit_id_'.$item_id);
    }
    
    public function delete_items_perms(array $item_ids)
    {
        foreach ($item_ids as $id)
        {
            $this->aauth->delete_multiple_perms(array(
                'item_events_browse_id_'.$id,
                'item_events_desc1_id_'.$id,
                'item_events_desc2_id_'.$id,
                'item_events_edit_id_'.$id,
                'item_edit_id_'.$id
            ));
        }

    }
    
    public function create_item_type_perms($item_type_id)
    {
        $this->aauth->create_perm('item_type_events_browse_id_'.$item_type_id);
        $this->aauth->create_perm('item_type_events_desc1_id_'.$item_type_id);
        $this->aauth->create_perm('item_type_events_desc2_id_'.$item_type_id);
        $this->aauth->create_perm('item_type_events_edit_id_'.$item_type_id);
        $this->aauth->create_perm('item_type_edit_id_'.$item_type_id);
    }
    
    public function delete_item_type_perms($item_type_id)
    {
        $this->aauth->delete_multiple_perms(array(
            'item_type_events_browse_id_'.$item_type_id,
            'item_type_events_desc1_id_'.$item_type_id,
            'item_type_events_desc2_id_'.$item_type_id,
            'item_type_events_edit_id_'.$item_type_id,
            'item_type_edit_id_'.$item_type_id,
            'item_type_add_delete_id_'.$item_type_id
        ));
    }

}
