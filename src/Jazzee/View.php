<?php
namespace Jazzee;
/**
 * The Jazzee View class
 * provides a path for adding global functionality to layouts, views, and elements
 */
class View extends \Foundation\VC\View{
  
  protected function path($path){
    return $this->controller->path($path);
  }
}

?>