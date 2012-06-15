<?php 
/**
 * setup_publishapplication index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */ 
?>
<h2>Publication Status</h2>
<?php if($published){ ?>
  <p>The application has been published</p>
    <?php if($this->controller->checkIsAllowed('setup_publishapplication', 'unpublish')){ ?>
      <p><a href='<?php print $this->path('setup/publishapplication/unpublish')?>'>Un-Publish Application</a></p>
    <?php }?>
<?php } else { ?>
  <?php if($ready){ ?>
    <p>Your application is ready to be published.</p>
    <?php if($this->controller->checkIsAllowed('setup_publishapplication', 'publish')){ ?>
      <p><a href='<?php print $this->path('setup/publishapplication/publish')?>'>Publish Application</a></p>
    <?php }?>
  <?php } else { ?>
      <p>Your application has the following problems: <ul>
        <?php foreach ($problems as $p){ ?>
          <li><?php print $p;?></li>
        <?php } ?>
      </ul></p>
    <?php if($this->controller->checkIsAllowed('setup_publishapplication', 'publishoverride')){ ?>
      <p><a href='<?php print $this->path('setup/publishapplication/publishoverride')?>'>Ignore problems and publish application</a></p>
    <?php }?>
  <?php } ?>
<?php } ?>