<?php
/**
 * Pull the file contents out and set them as the value
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form_BlobFilter extends Form_Filter{
  public function filter($value){
    if(!is_array($value)) //some other filter might have preprocessed the file already
      return $value;
    if(array_key_exists('tmp_name', $value))
      if(is_uploaded_file($value['tmp_name']) AND $string = file_get_contents($value['tmp_name']))
        return $string;
    
    return null; //failed to get any data from the file
  }
}
?>
