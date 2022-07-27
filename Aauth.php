<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Aauth is a User Authorization Library for CodeIgniter 2.x, which aims to make
 * easy some essential jobs such as login, permissions and access operations.
 * Despite ease of use, it has also very advanced features like private messages,
 * groupping, access management, public access etc..
 *
 * @author		Emre Akay <emreakayfb@hotmail.com>
 * @contributor Jacob Tomlinson
 * @contributor Tim Swagger (Renowne, LLC) <tim@renowne.com>
 * @contributor Raphael Jackstadt <info@rejack.de>
 *
 * @copyright 2014-2018 Emre Akay
 *
 * @version 2.5.15
 *
 * @license LGPL
 * @license http://opensource.org/licenses/LGPL-3.0 Lesser GNU Public License
 *
 * The latest version of Aauth can be obtained from:
 * https://github.com/emreakay/CodeIgniter-Aauth
 *
 * @todo separate (on some level) the unvalidated users from the "banned" users
 */
class Aauth
{

    /**
     * The CodeIgniter object variable
     * @access public
     * @var object
     */
    public $CI;

    /**
     * Variable for loading the config array into
     * @access public
     * @var array
     */
    public $config_vars;

    /**
     * The CodeIgniter object variable
     * @access public
     * @var object
     */
    public $aauth_db;

    /**
     * Array to cache permission-ids.
     * @access private
     * @var array
     */
    private $cache_perm_id = array();
    private $cache_perm_to_group = array();
    private $cache_perm_to_user = array();
    private $cache_user_to_group = array();
    
    private $cache_loaded = false;
    
    private $_cache_key = '';

    /**
     * Array to cache group-ids.
     * @access private
     * @var array
     */
//    private $cache_group_id;

    ########################
    # Base Functions
    ########################

    /**
     * Constructor
     */
    public function __construct()
    {
        // get main CI object
        $this->CI = & get_instance();

        $this->CI->load->library('session');

        // config/aauth.php
        $this->CI->config->load('aauth');
        $this->config_vars = $this->CI->config->item('aauth');

        $this->aauth_db = $this->CI->db;

        $this->_cache_key = $this->CI->db->database.'_auth';
        
        $this->CI->load->driver('cache', array('adapter' => 'file'));
    }

    private function _precache()
    {
        if ($this->cache_loaded)
        {
            return;
        }
        
        $this->cache_loaded = true;
        
        //load from file
        $cached_data = $this->CI->cache->get($this->_cache_key);
        
        if ($cached_data !== false)
        {
            $this->cache_perm_id = $cached_data['cache_perm_id'];
            $this->cache_perm_to_group = $cached_data['cache_perm_to_group'];
            $this->cache_perm_to_user = $cached_data['cache_perm_to_user'];
            $this->cache_user_to_group = $cached_data['cache_user_to_group'];
            return;
        }
        
        $query = $this->aauth_db->get($this->config_vars['perms']);
        foreach ($query->result() as $row)
        {
            $key = $row->name;
            $this->cache_perm_id[$key] = $row->id;
        }
        
        $query = $this->aauth_db->get($this->config_vars['perm_to_group']);
        foreach ($query->result() as $row)
        {
            
            $this->cache_perm_to_group[] = array($row->perm_id, $row->group_id);
        }
        
        
        $query = $this->aauth_db->get($this->config_vars['perm_to_user']);
        foreach ($query->result() as $row)
        {
            $this->cache_perm_to_user[] = array($row->perm_id, $row->user_id);
        }
        
          
        $query = $this->aauth_db->get($this->config_vars['user_to_group']);
        foreach ($query->result() as $row)
        {
            $this->cache_user_to_group[] = array($row->user_id, $row->group_id);
        }
        
        //store to cache
        $this->CI->cache->save(
                $this->_cache_key, 
                array(
                    'cache_perm_id' => $this->cache_perm_id,
                    'cache_perm_to_group' => $this->cache_perm_to_group,
                    'cache_perm_to_user' => $this->cache_perm_to_user,
                    'cache_user_to_group' => $this->cache_user_to_group
                ), 
                0);
    }
    

    
    private function _search_cache_pair($cache_array, $a, $b, $one_only = true)
    {
        $res = array();
        
        foreach ($cache_array as $row)
        {
            //alow for searching right and left column
            if (
                    $row[0] == $a && ($b === null) || 
                    ($row[0] == $a && $b !== null && $row[1] == $b) ||
                    $row[1] == $b && ($a === null) || 
                    ($row[1] == $b && $a !== null && $row[0] == $a) 
                )
            {
                if ($one_only)
                {
                    return $row;
                }
                else //collect all rows found
                {
                    $res[] = $row;
                }  
            }
        }
        
        if ($one_only)
        {
           return false;
        }
        else
        {
            return $res;
        }
    }
    
    
    public function reset_cache()
    {
        $this->cache_loaded = false;
        
        //must clear arrays
        $this->cache_perm_id = array();
        $this->cache_perm_to_group = array();
        $this->cache_perm_to_user = array();
        $this->cache_user_to_group = array();

        $this->CI->cache->delete($this->_cache_key);
        
    }


