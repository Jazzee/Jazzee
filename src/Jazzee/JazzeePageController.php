<?php
namespace Jazzee;
/**
 * Base page controller doesn't depend on anything so it is safe
 * for error pages and file pages to use it when they don't need acess
 * to configuration or session info setup by JazzeeController
 * @package jazzee
 */

class JazzeePageController extends \Foundation\VC\Controller
{ 
  /**
   * Virtual File system root directory
   * @var \Foundation\Virtual\Directory
   */
  protected $_vfs;
  
  /**
   * Basic page disply setup
   * 
   * Create the default layout varialbes so the layout doesn't have to guess if they are available
   * @return null
   */
  protected function beforeAction(){
    $this->buildVirtualFilesystem();
    //required layout variables get default values
    $this->setLayoutVar('requiredCss', array());
    $this->setLayoutVar('requiredJs', array());
    $this->setLayoutVar('pageTitle', '');
    $this->setLayoutVar('layoutTitle', '');
    $this->setLayoutVar('layoutContentTop', '');
    $this->setLayoutVar('navigation', false);
    $this->setLayoutVar('status', 'success'); //used in some json ajax requests
    
    //yui css library
    $this->addCss($this->path('resource/foundation/styles/reset-fonts-grids.css'));
    $this->addCss($this->path('resource/foundation/styles/base.css'));
    
    //our css
    $this->addCss($this->path('resource/styles/layout.css'));
    $this->addCss($this->path('resource/styles/style.css'));
    
    //default jquery theme
    $this->addCss($this->path('resource/foundation/styles/jquerythemes/ui-lightness/style.css'));
  }
  
  /**
   * Fall back on a relative path with no pretty urls
   * 
   * @param string $path
   * @return string
   */
  public function path($path){
    return './index.php?url=' . $path;
  }
  
  /**
   * No messages
   */
  public function getMessages(){
    return array();
  }
  
  /**
   * Build our virtual file system
   */
  protected function buildVirtualFileSystem(){
    $this->_vfs = new \Foundation\Virtual\VirtualDirectory();
    $this->_vfs->addDirectory('scripts', new \Foundation\Virtual\ProxyDirectory(__DIR__ . '/../scripts'));
    $this->_vfs->addDirectory('styles', new \Foundation\Virtual\ProxyDirectory(__DIR__ . '/../styles'));
    
    
    $virtualFoundation = new \Foundation\Virtual\VirtualDirectory();
    $virtualFoundation->addDirectory('javascript', new \Foundation\Virtual\ProxyDirectory(__DIR__ . '/../../lib/foundation/src/javascript'));
    $media = new \Foundation\Virtual\VirtualDirectory();
    $media->addFile('blank.gif', new \Foundation\Virtual\RealFile('blank.gif,', __DIR__ . '/../../lib/foundation/src/media/blank.gif'));
    $media->addFile('ajax-bar.gif', new \Foundation\Virtual\RealFile('ajax-bar.gif,', __DIR__ . '/../../lib/foundation/src/media/ajax-bar.gif'));
    $media->addDirectory('icons', new \Foundation\Virtual\ProxyDirectory( __DIR__ . '/../../lib/foundation/src/media/famfamfam_silk_icons_v013/icons'));
    
    $scripts = new \Foundation\Virtual\VirtualDirectory();
    $scripts->addFile('jquery.js', new \Foundation\Virtual\RealFile('jquery.js', __DIR__ . '/../../lib/foundation/lib/jquery/jquery-1.6.1.min.js'));
    $scripts->addFile('jquery.json.js', new \Foundation\Virtual\RealFile('jquery.json.js', __DIR__ . '/../../lib/foundation/lib/jquery/plugins/jquery.json-2.2.min.js'));
    $scripts->addFile('jquery.cookie.js', new \Foundation\Virtual\RealFile('jquery.cookie.js', __DIR__ . '/../../lib/foundation/lib/jquery/plugins/jquery.cookie-1.min.js'));
    $scripts->addFile('jqueryui.js', new \Foundation\Virtual\RealFile('jqueryui.js', __DIR__ . '/../../lib/foundation/lib/jquery/jquery-ui-1.8.13.min.js'));
    $scripts->addFile('form.js', new \Foundation\Virtual\RealFile('form.js', __DIR__ . '/../../lib/foundation/src/javascript/form.js'));
    
    $styles = new \Foundation\Virtual\VirtualDirectory();
    $styles->addDirectory('jquerythemes', new \Foundation\Virtual\ProxyDirectory(__DIR__ . '/../../lib/foundation/lib/jquery/themes'));
    
    $styles->addFile('base.css', new \Foundation\Virtual\RealFile('base.css', __DIR__ . '/../../lib/foundation/lib/yui/base-min.css'));
    $styles->addFile('reset-fonts-grids.css', new \Foundation\Virtual\RealFile('reset-fonts-grids.css', __DIR__ . '/../../lib/foundation/lib/yui/reset-fonts-grids-min.css'));

    $virtualFoundation->addDirectory('media',$media);
    $virtualFoundation->addDirectory('scripts',$scripts);
    $virtualFoundation->addDirectory('styles',$styles);
    $this->_vfs->addDirectory('foundation', $virtualFoundation);
  }
  
  /**
   * No Navigation
   */
  public function getNavigation(){
    return false;
  }
}