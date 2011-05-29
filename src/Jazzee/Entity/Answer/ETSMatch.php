<?php
namespace Jazzee\Entity\Answer;
/**
 * ETSMatch answers connect scores to applicant entered info
 */
class ETSMatch extends Standard
{
  
  public function applyStatus(){
    $arr = parent::applyStatus();
    $testType = $this->_answer->getPage()->getElementByFixedId(\Jazzee\Entity\Page\ETSMatch::FID_TEST_TYPE)->getJazzeeElement()->displayValue($this->_answer);
    
    switch($testType){
      case 'GRE/GRE Subject':
          if($this->_answer->getGREScore())
            $arr['Score Status'] = 'GRE Score recieved for test taken on ' . $this->_answer->getGREScore()->getTestDate()->format('m/d/Y');
          else $arr['Score Status'] = 'This score has not been received from ETS';
        break;
      case 'TOEFL':
        if($this->_answer->getTOEFLScore())
          $arr['Score Status'] = 'TOEFL Score recieved for test taken on ' . $this->_answer->getTOEFLScore()->getTestDate()->format('m/d/Y');
        else $arr['Score Status'] = 'This score has not been received from ETS';
      break;
      default:
        throw new \Jazzee\Exception("Unknown test type: {$testType} when trying to match a score");
    }
    return $arr;
  }
}