    ########################
    # Login Functions
    ########################
    //tested

    /**
     * Login user
     * Check provided details against the database. Add items to error array on fail, create session if success
     * @param string $email
     * @param string $pass
     * @param bool $remember
     * @return bool Indicates successful login.
     */
    public function login($username, $pass, $remember = false)
    {
        // Remove cookies first
        $cookie = array(
                'name'	 => 'user',
                'value'	 => '',
                'expire' => -3600,
                'path'	 => '/',
        );
        $this->CI->input->set_cookie($cookie);
        
        // if user is not verified

        $this->aauth_db->where('username', $username);
        $this->aauth_db->where('banned', 1);
        $query = $this->aauth_db->get($this->config_vars['users']);

        if ($query->num_rows() > 0)
        {
            //banned
            return false;
        }

        // to find user id, create sessions and cookies
        $this->aauth_db->where('username', $username);
        $query = $this->aauth_db->get($this->config_vars['users']);

        if ($query->num_rows() == 0)
        {
            //no user found
            return false;
        }

        $user = $this->authenticate($username, $pass);
                
        if ( $user )
        {
            // If email and pass matches
            // create session
            $data = array(
                'id' => $user->id,
                'username' => $user->username,
                'loggedin' => true
            );

            $this->CI->session->set_userdata($data);
            
            if ( $remember ){
                $this->CI->load->helper('string');
                $expire = $this->config_vars['remember'];
                $today = date("Y-m-d");
                $remember_date = date("Y-m-d", strtotime($today . $expire) );
                $random_string = random_string('alnum', 16);
                $this->update_remember($user->id, $random_string, $remember_date );
                $cookie = array(
                        'name'	 => 'user',
                        'value'	 => $user->id . "-" . $random_string,
                        'expire' => 99*999*999,
                        'path'	 => '/',
                );
                $this->CI->input->set_cookie($cookie);
            }

            

            // update last login
            $this->update_last_login($user->id);
            return true;
        } else
        {
            //wrong password or username
            return false;
        }
    }

    public function authenticate($username, $pass)
    {
        $query = null;
        $query = $this->aauth_db->where('username', $username);
        $query = $this->aauth_db->get($this->config_vars['users']);

        $row = $query->row();
        
        if ($query->num_rows() != 0 && $this->verify_password($pass, (string) $row->pass))
        {
            return $row;
        } 
        else
        {
            //wrong password or username
            return false;
        }
    }  
    
    //tested

    /**
     * Check user login
     * Checks if user logged in
     * @return bool
     */
    public function is_loggedin()
    {
        if ($this->CI->session->userdata('loggedin'))
        {
            return true;
        }
        else
        {
            if (!$this->CI->input->cookie('user', true))
            {
                return false;
            }
            else
            {
                $cookie = explode('-', $this->CI->input->cookie('user', true));
                if (!is_numeric($cookie[0]) OR strlen($cookie[1]) < 13)
                {
                    return false;
                }
                else
                {
                    $this->aauth_db->where('id', $cookie[0]);
                    $this->aauth_db->where('remember_exp', $cookie[1]);
                    $query = $this->aauth_db->get($this->config_vars['users']);

                    $row = $query->row();

                    if ($query->num_rows() < 1)
                    {
                        $this->update_remember($cookie[0]);
                        return false;
                    }
                    else
                    {

//                        Console::log('from remember me');
                        if (strtotime($row->remember_time) > strtotime("now"))
                        {
                            $this->login_fast($cookie[0]);
                            return true;
                        }
                        // if time is expired
                        else
                        {
                            return false;
                        }
                    }
                }
            }
        }
        return false;
    }

    //tested

