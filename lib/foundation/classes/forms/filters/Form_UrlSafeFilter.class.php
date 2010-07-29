<?php
/**
 * Make the value safe to use in URLs
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form_UrlSafeFilter extends Form_Filter{
  public function filter($value){
    return urlencode($value);
  }
}
?>
