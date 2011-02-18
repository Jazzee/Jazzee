<?php
/**
 * Install jazzee
 * Check the Configuration
 * Build new configuration
 * Build the database
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
ini_set('memory_limit', '32M');
class InstallController extends Controller {
  /**
   * Constant pointing to the config file
   * @const string
   */
  const CONFIG_PATH = "/etc/config.ini.php";
  
  /**
   * Specify layout to use.
   * @var String
   */
  protected $layout = 'default';
  
  /**
   * Path to controller
   * @var string $path
   */
  private $path;
  
  /**
   * Basic setup
   * @see Lvc_PageController::beforeAction()
   */
  protected function beforeAction() {
    $this->path = WWW_ROOT . '/index.php?url=install';
    //required layout variables get default values
    $this->setLayoutVar('requiredCss', array());
    $this->setLayoutVar('requiredJs', array());
    $this->setLayoutVar('pageTitle', '');
    $this->setLayoutVar('layoutTitle', '');
    $this->setLayoutVar('layoutContentTop', '');
    $this->setLayoutVar('layoutContentFooter', '<p>This Application has been designed to meet current web standards in xhtml, css, and javascript in order to be accessible to everyone. If you notice a problem with the application or find it inaccessible in any way please let us know.</p>');
    
 
    //yui css library
    $this->addCss('foundation/styles/reset-fonts-grids.css');
    $this->addCss('foundation/styles/base.css');
    
    //our css
    $this->addCss('common/styles/layout.css');
    $this->addCss('common/styles/style.css');
    
  }
	
  /**
   * Setup the database connection and create a new configuration file
   */
  public function actionIndex() {
    if(file_exists(SRC_ROOT . self::CONFIG_PATH)){
      $this->redirect($this->path . '/setup'); die;
    }
    $form = new Form;
    $form->action = $this->path;
    $field = $form->newField(array('legend'=>'Database Connection'));
    $type = $field->newElement('RadioList', 'dbBackend', array('label'=>'Backend', 'value'=>'mysql'));
    $type->addItem('mysql', 'MySQL');
    $type->addItem('pgsql', 'PostgreSQL');
    $type->addItem('mssql', 'Microsoft SQL Server (NOT for Sybase. Compile PHP --with-mssql)');
    $type->addItem('oci', 'Oracle 7/8/9/10');
    $type->addItem('sqlite', 'SQLite 2');
    $type->addItem('fbsql', 'FrontBase');
    $type->addItem('ibase', 'InterBase / Firebird');
    $type->addItem('querysim', 'QuerySim');
    
    $field->newElement('TextInput', 'dnHost', array('label'=>'Host', 'value'=>'localhost'), array('NotEmpty'=>true));
    $field->newElement('TextInput', 'dbUser', array('label'=>'Username', 'value'=>'root'), array('NotEmpty'=>true));
    $field->newElement('PasswordInput', 'dbPass', array('label'=>'Password'), array('NotEmpty'=>true));
    $field->newElement('TextInput', 'dbName', array('label'=>'Database Name'), array('NotEmpty'=>true));

    $form->newButton('submit', 'Save Configuration');
    if($input = $form->processInput($this->post)){
      $config = $this->newDefaultConfig(); 
      $root = $config->getRoot();
      $dsn = $input->dbBackend . '://' . $input->dbUser . ':' . $input->dbPass . '@' . $input->dnHost . '/' . $input->dbName;
      $root->getItem('section', 'system')->getItem('directive', 'dsn')->setContent($dsn);
      if($config->writeConfig(SRC_ROOT . self::CONFIG_PATH, 'INICommented') === true){
        $this->redirect($this->path . '/setup'); die;
      }
      $this->setVar('fileContents',$root->toString('INIFile'));
      $this->setVar('setupPath',$this->path . '/setup');
    }
    $this->setVar('form', $form);
  }
  
  /**
   * Build the database and create the first user
   */
  public function actionSetup(){
    if(!file_exists(SRC_ROOT . self::CONFIG_PATH)){
      $this->redirect($this->path); die;
    }
    $c = new Config;
    $config = $c->parseConfig(SRC_ROOT . self::CONFIG_PATH, 'INICommented'); 
    try {
      $connection = Doctrine_Manager::connection($config->getItem('section', 'system')->getItem('directive', 'dsn')->getContent());
      $tables = $connection->execute('SHOW TABLES')->fetchAll();
    }catch (Doctrine_Exception $e) {
      $this->setVar('message',$e->getMessage());
      return false;
    }
    if(!empty($tables)) {
      $this->redirect($this->path . '/complete'); die;
    }
    $form = new Form;
    $form->action = $this->path . '/setup';
    $field = $form->newField(array('legend'=>'First User'));
    $elemenet = $field->newElement('TextInput', 'email');
    $elemenet->label = 'Email Address';
    $elemenet->addValidator('NotEmpty');
    $elemenet->addValidator('EmailAddress');
    $elemenet->addFilter('Lowercase');
    $elemenet = $field->newElement('PasswordInput', 'password');
    $elemenet->label = 'Password';
    $elemenet->addValidator('NotEmpty');
    $elemenet = $field->newElement('PasswordInput', 'confirm-password');
    $elemenet->label = 'Confirm Password';
    $elemenet->addValidator('NotEmpty');
    $elemenet->addValidator('SameAs', 'password');
    $form->newButton('submit', 'Setup First User');
    if($input = $form->processInput($this->post)){
      try {
        Doctrine::createTablesFromModels(SRC_ROOT . '/app/models');
        Doctrine::loadData(SRC_ROOT . '/dev/fixtures');
        $user = new User;
        $user->email = $input->email;
        $user->password = $input->password;
        $role = $user->Roles->get(null);
        $role->roleID = 1;
        $user->save();
        $this->redirect($this->path . '/complete'); die;
      } catch (Doctrine_Exception $e) {
        $this->setVar('message',$e->getMessage());
      }
    }
    $this->setVar('form', $form);
  }
  
  /**
   * Display setup complete information
   */
  public function actionComplete(){
    $this->setVar('loginPath', WWW_ROOT . '/index.php?url=admin/login');
  }
  
  public function getNavigation() {
    return false;
  }
  
  /**
   * Build the default config file
   * @return Config
   */
  protected function newDefaultConfig() {
    $c = new Config;
    $config = $c->getRoot();
    $config->createComment('This file was auto generated during the jazzee installation.');
    $config->createComment('The syntax of the file is extremely simple.  Whitespace and Lines');
    $config->createComment('beginning with a semicolon are silently ignored (as you probably guessed).');
    $config->createComment('Section headers (e.g. [Foo]) are also silently ignored, even though');
    $config->createComment('they might mean something in the future.');
    $config->createBlank();
    $config->createComment('Directives are specified using the following syntax:');
    $config->createComment('directive = value');
    $config->createComment('Directive names are *case sensitive* - foo=bar is different from FOO=bar.');
    $config->createBlank();
    $config->createComment('The value can be a string, a number, a PHP constant (e.g. E_ALL or M_PI), one');
    $config->createComment('of the INI constants (On, Off, True, False, Yes, No and None) or an expression');
    $config->createComment('(e.g. E_ALL & ~E_NOTICE), or a quoted string ("foo").');
    $config->createBlank();

    $system = $config->createSection('system');
    $system->createComment('Mode is used to controll access to a production jazzee installation');
    $system->createComment('There are three possible levels');
    $system->createComment('LIVE - No access Controll');
    $system->createComment('SOFT - GET Requests are redirected to the maintence page, but POST requests are saved before they are redirected');
    $system->createComment('DOWN - ALL requests are redirected to the maintence page.  No database access');
    $system->createDirective('mode', 'LIVE');
    $system->createBlank();

    $system->createComment('There are three status levels available which controll things like log output');
    $system->createComment(' and payment processing:');
    $system->createComment('PRODUCTION - Live jazzee site open to the public');
    $system->createComment('PREVIEW - Open to testers and internal users only');
    $system->createComment('DEVELOPMENT - Open only to developers.  Logs and other dangerous information available on screen');
    $system->createDirective('status', 'PRODUCTION');
    $system->createBlank();
    
    $system->createComment('Force HTTPS connection and secure cookies.  Should only be disabled on internal development sites when SSL is not available.');
    $system->createComment('On productions systems it is also a good idea to reidrect non-secure traffic at the webserver level to ensure no data is transmitted in the clear.');
    $system->createDirective('forceSSL', true);
    $system->createBlank();
    
    $system->createComment('Doctrine DSN');
    $system->createComment('eg mysql://user:pass@localhost/databasename');
    $system->createComment('Info at http://www.doctrine-project.org/documentation/manual/1_2/en/introduction-to-connections');
    $system->createDirective('dsn', null);
    $system->createBlank();
    
    $system->createComment('Server/account to send email from');
    $system->createComment('Format: type://[username:password@]host:port');
    $system->createComment('eg smpt+ssl://user:123Pass$@mail.example.com:443');
    $system->createComment('leave null to use PHPs built in mail()');
    $system->createDirective('mailServer', null);
    $system->createBlank();
    
    $system->createComment('The FROM email address to apply by default and to system messages (like logs), if blank mail server will probably apply a default');
    $system->createDirective('mailDefaultFrom', null);
    $system->createBlank();
    
    $system->createComment('If an address was specified above you can also give it a nice name like Jazzee System which will be displayed in most mail clients');
    $system->createDirective('mailDefaultName', null);
    $system->createBlank();
    
    $system->createComment('If an email addres is specified here all mail will go to that address.  Usefull for testing and development environments');
    $system->createDirective('mailOverrideTo', null);
    $system->createBlank();
    
    $system->createComment('Path to the var directory where sessions, caches, logs etc are stored');
    $system->createComment('This only needs to be changed in development environments where the applicaiton root is over written regularly');
    $system->createDirective('varPath', null);
    $system->createBlank();
    
    $system->createComment('A comma seperated list of email addresses that critical system events should be sent to.');
    $system->createDirective('adminEmail', null);
    $system->createBlank();

    $system->createComment('The timezone this machine is located in.  A list of supported options can be found at http://php.net/manual/en/timezones.php');
    $system->createDirective('timezone', date_default_timezone_get());
    $system->createBlank();

    $system->createComment('Call this bootstrap file to load local customizations.');
    $system->createDirective('localBootstrap', null);
    $system->createBlank();
    
    return $c;
  }
}

?>
