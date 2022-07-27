<?php

/**
 * @author flexphperia
 *
 */
class User_perms_vo extends Vo_base
{

    protected static $_exlude_validation_keys = array('username');
    public $id;
    public $username;
    public $groups = array();
    public $perms = array();

    public function __construct($data = null)
    {
        parent::__construct($data);

        // cast to integers, beeter performance than setters
        $this->id = (int) $this->id;
        
        foreach ($this->groups as &$group)
        {
            $group = new Group_vo($group);
        }
    }

}
