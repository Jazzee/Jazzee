<?php

/**
 * Manage Test Scores
 * Import test scores from a variatey of sources
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class ManageScoresController extends \Jazzee\AdminController
{

  const MENU = 'Manage';
  const TITLE = 'Scores';
  const PATH = 'manage/scores';
  const ACTION_INDEX = 'Manage Scores';
  const ACTION_PRUNE = 'Remove Old Scores';
  const REQUIRE_APPLICATION = false;

  /**
   * Allow the user to pick a score type and upload the file
   * @todo fix the stats
   */
  public function actionIndex()
  {
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path('manage/scores'));
    $field = $form->newField();
    $field->setLegend('Import Scores');

    $element = $field->newElement('SelectList', 'type');
    $element->setLabel('Score Type');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->newItem('etsgre', 'GRE Scores (Ets Flat File Format)');
    $element->newItem('etstoefl', 'TOEFL Scores (Ets Flat File Format)');

    $element = $field->newElement('FileInput', 'file');
    $element->setLabel('File');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));

    $form->newButton('submit', 'Import');
    $this->setVar('form', $form);
    $greStats = $this->_em->getRepository('\Jazzee\Entity\GREScore')->getStatistics();
    $this->setVar('greCount', $greStats['total']);
    $this->setVar('greCycles', $greStats['cycles']);
    $this->setVar('greMatchedCount', 'Not updated');
    $this->setVar('greUnmatchedCount', 'Not updated');

    $toeflStats = $this->_em->getRepository('\Jazzee\Entity\TOEFLScore')->getStatistics();
    $this->setVar('toeflCount', $toeflStats['total']);
    $this->setVar('toeflMatchedCount', 'Not updated');
    $this->setVar('toeflUnmatchedCount', 'Not updated');


    if ($input = $form->processInput($this->post)) {
      $method = $input->get('type') . 'Scores';
      $this->$method($input);
    }
  }

  /**
   * Prune Old scores not mathced to any applicant
   */
  public function actionPrune()
  {
    $form = new \Foundation\Form();
    $form->setCSRFToken($this->getCSRFToken());
    $form->setAction($this->path('manage/scores/prune'));
    $field = $form->newField();
    $field->setLegend('Remove Old Scores');

    $element = $field->newElement('SelectList', 'type');
    $element->setLabel('Score Type');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->newItem('etsgre', 'GRE Scores');
    $element->newItem('etstoefl', 'TOEFL Scores');
    $element = $field->newElement('ShortDateInput', 'olderthan');
    $element->setLabel('Older Than');
    $element->setValue('5 years ago');
    $element->addValidator(new \Foundation\Form\Validator\NotEmpty($element));
    $element->addValidator(new \Foundation\Form\Validator\DateBefore($element, 'last year'));
    
    $form->newButton('submit', 'Remove Scores');
    $this->setVar('form', $form);
    
    if ($input = $form->processInput($this->post)) {
      switch($input->get('type')){
        case 'etsgre':
          $count = $this->_em->getRepository('Jazzee\Entity\GREScore')->pruneUnmatchedScores(new \DateTime($input->get('olderthan')));
          $this->addMessage('success', "{$count} GRE scores deleted.");
          $this->redirectPath('manage/scores');
          break;
        case 'etstoefl':
          $count = $this->_em->getRepository('Jazzee\Entity\TOEFLScore')->pruneUnmatchedScores(new \DateTime($input->get('olderthan')));
          $this->addMessage('success', "{$count} TOEFL scores deleted.");
          $this->redirectPath('manage/scores');
          break;
      }
    }
  }

  /**
   * Import GRE scores from ETS formated flat file
   * @param FormInput $input
   */
  protected function etsgreScores($input)
  {
    $fileArr = $input->get('file');
    $file = file($fileArr['tmp_name'], FILE_IGNORE_NEW_LINES);
    switch (strlen($file[0])) {
      case 500:
        $scores = $this->parseGREVersion1($file);
          break;
      case 600:
        $scores = $this->parseGREVersion2($file);
          break;
      default:
        $this->addMessage('error', "Unrecognized GRE format:  ({$fileArr['name']}) has " . strlen($file[0]) . ' characters per line.');

        return false;
    }
    $new = 0;
    $count = 0;
    foreach ($scores as $arr) {
      $count++;
      $parameters = array(
        'registrationNumber' => $arr['registrationNumber'],
        'testMonth' => $arr['testMonth'],
        'testYear' => $arr['testYear']
      );

      //these fields should not be null, but sometimes ETS messes it up
      $requiredFields = array('id', 'registrationNumber', 'testMonth', 'testYear', 'firstName', 'lastName', 'birthDate', 'testDate', 'testCode', 'testName', 'score1Type', 'score1Converted', 'score1Percentile', 'sequenceNumber', 'recordSerialNumber', 'cycleNumber', 'processDate');
      foreach ($arr as $name => $value) {
        if (in_array($name, $requiredFields) and is_null($value)) {
          $this->addMessage('error', "{$name} was not set on line {$count} for {$arr['firstName']} {$arr['lastName']}");
          $this->redirectPath('manage/scores');
        }
      }
      if (!$score = $this->_em->getRepository('\Jazzee\Entity\GREScore')->findOneBy($parameters)) {
        $score = new \Jazzee\Entity\GREScore();
        $score->setRegistrationNumber($arr['registrationNumber'], $arr['testMonth'], $arr['testYear']);
        $new++;
      }
      $score->setDepartmentCode($arr['departmentCode']);
      $score->setDepartmentName($arr['departmentName']);
      $score->setFirstName($arr['firstName']);
      $score->setMiddleInitial($arr['middleInitial']);
      $score->setLastName($arr['lastName']);
      $score->setBirthDate($arr['birthDate']);
      $score->setGender($arr['gender']);
      $score->setTestDate($arr['testDate']);
      $score->setTestCode($arr['testCode']);
      $score->setTestName($arr['testName']);
      $score->setScore1Type($arr['score1Type']);
      $score->setScore1Converted($arr['score1Converted']);
      $score->setScore1Percentile($arr['score1Percentile']);
      $score->setScore2Type($arr['score2Type']);
      $score->setScore2Converted($arr['score2Converted']);
      $score->setScore2Percentile($arr['score2Percentile']);
      $score->setScore3Type($arr['score3Type']);
      $score->setScore3Converted($arr['score3Converted']);
      $score->setScore3Percentile($arr['score3Percentile']);
      $score->setScore4Type($arr['score4Type']);
      $score->setScore4Converted($arr['score4Converted']);
      $score->setScore4Percentile($arr['score4Percentile']);
      $score->setSequenceNumber($arr['sequenceNumber']);
      $score->setRecordSerialNumber($arr['recordSerialNumber']);
      $score->setCycleNumber($arr['cycleNumber']);
      $score->setProcessDate($arr['processDate']);
      $this->_em->persist($score);
    }
    $this->_em->flush();
    $this->addMessage('success', count($scores) . " scores read from file, {$new} of them were new.");
    $this->redirectPath('manage/scores');
  }

  /**
   * ETS GRE Flat file version 1
   * @param array $arr
   */
  protected function parseGREVersion1($arr)
  {
    $scores = array();
    foreach ($arr as $line) {
      $score = array();
      $score['registrationNumber'] = substr($line, 376, 7);
      $score['testMonth'] = (int) substr($line, 383, 2);
      $score['testYear'] = substr($line, 387, 4);
      $score['departmentCode'] = substr($line, 36, 4);
      $score['departmentName'] = substr($line, 40, 30);
      $score['firstName'] = substr($line, 102, 24);
      $score['middleInitial'] = substr($line, 126, 1);
      $score['lastName'] = substr($line, 70, 32);
      $score['birthDate'] = date('Y-m-d', strtotime(substr($line, 288, 2) . '/' . substr($line, 290, 2) . '/' . substr($line, 292, 4)));
      $score['gender'] = substr($line, 296, 1);
      $score['testDate'] = date('Y-m-d H:i:s', strtotime(substr($line, 383, 2) . '/' . substr($line, 385, 2) . '/' . substr($line, 387, 4)));
      $score['testCode'] = substr($line, 391, 2);
      $score['testName'] = substr($line, 393, 20);
      $score['score1Type'] = substr($line, 413, 1);
      $score['score1Converted'] = substr($line, 414, 3);
      $score['score1Percentile'] = (float) substr($line, 417, 2);
      $score['score2Type'] = substr($line, 419, 1);
      $score['score2Converted'] = substr($line, 420, 3);
      $score['score2Percentile'] = (float) substr($line, 423, 2);
      $score['score3Type'] = substr($line, 425, 1);
      $score['score3Converted'] = substr($line, 426, 3);
      $score['score3Percentile'] = (float) substr($line, 429, 2);
      $score['score4Type'] = substr($line, 431, 1);
      $score['score4Converted'] = substr($line, 432, 3);
      $score['score4Percentile'] = (float) substr($line, 435, 2);
      $score['sequenceNumber'] = substr($line, 461, 4);
      $score['recordSerialNumber'] = substr($line, 465, 2);
      $score['cycleNumber'] = substr($line, 467, 4);
      $score['processDate'] = date('Y-m-d H:i:s', strtotime(substr($line, 471, 2) . '/' . substr($line, 473, 2) . '/' . substr($line, 475, 4)));
      $scores[] = $this->cleanScore($score);
    }

    return $scores;
  }

  /**
   * ETS GRE Flat file version 2
   * @param array $arr
   */
  protected function parseGREVersion2($arr)
  {
    $scores = array();
    foreach ($arr as $line) {
      $score = array();
      $score['registrationNumber'] = substr($line, 409, 7);
      $score['testMonth'] = (int) substr($line, 416, 2);
      $score['testYear'] = substr($line, 420, 4);
      $score['departmentCode'] = substr($line, 36, 4);
      $score['departmentName'] = substr($line, 40, 30);
      $score['firstName'] = substr($line, 136, 24);
      $score['middleInitial'] = substr($line, 160, 1);
      $score['lastName'] = substr($line, 104, 32);
      $score['birthDate'] = date('Y-m-d', strtotime(substr($line, 335, 4) . "-" . substr($line, 331, 2) . "-" . substr($line, 333, 2)));
      $score['gender'] = substr($line, 339, 1);
      $score['testDate'] = date('Y-m-d H:i:s', strtotime(substr($line, 416, 2) . "/" . substr($line, 418, 2) . "/" . substr($line, 420, 4)));
      $score['testCode'] = substr($line, 424, 2);
      $score['testName'] = substr($line, 426, 20);
      $score['score1Type'] = substr($line, 446, 1);
      $score['score1Converted'] = substr($line, 447, 3);
      $score['score1Percentile'] = (float) substr($line, 450, 2);
      $score['score2Type'] = substr($line, 460, 1);
      $score['score2Converted'] = substr($line, 461, 3);
      $score['score2Percentile'] = (float) substr($line, 464, 2);
      $score['score3Type'] = substr($line, 474, 1);
      $score['score3Converted'] = substr($line, 475, 3);
      $score['score3Percentile'] = (float) substr($line, 478, 2);
      $score['score4Type'] = substr($line, 488, 1);
      $score['score4Converted'] = substr($line, 489, 3);
      $score['score4Percentile'] = (float) substr($line, 492, 2);
      $score['sequenceNumber'] = substr($line, 526, 4);
      $score['recordSerialNumber'] = substr($line, 530, 2);
      $score['cycleNumber'] = substr($line, 532, 4);
      $score['processDate'] = date('Y-m-d H:i:s', strtotime(substr($line, 536, 2) . '/' . substr($line, 538, 2) . '/' . substr($line, 540, 4)));
      $scores[] = $this->cleanScore($score);
    }

    return $scores;
  }

  /**
   * Import TOEFL scores from ETS formated flat file
   * @param FormInput $input
   */
  protected function etstoeflScores($input)
  {
    $fileArr = $input->get('file');
    $file = file($fileArr['tmp_name'], FILE_IGNORE_NEW_LINES);
    switch (strlen($file[0])) {
      case 590:
        $scores = $this->parseTOEFLVersion1($file);
          break;
      case 900:
        $scores = $this->parseTOEFLVersion2($file);
          break;
      default:
        $this->addMessage('error', "Unrecognized TOEFL format:  ({$fileArr['name']}) has " . strlen($file[0]) . ' characters per line.');

        return false;
    }

    $new = 0;
    $count = 0;
    //we have to look for cases of the same score appearing twice in the same file
    $used = array();
    foreach ($scores as $arr) {
      $count++;
      $parameters = array(
        'registrationNumber' => $arr['registrationNumber'],
        'testMonth' => $arr['testMonth'],
        'testYear' => $arr['testYear']
      );

      //these fields should not be null, but sometimes ETS messes it up
      $requiredFields = array('id', 'registrationNumber', 'testMonth', 'testYear', 'lastName', 'birthDate', 'nativeCountry', 'nativeLanguage', 'testDate', 'testType');
      foreach ($arr as $name => $value) {
        if (in_array($name, $requiredFields) and is_null($value)) {
          $this->addMessage('error', "{$name} was not set on line {$count} for {$arr['firstName']} {$arr['lastName']}");
          $this->redirectPath('manage/scores');
        }
      }

      if (!$score = $this->_em->getRepository('\Jazzee\Entity\TOEFLScore')->findOneBy($parameters) and !in_array($arr['registrationNumber'] . $arr['testMonth'] . $arr['testYear'], $used)) {
        $used[] = $arr['registrationNumber'] . $arr['testMonth'] . $arr['testYear'];
        $score = new \Jazzee\Entity\TOEFLScore();
        $score->setRegistrationNumber($arr['registrationNumber'], $arr['testMonth'], $arr['testYear']);
        $score->setDepartmentCode($arr['departmentCode']);
        $score->setFirstName($arr['firstName']);
        $score->setMiddleName($arr['middleName']);
        $score->setLastName($arr['lastName']);
        $score->setBirthDate($arr['birthDate']);
        if (!is_null($arr['gender'])) {
          $score->setGender($arr['gender']);
        }
        $score->setNativeCountry($arr['nativeCountry']);
        $score->setNativeLanguage($arr['nativeLanguage']);
        $score->setTestDate($arr['testDate']);
        $score->setTestType($arr['testType']);
        $score->setListeningIndicator($arr['listeningIndicator']);
        $score->setSpeakingIndicator($arr['speakingIndicator']);
        $score->setIBTListening($arr['IBTListening']);
        $score->setIBTReading($arr['IBTReading']);
        $score->setIBTSpeaking($arr['IBTSpeaking']);
        $score->setIBTWriting($arr['IBTWriting']);
        $score->setIBTTotal($arr['IBTTotal']);
        $score->setTSEScore($arr['TSEScore']);
        $score->setListening($arr['listening']);
        $score->setWriting($arr['writing']);
        $score->setReading($arr['reading']);
        $score->setEssay($arr['essay']);
        $score->setTotal($arr['total']);
        $score->setTimesTaken($arr['timesTaken']);
        $score->setOffTopic($arr['offTopic']);
        $new++;
        $this->_em->persist($score);
      }
    }
    $this->addMessage('success', count($scores) . " scores read from file, {$new} of them were new.");
    $this->redirectPath('manage/scores');
  }

  /**
   * ETS TOEFL Flat file version 1
   * @param array $arr
   */
  protected function parseTOEFLVersion1($arr)
  {
    $scores = array();
    foreach ($arr as $line) {
      $score = array();
      $score['registrationNumber'] = ltrim(substr($line, 26, 16), 0);
      $score['testMonth'] = date('n', strtotime(substr($line, 542, 2) . "/" . substr($line, 544, 2) . "/" . substr($line, 538, 4)));
      $score['testYear'] = date('Y', strtotime(substr($line, 542, 2) . "/" . substr($line, 544, 2) . "/" . substr($line, 538, 4)));
      $score['departmentCode'] = substr($line, 9, 2);
      $score['firstName'] = substr($line, 72, 30);
      $score['middleName'] = substr($line, 102, 30);
      $score['lastName'] = substr($line, 42, 30);
      $score['birthDate'] = date('Y-m-d', strtotime(substr($line, 533, 2) . "/" . substr($line, 535, 2) . "/" . substr($line, 529, 4)));
      switch (substr($line, 537, 1)) {
        case 1:
          $score['gender'] = 'm';
            break;
        case 2:
          $score['gender'] = 'f';
            break;
        default:
          $score['gender'] = null;
      }
      $score['nativeCountry'] = substr($line, 446, 40);
      $score['nativeLanguage'] = substr($line, 489, 40);
      $score['testDate'] = date('Y-m-d', strtotime(substr($line, 542, 2) . "/" . substr($line, 544, 2) . "/" . substr($line, 538, 4)));
      $score['testType'] = substr($line, 555, 1);
      $score['listeningIndicator'] = substr($line, 556, 1);
      $score['speakingIndicator'] = substr($line, 557, 1);
      $score['IBTListening'] = substr($line, 558, 2);
      $score['IBTReading'] = substr($line, 560, 2);
      $score['IBTSpeaking'] = substr($line, 562, 2);
      $score['IBTWriting'] = substr($line, 564, 2);
      $score['IBTTotal'] = substr($line, 566, 3);
      $score['TSEScore'] = substr($line, 569, 2);
      $score['listening'] = substr($line, 573, 2);
      $score['writing'] = substr($line, 575, 2);
      $score['reading'] = substr($line, 577, 2);
      $score['essay'] = substr($line, 579, 2);
      $score['total'] = substr($line, 581, 3);
      $score['timesTaken'] = substr($line, 588, 1);
      $score['offTopic'] = substr($line, 589, 1);
      $scores[] = $this->cleanScore($score);
    }

    return $scores;
  }

  /**
   * ETS TOEFL Flat file version 2
   * @param array $arr
   */
  protected function parseTOEFLVersion2($arr)
  {
    $scores = array();
    foreach ($arr as $line) {
      $score = array();
      $score['registrationNumber'] = ltrim(substr($line, 26, 16), 0);
      $score['testMonth'] = date('n', strtotime(substr($line, 542, 2) . "/" . substr($line, 544, 2) . "/" . substr($line, 538, 4)));
      $score['testYear'] = date('Y', strtotime(substr($line, 542, 2) . "/" . substr($line, 544, 2) . "/" . substr($line, 538, 4)));
      $score['departmentCode'] = substr($line, 9, 2);
      $score['firstName'] = substr($line, 72, 30);
      $score['middleName'] = substr($line, 102, 30);
      $score['lastName'] = substr($line, 42, 30);
      $score['birthDate'] = date('Y-m-d', strtotime(substr($line, 533, 2) . "/" . substr($line, 535, 2) . "/" . substr($line, 529, 4)));
      switch (substr($line, 537, 1)) {
        case 1:
          $score['gender'] = 'm';
            break;
        case 2:
          $score['gender'] = 'f';
            break;
        default:
          $score['gender'] = null;
      }
      $score['nativeCountry'] = substr($line, 446, 40);
      $score['nativeLanguage'] = substr($line, 489, 40);
      $score['testDate'] = date('Y-m-d', strtotime(substr($line, 542, 2) . "/" . substr($line, 544, 2) . "/" . substr($line, 538, 4)));
      $score['testType'] = substr($line, 555, 1);
      $score['listeningIndicator'] = substr($line, 556, 1);
      $score['speakingIndicator'] = substr($line, 557, 1);
      $score['IBTListening'] = substr($line, 558, 2);
      $score['IBTReading'] = substr($line, 560, 2);
      $score['IBTSpeaking'] = substr($line, 562, 2);
      $score['IBTWriting'] = substr($line, 564, 2);
      $score['IBTTotal'] = substr($line, 566, 3);
      $score['TSEScore'] = substr($line, 569, 2);
      $score['listening'] = substr($line, 573, 2);
      $score['writing'] = substr($line, 575, 2);
      $score['reading'] = substr($line, 577, 2);
      $score['essay'] = substr($line, 579, 2);
      $score['total'] = substr($line, 581, 3);
      $score['timesTaken'] = substr($line, 588, 1);
      $score['offTopic'] = substr($line, 589, 1);
      $scores[] = $this->cleanScore($score);
    }

    return $scores;
  }

  /**
   * Take a score as an array and clean it up
   * Get rid of extra space and replace blanks with null
   * @param unknown_type $score
   */
  protected function cleanScore($score)
  {
    foreach ($score as &$value) {
      //remove the spaces at the end of lines
      $value = rtrim($value, ' ');
      //convert any blanks to null
      if (empty($value) OR strlen(preg_replace('#\s+#', '', $value)) == 0) {
        $value = null;
      }
    }

    return $score;
  }

}