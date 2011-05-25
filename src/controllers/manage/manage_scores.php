<?php
/**
 * Manage Test Scores
 * Import test scores from a variatey of sources
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage manage
 */
class ManageScoresController extends ManageController {
  const MENU = 'Manage';
  const TITLE = 'Scores';
  const PATH = 'manage/scores';
  
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
    $this->setVar('greCount', Doctrine::getTable('GREScore')->count());
    $q = Doctrine_Query::create()
        ->select('ID')
        ->from('Score')
        ->where('ScoreType = ?', 'gre')
        ->andWhere('ScoreID IS NOT NULL');
    $this->setVar('greMatchedCount', $q->execute()->count());
    $q = Doctrine_Query::create()
        ->select('ID')
        ->from('Score')
        ->where('ScoreType = ?', 'gre')
        ->andWhere('ScoreID IS NULL');
    $this->setVar('greUnmatchedCount', $q->execute()->count());
    
    
    $this->setVar('toeflCount', Doctrine::getTable('TOEFLScore')->count());
    $q = Doctrine_Query::create()
        ->select('ID')
        ->from('Score')
        ->where('ScoreType = ?', 'toefl')
        ->andWhere('ScoreID IS NOT NULL');
    $this->setVar('toeflMatchedCount', $q->execute()->count());
    $q = Doctrine_Query::create()
        ->select('ID')
        ->from('Score')
        ->where('ScoreType = ?', 'toefl')
        ->andWhere('ScoreID IS NULL');
    $this->setVar('toeflUnmatchedCount', $q->execute()->count());
    
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
    $f = file($input->file['tmp_name'], FILE_IGNORE_NEW_LINES);
    switch(strlen($f[0])){
      case 500:
        $scores = $this->parseGREVersion1($f);
        break;
      case 600:
        $scores = $this->parseGREVersion2($f);
        break;
      default:
        $this->messages->write('error', "Unrecognized GRE format:  ({$input->file['name']}) has " . strlen($f[0]) . ' characters per line.');
        return false;
    }
    $work = new UnitOfWork();
    $table = Doctrine::getTable('GREScore');
    $total = 0;
    $new = 0;
    foreach ($scores AS $arr){
      if(!$score = $table->findOneByRegistrationNumberAndTestMonthAndTestYear($arr['registrationNumber'], $arr['testMonth'], $arr['testYear'])){
        $score = new GREScore;
        $score->synchronizeWithArray($arr);
        $new++;
      } else {
        $score->processDate = $arr['processDate'];
        $score->cycleNumber = $arr['cycleNumber'];
        $score->recordSerialNumber = $arr['recordSerialNumber'];
        $score->sequenceNumber = $arr['sequenceNumber'];
      }
      $work->registerModelForCreateOrUpdate($score);
      $total++;
    }
    $work->commitAll();
    $this->messages->write('success', "{$total} scores read from file, {$new} of them were new.");
  }
  
  /**
   * ETS GRE Flat file version 1
   * @param array $arr
   */
  protected function parseGREVersion1($arr){
      $scores = array();
      foreach ($arr AS $line) {
        $score = array();
        $score['registrationNumber'] = substr($line, 376, 7);
        $score['testMonth'] = date('n', strtotime(substr($line, 383, 2) . '/' . substr($line, 385, 2) . '/' . substr($line, 387, 4)));
        $score['testYear'] = date('Y', strtotime(substr($line, 383, 2) . '/' . substr($line, 385, 2) . '/' . substr($line, 387, 4)));
        $score['departmentCode'] = substr($line, 36, 4);
        $score['departmentName'] = substr($line, 40, 30);
        $score['firstName'] = substr($line, 102, 24);
        $score['middleInitial'] = substr($line, 126, 1);
        $score['lastName'] = substr($line, 70, 32);
        $score['birthDate'] = date('Y-m-d', strtotime(substr($line, 288, 2) . '/' . substr($line, 290, 2) . '/' . substr($line, 292, 4)));
        $score['sex'] = substr($line, 296, 1);
        $score['testDate'] = date('Y-m-d H:i:s', strtotime(substr($line, 383, 2) . '/' . substr($line, 385, 2) . '/' . substr($line, 387, 4)));
        $score['testCode'] = substr($line, 391, 2);
        $score['testName'] = substr($line, 393, 20);
        $score['score1Type'] = substr($line, 413, 1);
        $score['score1Converted'] = substr($line, 414, 3);
        $score['score1Percentile'] = (float)substr($line, 417, 2);
        $score['score2Type'] = substr($line, 419, 1);
        $score['score2Converted'] = substr($line, 420, 3);
        $score['score2Percentile'] = (float)substr($line, 423, 2);
        $score['score3Type'] = substr($line, 425, 1);
        $score['score3Converted'] = substr($line, 426, 3);
        $score['score3Percentile'] = (float)substr($line, 429, 2);
        $score['score4Type'] = substr($line, 431, 1);
        $score['score4Converted'] = substr($line, 432, 3);
        $score['score4Percentile'] = (float)substr($line, 435, 2);
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
  protected function parseGREVersion2($arr){
      $scores = array();
      foreach ($arr AS $line) {
        $score = array();
        $score['registrationNumber'] = substr($line, 409, 7);
        $score['testMonth'] = date('n', strtotime(substr ($line, 416, 2) . "/" . substr ($line, 418, 2) . "/" . substr ($line, 420, 4)));
        $score['testYear'] = date('Y', strtotime(substr ($line, 416, 2) . "/" . substr ($line, 418, 2) . "/" . substr ($line, 420, 4)));
        $score['departmentCode'] = substr($line, 36, 4);
        $score['departmentName'] = substr($line, 40, 30);
        $score['firstName'] = substr($line, 136, 24);
        $score['middleInitial'] = substr($line, 160, 1);
        $score['lastName'] = substr($line, 104, 32);
        $score['birthDate'] = date('Y-m-d', strtotime(substr($line, 335, 4) . "-" . substr ($line, 331, 2) . "-" . substr ($line, 333, 2)));
        $score['sex'] = substr($line, 339, 1);
        $score['testDate'] = date('Y-m-d H:i:s', strtotime(substr ($line, 416, 2) . "/" . substr ($line, 418, 2) . "/" . substr ($line, 420, 4)));
        $score['testCode'] = substr($line, 424, 2);
        $score['testName'] = substr($line, 426, 20);
        $score['score1Type'] = substr($line, 446, 1);
        $score['score1Converted'] = substr($line, 447, 3);
        $score['score1Percentile'] = (float)substr($line, 450, 2);
        $score['score2Type'] = substr($line, 460, 1);
        $score['score2Converted'] = substr($line, 461, 3);
        $score['score2Percentile'] = (float)substr($line, 464, 2);
        $score['score3Type'] = substr($line, 474, 1);
        $score['score3Converted'] = substr($line, 475, 3);
        $score['score3Percentile'] = (float)substr($line, 478, 2);
        $score['score4Type'] = substr($line, 488, 1);
        $score['score4Converted'] = substr($line, 489, 3);
        $score['score4Percentile'] = (float)substr($line, 492, 2);
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
  protected function etstoeflScores($input){
    $f = file($input->file['tmp_name'], FILE_IGNORE_NEW_LINES);
    switch(strlen($f[0])){
      case 590:
        $scores = $this->parseTOEFLVersion1($f);
        break;
      default:
        $this->messages->write('error', "Unrecognized GRE format:  ({$input->file['name']}) has " . strlen($f[0]) . ' characters per line.');
        return false;
    }
    $work = new UnitOfWork();
    $table = Doctrine::getTable('TOEFLScore');
    $total = 0;
    $new = 0;
    foreach ($scores AS $arr){
      if(!$score = $table->findOneByRegistrationNumberAndTestMonthAndTestYear($arr['registrationNumber'], $arr['testMonth'], $arr['testYear'])){
        $score = new TOEFLScore;
        $score->synchronizeWithArray($arr);
        $new++;
      }
      $work->registerModelForCreateOrUpdate($score);
      $total++;
    }
    $work->commitAll();
    $this->messages->write('success', "{$total} scores read from file, {$new} of them were new.");
  }
  
  /**
   * ETS TOEFL Flat file version 1
   * @param array $arr
   */
  protected function parseTOEFLVersion1($arr){
      $scores = array();
      foreach ($arr AS $line) {
        $score = array();
        $score['registrationNumber'] = ltrim(substr($line, 26, 16), 0);
        $score['testMonth'] = date('n', strtotime(substr ($line, 540, 2) . "/" . substr ($line, 542, 2) . "/" . substr ($line, 538, 4)));
        $score['testYear'] = date('Y', strtotime(substr ($line, 540, 2) . "/" . substr ($line, 542, 2) . "/" . substr ($line, 538, 4)));
        $score['departmentCode'] = substr($line, 9, 2);
        $score['firstName'] = substr($line, 72, 30);
        $score['middleName'] = substr($line, 102, 30);
        $score['lastName'] = substr($line, 42, 30);
        $score['birthDate'] = date('Y-m-d', strtotime(substr ($line, 533, 2) . "/" . substr ($line, 535, 2) . "/" . substr ($line, 529, 4)));
        switch(substr($line, 537, 1)){
          case 1:
            $score['sex'] = 'm';
            break;
          case 2:
            $score['sex'] = 'f';
            break;
          default:
            $score['sex'] = null;
        }
        $score['nativeCountry'] = substr($line, 446, 40);
        $score['nativeLanguage'] = substr($line, 489, 40);
        $score['testDate'] = date('Y-m-d', strtotime(substr ($line, 540, 2) . "/" . substr ($line, 542, 2) . "/" . substr ($line, 538, 4)));
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
   * Enter description here ...
   * @param unknown_type $score
   */
  protected function cleanScore($score){
    foreach($score as &$value){
      //remove the spaces at the end of lines
      $value = rtrim($value, ' ');
      //convert any blanks to null
      if(empty($value) OR strlen(preg_replace('#\s+#','',$value)) == 0){
        $value = null;
      }
    }
    return $score;
  }
  
  public static function getControllerAuth(){
    $auth = new ControllerAuth;
    $auth->name = 'Manage Scores';
    $auth->addAction('index', new ActionAuth('Import Scores'));
    return $auth;
  }
}
?>