    /**
     * Logout user
     * Destroys the CodeIgniter session and remove cookies to log out user.
     * @return bool If session destroy successful
     */
    public function logout()
    {
        $cookie = array(
                'name'	 => 'user',
                'value'	 => '',
                'expire' => -3600,
                'path'	 => '/',
        );
        $this->CI->input->set_cookie($cookie);
        
        
        return $this->CI->session->sess_destroy();
    }

    //tested

    /**
     * Fast login
     * Login with just a user id
     * @param int $user_id User id to log in
     * @return bool TRUE if login successful.
     */
    public function login_fast($user_id)
    {
        $this->aauth_db->where('id', $user_id);
        $this->aauth_db->where('banned', 0);
        $query = $this->aauth_db->get($this->config_vars['users']);

        $row = $query->row();

        if ($query->num_rows() > 0)
        {

            // if id matches
            // create session
            $data = array(
                'id' => $row->id,
                'username' => $row->username,
                'loggedin' => true
            );

            $this->CI->session->set_userdata($data);
            return true;
        }
        return false;
    }

    //tested

    /**
     * Update last login
     * Update user's last login date
     * @param int|bool $user_id User id to update or FALSE for current user
     * @return bool Update fails/succeeds
     */
    public function update_last_login($user_id = false)
    {
        if ($user_id == false)
            $user_id = $this->CI->session->userdata('id');

        $data['last_login'] = date("Y-m-d H:i:s");

        $this->aauth_db->where('id', $user_id);
        return $this->aauth_db->update($this->config_vars['users'], $data);
    }
    
    /**
     * Update remember
     * Update amount of time a user is remembered for
     * @param int $user_id User id to update
     * @param int $expression
     * @param int $expire
     * @return bool Update fails/succeeds
     */
    public function update_remember($user_id, $expression=null, $expire=null) {

        $data['remember_time'] = $expire;
        $data['remember_exp'] = $expression;

        $this->aauth_db->where('id',$user_id);
        return $this->aauth_db->update($this->config_vars['users'], $data);
    }

    //tested

    /**
     * Create user
     * Creates a new user
     * @param string $username User's username
     * @return int|bool False if create fails or returns user id if successful
     */
    public function create_user($username, $pass)
    {
        $valid = true;

        if (empty($username))
        {
            $valid = false;
        }

        if ($valid && $this->user_exist_by_username($username))
        {
            $valid = false;
        }

        if (!$valid)
        {
            return false;
        }

        $data = array(
            'pass' => $this->hash_password($pass),
            'username' => $username,
            'date_created' => date("Y-m-d H:i:s"),
        );

        if ($this->aauth_db->insert($this->config_vars['users'], $data))
        {
            $user_id = $this->aauth_db->insert_id();
            
             // set default group
            $this->add_member($user_id, $this->config_vars['default_group_id']);
            

            $this->reset_cache();
            
            return $user_id;
        } 
        else
        {
            return false;
        }
    }

    //tested

    /**
     * Update user
     * Updates existing user details
     * @param int $user_id User id to update
     * @param string|bool $username User's name, or FALSE if not to be updated
     * @param string|bool $pass User's password, or FALSE if not to be updated
     * @return bool Update fails/succeeds
     */
    public function update_user($user_id, $username = false, $pass = false)
    {
        $data = array();
        $valid = true;
        $user = $this->get_user($user_id);

        if ($pass != false)
        {
            $data['pass'] = $this->hash_password($pass, $user_id);
        }

        if ($user->username == $username)
        {
            $username = false;
        }

        if ($username != false)
        {
            if ($this->user_exist_by_username($username))
            {
                $valid = false;
            }
            $data['username'] = $username;
        }

        if (!$valid || empty($data))
        {
            return false;
        }

        $this->aauth_db->where('id', $user_id);
        return $this->aauth_db->update($this->config_vars['users'], $data);
    }

    //tested

    /**
     * List users
     * Return users as an object array
     * @param bool|int $group_par Specify group id to list group or FALSE for all users
     * @param string $limit Limit of users to be returned
     * @param bool $offset Offset for limited number of users
     * @param bool $include_banneds Include banned users
     * @return array Array of users
     */
    public function list_users($group_id = false, $limit = false, $offset = false, $include_banneds = false)
    {

        // if group_par is given
        if ($group_id != false)
        {

//            $group_par = $this->get_group_id($group_par);
            $this->aauth_db->select('*')
                    ->from($this->config_vars['users'])
                    ->join($this->config_vars['user_to_group'], $this->config_vars['users'] . ".id = " . $this->config_vars['user_to_group'] . ".user_id")
                    ->where($this->config_vars['user_to_group'] . ".group_id", $group_id);

            // if group_par is not given, lists all users
        } else
        {
            $this->aauth_db->select('*')
                    ->from($this->config_vars['users']);
        }

        // banneds
        if (!$include_banneds)
        {
            $this->aauth_db->where('banned != ', 1);
        }

        $this->aauth_db->order_by('username ASC');

        // limit
        if ($limit)
        {

            if ($offset == false)
                $this->aauth_db->limit($limit);
            else
                $this->aauth_db->limit($limit, $offset);
        }

        $query = $this->aauth_db->get();

        return $query->result();
    }

