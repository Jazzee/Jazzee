<?php
/**
 * Any call to /static looks for a cached file 
 * 
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
class StaticController extends \Jazzee\PageController 
{ 
  public function actionGet($fileName){
    $safeFileName = basename($fileName);
    $file = new \Foundation\Virtual\RealFile($safeFileName, $this->getVarPath() . '/cache/public/' . $safeFileName);
    $file->output();
  }
}
?>