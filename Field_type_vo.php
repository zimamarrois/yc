<?php

/**
 * @author flexphperia
 *
 */
class Field_type_vo extends Vo_base
{

    protected static $_exlude_validation_keys = array('order');
    
    protected  $_exlude_send = array('order');
    
    public $id;
    public $order;
    public $type;
    public $label;
    public $label_show;
    public $position;
    public $allow_filtering;
    public $size;

    public function __construct($data = null)
    {
        parent::__construct($data);

        // cast to integers, beeter performance than setters
        $this->id = (int) $this->id;
        $this->label_show = (int) $this->label_show;
        $this->allow_filtering = (int) $this->allow_filtering;
    }

}
