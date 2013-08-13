<?php

namespace Jazzee;

/**
 * Dependancy free controller
 *
 * Base page controller doesn't depend on anything so it is safe
 * for error pages and file pages to use it when they don't need acess
 * to configuration or session info setup by JazzeeController
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class JazzeePageController extends \Foundation\VC\Controller
{

  /**
   *  @var \Jazzee\Configuration
   */
  protected $_config;

  /**
   *  @var \Foundation\Configuration
   */
  protected $_foundationConfig;

  /**
   * @var \Foundation\Cache
   */
  protected static $_cache;

  /**
   * Absolute server path
   * @var string
   */
  protected $_serverPath;

  /**
   * Mono log instance for jazzee logging
   * @var \Monolog\Logger
   */
  protected $_log;

  /**
   * Pear log instance for authentication logging
   * @var \Log
   */
  protected $_authLog;

  /**
   * Pear log instance for 404 logging
   * @var \Log
   */
  protected $_404Log;

  /**
   * Virtual File system root directory
   * @var \Foundation\Virtual\Directory
   */
  protected $_vfs;

  public function __construct()
  {
    $this->setupConfiguration();
    $this->setupVarPath();
    $this->setupLogging();
  }

  /**
   * Basic page disply setup
   *
   * Create the default layout varialbes so the layout doesn't have to guess if they are available
   * @return null
   */
  protected function beforeAction()
  {
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

    //anytime css has to go before jquery ui theme
    $this->addCss($this->path('resource/foundation/styles/anytime.css'));
    //default jquery theme
    $this->addCss('//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/ui-lightness/jquery-ui.css');

    //our css
    $this->addCss($this->path('resource/styles/layout.css'));
    $this->addCss($this->path('resource/styles/style.css'));

    //Set HTML purifier cache location
    \Foundation\Form\Filter\SafeHTML::setCachePath($this->_config->getVarPath() . '/tmp/');
  }

  /**
   * Create a good path even if modrewrite is not present
   * @param string $path
   * @return string
   */
  public function path($path)
  {
    return rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\.') . '/' . $path;
  }

  /**
   * Fully qualified path to a resource
   * @param string $path
   * @return string
   */
  public function absolutePath($path)
  {
    return $this->getServerPath() . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\.') . '/' . $path;
  }

  /**
   * Call any after action properties, redirect, and exit
   * @param string $path
   * @SuppressWarnings(PHPMD.ExitExpression)
   */
  public function redirectPath($path)
  {
    $this->redirect($this->absolutePath($path));
    $this->afterAction();
    exit(0);
  }

  /**
   * Call any after action properties, redirect, and exit
   * @SuppressWarnings(PHPMD.ExitExpression)
   * @param string $url
   */
  public function redirectUrl($url)
  {
    $this->redirect($url);
    $this->afterAction();
    exit(0);
  }

  /**
   * No messages
   */
  public function getMessages()
  {
    return array();
  }

  /**
   * Build our virtual file system
   */
  protected function buildVirtualFileSystem()
  {
    $this->_vfs = new \Foundation\Virtual\VirtualDirectory();
    $this->_vfs->addDirectory('scripts', new \Foundation\Virtual\ProxyDirectory(__DIR__ . '/../scripts'));
    $this->_vfs->addDirectory('styles', new \Foundation\Virtual\ProxyDirectory(__DIR__ . '/../styles'));

    $virtualFoundation = new \Foundation\Virtual\VirtualDirectory();
    $foundationPath = \Foundation\Configuration::getSourcePath();
    $virtualFoundation->addDirectory('javascript', new \Foundation\Virtual\ProxyDirectory($foundationPath . '/src/javascript'));
    $media = new \Foundation\Virtual\VirtualDirectory();
    $media->addFile('blank.gif', new \Foundation\Virtual\RealFile('blank.gif', $foundationPath . '/src/media/blank.gif'));
    $media->addFile('ajax-bar.gif', new \Foundation\Virtual\RealFile('ajax-bar.gif', $foundationPath . '/src/media/ajax-bar.gif'));
    $media->addFile('default_pdf_logo.png', new \Foundation\Virtual\RealFile('default_pdf_logo.png', $foundationPath . '/src/media/default_pdf_logo.png'));
    $media->addDirectory('icons', new \Foundation\Virtual\ProxyDirectory($foundationPath . '/src/media/famfamfam_silk_icons_v013/icons'));

    $scripts = new \Foundation\Virtual\VirtualDirectory();
    $scripts->addFile('jquery.js', new \Foundation\Virtual\RealFile('jquery.js', $foundationPath . '/lib/jquery/jquery-1.7.1.min.js'));
    $scripts->addFile('jquery.json.js', new \Foundation\Virtual\RealFile('jquery.json.js', $foundationPath . '/lib/jquery/plugins/jquery.json-2.2.min.js'));
    $scripts->addFile('jquery.cookie.js', new \Foundation\Virtual\RealFile('jquery.cookie.js', $foundationPath . '/lib/jquery/plugins/jquery.cookie-1.min.js'));
    $scripts->addFile('jquery.filter_input.js', new \Foundation\Virtual\RealFile('jquery.filter_input.js', $foundationPath . '/lib/jquery/plugins/jquery.filter_input.min.js'));
    $scripts->addFile('jqueryui.js', new \Foundation\Virtual\RealFile('jqueryui.js', $foundationPath . '/lib/jquery/jquery-ui-1.8.16.min.js'));
    $scripts->addFile('jquery.qtip.js', new \Foundation\Virtual\RealFile('jquery.qtip.min.js', $foundationPath . '/lib/jquery/plugins/qtip/jquery.qtip.min.js'));
    $scripts->addFile('jquery.wysiwyg.js', new \Foundation\Virtual\RealFile('jquery.wysiwyg.js', $foundationPath . '/lib/jquery/plugins/jwysiwyg/jquery.wysiwyg.full.min.js'));
    $scripts->addFile('anytime.js', new \Foundation\Virtual\RealFile('anytime.js', $foundationPath . '/lib/anytime/anytimec.js'));
    $scripts->addFile('form.js', new \Foundation\Virtual\RealFile('form.js', $foundationPath . '/src/javascript/form.js'));
    $scripts->addFile('SearchListElement.js', new \Foundation\Virtual\RealFile('SearchListElement.js', $foundationPath . '/src/javascript/SearchListElement.js'));

    $styles = new \Foundation\Virtual\VirtualDirectory();
    $styles->addDirectory('jquerythemes', new \Foundation\Virtual\ProxyDirectory($foundationPath . '/lib/jquery/themes'));

    $styles->addFile('base.css', new \Foundation\Virtual\RealFile('base.css', $foundationPath . '/lib/yui/base-min.css'));
    $styles->addFile('reset-fonts-grids.css', new \Foundation\Virtual\RealFile('reset-fonts-grids.css', $foundationPath . '/lib/yui/reset-fonts-grids-min.css'));
    $styles->addFile('jquery.qtip.css', new \Foundation\Virtual\RealFile('jquery.qtip.min.css', $foundationPath . '/lib/jquery/plugins/qtip/jquery.qtip.min.css'));
    $styles->addFile('anytime.css', new \Foundation\Virtual\RealFile('anytime.css', $foundationPath . '/lib/anytime/anytimec.css'));
    $styles->addFile('jquery.wysiwyg.css', new \Foundation\Virtual\RealFile('jquery.wysiwyg.css', $foundationPath . '/lib/jquery/plugins/jwysiwyg/jquery.wysiwyg.css'));
    $styles->addFile('jquery.wysiwyg.bg.png', new \Foundation\Virtual\RealFile('jquery.wysiwyg.bg.png', $foundationPath . '/lib/jquery/plugins/jwysiwyg/jquery.wysiwyg.bg.png'));
    $styles->addFile('jquery.wysiwyg.gif', new \Foundation\Virtual\RealFile('jquery.wysiwyg.gif', $foundationPath . '/lib/jquery/plugins/jwysiwyg/jquery.wysiwyg.gif'));

    $virtualFoundation->addDirectory('media', $media);
    $virtualFoundation->addDirectory('scripts', $scripts);
    $virtualFoundation->addDirectory('styles', $styles);

    $this->_vfs->addDirectory('foundation', $virtualFoundation);

    $jazzeePath = \Jazzee\Configuration::getSourcePath();
    $vOpenID = new \Foundation\Virtual\VirtualDirectory();
    $vOpenID->addDirectory('js', new \Foundation\Virtual\ProxyDirectory($jazzeePath . '/lib/openid-selector/js'));
    $vOpenID->addDirectory('css', new \Foundation\Virtual\ProxyDirectory($jazzeePath . '/lib/openid-selector/css'));
    $vOpenID->addDirectory('images', new \Foundation\Virtual\ProxyDirectory($jazzeePath . '/lib/openid-selector/images'));
    $this->_vfs->addDirectory('openid-selector', $vOpenID);

    $jazzeePath = \Jazzee\Configuration::getSourcePath();
    $this->_vfs->addFile('jsdiff.js', new \Foundation\Virtual\RealFile('jsdiff.js', $jazzeePath . '/lib/jsdiff.js'));
    $this->_vfs->addFile('jquery.tagcloud.js', new \Foundation\Virtual\RealFile('jquery.tagcloud.js', $jazzeePath . '/lib/addywaddy-jquery.tagcloud/jquery.tagcloud.js'));
    
  }

  /**
   * No Navigation
   */
  public function getNavigation()
  {
    return false;
  }

  /**
   * Setup the var directories
   */
  protected function setupVarPath()
  {
    $var = $this->_config->getVarPath();
    //check to see if all the directories exist and are writable
    $varDirectories = array('log', 'session', 'cache', 'tmp', 'uploads', 'cache/public');
    foreach ($varDirectories as $dir) {
      $path = $var . '/' . $dir;
      if (!is_dir($path)) {
        if (!mkdir($path)) {
          throw new Exception("Tried to create 'var/{$dir}' directory but {$path} is not writable by the webserver");
        }
      }
      if (!is_writable($path)) {
        throw new Exception("Invalid path to 'var/{$dir}' {$path} is not writable by the webserver");
      }
    }
  }

  /**
   * Setup configuration
   *
   * Load config.ini.php
   * translate to foundation config
   * create absolute path
   * set defautl timezone
   */
  protected function setupConfiguration()
  {
    $this->_config = new \Jazzee\Configuration();

    $this->_foundationConfig = new \Foundation\Configuration();
    if ($this->_config->getStatus() == 'DEVELOPMENT') {
      $this->_foundationConfig->setCacheType('array');
    } else {
      $this->_foundationConfig->setCacheType('apc');
    }
    $this->_foundationConfig->setMailSubjectPrefix($this->_config->getMailSubjectPrefix());
    $this->_foundationConfig->setMailDefaultFromAddress($this->_config->getMailDefaultFromAddress());
    $this->_foundationConfig->setMailDefaultFromName($this->_config->getMailDefaultFromName());
    $this->_foundationConfig->setMailOverrideToAddress($this->_config->getMailOverrideToAddress());
    $this->_foundationConfig->setMailServerType($this->_config->getMailServerType());
    $this->_foundationConfig->setMailServerHost($this->_config->getMailServeHost());
    $this->_foundationConfig->setMailServerPort($this->_config->getMailServerPort());
    $this->_foundationConfig->setMailServerUsername($this->_config->getMailServerUsername());
    $this->_foundationConfig->setMailServerPassword($this->_config->getMailServerPassword());

    \Foundation\VC\Config::setCache(self::getCache());

    if ((empty($_SERVER['HTTPS']) OR $_SERVER['HTTPS'] == 'off')) {
      $protocol = 'http';
    } else {
      $protocol = 'https';
    }
    
    if(in_array($_SERVER['SERVER_PORT'], array('80', '443'))){
        $port = '';
    } else {
        $port = ':' . $_SERVER['SERVER_PORT'];
    }

    $this->_serverPath = $protocol . '://' . $_SERVER['SERVER_NAME'] . $port;

    \Jazzee\Globals::setConfig($this->_config);
  }

  /**
   * Get the current configuration
   * @return \Jazzee\Configuration
   */
  public function getConfig()
  {
    return $this->_config;
  }

  /**
   * Setup logging
   */
  protected function setupLogging()
  {
    $path = $this->_config->getVarPath() . '/log';
    //create an access log with browser information
    $accessLog = new \Monolog\Logger('access');
    $accessLog->pushHandler(new \Monolog\Handler\StreamHandler($path . '/access_log'));

    $accessMessage = "[{$_SERVER['REQUEST_METHOD']} {$_SERVER['REQUEST_URI']} {$_SERVER['SERVER_PROTOCOL']}] " .
            '[' . (!empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '-') . '] ' .
            '[' . (!empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '-') . ']';
    $accessLog->addInfo($accessMessage);

    //create an authenticationLog
    $this->_authLog = new \Monolog\Logger('authentication');
    $this->_authLog->pushHandler(new \Monolog\Handler\StreamHandler($path . '/authentication_log'));

    //create an authenticationLog
    $this->_404Log = new \Monolog\Logger('404');
    $this->_404Log->pushHandler(new \Monolog\Handler\StreamHandler($path . '/404_log'));

    $this->_log = new \Monolog\Logger('jazzee');
    $this->_log->pushProcessor(new \Monolog\Processor\WebProcessor());
    $this->_log->pushProcessor(new \Monolog\Processor\IntrospectionProcessor());
    $this->_log->pushHandler(new \Monolog\Handler\StreamHandler($path . '/strict_log'));
    $this->_log->pushHandler(new \Monolog\Handler\StreamHandler($path . '/messages_log', \Monolog\Logger::INFO));
    $this->_log->pushHandler(new \Monolog\Handler\StreamHandler($path . '/error_log', \Monolog\Logger::ERROR));
    $this->_log->pushHandler(new \Monolog\Handler\SyslogHandler('jazzee', 'syslog', \Monolog\Logger::ERROR));

    //Handle PHP errors with out logs
    set_error_handler(array($this, 'handleError'));
    //catch any excpetions
    set_exception_handler(array($this, 'handleException'));
  }

  /**
   * Log something
   * @param string $message
   * @param integer $level
   */
  public function log($message, $level = \Monolog\Logger::INFO)
  {
    $this->_log->addRecord($level, $message);
  }

  /**
   * Handle PHP error
   * Takes input from PHPs built in error handler logs it
   * throws a jazzee exception to handle if the error reporting level is high enough
   * @param $code
   * @param $message
   * @param $file
   * @param $line
   * @throws \Jazzee\Exception
   */
  public function handleError($code, $message, $file, $line)
  {
    /* Map the PHP error to a Log priority. */
    switch ($code) {
      case E_WARNING:
      case E_USER_WARNING:
        $priority = \Monolog\Logger::WARNING;
          break;
      case E_NOTICE:
      case E_USER_NOTICE:
        $priority = \Monolog\Logger::INFO;
          break;
      case E_ERROR:
      case E_USER_ERROR:
        $priority = \Monolog\Logger::ERROR;
          break;
      default:
        $priority = \Monolog\Logger::INFO;
    }
    // Error reporting is currently turned off or suppressed with @
    if (error_reporting() === 0) {
      $this->_log->debug('Supressed error: ' . $message . ' in ' . $file . ' at line ' . $line);

      return false;
    }
    $this->_log->addRecord($priority, $message . ' in ' . $file . ' at line ' . $line);
    throw new \Exception('Jazzee caught a PHP error: ' . $message . ' in ' . $file . ' at line ' . $line);
  }

  /**
   * Handle PHP Exception
   * @SuppressWarnings(PHPMD.ExitExpression)
   * @param Exception $exception
   */
  public function handleException(\Exception $exception)
  {
    $message = $exception->getMessage();

    $userMessage = 'Unspecified Technical Difficulties';
    $code = 500;
    $log = $this->_log;
    if ($exception instanceof \Lvc_Exception) {
      $code = 404;
      $log = $this->_404Log;
      $userMessage = 'Sorry the page you are looking for could not be found.';
    }
    if ($exception instanceof \PDOException) {
      $message = 'Problem with database connection. PDO says: ' . $message;
      $userMessage = 'We are experiencing a problem connecting to our database.  Please try your request again.';
    }
    if ($exception instanceof \Foundation\Exception) {
      $userMessage = $exception->getUserMessage();
    }
    if ($exception instanceof \Foundation\Virtual\Exception) {
      $userMessage = $exception->getUserMessage();
      $code = $exception->getHttpErrorCode();
      $log = $this->_404Log;
    }
    /* Map the PHP error to a Log priority. */
    switch ($exception->getCode()) {
      case E_WARNING:
      case E_USER_WARNING:
        $priority = \Monolog\Logger::WARNING;
          break;
      case E_NOTICE:
      case E_USER_NOTICE:
        $priority = \Monolog\Logger::INFO;
          break;
      case E_ERROR:
      case E_USER_ERROR:
        $priority = \Monolog\Logger::CRITICAL;
          break;
      default:
        $priority = \Monolog\Logger::INFO;
    }
    $log->addRecord($priority, $message);
    //send the error to PHP as well
    error_log($message);
    // Get a request for the error page
    $request = new \Lvc_Request();
    $request->setControllerName('error');
    $request->setActionName('index');
    $request->setActionParams(array('error' => $code, 'message' => $userMessage));

    // Get a new front controller without any routers, and have it process our handmade request.
    $frontController = new \Lvc_FrontController();
    $frontController->processRequest($request);
    exit(1);
  }
  
  /**
   * Get the cache
   * We use a static method here so the cache is always available
   * 
   * @return \Foundation\Cache
   */
  public static function getCache(){
    if(!isset(self::$_cache)){
      $config = new \Jazzee\Configuration();
      $foundationConfig = new \Foundation\Configuration();
      if ($config->getStatus() == 'DEVELOPMENT') {
        $foundationConfig->setCacheType('array');
      } else {
        $foundationConfig->setCacheType('apc');
      }
      //use the path as a namespace so multiple installs on the same system dont conflict
      self::$_cache = new \Foundation\Cache('JAZZEE-' . str_ireplace(array('/', ' '), '', __DIR__), $foundationConfig);
    }
    return self::$_cache;
  }

  /**
   * Get the absolute server path
   * @return string
   */
  public function getServerPath(){
    return $this->_serverPath;
  }
}