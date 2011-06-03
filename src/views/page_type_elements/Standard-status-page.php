<?php 
/**
 * StandardPage Answer Status Element
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage apply
 */
?>
<table>
  <thead>
    <tr>
      <th><?php print $page->getPage()->getVar('answerStatusTitle');?></th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
<?php 
if($answers = $page->getJazzeePage()->getAnswers()){
  foreach($answers as $answer){
    $search = array();
    $replace = array();
    foreach($page->getPage()->getElements() as $element){
      $search[] = '%' . preg_replace('/\s+/', '_', strtoupper($element->getTitle())) . '%';
      $replace[] = $element->getJazzeeElement()->displayValue($answer);
    }?>
    <tr>
    <td><?php print str_ireplace($search, $replace, $page->getPage()->getVar('answerStatusText')); ?></td>
    <td>
      <strong>Last Updated:</strong> <?php print $answer->getUpdatedAt()->format('M d Y g:i a');?>
      <?php if($answer->getPublicStatus()){?><br />Status: <?php print $answer->getPublicStatus()->getName();}?>
    </td>
    </tr>
<?php   
  }
}
?>
  </tbody>
</table>