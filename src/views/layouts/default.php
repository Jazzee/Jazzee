<?php
/**
 * Default Jazzee layout
 *
 */
header('Content-Type:text/html; charset=UTF-8');
header('X-FRAME-OPTIONS: SAMEORIGIN');
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

print '<?xml version="1.0" encoding="UTF-8"?>'
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title><?php echo $pageTitle ?></title><?php
    foreach ($requiredCss as $link => $use) {
      if ($use) {
        print '<link rel="stylesheet" href="' . $link . '" type="text/css" media="all" charset="utf-8" />' . "\n";
      }
    }
    foreach ($requiredJs as $link => $use) {
      if ($use) {
        print '<script type="text/javascript" src="' . $link . '"></script>' . "\n";
      }
    }?>
    <!-- Inject the absolute path in the Services prototype so it can be available to other javascript classes  -->
    <script type='text/javascript'>
      Services.prototype.absoluteBasePath = '<?php print $this->path(''); ?>';
    </script>
  </head>
  <body>
    <div id='doc3' class='yui-t2'>
      <div id='hd'><h1><?php echo $layoutTitle ?></h1></div>
      <div id='bd'>
        <div id='bar' class='yui-b tall'>
          <?php
          if ($navigation = $this->controller->getNavigation()) {
            $navigation->addClass('v-navigation');
            $this->renderElement('navigation', array('container' => $navigation));
          }
          ?>
        </div>
        <div id='yui-main'>
          <div id='main' class='yui-b'>
            <div id='topbar'><?php echo $layoutContentTop ?></div>
            <div id='messages'><?php $this->renderElement('messages') ?></div>
            <div id='content' class='tall'><?php echo $layoutContent ?></div>

            <div id='ft'></div>

          </div> <!-- end main -->
        </div> <!-- end yui-main -->
      </div> <!-- end bd -->
    </div><!-- end doc2 -->
  </body>
</html>