    //tested

    /**
     * Get user
     * Get user information
     * @param int|bool $user_id User id to get or FALSE for current user
     * @return object User information
     */
    public function get_user($user_id = false)
    {
        if ($user_id == false)
            $user_id = $this->CI->session->userdata('id');

        $query = $this->aauth_db->where('id', $user_id);
        $query = $this->aauth_db->get($this->config_vars['users']);

        if ($query->num_rows() <= 0)
        {
            return false;
        }
        return $query->row();
    }

    //not tested excatly

    /**
     * Delete user
     * Delete a user from database. WARNING Can't be undone
     * @param int $user_id User id to delete
     * @return bool Delete fails/succeeds
     */
    public function delete_user($user_id)
    {
        $this->aauth_db->where('id', $user_id);
        $this->aauth_db->delete($this->config_vars['users']);
        
        $this->reset_cache();

        return $this->aauth_db->affected_rows() === 1;
    }

    //tested

    /**
     * Ban user
     * Bans a user account
     * @param int $user_id User id to ban
     * @return bool Ban fails/succeeds
     */
    public function ban_user($user_id)
    {

        $data = array(
            'banned' => 1
        );

        $this->aauth_db->where('id', $user_id);

        return $this->aauth_db->update($this->config_vars['users'], $data);
    }

    //tested

    /**
     * Unban user
     * Activates user account
     * Same with unlock_user()
     * @param int $user_id User id to activate
     * @return bool Activation fails/succeeds
     */
    public function unban_user($user_id)
    {

        $data = array(
            'banned' => 0
        );

        $this->aauth_db->where('id', $user_id);

        return $this->aauth_db->update($this->config_vars['users'], $data);
    }

    //tested

    /**
     * Check user banned
     * Checks if a user is banned
     * @param int $user_id User id to check
     * @return bool False if banned, True if not
     */
    public function is_banned($user_id)
    {

        if (!$this->user_exist_by_id($user_id))
        {
            return true;
        }

        $query = $this->aauth_db->where('id', $user_id);
        $query = $this->aauth_db->where('banned', 1);

        $query = $this->aauth_db->get($this->config_vars['users']);

        if ($query->num_rows() > 0)
            return true;
        else
            return false;
    }

    /**
     * user_exist_by_username
     * Check if user exist by username
     * @param $user_id
     *
     * @return bool
     */
    public function user_exist_by_username($name)
    {
        $query = $this->aauth_db->where('username', $name);

        $query = $this->aauth_db->get($this->config_vars['users']);

        if ($query->num_rows() > 0)
            return true;
        else
            return false;
    }

    /**
     * user_exist_by_id
     * Check if user exist by user email
     * @param $user_email
     *
     * @return bool
     */
    public function user_exist_by_id($user_id)
    {
        $query = $this->aauth_db->where('id', $user_id);

        $query = $this->aauth_db->get($this->config_vars['users']);

        if ($query->num_rows() > 0)
            return true;
        else
            return false;
    }

    /**
     * Get user id
     * Get user id from email address, if par. not given, return current user's id
     * @param string|bool $email Email address for user
     * @return int User id
     */
    public function get_user_id($username = false)
    {

        if (!$username)
        {
            $query = $this->aauth_db->where('id', $this->CI->session->userdata('id'));
        } else
        {
            $query = $this->aauth_db->where('username', $username);
        }

        $query = $this->aauth_db->get($this->config_vars['users']);

        if ($query->num_rows() <= 0)
        {
//			$this->error($this->CI->lang->line('aauth_error_no_user'));
            return false;
        }
        return $query->row()->id;
    }

    /**
     * Get user groups
     * Get groups a user is in
     * @param int|bool $user_id User id to get or FALSE for current user
     * @return array Groups
     */
    public function get_user_group_ids($user_id = false)
    {
        $this->_precache();
        
        if (!$user_id)
        {
            $user_id = $this->CI->session->userdata('id');
        }
        
        if (!$user_id)
        {            
            return array($this->config_vars['public_group_id']);
        } 
        else if ($user_id)
        {
            $rows = $this->_search_cache_pair($this->cache_user_to_group, $user_id, null, false); //search mutliple
            
            return array_column($rows, 1);
        }
    }

