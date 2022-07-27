<?php
/**
 * @author flexphperia
 *
 */
class Item_type_vo extends Vo_base{

    protected static $_exlude_validation_keys = array('order');
    
    protected  $_exlude_store = array('order', 'event_types', 'field_types');

    public $id;
    
    public $order;
    public $name;
    public $icons;
    
    public $desc1_label;
    public $desc1_label_show = 0;
    public $desc1_type = 'short';
    
    public $desc2_label;
    public $desc2_label_show = 0;
    public $desc2_type  = 'short';
    public $desc2_disabled = 1; //default
    
    public $desc_show_how  = 'asone';
    
    
    public $event_types = array();
    
    public $field_types = array();
   

    public function __construct($data = null)
    {
        parent::__construct($data);
        
        // cast to integers, beeter performance than setters
        $this->id = (int) $this->id;
        $this->order = (int) $this->order;
        $this->desc1_label_show = (int) $this->desc1_label_show;
        $this->desc2_label_show = (int) $this->desc2_label_show;
        $this->desc2_disabled = (int) $this->desc2_disabled;
        
        foreach ($this->event_types as &$event_type)
        {
            $event_type = new Event_type_vo($event_type);
        }
        
        foreach ($this->field_types as &$field_type)
        {
            $field_type = new Field_type_vo($field_type);
        }

    }    
}