<?php
/**
 * @author flexphperia
 *
 */
class Group_vo extends Vo_base{

    public $id;
    public $name;
    public $perms = array();


    public function __construct($data = null)
    {
        parent::__construct($data);
        
        // cast to integers, beeter performance than setters
        $this->id = (int) $this->id;

    }    
}