<?php

namespace Jazzee\Migration;

/**
 * Fix problem with education page child answers
 */
class Version20130718000000 extends \Doctrine\DBAL\Migrations\AbstractMigration
{
  public function up(\Doctrine\DBAL\Schema\Schema $schema)
  {
    $sql = 'SELECT id from answers WHERE page_id IN ' .
      '(SELECT id from pages where type_id= ' .
      '(SELECT id from page_types where class="\\\Jazzee\\\Page\\\Education")) ' .
      'AND school_id IS NOT NULL ' .
      'AND id IN (SELECT parent_id FROM answers)';
    $rows = $this->connection->executeQuery($sql)->fetchAll();
    $fixAnswer = $this->connection->prepare('DELETE FROM answers WHERE parent_id = ? AND id NOT IN (SELECT answer_id from element_answers)');
    $count = 0;
    foreach($rows as $row){
      $fixAnswer->execute(array($row['id']));
      $count++;
    }
    if($count > 0){
      $this->write("<info>Removed {$count} answers for Education pages where a bug caused an extra child answer to be stored.</info>");
    }
    $sql = 'SELECT applicant_id, answers.id, page_id, parent_id, applicants.firstName, applicants.lastName,' .
      '(SELECT title from pages where id=(SELECT parent_id from pages where id=answers.page_id)) as pageTitle, ' .
      '(SELECT title from application_pages where page_id=(SELECT parent_id from pages where id=answers.page_id) and application_id = (SELECT application_id from applicants where id=applicant_id)) as applicationPageTitle, ' .
      '(SELECT name from programs where id=(SELECT program_id from applications WHERE id=(SELECT application_id from applicants where id=applicant_id))) as programName ' .
      'from answers LEFT JOIN applicants on applicants.id=applicant_id WHERE parent_id IN ' . 
      '(SELECT id from answers WHERE page_id IN ' .
      '(SELECT id from pages where type_id= ' .
      '(SELECT id from page_types where class="\\\Jazzee\\\Page\\\Education")) ' .
      'AND school_id IS NULL) AND answers.id NOT IN (SELECT answer_id FROM element_answers)';
    $rows = $this->connection->executeQuery($sql)->fetchAll();

    $statement = 'INSERT INTO element_answers (answer_id, element_id, position, eShortString) VALUES ' .
    '(:answerId, (SELECT id FROM elements WHERE page_id = :pageId AND fixedId=' . \Jazzee\Page\Education::ELEMENT_FID_NAME . '), 0, "Unknown School"), ' .
    '(:answerId, (SELECT id FROM elements WHERE page_id = :pageId AND fixedId=' . \Jazzee\Page\Education::ELEMENT_FID_COUNTRY . '), 0, "Unknown"), ' .
    '(:answerId, (SELECT id FROM elements WHERE page_id = :pageId AND fixedId=' . \Jazzee\Page\Education::ELEMENT_FID_CITY . '), 0, "Unknown")';
    $addUnknowSchool = $this->connection->prepare($statement);
    $addThread = $this->connection->prepare("INSERT INTO threads (applicant_id, subject, createdAt) VALUES (:applicantId, :subject, :now)");
    $addMessage = $this->connection->prepare("INSERT INTO messages (thread_id, sender, text, createdAt, isRead) VALUES (:threadId, :sender, :message, :now, 0)");

    $subject = 'Problem has occurred with your application.';
    $sender = \Jazzee\Entity\Message::PROGRAM;
    $now = new \DateTime();
    $messageCount = 0;
    $answerCount = 0;
    $applicantMessages = array();
    foreach($rows as $row){
      if(!in_array($row['applicant_id'], $applicantMessages)){
        $applicantMessages[] = $row['applicant_id'];
        $pageName = $row['applicationPageTitle'] == null?$row['pageTitle']:$row['applicationPageTitle'];
        $message = "Dear {$row['firstName']} {$row['lastName']}, <br />" .
        "Our application system has had an error in recording one or more of your school " .
        "institution name(s). We have corrected our system, but in order for your application " .
        "to reflect your information correctly, it will require your input. Please click on 'Back to Application,' then " .
        "select '{$pageName}' from the navigation and review the school information entered. " .
        "Please delete entries for schools where the School name is listed as 'Unknown School,' " .
        "and re-enter from the beginning all information for that school.<br /><br />" .
        "Our apologies for the inconvenience.<br />" .
        $row['programName'];
 
        $addThread->bindValue(':applicantId', $row['applicant_id']);
        $addThread->bindValue(':subject', $subject);
        $addThread->bindValue(':now', $now, "datetime");
        $addThread->execute();

        $addMessage->bindValue(':threadId', $this->connection->lastInsertId());
        $addMessage->bindValue(':message', $message);
        $addMessage->bindValue(':now', $now, "datetime");
        $addMessage->bindValue(':sender', $sender);
        $addMessage->execute();
        $messageCount++;
      }
      
      $addUnknowSchool->bindValue(':answerId', $row['id']);
      $addUnknowSchool->bindValue(':pageId', $row['page_id']);
      $addUnknowSchool->execute();
      $answerCount++;
    }
    if($messageCount > 0){
      $this->write("<info>Added {$messageCount} messages for applicants where a bug caused Known School data to be dropped.</info>");
    }
    if($answerCount > 0){
      $this->write("<info>Modified {$answerCount} answers for Education pages where a bug caused Known School data to be dropped.</info>");
    }
    
  }
  
  public function down(\Doctrine\DBAL\Schema\Schema $schema){}
}