    /**
     * Get user permissions
     * Get user permissions from user id ( ! Case sensitive)
     * @param int|bool $user_id User id to get or FALSE for current user
     * @return int Group id
     */
    public function get_user_perms($user_id = false)
    {
        $this->_precache();
        
        if (!$user_id)
        {
            $user_id = $this->CI->session->userdata('id');
        }

        $res = array();
        if ($user_id)
        {
            $rows = $this->_search_cache_pair($this->cache_perm_to_user, null, $user_id, false); //search mutliple

            
            $flipped = array_flip($this->cache_perm_id);

            $res = array();
            foreach ($rows as $row)
            {
                $res[] = $flipped[$row[0]];
            }

            return $res;
        }

        return $res;
    }

    //tested

    /**
     * Hash password
     * Hash the password for storage in the database
     * (thanks to Jacob Tomlinson for contribution)
     * @param string $pass Password to hash
     * @return string Hashed password
     */
    function hash_password($pass)
    {
        return password_hash($pass, PASSWORD_DEFAULT);
    }

    /**
     * Verify password
     * Verfies the hashed password
     * @param string $password Password
     * @param string $hash Hashed Password
     * @return bool False or True
     */
    function verify_password($password, $hash)
    {
        return password_verify($password, $hash);
    }

    ########################
    # Group Functions
    ########################
    //tested

    /**
     * Create group
     * Creates a new group
     * @param string $group_name New group name
     * @param string $definition Description of the group
     * @return int|bool Group id or FALSE on fail
     */
    public function create_group($group_name)
    {
        $query = $this->aauth_db->get_where($this->config_vars['groups'], array('name' => $group_name));

        if ($query->num_rows() < 1)
        {
            $data = array(
                'name' => $group_name
            );
            $this->aauth_db->insert($this->config_vars['groups'], $data);
            
            $this->reset_cache();
            
            return $this->aauth_db->insert_id();
        }

        return false;
    }

    //tested

    /**
     * Update group
     * Change a groups name
     * @param int $group_id Group id to update
     * @param string $group_name New group name
     * @return bool Update success/failure
     */
    public function update_group($group_id, $group_name = false)
    {
//        $group_id = $this->get_group_id($group_par);

        if ($group_name != false)
        {
            $data['name'] = $group_name;
        }

        $this->aauth_db->where('id', $group_id);
        return $this->aauth_db->update($this->config_vars['groups'], $data);
    }

    //tested

    /**
     * Delete group
     * Delete a group from database. WARNING Can't be undone
     * @param int $group_id User id to delete
     * @return bool Delete success/failure
     */
    public function delete_group($group_id)
    {

        $this->aauth_db->where('id', $group_id);
        $this->aauth_db->delete($this->config_vars['groups']);
        
        $this->reset_cache();

        return $this->aauth_db->affected_rows() === 1;
    }

    //tested

    /**
     * Add member
     * Add a user to a group
     * @param int $user_id User id to add to group
     * @param int|string $group_par Group id or name to add user to
     * @return bool Add success/failure
     */
    public function add_member($user_id, $group_id)
    {
//        $group_id = $this->get_group_id($group_par);

        if (!$group_id)
        {

            return false;
        }

        $this->aauth_db->where('user_id', $user_id);
        $this->aauth_db->where('group_id', $group_id);
        $query = $this->aauth_db->get($this->config_vars['user_to_group']);

        if ($query->num_rows() < 1)
        {
            $data = array(
                'user_id' => $user_id,
                'group_id' => $group_id
            );

            $ret =  $this->aauth_db->insert($this->config_vars['user_to_group'], $data);
            $this->reset_cache();
            
            return $ret;
        }
        return true;
    }

    //tested

    /**
     * Remove member
     * Remove a user from a group
     * @param int $user_id User id to remove from group
     * @param int|string $group_par Group id or name to remove user from
     * @return bool Remove success/failure
     */
    public function remove_member($user_id, $group_id)
    {
//        $group_par = $this->get_group_id($group_par);
        $this->aauth_db->where('user_id', $user_id);
        $this->aauth_db->where('group_id', $group_id);
        $ret = $this->aauth_db->delete($this->config_vars['user_to_group']);
        
        $this->reset_cache();
        
        return $ret;
    }

