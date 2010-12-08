<?php 
/**
 * setup_pages capabilities view
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
 * @subpackage setup
 */
if(isset($page)){
  $class = new $page->Page->PageType->class($page);
  $tabs = $class->getTabs();
  print '"tabs":[';
  foreach($tabs as $name => $tab){
    print '{';
    print '"name":"' . $name . '",';
    print '"title":"' . $tab->title . '",';
    print '"type":"' . $tab->type . '",';
    switch($tab->type){
      case 'form':
        $form = $tab->getForm();
        $form->action = $this->path("setup/pages/postForm/{$name}/{$page->id}");
        $this->renderElement('jsonForm', array('form'=>$form));
        break;
      case 'elements':
        print '"pageID":"' . $tab->elementPageID . '",'; 
        print '"elements":[';
        foreach($tab->getElements() as $element){
          $e = new $element->ElementType->class($element);
          print '{';
            print '"id": ' . json_encode($element->id) . ',';
            $form = $e->getPropertiesForm();
            $form->action = $this->path("setup/pages/editElement/{$element->id}");
            $this->renderElement('jsonForm', array('form'=>$form));
            print ',';
            print '"hasListItems": ' . json_encode($e->hasListItems()) . ',';
            $items = array();
            foreach($element->ListItems as $item){
              $items[] = array(
                'id' => $item->id,
                'value' => $item->value,
                'active' => $item->active
              );
            }
            print '"listItems":' . json_encode($items);
          print '},';
        }
        print ']';
      break;
      case 'preview':
        print '"html":' . json_encode($tab->getHTML());
      break;
      default:
        throw new Jazzee_Exception("Tab type {$tab->type} is not valid");
    } //end switch type
    print '},';
  }
  print ']';
}
?>