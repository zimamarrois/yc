<?php
/*
 * Library used for manipulationg images,
 * Should not use CI depencities, is used by image script used to display image
 * 
 * @author flexphperia.net
 */
class Image_operator{
    
    
    private $_cache_dir;

    public function __construct()
    {
        //only when used inside CI 
        if (function_exists ( 'get_instance' ) )
        {
           // Assign the CodeIgniter super-object
           $this->CI =& get_instance(); 
        }

        //config_item is used coz image generator script uses this class in its own context without whole CI
        $this->_cache_dir = config_item('yc_storage_path').'cache/'; // where to store the generated re-sized images

    }
    
    /**
     * Generates path to resized image file
     * 
     * @param type $filename
     * @param type $gallery_id
     * @param type $height
     * @param type $watermark
     * @return type
     */
    public function image_cache_filepath($filename, $dir, $width)
    {      
        return $this->_cache_dir.$dir.'/width-'.$width.'/'.$filename;
    }
    

    /**
     * Checks whenever cahe file for an image is valid
     * 
     * @param type $cache_file
     * @param type $filename_path
     * @return boolean
     */
    public function cache_valid($cache_file, $filename_path)
    {
        if (!file_exists($cache_file))
        {
            return false;
        }

        //should we use cached file for specific resolution?
        $use_cached = filemtime($filename_path) <= filemtime($cache_file);
        
        return $use_cached;
    }
    
   
    
    /**
     * Create resized image and saves it in chace dir for gallery
     * 
     * @param type $filename
     * @param type $width
     * @return boolean created or cached file path or false when nothing was created
     */
    public function image_resized_create($filename, $dir, $width)
    {
        $filename_path = config_item('yc_storage_path').$dir.'/'.$filename;
        
        $cache_file = $this->image_cache_filepath(
                    $filename, 
                    $dir,
                    $width
                );
        
        //cache is valid, skip creating thumbnail
        if ( $this->cache_valid($cache_file, $filename_path) )
        {
            return $cache_file;
        }

        $res = $this->_resize_image(
                        $filename_path, 
                        $cache_file, 
                        $width, 
                        true //sharpen
                ); 
        
        if ($res)
        {
            return $cache_file;
        }
        
        return false;
    }
    
    

    
    /**
     * Deletes image from uploads
     * 
     */
    public function delete_upload($filename)
    {
        unlink($this->CI->config->item('yc_uploads_path').$filename);
        $this->_delete_cache($filename, 'uploads');
        
        return true;
    } 
    
        
    /**
     * Deletes image from temps
     * 
     */
    public function delete_temp($filename)
    {
        unlink($this->CI->config->item('yc_temp_path').$filename);
        $this->_delete_cache($filename, 'temp');
        
        return true;
    }   
    
    /**
     * Deletes all temp image files older than 10 minutes
     * 
     */    
    public function delete_old_temp()
    {
        $now = time();

        //find all files in cache 
        foreach (glob($this->CI->config->item('yc_temp_path').'*.{jpg,png,svg}', GLOB_BRACE) as $file)
        {
            if ($now - filemtime($file) >= 600) { // 10 minutes
                unlink($file);
                
                $this->_delete_cache(basename($file), 'temp');
            }
        }
        
        return true;
    } 
    
    /**
     * Deletes image from cache
     * 
     * @param string $filename
     * @return boolean
     */
    private function _delete_cache($filename, $dir)
    {
        //find all files in cache 
        foreach (glob($this->CI->config->item('yc_cache_path').$dir.'/*/*.{jpg,png,svg}', GLOB_BRACE) as $file)
        {
//             var_dump($file);
            if ( basename($file) === $filename ) //if found in that we wanna delete
            {
                unlink($file);
            }
        }
        
        return true;
    }  
    
    
 

    /**
     * Used internally to resize image
     * 
     * @param type $filepath
     * @param type $targetpath
     * @param type $width
     * @param type $sharpen
     * @param type $quality
     * @return boolean if image is created returns true, if not false
     * @throws type
     */
    private function _resize_image($filepath, $targetpath, $width = null, $sharpen = true, $quality = 92)
    {
        $image = new Image( $filepath );
        
        $res = $image->resize($width, null, 'fit', $sharpen);
        
        $res = $image->autorotate() || $res;

        if ($res)
        {   //save only when any operations on image was made
            // does the directory exist already?
            ///get parent dir
            $cache_dir = dirname($targetpath);
            
            if (!is_dir($cache_dir)) 
            { 
                if (!mkdir($cache_dir, 0755, true)) 
                {
                  // check again if it really doesn't exist to protect against race conditions
                  if (!is_dir($cache_dir))
                  {        
                        throw Exception("Failed to create dir: $cache_dir");
                  }
                }
            }

            $image->save($targetpath, $quality);
            return true;
        } 
        
        return false;
    }
     
    
}