<?php
/**
 * @author flexphperia
 *
 */
class User_edit_vo extends Vo_base{

    public $id;
    public $username;
    public $pass_change;
    public $password;
    

    public function __construct($data = null)
    {
        parent::__construct($data);
        
        // cast to integers, beeter performance than setters
        $this->id = !empty($this->id) ? (int) $this->id : null;
        $this->pass_change = (int) $this->pass_change;

    }    
}