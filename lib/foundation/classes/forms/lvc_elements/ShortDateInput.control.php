<?php
/**
 * Input element form control
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
?>
 <input type='hidden' name='<?php print $element->name ?>' value='<? print $element->value ?>' />
<select name='<?php print $element->name ?>-month'>
  <option value=''>&nbsp;</option>
  <?php for($i = 1;$i<=12;$i++){
    print "<option value='{$i}'";
    if(!is_null($element->value) AND date('n',strtotime($element->value)) == $i) print ' selected';
    print '>';
    print date('F', strtotime("{$i}/1/1970"));
    print '</option>';
  }?>
</select>
<span>&#47;</span>
<select name='<?php print $element->name ?>-year'>
  <option value=''>&nbsp;</option>
  <?php //go forward 5 years and back 50 years for the year dropdown 
  for($i = date('Y', time()+31556926*5);$i >= date('Y', time()-(31556926*50));$i--){
    print "<option value='{$i}'";
    if(!is_null($element->value) AND date('Y',strtotime($element->value)) == $i) print ' selected';
    print '>';
    print $i;
    print '</option>';
  }?>
</select>
<?php /* input for year - this works too
<input name='<?php print $element->name ?>-year' size='4' value='
<?php if(!is_null($element->value)){
  print date('Y',strtotime($element->value));
} else {
  print date('Y');
}?>
 ' />
*/ ?>