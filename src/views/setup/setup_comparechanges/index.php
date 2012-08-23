<?php
/**
 * setup_comparechanges index view
 *
 */
?>
<noscript>This Page Requires javascript.  Please consult your department IT support for help enabling Javascript in your browser.</noscript>
<div id='ajaxstatus'></div>
<div id='canvas'>
  <ul>
    <?php foreach($cycles as $arr){?>
    <li>
      <a href="<?php print $this->path('setup/comparechanges/compare/' . $arr['id']); ?>"><?php print $arr['name'];?></a>
    </li>
    <?php } ?>
  </ul>
  <div id='comparison'></div>
</div>