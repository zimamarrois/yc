<?php
/**
 * This helper extends default file_helper
 */


/**
 * Compresses html output
 * 
 * @param string $html
 * @return string
 */
function compress_html($html)
{    
    return preg_replace(array(
                '/\>[^\S ]+/s',  // strip whitespaces after tags, except space
                '/[^\S ]+\</s',  // strip whitespaces before tags, except space
                '/(\s)+/s'       // shorten multiple whitespace sequences
            ), array(
                    '>',
                    '<',
                    '\\1'
            ), $html);

}

/**
 * Recursive delete folder with subfolders etc
 * 
 * @param string $dir
 * @return boolean
 */
function recursive_delete($dir)
{
    if (is_dir($dir)) { 
        $objects = scandir($dir); 
        foreach ($objects as $object) { 
          if ($object != "." && $object != "..") { 
            if (is_dir($dir."/".$object))
              recursive_delete($dir."/".$object);
            else
              unlink($dir."/".$object); 
          } 
        }
        rmdir($dir); 
      } 
}
