<?php
/**
 * Loads theme files and includes
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
class ResourceController extends Lvc_PageController{
  /**
   * The singleton resource
   * @var Resource
   */
  protected $resource;
  
  protected function beforeAction(){
    $this->resource = Resource::getInstance();
  }
  
  public function actionGet($path){
    $this->resource->output($path);
  }
}
?>