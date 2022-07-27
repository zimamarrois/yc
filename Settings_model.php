<?php
/**
 * @author flexphperia.net
 */
class Settings_model extends CI_Model {
    
    //storage to load settings only once
    public $settings_vo = null;
    
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Load app settings, do it only once
     * 
     * @return Settings_vo
     */
    public function load()
    {
        if ($this->settings_vo === null)
        {
            $query = $this->db->get('yc_settings');
            $row = $query->row_array();
            
            unset($row['id']);
            
            $this->settings_vo = new Settings_vo($row);
        }
        
        return $this->settings_vo;
    }
    
    /**
     * Saves settings to db
     * 
     * @param Settings_vo $vo
     * @return boolean true on success and false on fail
     */
    public function save(Settings_vo $vo)
    {
        $this->settings_vo = null;
        $this->db->update('yc_settings', $vo->to_storage_array(), "id = 1");
        
        return true;
    }
    

    
}
