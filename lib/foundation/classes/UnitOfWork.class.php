<?php
require_once('Foundation.class.php');
/**
 * A UnitOfWork
 * is used in complex ORM tasks to ensure persistence accross all the modles being worked with
 * Mostly stolen from http://www.doctrine-project.org/projects/orm/1.2/docs/cookbook/creating-a-unit-of-work-using-doctrine/en
 *
 */
class UnitOfWork{
  /**
   * The connection we are working with
   * @var Doctrine_Connection
   */
  protected $connection;
  
  /**
   * Collection of models to be persisted
   * @var array Doctrine_Record
   */
  protected $createOrUpdateCollection = array();

  /**
   * Collection of models to be deleted
   * @var array Doctrine_Record
   */
  protected $deleteCollection = array();
  
  /**
   * Constructor
   * Use the passed connection or try and use the current connection if one isn't passed
   * @param Doctrine_Connection $connection
   */
  public function __construct(Doctrine_Connection $connection = null){
    if(is_null($connection)){
      $this->connection = Doctrine_Manager::connection();
    } else {
      $this->connection = $connection;
    }
  }
  
  /**
   * Add a model object to the create collection
   * @param Doctrine_Record $model
   */
  public function registerModelForCreateOrUpdate(Doctrine_Record $model){
    if ($this->_existsInCollections($model)) throw new Foundation_Exception('Model already registered for persistence');
    $this->createOrUpdateCollection[] = $model;
  }

  /**
   * Add a model object to the delete collection
   *
   * @param Doctrine_Record $model
   */
  public function registerModelForDelete(Doctrine_Record $model){
    if ($this->_existsInCollections($model)) throw new Foundation_Exception('Model already registered for persistence');
    $this->deleteCollection[] = $model;
  }

  /**
   * Clear the Unit of Work
   */
  public function ClearAll(){
    $this->deleteCollection = array();
    $this->createOrUpdateCollection = array();
  }

  /**
   * Perform a Commit and clear the Unit Of Work. Throw an Exception if it fails and roll back.
   */
  public function commitAll(){
    try {
      $this->connection->beginTransaction();
      foreach ($this->createOrUpdateCollection as $model) {
        $model->save();
      }
      foreach ($this->deleteCollection as $model) {
        $model->delete($conn);
      }
      $this->connection->commit();
    } catch(Doctrine_Exception $e) {
      $this->connection->rollback();
      throw new Foundation_Exception('Unable to commit transaction: ' . $e->getMessage());
    }
    $this->clearAll();
  }
  
  /**
   * Check if a model is already in one of the collections
   * @param Doctrine_Record $model
   */
  protected function _existsInCollections(Doctrine_Record $model){
    foreach ($this->createOrUpdateCollection as $m) {
      if($model->getOid() == $m->getOid()) return true;
    }
    foreach ($this->deleteCollection as $m) {
      if ($model->getOid() == $m->getOid()) return true;
    }
    return false;
  }
}
?>