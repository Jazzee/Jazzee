<?php
/**
 * OpenID Admin Authentication Controller
 * 
 * OpenID is used for SSO accross the internet.  Organizations like google and yahoo
 * already provide thier users with an OpenID so if you don't want to maintain
 * your own identity provider or if you are just testing jazzee then this is a good
 * choice.
 * 
 */
namespace Jazzee\AdminAuthentication;
@include_once 'OpenID/RelyingParty.php';
@include_once 'OpenID/Extension/AX.php';
class OpenID implements \Jazzee\Interfaces\AdminAuthentication{
  const LOGIN_ELEMENT = 'OpenID_Login';
  
  const SESSION_VAR_ID = 'openid_id';
  /**
   * Our authenticated user
   * @var \Jazzee\Entity\User
   */
  private $_user;
  
  /**
   * Config instance
   * @var \Jazzee\Controller 
   */
  private $_controller;
  
  /**
   * Constructor
   * 
   * Require authentication and setup the user if a valid session is detected
   * 
   * @param \Jazzee\Interfaces\AdminController
   */
  public function __construct(\Jazzee\Interfaces\AdminController $controller){
    if(!class_exists('OpenID')){
      throw new \Jazzee\Exception("Pear OpenID library is required to use OpenID for authentication.");
    }
    $this->_controller = $controller;
    if($this->_controller->getStore()->check(self::SESSION_VAR_ID)){
      $this->_user = $this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\User')->findOneBy(array('uniqueName'=>$this->_controller->getStore()->get(self::SESSION_VAR_ID)));
    }
  }
  
  public function isValidUser(){
    return (bool)$this->_user;
  }
  
  public function getUser(){
    return $this->_user;
  }

  public function loginUser(){
    
    $returnTo = $this->_controller->path('login');
    $realm    = $this->_controller->path('');
    if(!empty($_POST['openid_identifier'])) {
      $identifier = $_POST['openid_identifier'];
      $rp = new \OpenID_RelyingParty($returnTo, $realm, $identifier);
      $authRequest = $rp->prepare();
      $ax = new \OpenID_Extension_AX(\OpenID_Extension::REQUEST);
      $ax->set('type.email', 'http://axschema.org/contact/email');
      $ax->set('type.firstname', 'http://axschema.org/namePerson/first');
      $ax->set('type.lastname', 'http://axschema.org/namePerson/last');
      $ax->set('mode', 'fetch_request');
      $ax->set('required', 'email,firstname,lastname');
      $authRequest->addExtension($ax);

      header('Location: ' . $authRequest->getAuthorizeURL());
      exit(0);
    }
    $rp = new \OpenID_RelyingParty($returnTo, $realm);
    $arr = explode('?', $_SERVER['REQUEST_URI']);
    $queryString = isset($arr[1])?$arr[1]:'';
    if($queryString){
      $message = new \OpenID_Message($queryString, \OpenID_Message::FORMAT_HTTP);
      $result = $rp->verify(new \Net_URL2($returnTo), $message);
      if ($result->success()) {
        $this->_controller->getStore()->expire();
        $this->_controller->getStore()->touchAuthentication();
        $ax = new \OpenID_Extension_AX(\OpenID_Extension::RESPONSE, $message);
        $uniqueName = $message->get('openid.claimed_id');
        $email = $ax->get('value.email');
        $firstName = $ax->get('value.firstname');
        $lastName = $ax->get('value.lastname');
        $this->_controller->getStore()->set(self::SESSION_VAR_ID, $uniqueName);
        $user = $this->_controller->getEntityManager()->getRepository('\Jazzee\Entity\User')->findOneBy(array('uniqueName'=>$uniqueName));
        if(!$user){
          $user = new \Jazzee\Entity\User;
          $user->setUniqueName($uniqueName);
        }
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail($email);
        $this->_controller->getEntityManager()->persist($user);
        $this->_user = $user;
      }
    }
  }
  
  public function logoutUser(){
    $this->_user = null;
    $this->_controller->getStore()->expire();
  }
}

