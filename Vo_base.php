<?php

/**
 * Base class for all value objects.
 * 
 * 
 * @author flexphperia
 *
 */
class Vo_base
{

    //property names not stored in storage (JSON, PHP files)
    protected  $_exlude_store = array();
    
    protected  $_exlude_send = array();
    
    //property names excluded from validation keys
    protected static $_exlude_validation_keys = array();
    

    /**
     *
     * @param object $data            
     */
    public function __construct($data = null)
    {
        if (!$data){
            return;
        }

        // fill from data to object
        foreach ($data as $name => $value){
            
            //only set properties that exists
            if ( property_exists($this, $name) )
            {
                $this->$name = $value;
            }
            else
            {
                throw new Exception('Vo property not exists: '. $name);
            }
        }
    }
    
    /**
     * Returns array of properties needed in validation
     * 
     * @return array
     */
    public static function validation_keys() 
    { 
        $reflect = new ReflectionClass(get_called_class());
        $props = $reflect->getProperties(ReflectionProperty::IS_PUBLIC );
        
        $a = array();
        foreach ($props as $prop)
        {
            //late static binding
            if (!in_array ( $prop->name, static::$_exlude_validation_keys ) ) 
            {
                $a[] = $prop->name;
            }
        }
        
        return $a;
    } 
    
    /**
     * Will return array all properties of this object
     * 
     * @return array
     */
    public function to_array()
    {
        $result = get_object_vars($this);

        unset($result['_exlude_store']); //remove
        unset($result['_exlude_send']); //remove
        
        
        $this->_recursive_call($result, 'to_array');
        
        return $result;
    }
    
    public function to_storage_array()
    {
        $result = get_object_vars($this);
        
        //if preaparing for storage unset that parameters that are not needed
        foreach ($this->_exlude_store as $param)
        {
            unset( $result[$param] );
        }
        
        unset($result['_exlude_store']); //remove
        unset($result['_exlude_send']); //remove
        
        $this->_recursive_call($result, 'to_storage_array');
        
        return $result;
    }
    
    public function to_send_array()
    {
        $result = get_object_vars($this);
        
        foreach ($this->_exlude_send as $param)
        {
            unset( $result[$param] );
        }
        
        unset($result['_exlude_store']); //remove
        unset($result['_exlude_send']); //remove
        
        $this->_recursive_call($result, 'to_send_array');

        return $result;
    }
    
    /**
     * Recursivly walk thru array to find what is vo and call its method
     * @param type $array
     * @param type $method
     */
    private function _recursive_call(&$array, $method)
    {
        foreach ($array as $key => &$value)
        {
            if (is_array($value))
            {
                $this->_recursive_call($value, $method);
            }
            else if (is_subclass_of($value, 'Vo_base')) //call method
            {
                $value = $value->{$method}();
            }
        } 
    }
    
    /**
     * Copies all properties and array entries not found in obj_to from obj_From
     * 
     * @param type $obj_to
     * @param type $obj_from
     */
    public function copy_empty($obj_to, $obj_from)
    {
        $reflect = new ReflectionClass($obj_to);
        $props = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
        
        foreach ($props as $prop)
        {
            $prop = $prop->name;
            
            if (  $obj_to->{$prop} === null || (is_array($obj_to->{$prop}) && empty($obj_to->{$prop})) ) 
            {
//                var_dump('copying obj prop: '.$prop . ' prev value was'. $obj_to->{$prop} );
                $obj_to->{$prop} = $obj_from->{$prop};
            }
            else if (is_array($obj_to->{$prop})) 
            {
                $this->_copy_array( $obj_to->{$prop}, $obj_from->{$prop} );
            }
            else if (is_object($obj_to->{$prop})) 
            {
                $this->copy_empty( $obj_to->{$prop}, $obj_from->{$prop} );
            }
            
        }
    }
    
    
    private function _copy_array(&$arr_to, &$arr_from)
    {
        foreach ($arr_to as $key => &$value)
        {
            if ( $value === null  )
            {
//                var_dump('copying arr value: '.$key);
                $value = $arr_from[$key];
            }
            else if (is_array($value)) 
            {
                $this->_copy_array($value, $arr_from[$key] );
            }
            else if (is_object($value)) 
            {
                $this->copy_empty($value, $arr_from[$key] );
            }
        }
    }
    
    
}
