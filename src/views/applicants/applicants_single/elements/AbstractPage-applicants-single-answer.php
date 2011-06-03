<?php 
/**
 * Applicants single answer
 */
?>
<tr id='answer<?print $answer->getId() ?>'>
  <?php foreach($page->getPage()->getElements() as $element){?><td><?php print $element->getJazzeeElement()->displayValue($answer); ?></td><?php }?>
<td>
  <?php foreach($answer->getJazzeeAnswer()->applicantsStatus() as $name => $value) print $name?>: <?php print $value?><br />
</td>
<?php if($page->getJazzeePage()->allowAttachments()){?><td>Attachment</td><?php }?>
<?php if($this->controller->checkIsAllowed('applicants_single', 'editAnswer')){ ?>
  <td>
    <?php foreach($answer->getJazzeeAnswer()->applicantsTools() as $tool){?>
      <?php if($this->controller->checkIsAllowed('applicants_single', $tool['class'])){ ?>
        <a href='<?php print $this->path('admin//applicants/single/' . $answer->getApplicant()->getId() . $tool['path'])?>' class='<?php print $tool['class']?>'><?php print $tool['title']?></a><br />     
      <?php } ?>
    <?php }?>
  </td>
<?php }?>
</tr>