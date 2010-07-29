<?php
/**
 * Form element layout in a yui grid
 * Sets the structure for the element and the individual controls and displays fill in data
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
//call any pre rendering functions for validators
$element->preRender(); 
?>
<div class='field <?php echo $element->class; ?>'>
<?php if(!empty($element->instructions)) echo "<p class='instructions'>{$element->instructions}</p>"; ?>
<?php foreach($element->messages AS $message) echo "<p class='message'>{$message}</p>"; ?>
  <div class='element yui-gf'>
    <div class='yui-u first label'>
      <?php 
      if(!empty($element->label)){
        echo "<label for='{$element->name}'";
        if($element->required) echo " class='required'";
        echo ">{$element->label}:</label> "; 
      }
      ?>
    </div>
    <div class='yui-u control'>
      <?php
        $possibleViews = array();
        $pattern = array(
          '/^Form_/',
          '/Element$/'
        );
        $name =  'Form_' . $element->class . 'Element';
        do{
          $possibleViews[] = preg_replace($pattern, '', $name);
        }while ($name = get_parent_class($name) AND $name != 'Form_Element');
        foreach($possibleViews as $view){
          if($this->elementExists($view . '.control')){
            $this->renderElement($view . '.control',  array('element'=>$element));
            break;
          }
        }
       ?>
      <?php if(!empty($element->format)) echo "<p class='format'>{$element->format}</p>"; ?>
    </div>
  </div>
</div>