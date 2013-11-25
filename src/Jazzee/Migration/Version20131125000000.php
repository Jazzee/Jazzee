<?php

namespace Jazzee\Migration;

/**
 * Create templates table and move existing decision letters to templates
 */
class Version20131125000000 extends \Doctrine\DBAL\Migrations\AbstractMigration
{
    public function up(\Doctrine\DBAL\Schema\Schema $schema)
    {
        $table = $schema->getTable('decisions');
        $table->addColumn('decisionLetter', 'text', array('notNull' => false));

        $table = $schema->createTable('templates');
        $table->addColumn('id', 'bigint', array('autoincrement' => true));
        $table->addColumn('type', 'string', array('length' => 255));
        $table->addColumn('title', 'string', array('length' => 255));
        $table->addColumn('text', 'text', array('notNull' => false));
        $table->addColumn('application_id', 'bigint', array(
            'precision' => 10,
            'notNull' => false,
        ));
        $table->addIndex(array('application_id'), 'IDX_6F287D8E3E030ACD');
        $table->setPrimaryKey(array('id'), 'primary');
        $table->addForeignKeyConstraint(
            'applications',
            array('application_id'),
            array('id'),
            array('onDelete' => 'CASCADE'),
            'FK_6F287D8E3E030ACD'
        );
        
    }
  
    /**
     * Migrate schools from the old education apge format to the new global school list
     * 
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function postUp(\Doctrine\DBAL\Schema\Schema $schema)
    {
        $stmt = 'INSERT INTO templates (type, title, text, application_id) VALUES ' .
            '(:type, :title, :text, :application_id)';
        $newLetterTemplates = $this->connection->prepare($stmt);
        $stmt = 'UPDATE decisions SET decisionLetter = :decisionLetter WHERE id = :id';
        $decisionLetterFromTemplate = $this->connection->prepare($stmt);
        $findDecisions = 'SELECT d.*,a.*, d.id as decisionId FROM decisions AS d ' .
            'LEFT JOIN applicants a ON a.id = d.applicant_id ' .
            'WHERE a.application_id = ? AND ' .
            '(d.finalAdmit IS NOT NULL OR d.finalDeny IS NOT NULL)';
        $sql = 'SELECT admitLetter, denyLetter, id from applications';
        $results = $this->connection->executeQuery($sql);
        $updated = 0;
        $letterSaved = 0;
        $search = array(
            '_Admit_Date_',
            '_Deny_Date_',
            '_Applicant_Name_',
            '_Offer_Response_Deadline_'
        );
        while ($row = $results->fetch()) {
            if(!empty($row['admitLetter'])){
                $admitLetter = $row['admitLetter'];
                $newLetterTemplates->bindValue(':type', \Jazzee\Entity\Template::DECISION_ADMIT);
                $newLetterTemplates->bindValue(':title', 'Default Letter');
                $newLetterTemplates->bindParam(':text', $row['admitLetter']);
                $newLetterTemplates->bindParam(':application_id', $row['id']);
                $newLetterTemplates->execute();
                $updated++;
            }
            if(!empty($row['denyLetter'])){
                $denyLetter = $row['denyLetter'];
                $newLetterTemplates->bindValue(':type', \Jazzee\Entity\Template::DECISION_DENY);
                $newLetterTemplates->bindValue(':title', 'Default Letter');
                $newLetterTemplates->bindParam(':text', $row['denyLetter']);
                $newLetterTemplates->bindParam(':application_id', $row['id']);
                $newLetterTemplates->execute();
                $updated++;
            }
            $decisions = $this->connection->executeQuery($findDecisions, array($row['id']));
            
            foreach($decisions as $decision){
                $replace = array();
                $text = false;
                if(!is_null($decision['finalAdmit'])){
                    $date = new \DateTime($decision['finalAdmit']);
                    $decisionDate = $date->format('F jS Y');
                    $text = $admitLetter;
                    $date = new \DateTime($decision['offerResponseDeadline']);
                    $offerResponseDate = $date->format('F jS Y g:ia');
                } else if(!is_null($decision['finalDeny'])){
                    $date = new \DateTime($decision['finalDeny']);
                    $decisionDate = $date->format('F jS Y');
                    $text = $denyLetter;
                    $offerResponseDate = null;
                }
                if($text){
                    $name = array();
                    foreach(array('firstName', 'middleName', 'lastName', 'suffix') as $key){
                        if(!empty($decision[$key])){
                            $name[] = $decision[$key];
                        }
                    }
                    $replace[] = $decisionDate;
                    $replace[] = $decisionDate; //add twice once for each type of search term admit/deny
                    $replace[] = implode(' ', $name);
                    $replace[] = $offerResponseDate;
                    $letter = str_ireplace($search, $replace, $text);
                    $decisionLetterFromTemplate->bindParam('id', $decision['decisionId']);
                    $decisionLetterFromTemplate->bindValue('decisionLetter', $letter);
                    $decisionLetterFromTemplate->execute();
                    $letterSaved++;
                }
            }
        }
        if($updated > 0){
          $this->write("<info>Copied {$updated} decision letter templates.</info>");
        }
        if($letterSaved > 0){
          $this->write("<info>Copied {$letterSaved} decision letters to applicant decisions.</info>");
        }
    }

    public function down(\Doctrine\DBAL\Schema\Schema $schema)
    {
        $schema->dropTable('templates');
        $table = $schema->getTable('decisions');
        $table->dropColumn('decisionLetter');
    }
}
