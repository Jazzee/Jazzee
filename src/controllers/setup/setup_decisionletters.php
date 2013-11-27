<?php

/**
 * Setup the Decision Letters
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class SetupDecisionlettersController extends \Jazzee\AdminController
{

  const MENU = 'Setup';
  const TITLE = 'Decision Templates';
  const PATH = 'setup/decisionletters';
  const ACTION_INDEX = 'View Letters';
  const ACTION_NEW = 'Create Template';
  const ACTION_EDIT = 'Edit Template';
  const ACTION_DELETE = 'Remove Template';
  const ACTION_COPY = 'Copy Template';

  /**
   * View Decision Letters
   */
    public function actionIndex()
    {
        $admitTemplates = $this->_em->getRepository('Jazzee\Entity\Template')->
            findBy(array('type' => \Jazzee\Entity\Template::DECISION_ADMIT, 'application' => $this->_application));
        $denyTemplates = $this->_em->getRepository('Jazzee\Entity\Template')->
            findBy(array('type' => \Jazzee\Entity\Template::DECISION_DENY, 'application' => $this->_application));
        $this->setVar('admitTemplates', $admitTemplates);
        $this->setVar('denyTemplates', $denyTemplates);
    }
  
    /**
     * Edit Decision letter template
     * @param int $templateId
     */
    public function actionEdit($templateId)
    {
        if ($template = $this->_em->getRepository('\Jazzee\Entity\Template')->findOneBy(array('id' => $templateId, 'application' => $this->_application))) {
            $now = new DateTime('now');
            $search = array(
                '_Admit_Date_',
                '_Deny_Date_',
                '_Applicant_Name_',
                '_Offer_Response_Deadline_'
            );
            $replace = array();
            $replace[] = "&lt;&lt;admission date&gt;&gt; [formatted: ".$now->format('F jS Y')."]";
            $replace[] = "&lt;&lt;admission date&gt;&gt; [formatted: ".$now->format('F jS Y')."]";
            $replace[] = 'John Smith';
            $replace[] = "&lt;&lt;offer deadline date&gt;&gt; [formatted: ".$now->format('F jS Y g:ia')."]";
            $this->setVar('search', $search);
            $this->setVar('replace', $replace);
            $this->setVar('template', $template);
            
            $form = new \Foundation\Form();
            $form->setCSRFToken($this->getCSRFToken());
            $form->setAction($this->path("setup/decisionletters/edit/{$templateId}"));
            $field = $form->newField();
            $field->setLegend('Edit Template');
            
            if($template->getType() == \Jazzee\Entity\Template::DECISION_ADMIT){
                $tokens = 'These tokens will be replaced in the text: _Admit_Date_, _Applicant_Name_, ' .
                    '_Offer_Response_Deadline_';
                $field->setInstructions($tokens);
            }
            if($template->getType() == \Jazzee\Entity\Template::DECISION_DENY){
                $field->setInstructions('These tokens will be replaced in the text: _Deny_Date_, _Applicant_Name_');
            }
            $element = $field->newElement('TextInput', 'title');
            $element->setLabel('Title');
            $element->setValue($template->getTitle());
            $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
            $element->addFilter(new \Foundation\Form\Filter\SafeHTML($element));
            
            $element = $field->newElement('Textarea', 'text');
            $element->setLabel('Content');
            $element->setValue($template->getText());
            $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
            $element->addFilter(new \Foundation\Form\Filter\SafeHTML($element));

            $form->newButton('submit', 'Save');

            if ($input = $form->processInput($this->post)) {
              $template->setTitle($input->get('title'));
              $template->setText($input->get('text'));
              $this->_em->persist($template);
              $this->addMessage('success', $template->getTitle() . ' saved.');
              $this->redirectPath('setup/decisionletters/edit/' . $template->getId());
            }

            $this->setVar('form', $form);
        }
    }
    
    /**
     * Delete Decision letter template
     * @param int $templateId
     */
    public function actionDelete($templateId)
    {
        if ($template = $this->_em->getRepository('\Jazzee\Entity\Template')->findOneBy(array('id' => $templateId, 'application' => $this->_application))) {
            $this->_em->remove($template);  
            $this->addMessage('success', $template->getTitle() . ' removed.');
            $this->redirectPath('setup/decisionletters');
        }
    }
  
    /**
     * New Decision letter template
     */
    public function actionNew()
    {
        $form = new \Foundation\Form();
        $form->setCSRFToken($this->getCSRFToken());
        $form->setAction($this->path("setup/decisionletters/new"));
        $field = $form->newField();
        $field->setLegend('New Template');

        $element = $field->newElement('RadioList', 'type');
        $element->setLabel('Type');
        $element->newItem(\Jazzee\Entity\Template::DECISION_ADMIT, 'Admit Letter');
        $element->newItem(\Jazzee\Entity\Template::DECISION_DENY, 'Deny Letter');
        $element->setValue(\Jazzee\Entity\Template::DECISION_ADMIT);
        $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
        
        $element = $field->newElement('Textinput', 'title');
        $element->setLabel('Title');
        $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
        $element->addFilter(new \Foundation\Form\Filter\SafeHTML($element));

        $form->newButton('submit', 'Create');

        if ($input = $form->processInput($this->post)) {
          $template = new \Jazzee\Entity\Template($input->get('type'));
          $template->setTitle($input->get('title'));
          $template->setApplication($this->_application);
          $this->_em->persist($template);
          $this->_em->flush();
          $this->addMessage('success', $template->getTitle() . ' created.');
          $this->redirectPath('setup/decisionletters/edit/' . $template->getId());
        }

        $this->setVar('form', $form);
        
    }
  
    /**
     * Copy Decision letter template
     * @param int $templateId
     */
    public function actionCopy($templateId)
    {
        if ($template = $this->_em->getRepository('\Jazzee\Entity\Template')->findOneBy(array('id' => $templateId, 'application' => $this->_application))) {
            $newTemplate = new \Jazzee\Entity\Template($template->getType());
            $newTemplate->setTitle($template->getTitle() . ' copy');
            $newTemplate->setText($template->getText());
            $newTemplate->setApplication($template->getApplication());
            $this->_em->persist($newTemplate);
            $this->addMessage('success', $template->getTitle() . ' copied.');
            $this->redirectPath('setup/decisionletters');
        }
    }

}