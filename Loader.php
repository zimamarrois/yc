<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Modular Extensions - HMVC
 *
 * Adapted from the CodeIgniter Core Classes
 * @link	http://codeigniter.com
 *
 * Description:
 * This library extends the CodeIgniter CI_Loader class
 * and adds features allowing use of modules and the HMVC design pattern.
 *
 * Install this file as application/third_party/MX/Loader.php
 *
 * @copyright	Copyright (c) 2015 Wiredesignz
 * @version 	5.5
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 **/
class MX_Loader extends CI_Loader
{
	public $_ci_cached_vars = array();
        
        public $controller;

	/** Initialize the loader variables **/
	public function initialize($controller = NULL)
	{
            $this->controller = $controller;
//            var_dump($controller);
            parent::initialize();
	}

	public function __get($class)
	{
            return $this->controller->$class;
//            return ($this->controller) ? $this->controller->$class : CI::$APP->$class;
	}

	public function _ci_load($_ci_data)
	{
		extract($_ci_data);

		if (isset($_ci_view))
		{
			$_ci_path = '';

			/* add file extension if not provided */
			$_ci_file = (pathinfo($_ci_view, PATHINFO_EXTENSION)) ? $_ci_view : $_ci_view.'.php';

			foreach ($this->_ci_view_paths as $path => $cascade)
			{
				if (file_exists($view = $path.$_ci_file))
				{
					$_ci_path = $view;
					break;
				}
				if ( ! $cascade) break;
			}
		}
		elseif (isset($_ci_path))
		{

			$_ci_file = basename($_ci_path);
			if( ! file_exists($_ci_path)) $_ci_path = '';
		}

		if (empty($_ci_path))
			show_error('Unable to load the requested file: '.$_ci_file);

		if (isset($_ci_vars))
			$this->_ci_cached_vars = array_merge($this->_ci_cached_vars, (array) $_ci_vars);

		extract($this->_ci_cached_vars);

		ob_start();

		if ((bool) @ini_get('short_open_tag') === FALSE && CI::$APP->config->item('rewrite_short_tags') == TRUE)
		{
			echo eval('?>'.preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', file_get_contents($_ci_path))));
		}
		else
		{
			include($_ci_path);
		}

		log_message('debug', 'File loaded: '.$_ci_path);

		if ($_ci_return == TRUE) return ob_get_clean();

		if (ob_get_level() > $this->_ci_ob_level + 1)
		{
			ob_end_flush();
		}
		else
		{
			CI::$APP->output->append_output(ob_get_clean());
		}
	}
}