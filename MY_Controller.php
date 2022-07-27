<?php
require APPPATH."third_party/MX/Controller.php";

/**
 * Extends default controller 
 *
 * @author flexphperia.net
 */
class MY_Controller extends MX_Controller{
    
    //this is passed into view too
    public $settings_vo;
    public $path;
    
    //array of js scripts and styles registered to add at body end
    private $_scripts = array();
    private $_styles = array();
    private $_append_to_body = '';
   
    
    public function __construct()
    {
        parent::__construct();
        
        //db is loaded in hook
        $this->load->library('aauth');
        //do not do this by autoloading, demo hook override need this to be here

        $this->load->model('items_model');
        $this->load->model('item_types_model');
        
        $this->settings_vo = $this->settings_model->load();
        $this->path = $this->router->fetch_class().'/'.$this->router->fetch_method();
        
        if(ENVIRONMENT == 'development')
        {
            $this->load->library('console');
            $this->output->enable_profiler(TRUE);
        }
        $this->load->library('user_agent');
        if ($this->agent->browser() == 'Internet Explorer')
        {
            $data = array(
               'title' => 'IE',
                'no_js' => true,
            );

            $this->load->view('parts/header', $data);
            $this->load->view('pages/oldbrowser');
            $this->load->view('parts/footer', $data);

            $this->output->_display();
            die;
        }

        $check_version = false;
        //will relog if rememeber me
        if ($this->aauth->is_loggedin()){
            
            //if older than 30 days
            if ( time() - $this->settings_vo->last_version_check > 30 * 24 * 60 * 60 )
            {
                $check_version = true;
                $this->settings_vo->last_version_check = time();

                ///update settings
                $this->settings_model->save($this->settings_vo);
            }
        }
        
        $this->check_version = $check_version;
    }
    
    /**
     * Register js script to render at body end. 
     * Store by name, to ommit duplicates
     * 
     * @param string $name
     * @param string $file_path
     */
    public function register_script($name, $file_path)
    {
        if ($name)
        {
            $this->_scripts[$name] = $file_path;
        }
        else
        {
            $this->_scripts[] = $file_path;
        }
    }
    
    public function unregister_script($name)
    {
        unset($this->_scripts[$name]);
    }
    
    /**
     * Renders registred scripts
     * 
     */
    public function render_scripts()
    {
        foreach ($this->_scripts as $path) {
            ?>
            <script src="<?php echo $path; ?>" ></script>
            <?php
        }
    }
    
    
    /**
     * Register css to render at header. 
     * Store by name, to ommit duplicates
     * 
     * @param string $name
     * @param string $file_path
     */
    public function register_style($name, $file_path)
    {
        if ($name)
        {
            $this->_styles[$name] = $file_path;
        }
        else
        {
            $this->_styles[] = $file_path;
        }
    }
    
    public function unregister_style($name)
    {
        unset($this->_styles[$name]);
    }
    
    /**
     * Renders registred scripts
     * 
     */
    public function render_styles()
    {
        foreach ($this->_styles as $path) {
            ?>
            <link rel="stylesheet" href="<?php echo $path; ?>" /> 
            <?php
        }
    }
    
    /*
     * Checks permissions and if not logged, redirects to login page, if logged, shows 403
     */
     public function html_auth_check($perms, $operator = null)
     {
        if ($this->aauth->is_loggedin() && !can($perms, $operator))
        {
            $this->show_403();
            $this->output->_display();
            exit;
        }
        else if (!can($perms, $operator)){
            redirect('login');
        }
     }
     
     
     public function json_auth_check($perm)
     {
        if (!can($perm))
        {
            $this->output->json_forbidden();
            $this->output->_display();
            exit;
        }
     }
    
    
    public function show_403()
    {
        $data = array(
            'title' => '403',
            'is_error_page' => true
        );

        $this->load->view('parts/header', $data);
        $this->load->view('errors/html/error_403');
        $this->load->view('parts/footer'); 
    }
    
    
    public function show_404()
    {
        $data = array(
            'title' => '404',
            'is_error_page' => true
        );

        $this->load->view('parts/header', $data);
        $this->load->view('errors/html/error_404');
        $this->load->view('parts/footer'); 
    }
    
    
    public function append_to_body($html)
    {
        $this->_append_to_body .= $html;
    }
    
    public function render_append_to_body()
    {
        echo $this->_append_to_body;
    }

    
}