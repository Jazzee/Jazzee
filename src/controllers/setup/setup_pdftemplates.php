<?php

/**
 * Create PDF Templates to download for applicants
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class SetupPdftemplatesController extends \Jazzee\AdminController
{

  const MENU = 'Setup';
  const TITLE = 'PDF Template';
  const PATH = 'setup/pdftemplates';
  const ACTION_INDEX = 'View Templates';
  const ACTION_NEW = 'Create New Template';
  const ACTION_EDIT = 'Edit Existing Template Fields';
  const ACTION_DELETE = 'Delete Existing Template';
  const ACTION_DOWNLOAD = 'Download Existing Template';

  /**
   * List templates for this application
   */
  public function actionIndex()
  {
    $this->setVar('templates', $this->_application->getTemplates());
  }

  /**
   * Create a new template
   */
  public function actionNew()
  {
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path("setup/pdftemplates/new"));
    $field = $form->newField();
    $field->setLegend('New Template');
    
    $element = $field->newElement('TextInput', 'title');
    $element->setLabel('Title');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $element = $field->newElement('FileInput', 'file');
    $element->setLabel('PDF Template');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addFilter(new \Foundation\Form\Filter\Blob($element));

    $form->newButton('submit', 'Create New Template');
    $this->setVar('form', $form);
    if ($input = $form->processInput($this->post)) {
      $template = new \Jazzee\Entity\PDFTemplate;
      $template->setTitle($input->get('title'));
      $template->setFile($input->get('file'));
      
      $pdfLib = new PDFLib();
      $pdfLib->set_option("errorpolicy=exception");
      $document = $pdfLib->open_pdi_document($template->getTmpFilePath(), "");
      $pagecount = $pdfLib->pcos_get_number($document, "length:pages");
      $fonts = array();
      for($pageNum = 0; $pageNum < $pagecount; $pageNum++){
        $blockcount = $pdfLib->pcos_get_number($document,"length:pages[{$pageNum}]/blocks");
        for($blockNum = 0; $blockNum < $blockcount; $blockNum++){
          if($pdfLib->pcos_get_number($document,"type:pages[{$pageNum}]/blocks[{$blockNum}]/fontname") == 4){
            $fontName = $pdfLib->pcos_get_string($document,"pages[{$pageNum}]/blocks[{$blockNum}]/fontname");
            if(!array_key_exists($fontName, $fonts)){
              $fonts[$fontName] = array();
            }
            $fonts[$fontName][] = $pdfLib->pcos_get_string($document,"pages[{$pageNum}]/blocks[{$blockNum}]/Name");
          }
        }
      }
      foreach(array_keys($fonts) as $name){
        try{
          $pdfLib->load_font($name, "unicode", '');
        } catch(PDFlibException $e){
          $blocks = implode(',', $fonts[$name]);
          $form->getElementByName('file')->addMessage("Your PDF contains the font '{$name}' in the {$blocks} blocks.  This font is not available in unicode and cannot be used here.");
          return;
        }
      }
      $template->setApplication($this->_application);
      $this->_application->addTemplate($template);
      $this->getEntityManager()->persist($template);
      $this->addMessage('success', 'Template Created Successfully');
      $this->getEntityManager()->flush();
      $this->redirectPath('setup/pdftemplates/edit/' . $template->getId());
    }
  }
  
  

  /**
   * Edit a template
   */
  public function actionEdit($id)
  {
    if($template = $this->_application->getTemplateById($id)){
      $pdfLib = new PDFLib();
      $pdfLib->set_option("errorpolicy=exception");
      $tmpFile = tempnam($this->_config->getVarPath() . '/tmp/', 'pdftemplate');
      
      $document = $pdfLib->open_pdi_document($template->getTmpFilePath(), "");
      $pagecount = $pdfLib->pcos_get_number($document, "length:pages");
      $blocks = array();
      for($pageNum = 0; $pageNum < $pagecount; $pageNum++){
        $blockcount = $pdfLib->pcos_get_number($document,"length:pages[{$pageNum}]/blocks");
        for($blockNum = 0; $blockNum < $blockcount; $blockNum++){
          $blocks[] = $pdfLib->pcos_get_string($document,"pages[{$pageNum}]/blocks[{$blockNum}]/Name");
        }
      }
      $blocks = array_unique($blocks);
      $form = new \Foundation\Form();
      $form->setCSRFToken($this->getCSRFToken());
      $form->setAction($this->path("setup/pdftemplates/edit/" . $template->getId()));
      $field = $form->newField();
      $field->setLegend('Edit Template Blocks');

      $element = $field->newElement('TextInput', 'title');
      $element->setLabel('Title');
      $element->setValue($template->getTitle());
      $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
      $blockElements = array(
        'applicant-firstName' => 'Applicant: First Name',
        'applicant-lastName' => 'Applicant: Last Name',
        'applicant-middleName' => 'Applicant: Middle Name',
        'applicant-suffix' => 'Applicant: Suffix',
        'applicant-fullName' => 'Applicant: Full Name',
        'applicant-email' => 'Applicant: Email',
        'applicant-id' => 'Applicant: ID',
        'applicant-externalid' => 'Applicant: External ID'
      );
      foreach($this->_application->getApplicationPages() as $applicationPage){
        if($applicationPage->getJazzeePage() instanceof \Jazzee\Interfaces\PdfPage){
          foreach($applicationPage->getJazzeePage()->listPdfTemplateElements() as $key => $title){
            $blockElements[$key] = $title;
          }
        }
      }
      foreach($blocks as $blockName){
        $element = $field->newElement('SelectList', 'block_' . $blockName);
        $element->setLabel("Block {$blockName}");
        $element->newItem(null, '');
        foreach($blockElements as $id => $title){
          $element->newItem($id, $title);
        }
        if($template->hasBlock($blockName)){
          $blockData = $template->getBlock($blockName);
          switch($blockData['type']){
            case 'applicant':
              $element->setValue('applicant-'.$blockData['element']);
              break;
            case 'page':
              $element->setValue('page-'.$blockData['pageId'].'-element-'.$blockData['elementId']);
              break;
          }
        }
      }
      if ($input = $form->processInput($this->post)) {
        $template->setTitle($input->get('title'));
        $template->clearBlocks();
        $matches = array(); //initialize for use in preg_match
        foreach($blocks as $blockName){
          if($blockElement = $input->get('block_' . $blockName)){
            if(preg_match('#applicant-([a-z]+)#i', $blockElement, $matches)){
              $template->addBlock($blockName, array('type'=> 'applicant', 'element'=>$matches[1]));
            } else if(preg_match('#page-([0-9]+)-element-([0-9]+)#i', $blockElement, $matches)){
              $template->addBlock($blockName, array('type'=> 'page', 'pageId' => $matches[1], 'elementId'=>$matches[2]));
            }
          }
        }
        $this->getEntityManager()->persist($template);
        $this->addMessage('success', 'Template Saved Successfully');
        $this->redirectPath('setup/pdftemplates');
      }

      $form->newButton('submit', 'Save');
      $this->setVar('form', $form);
    } else {
      $this->addMessage('error', 'Unable to edit template.  It is not associated with this application.');
      $this->redirectPath('setup/pdftemplates');
    }

  }

  /**
   * Delete a template
   */
  public function actionDelete($id)
  {
    if($template = $this->getEntityManager()->getRepository('\Jazzee\Entity\PDFTemplate')->findOneBy(array('id' => $id, 'application' => $this->_application->getId()))){
      $this->getEntityManager()->remove($template);
      $this->addMessage('success', 'Template Deleted Successfully');
    } else {
      $this->addMessage('error', 'Unable to delete template.  It is not associated with this application.');
    }
    $this->redirectPath('setup/pdftemplates');
  }

  /**
   * Download a template
   */
  public function actionDownload($id)
  {
    if($template = $this->_application->getTemplateById($id)){
      header("Content-type: application/pdf");
      header('Content-Disposition: attachment; filename=' . $template->getTitle() . '.pdf');
      print $template->getFile();
      exit();
    }
  }

}