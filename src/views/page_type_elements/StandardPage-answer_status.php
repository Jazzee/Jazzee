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
      <th><?php print $page->Page->getVar('answerStatusTitle');?></th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
<?php 
if($answers = $page->getAnswers()){
  foreach($answers as $answer){
    print '<tr>';
    $search = array();
    $replace = array();
    foreach($answer->getElements() as $id => $title){
      $search[] = '%' . preg_replace('/\s+/', '_', strtoupper($title)) . '%';
      $replace[] = $answer->getDisplayValueForElement($id);
    }
    print '<td>';
    print str_ireplace($search, $replace, $page->Page->getVar('answerStatusText'));
    print '</td><td>';
    foreach($answer->applyStatus() as $title => $value){
      print "{$title}: {$value} <br />"; 
    }
    print '</td></tr>';
  }
}
?>
  </tbody>
</table>