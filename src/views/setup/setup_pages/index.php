<?php
/**
 * s index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage admin
 * @subpackage setup
 */
?>
<noscript>This Page Requires javascript.  Please consult your department IT support for help enabling Javascript in your browser.</noscript><?php
if ($published) { ?>
  <p><strong>Warning:</strong> You are editing an application which has already been published.  This can result in applicant data loss.</p><?php
} ?>
<div id='canvas' class='yui-t2'>
  <div id='save'></div>
  <div id='yui-main'>
    <div id='editPage' class='container yui-b'>
      <span id="pageToolbar" class="ui-widget-header ui-corner-all toolbar"></span>
      <div id='pageInfo'></div>
      <div id='workspace'></div>
    </div>
  </div>
  <div class='yui-b' id='pages' class='container'></div>
</div>