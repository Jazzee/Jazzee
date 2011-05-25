<?php
/**
 * Any call to /resource gets passed to the virtual file system
 */
class ResourceController extends \Jazzee\Controller 
{ 
  public function actionGet($path){
    $this->_vfs->find($path)->output();
  }
}
?>