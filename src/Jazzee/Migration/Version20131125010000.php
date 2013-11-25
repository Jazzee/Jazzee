<?php

namespace Jazzee\Migration;

/**
 * Remove decision letters from application
 */
class Version20131125010000 extends \Doctrine\DBAL\Migrations\AbstractMigration
{
    public function up(\Doctrine\DBAL\Schema\Schema $schema)
    {
        $table = $schema->getTable('applications');
        $table->dropColumn('admitLetter');
        $table->dropColumn('denyLetter');
    }

    public function down(\Doctrine\DBAL\Schema\Schema $schema)
    {
        $table = $schema->getTable('applications');
        $table->addColumn('admitLetter', 'text', array('notNull' => false));
        $table->addColumn('denyLetter', 'text', array('notNull' => false));
    }
  
    /**
     * Move decision letters from templates back to applications table
     * 
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    public function postDown(\Doctrine\DBAL\Schema\Schema $schema)
    {
        $updateAdmit = $this->connection->
            prepare('UPDATE applications SET admitLetter=:letter WHERE id=:id');
        $updateDeny = $this->connection->
            prepare('UPDATE applications SET denyLetter=:letter WHERE id=:id');
        $results = $this->connection->executeQuery(
            'SELECT type, text, application_id FROM templates WHERE type IN (?)',
            array(array(\Jazzee\Entity\Template::DECISION_ADMIT, \Jazzee\Entity\Template::DECISION_DENY)), 
            array(\Doctrine\DBAL\Connection::PARAM_INT_ARRAY)
        );
        $updated = 0;
        while ($row = $results->fetch()) {
            if($row['type'] == \Jazzee\Entity\Template::DECISION_ADMIT){
                $updateAdmit->bindParam(':letter', $row['text']);
                $updateAdmit->bindParam(':id', $row['application_id']);
                $updateAdmit->execute();
                $updated++;
            }
            if($row['type'] == \Jazzee\Entity\Template::DECISION_DENY){
                $updateDeny->bindParam(':letter', $row['text']);
                $updateDeny->bindParam(':id', $row['application_id']);
                $updateDeny->execute();
                $updated++;
            }
        }
        if($updated > 0){
          $this->write("<info>Copied {$updated} decision letter templates.</info>");
        }
    }
}
