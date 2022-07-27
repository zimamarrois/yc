<?php
/**

 *
 * @author flexphperia.net
 */
class MY_Model extends CI_Model
{
    //storage sorted properly
    protected $_storage = array();
    
    protected $_cache_key = '';
    
    protected $_cache_loaded = false;
    
    
    public function __construct()
    {
        parent::__construct();
        
        //store database name in cache key
        $this->_cache_key = $this->db->database . '_';
        
        $this->load->driver('cache', array('adapter' => 'file'));
    }
    

    public function load_cache()
    {
        if ($this->_cache_loaded)
        {
            return;
        }
        
        $cached_data = $this->cache->get($this->_cache_key);
        
//        Console::log($cached_data);
        
        if ($cached_data !== false)
        {
            $this->_storage = $cached_data; 
        }
        
        $this->_cache_loaded = true;
    }
    
    
    public function save_cache()
    {
        $this->cache->save($this->_cache_key, $this->_storage, 0);
    }
    
    public function reset_storage()
    {
        $this->_storage = array();
        
        if ($this->_cache_key)
        {
//            var_dump($this->_cache_key);
             $this->cache->delete($this->_cache_key);
        }
        
        $this->_cache_loaded = false;
    }
    
    /*
     * We have to clear all cache, all caches are dependand
     */
    public function clear_all_cache()
    {
        $this->load->model('event_types_model');
        $this->event_types_model->reset_storage();
        
        $this->load->model('item_types_model');
        $this->item_types_model->reset_storage();
        
        $this->load->model('items_model');
        $this->items_model->reset_storage();
    }
    
    /**
     * Returns from storage
     * 
     * @param type $ids
     * @return type
     */
    protected function _return_from_storage($ids = null)
    {
        $ret = array();
//        die;
        if ($ids === null)
        {
            return $this->_storage;
        }
        else
        {
            $what_rem = array_diff(array_keys($this->_storage), $ids);
            $ids_found = array_diff(array_keys($this->_storage), $what_rem);
            $ids_not_found = array_diff($ids, $ids_found);
            
            foreach ($ids_found as $id)
            {
                $ret[$id] = $this->_storage[$id];
            }    
            
            foreach ($ids_not_found as $id)
            {
                $ret[$id] = false;
            }   

        }
        
        return $ret;
    }
  
}