    //tested

    /**
     * Remove member
     * Remove a user from all groups
     * @param int $user_id User id to remove from all groups
     * @return bool Remove success/failure
     */
    public function remove_member_from_all($user_id)
    {
        $this->aauth_db->where('user_id', $user_id);
        $ret = $this->aauth_db->delete($this->config_vars['user_to_group']);
        
        $this->reset_cache();
        
        return $ret;
    }

    //tested

    /**
     * Is member
     * Check if current user is a member of a group
     * @param int|string $group_par Group id or name to check
     * @param int|bool $user_id User id, if not given current user
     * @return bool
     */
    public function is_member($group_id, $user_id = false)
    {
        $this->_precache();
        
        // if user_id FALSE (not given), current user
        if (!$user_id)
        {
            $user_id = $this->CI->session->userdata('id');
        }
        
        if (!$user_id)
        {
            return false;
        }
        
        $allowed = $this->_search_cache_pair($this->cache_user_to_group, $user_id, $group_id); //find first

        if ($allowed)
        {
            return true;
        } 
        else
        {
            return false;
        }
    }

    //tested

    /**
     * Is admin
     * Check if current user is a member of the admin group
     * @param int $user_id User id to check, if it is not given checks current user
     * @return bool
     */
    public function is_admin($user_id = false)
    {
        return $this->is_member($this->config_vars['admin_group_id'], $user_id);
    }

    //tested

    /**
     * List groups
     * List all groups
     * @return object Array of groups
     */
    public function list_groups()
    {
        $this->aauth_db->order_by('name ASC');
        $query = $this->aauth_db->get($this->config_vars['groups']);
        return $query->result();
    }

    //tested

    /**
     * Get group name
     * Get group name from group id
     * @param int $group_id Group id to get
     * @return string Group name
     */
    public function get_group_name($group_id)
    {
        $query = $this->aauth_db->where('id', $group_id);
        $query = $this->aauth_db->get($this->config_vars['groups']);

        if ($query->num_rows() == 0)
            return false;

        $row = $query->row();
        return $row->name;
    }

    //tested

    /**
     * Get group id
     * Get group id from group name or id ( ! Case sensitive)
     * @param int|string $group_par Group id or name to get
     * @return int Group id
     */
//    public function get_group_id($group_name)
//    {
//        $query = $this->aauth_db->where('name', $group_name);
//        $query = $this->aauth_db->get($this->config_vars['groups']);
//
//        if ($query->num_rows() == 0)
//            return false;
//
//        $row = $query->row();
//        return $row->id;
//        
        
//        if (is_numeric($group_par))
//        {
//            return $group_par;
//        }
//
//        $key = str_replace(' ', '', trim(strtolower($group_par)));

//        if (isset($this->cache_group_id[$key]))
//        {
//            return $this->cache_group_id[$key];
//        } else
//        {
//            return false;
//        }
//    }

    /**
     * Get group
     * Get group from group name or id ( ! Case sensitive)
     * @param int|string $group_par Group id or name to get
     * @return int Group id
     */
    public function get_group($group_id)
    {
//        if ($group_id = $this->get_group_id($group_par))
//        {
            $this->aauth_db->where('id', $group_id);
            $query = $this->aauth_db->get($this->config_vars['groups']);

            if ($query->num_rows() != 0)
            {
                return $query->row();
            }

        return false;
    }

    /**
     * Get group permissions
     * Get group permissions from group name or id ( ! Case sensitive)
     * @param int|string $group_par Group id or name to get
     * @return int Group id
     */
//    public function get_group_perm_names($group_par)
//    {
//        if ($group_id = $this->get_group_id($group_par))
//        {
//            $rows = $this->_search_cache_pair($this->cache_perm_to_group, null, $group_id, false);
//            
//            
//            $query = $this->aauth_db->select($this->config_vars['perms'] . '.*');
//            $query = $this->aauth_db->where('group_id', $group_id);
//            $query = $this->aauth_db->join($this->config_vars['perms'], $this->config_vars['perms'] . '.id = ' . $this->config_vars['perm_to_group'] . '.perm_id');
//            $query = $this->aauth_db->get($this->config_vars['perm_to_group']);
//
//            return $query->result();
//        }
//
//        return false;
//    }

    ########################
    # Permission Functions
    ########################
    //tested

