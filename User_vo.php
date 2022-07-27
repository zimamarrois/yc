<?php
/**
 * @author flexphperia
 *
 */
class User_vo extends Vo_base{

    public $id;
    public $username;
    

    public function __construct($data = null)
    {
        parent::__construct($data);
        
        // cast to integers, beeter performance than setters
        $this->id = (int) $this->id;

    }    
}