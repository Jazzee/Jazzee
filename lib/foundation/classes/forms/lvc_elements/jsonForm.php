<?php
/**
 * JSON Form layout
 * Output a JSON form
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
$f = $form;
$form = array();
$form['attributes'] = array();
foreach($f->getAttributes() as $memberName => $htmlName){
  if(isset($f->$memberName)){
    $form['attributes'][] = array(
      'name' => $htmlName,
      'value' => $f->$memberName
    );
  }
 }
 $form['fields'] = array();
 foreach($f->getFields() as $fl){
   $field = array(
     'legend' => $fl->legend,
     'instructions' => $fl->instructions,
     'attributes' => array(),
     'elements' => array()
   );
   foreach($fl->getAttributes() as $memberName => $htmlName){
     $value = $fl->$memberName;
     if(!is_null($value)){
       $field['attributes'][] = array(
         'name' => $htmlName,
         'value' =>$value
       );
     }
   }
   foreach($fl->getElements() as $e){
     $e->preRender();
     $element = array(
       'name' => $e->name,
       'class' => $e->class,
       'value' => $e->value,
       'required' => $e->required,
       'instructions' => $e->instructions,
       'format' => $e->format,
       'label' => $e->label,
       'attributes' => array(),
       'messages' => array(),
       'items' => array(),
       'views' => array()
     );
     foreach($e->messages AS $message) $element['messages'][] = $message;
     $pattern = array(
       '/^Form_/',
       '/Element$/'
     );
     $name =  'Form_' . $e->class . 'Element';
     do{
       $element['views'][] = preg_replace($pattern, '', $name);
     } while ($name = get_parent_class($name) AND $name != 'Form_Element');
     foreach($e->getAttributes() as $memberName => $htmlName){
       if(isset($e->$memberName)){
         $element['attributes'][] = array(
           'name' => $htmlName,
           'value' =>$e->$memberName
         );
       }
     }
     if(method_exists($e, 'getItems')){
       foreach($e->getItems() as $i){
         $item = array(
           'value' => $i->value,
           'label' => $i->label,
           'attributes' => array(),
         ); 
         foreach($i->getAttributes() as $memberName => $htmlName){
           if(isset($i->$memberName)){
             $item['attributes'][] = array(
               'name' => $htmlName,
               'value' =>$i->$memberName
             );
           }
         }
         $element['items'][] = $item;
       }
     }
     $field['elements'][] = $element;
   }
   $form['fields'][] = $field;
 }
?>
"form":<?php print json_encode($form); ?>