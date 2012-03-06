<?php 
/**
 * Public Error Page
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
?>
<p>The following error occurred while completing your request: <strong><?php echo $message ?></strong></p>
<p>You can try your request again, or <a href="<?php print $this->controller->path('');?>" title='home page'>Start from the home page</a>

