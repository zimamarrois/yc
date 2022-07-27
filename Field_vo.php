<?php

/**
 * @author flexphperia
 *
 */
class Field_vo extends Vo_base
{
    public $id;
    public $type;
    public $value;
    public $options = array();

    public function __construct($data = null)
    {
        parent::__construct($data);

        // cast to integers, beeter performance than setters
        $this->id = (int) $this->id;
        $this->type = $this->type ? new Field_type_vo($this->type) : $this->type;
        $this->options = !$this->options ? array() : $this->options;

    }

}
