<?php 
/**
 * apply_status view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
// var_dump(((ApplyStatusController::ADMITTED | ApplyStatusController::DECLINED) & $status) == true);
if(!$applicant->locked):?>
  <h2>Application Status: <em>Not Complete</em></h2>
  <p>Your application was not completed before the application deadline and will not be reviewed.</p>
<?php elseif(!$status):?>
  <h2>Application Status: <em>Under Review</em></h2>
<?php elseif((ApplyStatusController::DECLINED & $status) == true):?>
  <h2>Application Status: <em>Declined</em></h2>
<?php elseif((ApplyStatusController::ACCEPTED & $status) == true):?>
  <h2>Application Status: <em>Accepted</em></h2>
<?php elseif((ApplyStatusController::DENIED & $status) == true):?>
  <h2>Application Status: <em>Denied</em></h2>
<?php elseif((ApplyStatusController::ADMITTED & $status) == true):?>
  <h2>Application Status: <em>Admitted</em></h2>
<?php endif;?>

<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum felis ante, malesuada sed auctor non, consequat nec ligula. Etiam adipiscing dui massa. Donec nec magna lacus. Vivamus non erat ante. Maecenas posuere lobortis imperdiet. Maecenas nibh velit, interdum nec cursus a, ornare quis lacus. Quisque tincidunt venenatis porttitor. Vivamus sit amet feugiat purus. Donec eleifend nunc eu mauris imperdiet vitae vestibulum magna molestie. Morbi diam libero, laoreet pellentesque scelerisque quis, imperdiet quis felis. Pellentesque et risus et ligula molestie ultricies eu ut libero. Nam eleifend purus non tellus vulputate ornare. Maecenas suscipit lacus id dolor fringilla vestibulum.</p>

<p>Nunc nisi urna, vestibulum ut pretium sed, laoreet et nisl. In iaculis neque ac metus mollis dictum. Curabitur cursus bibendum turpis id tempus. Nulla tempus pharetra leo id sagittis. Donec vel nulla at risus luctus lacinia in at nisi. Nulla dapibus, sapien id hendrerit vulputate, ipsum elit scelerisque dui, ac tincidunt enim neque eget justo. Proin id justo metus. Etiam elementum scelerisque turpis. Mauris euismod dolor et erat pulvinar tincidunt. Ut lorem neque, ultricies viverra dignissim nec, pretium sit amet nisi. Suspendisse turpis nisi, fermentum eget tincidunt et, interdum sed urna. Donec nunc libero, molestie ac faucibus in, placerat a est. </p>