<?php
/**
 * Wide layout
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage dusk
 */
header('Content-Type:text/html; charset=UTF-8');
print '<?xml version="1.0" encoding="UTF-8"?>' ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
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
    <div id='doc3'>
      <div id='hd'><h1><?php echo $layoutTitle ?></h1></div>
      <div id='bd'>
          <div id='main'>
            <div id='topbar'>
              <?php
                if($navigation = $this->controller->getNavigation()){
                  $navigation->addClass('h-navigation');
                  $this->renderElement('navigation', array('container'=>$navigation));
                }
              ?>
              <?php echo $layoutContentTop ?>
            </div>
            <div id='messages'><?php $this->renderElement('messages') ?></div>
            <div id='content' class='tall'><?php echo $layoutContent ?></div>
            <div id='ft'><p>This Application has been designed to meet current web standards in xhtml, css, and javascript in order to be accessible to everyone. If you notice a problem with the application or find it inaccessible in any way please let us know.</p></div>
          </div> <!-- end main -->
        </div> <!-- end yui-main -->
      </div> <!-- end bd -->
    </div><!-- end doc3 -->
  </body>
</html>