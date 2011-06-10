<?php
/**
 * Logout
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 * @subpackage admin
 */
class AdminLogoutController extends \Jazzee\AdminController {
  const MENU = 'My Account';
  const TITLE = 'Logout';
  const PATH = 'logout';
  
  /**
   * Display index
   */
  public function actionIndex(){
    $this->layout = 'default';
    $this->setLayoutVar('pageTitle', 'Logout');
    $this->setLayoutVar('layoutTitle', 'Logout');
    $this->_adminAuthentication->logoutUser();
    $this->_user = null;
  }
  
  public static function isAllowed($controller, $action, \Jazzee\Entity\User $user = null, \Jazzee\Entity\Program $program = null){
    //anyone can logout
    return true;
  }
  
  /**
   * Get the navigation
   * @return Navigation
   */
  public function getNavigation(){
    $navigation = new \Foundation\Navigation\Container();
    $menu = new \Foundation\Navigation\Menu();
    
    $menu->setTitle('Navigation');
    if(empty($this->application)){
      $link = new \Foundation\Navigation\Link('Login');
      $link->setHref($this->path('welcome'));
      $menu->addLink($link);
    }
    $navigation->addMenu($menu);
    return $navigation;
  }
}
?>