    /**
     * Create permission
     * Creates a new permission type
     * @param string $perm_name New permission name
     * @param string $definition Permission description
     * @return int|bool Permission id or FALSE on fail
     */
    public function create_perm($perm_name)
    {
        $query = $this->aauth_db->get_where($this->config_vars['perms'], array('name' => $perm_name));

        if ($query->num_rows() < 1)
        {
            $data = array(
                'name' => $perm_name
            );
            $this->aauth_db->insert($this->config_vars['perms'], $data);
            $inserted_id = $this->aauth_db->insert_id();
            $this->reset_cache();
            return $inserted_id;
        }
        return false;
    }

    //tested

    /**
     * Update permission
     * Updates permission name and description
     * @param int|string $perm_par Permission id or permission name
     * @param string $perm_name New permission name
     * @param string $definition Permission description
     * @return bool Update success/failure
     */
    public function update_perm($perm_id, $perm_name = false)
    {

//        $perm_id = $this->get_perm_id($perm_par);

        if ($perm_name != false)
        {
            $data['name'] = $perm_name;
        }


        $this->aauth_db->where('id', $perm_id);
        $ret = $this->aauth_db->update($this->config_vars['perms'], $data);
        
        $this->reset_cache();
        
        return $ret;
    }

    //not ok

    /**
     * Delete permission
     * Delete a permission from database. WARNING Can't be undone
     * @param int|string $perm_par Permission id or perm name to delete
     * @return bool Delete success/failure
     */
    public function delete_perm($perm_name)
    {
//        $perm_id = $this->get_perm_id($perm_par);

        $this->aauth_db->where('name', $perm_name);
        $this->aauth_db->delete($this->config_vars['perms']);

        $this->reset_cache();
        
        return $this->aauth_db->affected_rows() === 1;
    }
    
    //used to delete mulplie
    public function delete_multiple_perms($perm_names)
    {

        $this->aauth_db->where_in('name', $perm_names);
        $this->aauth_db->delete($this->config_vars['perms']);

        $this->reset_cache();
    }

    /**
     * List Group Permissions
     * List all permissions by Group
     * @param int $group_par Group id or name to check
     * @return object Array of permissions
     */
    public function list_group_perms($group_id)
    {
        $this->_precache();
        
        $rows = $this->_search_cache_pair($this->cache_perm_to_group, null, $group_id, false); //find first
        
        $flipped = array_flip($this->cache_perm_id);
        
        $res = array();
        foreach ($rows as $row)
        {
            
            $res[] = $flipped[$row[0]];
        }
        
        return $res;
    }

    /**
     * Is user allowed
     * Check if user allowed to do specified action, admin always allowed
     * first checks user permissions then check group permissions
     * @param int $perm_par Permission id or name to check
     * @param int|bool $user_id User id to check, or if FALSE checks current user
     * @return bool
     */
    public function is_allowed($perm_par, $user_id = false)
    {
        
        if ($user_id == false)
        {
            $user_id = $this->CI->session->userdata('id');
        }

        if ($this->is_admin($user_id))
        {
            return true;
        }

        if (!$perm_id = $this->get_perm_id($perm_par))
        {
            return false;
        }

        
        $this->_precache();
        
        $allowed = false;
        if ($user_id) //can be null
        {
            $allowed = $this->_search_cache_pair($this->cache_perm_to_user, $perm_id, $user_id); //find first
        }

        if ($allowed)
        {
            return true;
        } 
        else
        {
            $g_allowed = false;
            foreach ($this->get_user_group_ids($user_id) as $group_id)
            {
                if ($this->is_group_allowed($perm_id, $group_id))
                {
                    $g_allowed = true;
                    break;
                }
            }
            return $g_allowed;
        }
    }

    /**
     * Is Group allowed
     * Check if group is allowed to do specified action, admin always allowed
     * @param int $perm_par Permission id or name to check
     * @param int|string|bool $group_par Group id or name to check, or if FALSE checks all user groups
     * @return bool
     */
    public function is_group_allowed($perm_par, $group_id = false)
    {
        $this->_precache();
        
        $perm_id = $this->get_perm_id($perm_par);

        if ($group_id != false) //group id given
        {
            // if group is admin group, as admin group has access to all permissions
            if ($group_id == 1)
            {
                return true;
            }

            $row = $this->_search_cache_pair($this->cache_perm_to_group, $perm_id, $group_id);

            $g_allowed = false;

            if ($row)
            {
                $g_allowed = true;
            }
            return $g_allowed;
        }
        // if group par is not given
        // checks current user's all groups
        else
        {
            // if public is allowed or he is admin
            if ($this->is_admin($this->CI->session->userdata('id')) OR
                    $this->is_group_allowed($perm_id, $this->config_vars['public_group']))
            {
                return true;
            }

            // if is not login
            if (!$this->is_loggedin())
            {
                return false;
            }

            $group_ids = $this->get_user_group_ids();
            foreach ($group_ids as $g_id)
            {
                if ($this->is_group_allowed($perm_id, $g_id))
                {
                    return true;
                }
            }
            return false;
        }
    }

