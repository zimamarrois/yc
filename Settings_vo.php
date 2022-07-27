<?php
/**
 * @author flexphperia
 *
 */
class Settings_vo extends Vo_base{

    protected  $_exlude_send = array('last_version_check');
    
    protected static $_exlude_validation_keys =  array('last_version_check');

    public $show_empty_rows;
    public $browse_month_num;
    public $disable_filtering;
    public $editor_month_num;
    public $editor_hide_tips;
    
    public $hide_login_button;
    public $hide_header;
    public $edit_one_only;
    public $logo;
    public $title;
    public $language;
    
    public $last_version_check;
    

    public function __construct($data = null)
    {
        parent::__construct($data);
        
        // cast to integers, beeter performance than setters
        $this->show_empty_rows = (int) $this->show_empty_rows;
        $this->browse_month_num = (int) $this->browse_month_num;
        $this->disable_filtering = (int) $this->disable_filtering;
        $this->editor_month_num = (int) $this->editor_month_num;
        $this->editor_hide_tips = (int) $this->editor_hide_tips;
        
        $this->hide_login_button = (int) $this->hide_login_button;
        $this->edit_one_only = (int) $this->edit_one_only;
        
        $this->last_version_check = (int) $this->last_version_check;

    }    
}