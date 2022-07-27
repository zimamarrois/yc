<?php

/**
 * @author flexphperia
 *
 */
class Cal_event_vo extends Vo_base
{
    protected static $_exlude_validation_keys = array('order', 'year', 'month', 'day', 'item');
    
    protected  $_exlude_store = array('id', 'year', 'month', 'day', 'item', 'type');
    
    public $id;
    public $order;
    public $year;
    public $month;
    public $day;
    public $is_main = 0;
    public $description1;
    public $description2;
    
    public $item;
    public $type;

    public function __construct($data = null)
    {
        parent::__construct($data);

        // cast to integers, beeter performance than setters
        $this->id = (int) $this->id;
        $this->order = (int) $this->order;
        $this->year = (int) $this->year;
        $this->month = (int) $this->month;
        $this->day = (int) $this->day;
        $this->is_main = (int) $this->is_main;
        
        $this->type = $this->type !== null ? new Event_type_vo($this->type) : $this->type;

    }
    
    public function has_any_description()
    {
        $d1 = $this->description1;
        $d2 = !$this->item->type->desc2_disabled ? $this->description2 : '';

        if ( !empty($d1.$d2) )
        {
            return true;
        }
        
        return false;
    }
    
               

}