    //tested

    /**
     * Allow User
     * Add User to permission
     * @param int $user_id User id to deny
     * @param int $perm_par Permission id or name to allow
     * @return bool Allow success/failure
     */
    public function allow_user($user_id, $perm_par)
    {
        $perm_id = $this->get_perm_id($perm_par);

        if (!$perm_id)
        {
            return false;
        }

        $query = $this->aauth_db->where('user_id', $user_id);
        $query = $this->aauth_db->where('perm_id', $perm_id);
        $query = $this->aauth_db->get($this->config_vars['perm_to_user']);

        // if not inserted before
        if ($query->num_rows() < 1)
        {

            $data = array(
                'user_id' => $user_id,
                'perm_id' => $perm_id
            );
            $ret = $this->aauth_db->insert($this->config_vars['perm_to_user'], $data);
            $this->reset_cache();
            return $ret;
        }
        return true;
    }

    //tested

    /**
     * Deny User
     * Remove user from permission
     * @param int $user_id User id to deny
     * @param int $perm_par Permission id or name to deny
     * @return bool Deny success/failure
     */
    public function deny_user($user_id, $perm_par)
    {

        $perm_id = $this->get_perm_id($perm_par);

        $this->aauth_db->where('user_id', $user_id);
        $this->aauth_db->where('perm_id', $perm_id);

        $ret = $this->aauth_db->delete($this->config_vars['perm_to_user']);
        
        $this->reset_cache();
        return $ret;
    }

    //tested

    /**
     * Allow Group
     * Add group to permission
     * @param int|string|bool $group_par Group id or name to allow
     * @param int $perm_par Permission id or name to allow
     * @return bool Allow success/failure
     */
    public function allow_group($group_id, $perm_name)
    {
        $perm_id = $this->get_perm_id($perm_name);

        if (!$perm_id)
        {
            return false;
        }

//        $group_id = $this->get_group_id($group_par);

        if (!$group_id)
        {
            return false;
        }

        $query = $this->aauth_db->where('group_id', $group_id);
        $query = $this->aauth_db->where('perm_id', $perm_id);
        $query = $this->aauth_db->get($this->config_vars['perm_to_group']);

        if ($query->num_rows() < 1)
        {

            $data = array(
                'group_id' => $group_id,
                'perm_id' => $perm_id
            );

            $ret =  $this->aauth_db->insert($this->config_vars['perm_to_group'], $data);
            $this->reset_cache();
            
            return $ret;
        }

        return true;
    }

    //tested

    /**
     * Deny Group
     * Remove group from permission
     * @param int|string|bool $group_par Group id or name to deny
     * @param int $perm_par Permission id or name to deny
     * @return bool Deny success/failure
     */
    public function deny_group($group_id, $perm_name)
    {

        $perm_id = $this->get_perm_id($perm_name);
//        $group_id = $this->get_group_id($group_par);

        $this->aauth_db->where('group_id', $group_id);
        $this->aauth_db->where('perm_id', $perm_id);
        
        

        $ret = $this->aauth_db->delete($this->config_vars['perm_to_group']);
        
        $this->reset_cache();
        return $ret;
    }

    //tested

    /**
     * List Permissions
     * List all permissions
     * @return object Array of permissions
     */
    public function list_perms()
    {
        $res = array();
        $this->aauth_db->order_by('name ASC');
        $query = $this->aauth_db->get($this->config_vars['perms']);
        
        foreach ($query->result() as $row)
        {
            $res[] = $row->name;
        }
        return $res;
    }

    
    
    //tested
    /**
     * Get permission id
     * Get permission id from permisison name or id
     * @param int|string $perm_par Permission id or name to get
     * @return int Permission id or NULL if perm does not exist
     */
    public function get_perm_id($perm_name)
    {
        $this->_precache();
        
        if (is_numeric($perm_name))
        {
            return $perm_name;
        }

        $key = $perm_name;
        if (isset($this->cache_perm_id[$key]))
        {
            return $this->cache_perm_id[$key];
        } 
        else
        {
            return false;
        }
    }


}