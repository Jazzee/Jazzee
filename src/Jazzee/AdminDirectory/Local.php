<?php
/**
 * Local Admin Directory
 * 
 * Find users in the local directory
 * 
 */
namespace Jazzee\AdminDirectory;
class Local implements \Jazzee\Interfaces\AdminDirectory{
  /**
   * Controller instance
   * @var \Jazzee\AdminController
   */
  private $_controller;
  
  public function __construct(\Jazzee\Interfaces\AdminController $controller){
    $this->_controller = $controller;
  }
  
  public function search(\Foundation\Form\Input $input){
    $users = $this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\User')->findByName($input->get('firstName') . '%', $input->get('lastName') . '%');
    return $this->parseSearchResult($users);
  }
  
  public function getSearchForm(){
    $form = new \Foundation\Form();
    $field = $form->newField();
    $field->setLegend('Find New Users');
    $element = $field->newElement('TextInput','firstName');
    $element->setLabel('First Name');

    $element = $field->newElement('TextInput','lastName');
    $element->setLabel('Last Name');
    
    $form->newButton('submit', 'Search');
    return $form;
  }
  
  function findByUniqueName($uniqueName){
    $users = $this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\User')->findBy(array('uniqueName'=>$uniqueName));
    return $this->parseSearchResult($users);
  }
  
  /**
   * Parse the LDAP search results into a nice array
   * 
   * @param array \Jazzee\Entity\User $users
   * @return array
   */
  protected function parseSearchResult(array $users){
    $result = array();
    foreach($users as $user) {
      $result[] = array(
        'userName' => $user->getUniqueName(),
        'firstName' => $user->getFirstName(),
        'lastName' => $user->getLastName(),
        'emailAddress' => $user->getEmail(),
      );
    }
    return $result; 
  }
}

