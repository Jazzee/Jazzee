<?php
namespace Jazzee;

/**
 * Jazzee base controller
 * Requires working configuration and dependancies.
 * Most usefull contorllers descend from here
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class JazzeeController extends PageController
{

  /**
   * Holds the session
   * @var \Foundation\Session
   */
  protected $_session;

  /**
   * Holds the EmailServer class for sending messages
   * @var \Foundation\Mail\Server
   */
  protected $_mailServer;

  /**
   * Holds the Doctrine EntityManager
   * @var \Doctrine\ORM\EntityManager
   */
  protected $_em;

  /**
   * Are we in preview mode
   * @var boolean
   */
  protected $_previewMode;

  /**
   * Constructor
   * Set up configuration containers
   * Start session handling
   * Setup error processing and email
   * @SuppressWarnings(PHPMD.ExitExpression)
   */
  public function __construct()
  {
    parent::__construct();
    $this->_previewMode = false;
    if ($this->_config->getMode() == 'MAINTENANCE') {
      $request = new \Lvc_Request();
      $request->setControllerName('maintenance');
      $request->setActionName('index');

      // Get a new front controller without any routers, and have it process our handmade request.
      $frontController = new \Lvc_FrontController();
      $frontController->processRequest($request);
      exit();
    }
    $this->setupSession();
    $this->setupDoctrine();
  }

  /**
   * Before any action is taken do some basic setup
   * Look for out of bounds file uploads
   * Crate a navigation instance
   * Create the default layout varialbes so the layout doesn't have to guess if they are available
   * @return null
   */
  protected function beforeAction()
  {
    parent::beforeAction();
    /*
      When the php post_max_size attribute is exceed the POST array is blanked.
      So a check has to be done using the CONTENT_LENGTH superglobal against the post_max_size value on every request
     */
    if (!empty($_SERVER['CONTENT_LENGTH'])) {
      $max = \Foundation\Utility::convertIniShorthandValue(\ini_get('post_max_size'));
      if ($_SERVER['CONTENT_LENGTH'] > $max) {
        $this->addMessage('error', 'Your input has exceeded the maximum allowed size.  If you are trying to upload a file it is too large.  Please reduce your file size and try again');
      }
    }

    //add jquery
    $this->addScript('//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js');
    $this->addScript('//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js');
    $this->addScript($this->path('resource/foundation/scripts/jquery.json.js'));
    $this->addScript($this->path('resource/foundation/scripts/jquery.cookie.js'));
    $this->addScript($this->path('resource/foundation/scripts/jquery.qtip.js'));
    $this->addScript($this->path('resource/foundation/scripts/anytime.js'));

    $this->addCss($this->path('resource/foundation/styles/jquery.qtip.css'));

    //add the Services javascript class
    $this->addScript($this->path('resource/scripts/classes/Services.class.js'));

    if ($this->_config->getBroadcastMessage()) {
      $this->addMessage('info', $this->_config->getBroadcastMessage());
    }
  }

  /**
   * Flush the EntityManager to persist all changes
   */
  protected function afterAction()
  {
    $this->_em->flush();
  }

  /**
   * Add a message for the user
   * @param string $type
   * @param string $text
   */
  public function addMessage($type, $text)
  {
    if (isset($this->_session->getStore('messages')->messages)) {
      $messages = $this->_session->getStore('messages')->messages;
    } else {
      $messages = array();
    }
    $hash = md5($type . $text);
    $messages[$hash] = array('type' => $type, 'text' => $text);
    $this->_session->getStore('messages')->messages = $messages;
  }

  /**
   * Get messages
   * @return array
   */
  public function getMessages()
  {
    $messages = array();
    if (isset($this->_session->getStore('messages')->messages)) {
      $messages = array_values($this->_session->getStore('messages')->messages);
      $this->_session->getStore('messages')->messages = array();
    }

    return $messages;
  }

  /**
   * Get a new mail message class
   *
   * @return \Foundation\Mail\Message
   */
  public function newMailMessage()
  {
    return new \Foundation\Mail\Message($this->_foundationConfig);
  }

  /**
   * Setup Doctrine ORM
   */
  protected function setupDoctrine()
  {
    //setup doctrine
    $doctrineConfig = new \Doctrine\ORM\Configuration();
    //We use different caching and proxy settings in Development status
    if ($this->_config->getStatus() == 'DEVELOPMENT') {
      $doctrineConfig->setAutoGenerateProxyClasses(true);
      $doctrineConfig->setProxyDir($this->_config->getVarPath() . '/tmp');
    } else {
      $doctrineConfig->setAutoGenerateProxyClasses(false);
      $doctrineConfig->setProxyDir(__DIR__ . '/Entity/Proxy');
      if (!extension_loaded('apc')) {
        throw new Exception('APC cache is required, but was not available.');
      }
    }
    $driver = $doctrineConfig->newDefaultAnnotationDriver(array(__DIR__ . "/Entity"));
    $doctrineConfig->setMetadataDriverImpl($driver);

    $doctrineConfig->setProxyNamespace('Jazzee\Entity\Proxy');
    $doctrineConfig->setMetadataCacheImpl(self::getCache());
    $doctrineConfig->setQueryCacheImpl(self::getCache());
    $doctrineConfig->setResultCacheImpl(self::getCache());

    $connectionParams = array(
      'dbname' => $this->_config->getDbName(),
      'user' => $this->_config->getDbUser(),
      'password' => $this->_config->getDbPassword(),
      'host' => $this->_config->getDbHost(),
      'port' => $this->_config->getDbPort(),
      'driver' => $this->_config->getDbDriver()
    );
    $previewStore = $this->_session->getStore('preview', 3600);
    if ($previewStore->check('previewdbpath')) {
      $this->_previewMode = true;
      $connectionParams['driver'] = 'pdo_sqlite';
      $connectionParams['path'] = $previewStore->get('previewdbpath');
      $exitLink = $this->path('preview/end');
      $this->addMessage('info', "You are in preview mode, the changes you make will not be saved.  You can exit preview mode by visiting <a href='{$exitLink}'>{$exitLink}</a>");
    }
    $eventManager = new \Doctrine\Common\EventManager();
    $eventManager->addEventListener(array(\Doctrine\ORM\Events::onFlush), new \Jazzee\Entity\ApplicantEventListener());
    $eventManager->addEventListener(array(\Doctrine\ORM\Events::onFlush, \Doctrine\ORM\Events::preRemove), new \Jazzee\Entity\AnswerEventListener());
    $eventManager->addEventListener(array(\Doctrine\ORM\Events::onFlush), new \Jazzee\Entity\ApplicationEventListener());
    $this->_em = \Doctrine\ORM\EntityManager::create($connectionParams, $doctrineConfig, $eventManager);
    $this->_em->getConfiguration()->addCustomHydrationMode('ApplicantArrayHydrator', 'Jazzee\Entity\ApplicantArrayHydrator');
    $this->_em->getConfiguration()->addCustomHydrationMode('ApplicantDisplayHydrator', 'Jazzee\Entity\ApplicantDisplayHydrator');
    $this->_em->getConfiguration()->addCustomHydrationMode('ApplicantPDFTemplateHydrator', 'Jazzee\Entity\ApplicantPDFTemplateHydrator');

    \Jazzee\Globals::setEntityManager($this->_em);
  }

  /**
   * Get the entity manager
   *
   * @return \Doctrine\ORM\EntityManager
   */
  public function getEntityManager()
  {
    return $this->_em;
  }

  /**
   * Setup Sessions
   */
  protected function setupSession()
  {
    //setup the session based on the configuration
    $this->_session = new \Foundation\Session();

    //if the session name variable is empty then there is no way to login and fix it so look for an empty session name and default to the ini value if it is blank
    $this->_session->setConfigVariable('name', $this->_config->getSessionName());
    //cookies last forever (until browser is closed) which takes the users local clock out of the picture
    //Timeouts are handled By Session internally by expiring the Session_Store
    $this->_session->setConfigVariable('cookie_lifetime', 0);
    //since files are stored in sessions destroy any files after one day
    $this->_session->setConfigVariable('gc_maxlifetime', 86400);
    $this->_session->setConfigVariable('use_only_cookies', true);
    $this->_session->setConfigVariable('hash_function', 1);
    $this->_session->setConfigVariable('save_path', $this->_config->getVarPath() . '/session/');
    if (!empty($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] == 'on') {
      $this->_session->setConfigVariable('cookie_secure', true);
    }
    $this->_session->setConfigVariable('cookie_path', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\.') . '/');
    //browsers give inconsisten results when the domain is used to set the cookie, instead use an empty string to restrict the cookie to this domain
    $this->_session->setConfigVariable('cookie_domain', '');
    $this->_session->start();
  }

  /**
   * Get a secret key for csrf validation
   * @return string
   */
  public function getCSRFToken()
  {
    $store = $this->_session->getStore('csrf');
    if (!isset($store->token)) {
      $store->token = md5(uniqid('csrftoken' . rand(), true) . session_id());
    }

    return $store->token;
  }

  /**
   * Check if the user is in preview mode
   * @return boolean
   */
  public function isPreviewMode()
  {
    return $this->_previewMode;
  }

}