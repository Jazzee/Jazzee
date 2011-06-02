<?php
/**
 * Default Jazzee layout
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
print '<?xml version="1.0" encoding="iso-8859-1"?>' ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
  <title><?php echo $pageTitle ?></title>
  <?php
  foreach($requiredCss as $link => $use){
    if($use) print '<link rel="stylesheet" href="'. $link .'" type="text/css" media="all" charset="utf-8" />' . "\n";
  }
  foreach($requiredJs as $link => $use){
    if($use) print '<script type="text/javascript" src="' . $link . '"></script>' . "\n";
  }
  ?>
  </head>
  <body>
    <div id='doc2' class='yui-t2'>
      <div id='hd'><h1><?php echo $layoutTitle ?></h1></div>
      <div id='bd'>
      <div id='bar' class='yui-b tall'>
      <?php
        if($navigation = $this->controller->getNavigation()){
          $navigation->addClass('v-navigation');
          $this->renderElement('navigation', array('container'=>$navigation));
        }
      ?>
      </div>
        <div id='yui-main'>
          <div id='main' class='yui-b'>
            <div id='topbar'><?php echo $layoutContentTop ?></div>
            <div id='messages'><?php $this->renderElement('messages') ?></div>
            <div id='content' class='tall'><?php echo $layoutContent ?></div>
            <div id='ft'><?php echo $layoutContentFooter ?></div>
          </div> <!-- end main -->
        </div> <!-- end yui-main -->
      </div> <!-- end bd -->
    </div><!-- end doc2 -->
  </body>
</html>