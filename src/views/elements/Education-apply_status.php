<?php
/**
 * StandardPage Answer Status Element
 */
?>
<table>
  <thead>
    <tr>
      <th><?php print $page->getPage()->getVar('answerStatusTitle'); ?></th>
      <th>Status</th>
    </tr>
  </thead>
  <tbody>
    <?php
    if ($answers = $page->getJazzeePage()->getAnswers()) {
      foreach ($answers as $answer) {
        $search = array(
            '_SCHOOL_NAME_',
            '_SCHOOL_LOCATION_'
        );
        $replace = array();
        if ($school = $answer->getSchool()) {
            $replace[] = $school->getName();
            $replace[] = $school->getLocationSummary();
        } else {
            $child = $answer->getChildren()->first();
            $element = $child->getPage()->getElementByFixedId(\Jazzee\Page\Education::ELEMENT_FID_NAME);
            $element->getJazzeeElement()->setController($this->controller);
            $replace[] = $element->getJazzeeElement()->displayValue($child);
            
            $newSchoolElements = array(
                \Jazzee\Page\Education::ELEMENT_FID_CITY,
                \Jazzee\Page\Education::ELEMENT_FID_STATE,
                \Jazzee\Page\Education::ELEMENT_FID_COUNTRY,
                \Jazzee\Page\Education::ELEMENT_FID_POSTALCODE
            );
            $location = array();
            foreach($newSchoolElements as $fid){
              $element = $child->getPage()->getElementByFixedId($fid);
              $element->getJazzeeElement()->setController($this->controller);
              $value = $element->getJazzeeElement()->displayValue($child);
              if($value){
                  $location[] = $value;
              }
            }
            $replace[] = implode(' ', $location);
        }
        foreach ($page->getPage()->getElements() as $element) {
          $element->getJazzeeElement()->setController($this->controller);
          $search[] = '_' . preg_replace('/\s+/', '_', strtoupper($element->getTitle())) . '_';
          $replace[] = $element->getJazzeeElement()->displayValue($answer);
        }
        ?>
        <tr>
          <td><?php print str_ireplace($search, $replace, $page->getPage()->getVar('answerStatusText')); ?></td>
          <td>
            <strong>Last Updated:</strong> <?php print $answer->getUpdatedAt()->format('M d Y g:i a'); ?>
            <?php
            if ($answer->getPublicStatus()) { ?>
              <br />Status: <?php print $answer->getPublicStatus()->getName();
            } ?>
          </td>
        </tr><?php
      }
    }
    ?>
  </tbody>
</table>