<?php
/**
 * Any call to /resource gets passed to the virtual file system
 * 
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
class ResourceController extends \Jazzee\PageController 
{ 
  public function actionGet($path){
    $this->_vfs->find($path)->output();
  }
}
?>