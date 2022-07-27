<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config_aauth = array();

$config_aauth["default"] = array(
 'admin_group_id'                => 1,
 'public_group_id'               => 2,
 'default_group_id'              => 2, //the same as public, to inherite permisssions
 'remember'                     => ' +2 month',

 'users'                          => 'yc_auth_users',
 'groups'                         => 'yc_auth_groups',
 'user_to_group'                  => 'yc_auth_user_to_group',
 'perms'                          => 'yc_auth_perms',
 'perm_to_group'                  => 'yc_auth_perm_to_group',
 'perm_to_user'                   => 'yc_auth_perm_to_user'

);

$config['aauth'] = $config_aauth['default'];

