<?php

/**
 * @author flexphperia
 *
 */
class Item_vo extends Vo_base
{

//    protected static $_exlude_validation_keys = array('type');

    protected $_exlude_store = array('type', 'fields');
    public $id;
    public $type;
    public $name;
    public $icon;
    public $fields = array();

    public function __construct($data = null)
    {
        parent::__construct($data);

        // cast to integers, beeter performance than setters
        $this->id = (int) $this->id;
        $this->type = $this->type !== null ? new Item_type_vo($this->type) : $this->type;

//        $this->item_type_id = $this->type ? $this->type->id : (int) $this->item_type_id;

        foreach ($this->fields as &$field)
        {
            $field = new Field_vo($field);
        }
    }

}
