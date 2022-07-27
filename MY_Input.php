<?php
/**

 *
 * @author flexphperia.net
 */
class MY_Input extends CI_Input
{

    public function __construct()
    {
        parent::__construct();

        
        if (is_array($_POST))
        {
            $this->_fix_arrays($_POST);
        }

    }
    
        // jquery does not send empty arrays, so when [|empty-array|] string is passed  create empty array from this value
    private function _fix_arrays(&$arr)
    {

        foreach ($arr as $key => &$val)
        {
            if (is_array($val))
            {
                $this->_fix_arrays($val);
            }
            else if ($val == '[|empty-array|]')
            {
                $val = array();
            }
        }
       
    }
}
