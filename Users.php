<?php

/**
 * Main controller
 * 
 * @author flexphperia.net
 */
class Users extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

    }

    public function index()
    {
        $this->html_auth_check(array('add_delete_users', 'edit_users'), 'or');
        
        //get all users
        $users = $this->aauth->list_users();

        $items = array();
        
        $can_delete_add = can('add_delete_users');

        foreach ($users as $user)
        {
            $items[] = array(
                'link' => site_url('users/edit/' . $user->id),
                'text' => $user->username,
                'details' => array('ID: ' . $user->id),
                'delete_button' => $can_delete_add && $user->id != 1,
                'data' => array('id' => $user->id)
            );
        }

        $data = array(
            'title' => lang('users_title'),
            'list_data' => array(
                'subtitle' => lang('users_count'),
                'count' => count($items),
                'items' => $items,
            ),
            'can_add' => $can_delete_add
        );

        $this->load->view('parts/header', $data);
        $this->load->view('pages/users', $data);
        $this->load->view('parts/footer');
    }

    public function edit($user_id = 0)
    {
        if ($user_id !== 0 && !$this->validator->validate_id($user_id))
        {
            return $this->show_404();
        }
        
        //editing user
        if ($user_id !== 0)
        {
            $this->html_auth_check(array('add_delete_users', 'edit_users'), 'or');
            
            $user = $this->aauth->get_user($user_id);

            if (!$user)
            {
                //not found
                return $this->show_404();
            }
        }
        else
        {
            $this->html_auth_check('add_delete_users');
        }
        
        $user_vo = new User_vo(array('id' => $user_id, 'username' => $user_id !== 0 ? $user->username : ''));
        

        $data = array(
            'title' => lang('user_edit_title'),
            'add_mode' => $user_id == 0,
            'user_login' => $user_vo->username, //used only in editing mode
            'login_disabled' => $user_id == 1, //used when admin editing or something
            'user_vo' =>  $user_vo
        );

        $this->load->view('parts/header', $data);
        $this->load->view('pages/user_edit', $data);
        $this->load->view('parts/footer');
        
//        echo 'edycja' . $user_id;
    }

    public function delete()
    {
        if (!can('add_delete_users'))
        {
            return $this->output->json_forbidden();
        }
        
        $this->load->library('validator');

        //user admin cannot be deleted
        if (!$this->validator->validate_id($this->input->post('id')) || $this->input->post('id') == '1')
        {
            return $this->output->json_wrong_params();
        }

        $result = $this->aauth->delete_user($this->input->post('id'));

        //error while deleting 
        if ($result === false)
        {
            return $this->output->json_not_found();
        }


        return $this->output->json_response(
            array(
                lang('admin_ajax_user_deleted'),
                self::run('users/index', '.item-list') //return part of other method
            )
        ); //return okay response
    }
    
    public function save()
    {       
        $this->load->library('validator');
        
        if ( !$this->validator->validate_user_edit($this->input->post()) )
        {
            return $this->output->json_wrong_params();
        }
        
        $user_edit_vo = new User_edit_vo($this->input->post());
        
        $updated_id = null;
        

        
        if (!$user_edit_vo->id)  //new user
        {
            if (!can('add_delete_users'))
            {
                return $this->output->json_forbidden();
            }
            
            
            //user exists
            if ($this->aauth->user_exist_by_username($user_edit_vo->username))
            {
                return $this->output->json_response(array(
                        lang('validation_user_exists'),
                        'user_exists'
                    )); //return okay response but with message
            }
            
            $result_db = $this->aauth->create_user($user_edit_vo->username, $user_edit_vo->password);
            
            //not found
            //error while saving 
            if ($result_db === false)
            {
                return $this->output->json_response(lang('ajax_error_saving_data'), 2);
            }
            
            $updated_id = $result_db;
        }
        else //updating user
        {
            if (!can(array('add_delete_users', 'edit_users'), 'or'))
            {
                return $this->output->json_forbidden();
            }
            
            $curr_user = $this->aauth->get_user($user_edit_vo->id);
            
            //changed username so validate that is exist
            if ($curr_user->username != $user_edit_vo->username && $this->aauth->user_exist_by_username($user_edit_vo->username))
            {
                return $this->output->json_response(array(
                        lang('validation_user_exists'),
                        'user_exists'
                    )); //return okay response but with message
            }
            
            $this->aauth->update_user($user_edit_vo->id, $user_edit_vo->username, $user_edit_vo->pass_change ? $user_edit_vo->password : false);
            $updated_id = $user_edit_vo->id;
        }
        
        $user = $this->aauth->get_user($updated_id);
        $user_vo = new User_vo(array('id' => $user->id, 'username' => $user->username));
        

        $return_data = array(
            lang('admin_ajax_user_saved'),
            $user_vo->to_send_array()
        ); 

        return $this->output->json_response($return_data); //return okay response, changed
    }
    
    
    public function change_password()
    {  
        if (!$this->aauth->is_loggedin())
        {
            redirect('login');
        }
        
        $data = array(
            'title' => lang('user_pass_title'),
        );

        $this->load->view('parts/header', $data);
        $this->load->view('pages/user_password', $data);
        $this->load->view('parts/footer');
    }
    
    /**
     *      * 
     * @return json
     */
    public function save_password()
    {  
        if (!$this->aauth->is_loggedin())
        {
            return $this->output->json_forbidden();
        }
        
        $this->load->library('validator');
        $pass_vo = new Pass_change_vo($this->input->post());

        if ( !$this->validator->validate_pass_change($pass_vo) )
        {
            return $this->output->json_wrong_params();
        }
        
        $user = $this->aauth->get_user(false); //currently logged

        //check that old password is correct or not
        if ( !$this->aauth->authenticate($user->username, $pass_vo->old_password) )
        {
            return $this->output->json_response(array(
                        lang('validation_wrong_current_password'),
                        'wrong_current'
                    )); //return okay response but with message
        }

        //change password
        $this->aauth->update_user($user->id, false, $pass_vo->new_password1);

        return $this->output->json_response(array(
                    lang('admin_ajax_password_changed'),
                    null
                )); //return okay response but with message
    }

}
