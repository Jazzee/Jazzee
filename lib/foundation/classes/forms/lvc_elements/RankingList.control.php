<?php
/**
 * RankingList element form control
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
?>
<ol>
<?php for($i = 0; $i < $element->rankItems; $i++):?>
  <li>
    <label for='<?php print $element->name . '_' . $i ?>'
    <?php if($i<$element->minimumItems) echo " class='required'"; ?>
    ><?php print ordinalValue($i+1)?> choice:</label>
    <select name='<?php print $element->name?>[]' id='<?php print $element->name . '_' . $i ?>' <?php
    foreach($element->getAttributes() as $memberName => $htmlName){
      $this->renderElement('attribute', array('name'=>$htmlName, 'value'=>$element->$memberName));
    }
    ?>>
    <option value='0'></option>
    <?php foreach($element->getItems() as $item){
      echo '<option';
      if(isset($element->value[$i]) AND $element->value[$i] == $item->value){
        print ' selected="selected"';
      }
      foreach($item->getAttributes() as $memberName => $htmlName){
        $this->renderElement('attribute', array('name'=>$htmlName, 'value'=>$item->$memberName));
      }
      echo ">{$item->label}</option>";
    }
    ?>
    </select>
  </li>
<?php endfor; ?>
</ol>