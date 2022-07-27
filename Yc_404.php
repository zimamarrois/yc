<?php
/**
 * 404 controller
 * 
 * @author flexphperia.net
 */
class Yc_404 extends MY_Controller {

        public function index()
        {
            return $this->show_404();
//            $data = array(
//                'title' => '404'
//            );
//
//            $this->load->view('parts/header', $data);
//            $this->load->view('errors/html/error_404');
//            $this->load->view('parts/footer'); 
        }
}
