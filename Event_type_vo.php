<?php
/**
 * @author flexphperia
 *
 */
class Event_type_vo extends Vo_base{


    public $id;
    
    public $name;
    public $icon;
    
    

    public function __construct($data = null)
    {
        parent::__construct($data);
        
        // cast to integers, beeter performance than setters
        $this->id = (int) $this->id;

    }    
}