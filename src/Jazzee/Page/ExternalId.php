<?php
namespace Jazzee\Page;

/**
 * Get recommender information from applicnats and send out invitations
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ExternalId extends AbstractPage
{
  /**
   * These fixedIDs make it easy to find the element we are looking for
   * @const integer
   */

  const FID_EXT_ID = 2;


  public function __construct(\Jazzee\Entity\ApplicationPage $applicationPage)
  {
    $this->_applicationPage = $applicationPage;
    $this->_applicationPage->setMin(1);
    $this->_applicationPage->setMax(1);
  }

  /**
   * Create the recommenders form
   */
  public function setupNewPage()
  {
    $entityManager = $this->_controller->getEntityManager();
    $types = $entityManager->getRepository('Jazzee\Entity\ElementType')->findAll();
    $elementTypes = array();
    foreach ($types as $type) {
      $elementTypes[$type->getClass()] = $type;
    };
    $count = 1;
    foreach (array(self::FID_EXT_ID => 'External ID') as $fid => $title) {
      $element = new \Jazzee\Entity\Element;
      $element->setType($elementTypes['\Jazzee\Element\TextInput']);
      $element->setTitle($title);
      //      $element->required();
      $element->setWeight($count);
      $element->setFixedId($fid);
      $this->_applicationPage->getPage()->addElement($element);
      $entityManager->persist($element);
      $count++;
    }
    
  }

  public function newAnswer($input)
  {
    error_log("saving new answer");
    $elem = $this->_applicationPage->getPage()->getElementByFixedId(self::FID_EXT_ID);
    $id = $input->get("el".$elem->getId());
    error_log(" given external id is ".$id);
    $this->_applicant->setExternalId($id);

    $this->_controller->getEntityManager()->persist($this->_applicant);
    $this->_controller->getEntityManager()->flush();
	
  }

  public function updateAnswer($input, $answerId)
  {
    error_log("in update answer, answerid: ".$answerId);

  }
  public function deleteAnswer($answerId)
  {
    $this->_applicant->setExternalId(null);

    $this->_controller->getEntityManager()->persist($this->_applicant);
    $this->_controller->getEntityManager()->flush();

  }


  public function fill($answerId)
  {
    $id = $this->_applicant->getExternalId();
    $elem = $this->_applicationPage->getPage()->getElementByFixedId(self::FID_EXT_ID);
    error_log("FILL: current external id is ".$id);
    $this->getForm()->getElementByName('el' . $elem->getId())->setValue($id);
  


  }

  public static function applicantsSingleElement()
  {
    return 'ExternalId-applicants-single';
  }


  public function getStatus()
  {
    $answers = $this->getAnswers();
    if (!$this->_applicationPage->isRequired() and count($answers) and $answers[0]->getPageStatus() == self::SKIPPED) {
      return self::SKIPPED;
    }
    $completedAnswers = 0;
    foreach ($answers as $answer) {
      if ($answer->isLocked()) {
        $completedAnswers++;
      }
    }
    if (is_null($this->_applicationPage->getMin()) or $completedAnswers < $this->_applicationPage->getMin()) {
      return self::INCOMPLETE;
    } else {
      return self::COMPLETE;
    }
  }

  public function getArrayStatus(array $answers)
  {
    if (!$this->_applicationPage->isRequired() and count($answers) and $answers[0]['pageStatus'] == self::SKIPPED) {
      return self::SKIPPED;
    }
    $completedAnswers = 0;
    foreach ($answers as $answer) {
      if ($answer['locked']) {
        $completedAnswers++;
      }
    }
    if (is_null($this->_applicationPage->getMin()) or $completedAnswers < $this->_applicationPage->getMin()) {
      return self::INCOMPLETE;
    } else {
      return self::COMPLETE;
    }
  }


  public static function applyPageElement()
  {
    return 'ExternalId-apply_page';
  }

  public static function pageBuilderScriptPath()
  {
    return 'resource/scripts/page_types/JazzeePageExternalId.js';
  }


}