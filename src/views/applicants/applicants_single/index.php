<?php 
/**
 * applicants_single index view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * Create a blank canvas to draw the applicant on
 */
?>
<div id='container'>
  <div id='bio'>
    <h1><?php print $applicant->getFullName() ?> <?php print $applicant->getEmail() ?>
    <?php if($this->controller->checkIsAllowed('applicants_single', 'editAccount')){?> (<a class='editAccount' href='<?php print $this->path('admin/applicants/single/' . $applicant->getId() . '/editAccount')?>'>Edit</a>) <?php } ?></h1>
  </div><!-- bio -->
    <div id='status'>
    <?php if($this->controller->checkIsAllowed('applicants_single', 'pdf')){ ?>
      <p>Print PDF 
        <a href='<?php print $this->path('admin/applicants/single/' . $applicant->getId() . '/pdf/portrait.pdf')?>'>Portrait</a>
        <a href='<?php print $this->path('admin/applicants/single/' . $applicant->getId() . '/pdf/landscape.pdf')?>'>Landscape</a>
      </p>
    <?php } ?>
    <table>
      <thead>
        <tr><th>Actions</th><th>Admission Status</th><th>Tags</th></tr>
      </thead>
      <tbody>
        <tr>
          <td id="actions" />
          <td id="decisions" />
          <td id=tags />
        </tr>
      </tbody>
    </table>
  </div><!-- status -->
  <div id='pages'>
  <?php foreach($applicant->getApplication()->getPages() as $page){
    if($page->getJazzeePage()->showReviewPage()){
      $elementName = \Foundation\VC\Config::findElementCacading($page->getPage()->getType()->getClass(), '', '-applicants-single-page');
      $this->renderElement($elementName, array('applicant'=>$applicant, 'page'=>$page));
    } //end if show review page
  }//endforeach page ?>
  </div><!-- pages -->
  <div id='attachments'></div>
</div><!-- container -->