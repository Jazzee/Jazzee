<?php
/**
 * Manage Test Scores
 * Import test scores from a variatey of sources
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage manage
 */
class ManageScoresController extends ManageController {
  /**
   * Allow the user to pick a score type and upload the file
   */
  public function actionIndex(){
    $form = new Form;
    $form->action = $this->path("manage/scores/");
    $field = $form->newField(array('legend'=>"Import Scores"));
    
    $element = $field->newElement('SelectList','type');
    $element->label = 'Score Type';
    $element->addValidator('NotEmpty');
    $element->addItem('etsgre', 'GRE Scores (Ets Flat File Format)');
    $element->addItem('etstoefl', 'TOEFL Scores (Ets Flat File Format)');
    
    $element = $field->newElement('FileInput','file');
    $element->label = 'File';
    $element->addValidator('NotEmpty');
    
    $form->newButton('submit', 'Import');
    $this->setVar('form', $form);
    
    if($input = $form->processInput($this->post)){
      $method = $input->type . 'Scores';
      $this->$method($input);
    }
  }
  
  /**
   * Import GRE scores from ETS formated flat file
   * @param FormInput $input
   */
  protected function etsgreScores($input){
    $f = file($input->file['tmp_name']);
    if(empty($f) OR (strlen($f[0]) != 501 AND strlen($f[0]) != 502)){
      $this->messages->write('error', "Invalid GRE File uploaded.  ({$input->file['name']})");
      return false;
    } else {
      $table = Doctrine::getTable('GREScore');
      $total = 0;
      $new = 0;
      foreach ($f AS $line) {
        if(!$score = $table->findOneByRegistrationNumber(substr($line, 376, 7))){
          $score = new GREScore;
          $new++;
        }
        $score->registrationNumber = substr($line, 376, 7);
        $score->departmentCode = substr($line, 36, 4);
        $score->departmentName = substr($line, 40, 30);
        $score->firstName = substr($line, 102, 24);
        $score->middleInitial = substr($line, 126, 1);
        $score->lastName = substr($line, 70, 32);
        $score->birthDate = date('Y-m-d', strtotime(substr($line, 288, 2) . '/' . substr($line, 290, 2) . '/' . substr($line, 292, 4)));
        $sex = substr($line, 296, 1);
        $score->sex = $sex==' '?null:$sex;
        $score->testDate = date('Y-m-d H:i:s', strtotime(substr($line, 383, 2) . '/' . substr($line, 385, 2) . '/' . substr($line, 387, 4)));
        $score->testCode = substr($line, 391, 2);
        $score->testName = substr($line, 393, 20);
        $score->score1Type = substr($line, 413, 1);
        $score->score1Converted = substr($line, 414, 3);
        $score->score1Percentile = (float)substr($line, 417, 2);
        $score->score2Type = substr($line, 419, 1);
        $score->score2Converted = substr($line, 420, 3);
        $score->score2Percentile = (float)substr($line, 423, 2);
        $score->score3Type = substr($line, 425, 1);
        $score->score3Converted = substr($line, 426, 3);
        $score->score3Percentile = (float)substr($line, 429, 2);
        $score->score4Type = substr($line, 431, 1);
        $score->score4Converted = substr($line, 432, 3);
        $score->score4Percentile = (float)substr($line, 435, 2);
        $score->sequenceNumber = substr($line, 461, 4);
        $score->recordSerialNumber = substr($line, 465, 2);
        $score->cycleNumber = substr($line, 467, 4);
        $score->processDate = date('Y-m-d H:i:s', strtotime(substr($line, 471, 2) . '/' . substr($line, 473, 2) . '/' . substr($line, 475, 4)));
        $score->save();
        $total++;
      }
      $this->messages->write('success', "{$total} scores read from file, {$new} of them were new.");
    }
  }
  
  public static function getControllerAuth(){
    $auth = new ControllerAuth;
    $auth->name = 'Manage Scores';
    $auth->addAction('index', new ActionAuth('Import Scores'));
    return $auth;
  }
}
?>