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
   * Specify layout to use.
   * @var String
   */
  protected $layout = 'default';

  /**
   * Hold user messages.
   * @var array
   */
  protected $messages = array();

  /**
   * Boolean indicating whether the install has started.
   * @var boolean
   */
  protected $installStarted = false;
  
  /**
   * Boolean indicating whether the install completed successfully.
   * @var boolean
   */
  protected $installCompleted = false;

  /**
   * Where the new config file can be found after it is edited.
   * @var string
   */
  protected $configFilePath;
  
  protected function beforeAction() {
    //required layout variables get default values
    $this->setLayoutVar('requiredCss', array());
    $this->setLayoutVar('requiredJs', array());
    $this->setLayoutVar('pageTitle', '');
    $this->setLayoutVar('layoutTitle', '');
    $this->setLayoutVar('layoutContentTop', '');
    $this->setLayoutVar('layoutContentFooter', '<p>This Application has been designed to meet current web standards in xhtml, css, and javascript in order to be accessible to everyone. If you notice a problem with the application or find it inaccessible in any way please let us know.</p>');
    
    //add jquery
    $this->addScript('foundation/scripts/jquery.js');
    $this->addScript('foundation/scripts/jqueryui.js');
    $this->addScript('foundation/scripts/jquery.json.js');
    
    //yui css library
    $this->addCss('foundation/styles/reset-fonts-grids.css');
    $this->addCss('foundation/styles/base.css');
    
    //our css
    $this->addCss('common/styles/layout.css');
    $this->addCss('common/styles/style.css');
    
    //jquery's style info
    $this->addCss('foundation/styles/jquery/themes/smoothness/style.css');
  }
	
  public function actionIndex() {
    if (file_exists(SRC_ROOT . '/etc/config.ini.php')) {
      $this->messages[] = 'This site has already been configured.  If you want to re-run configuration please remove ' . SRC_ROOT . '/etc/config.ini.php';
    } else {
      //Write the new configuration to var/tmp
      define('TMP_DIR', SRC_ROOT . '/var/tmp');
      if (!is_writable(TMP_DIR)) {
        $this->messages[] = 'Can not write to: ' . TMP_DIR . '.  Pleae make ' . TMP_DIR . ' writable by the webserver.';
      } else if (file_exists(TMP_DIR . '/config.new.php')) {
        $this->messages[] = 'You already have a config file at.' . TMP_DIR . '/config.new.php.  Please delete this file before running this script again.';
      } else if (!is_writable(SRC_ROOT . '/etc')) {
        $this->messages[] = 'Can not write to: ' . SRC_ROOT . '/etc.  Pleae make ' . SRC_ROOT . '/etc writable by the webserver.';
      } else {
        $form = $this->getConfigForm();
        $this->setVar('form', $form);
        if ($input = $form->processInput($this->post)) {
          $this->installStarted = true;
          if ($this->saveFileConfig($input)) {
            if ($this->buildDb()) {
              if ($this->setDefaults()) {
                if ($this->createUser($input)) {
                  $this->installCompleted = true;
                } else {
                  //rollback from failed createUser
                  // unsetDefaults
                  $this->destroyDb();
                  $this->removeFileConfig();
                }
              } else {
                //rollback from failed setDefaults
                // unsetDefaults
                $this->destroyDb();
                $this->removeFileConfig();
              }
            } else {
              //rollback from failed buildDb
              $this->removeFileConfig();
            }
          } else {
            //rollback from failed saveFileConfig
            $this->removeFileConfig();
          }
          $this->setVar('form', null);
        }
      }
    }

    if ($this->installStarted and $this->installCompleted) {
      $this->messages[] = 'Installation completed successfully! <a href="/admin/login">Login</a> to continue setup';
    }
    $this->setVar('messages', $this->messages);
  }
  
  public function getNavigation() {
    return false;
  }
  
  /**
   * Build the default config file
   * @param string $path where to write the file to
   */
  private function newDefaultConfig($path) {
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

    $system = $config->createSection('system');
    $system->createComment('Mode is used to controll access to a production jazzee installation');
    $system->createComment('There are three possible levels');
    $system->createComment('LIVE - No access Controll');
    $system->createComment('SOFT - GET Requests are redirected to the maintence page, but POST requests are saved before they are redirected');
    $system->createComment('DOWN - ALL requests are redirected to the maintence page.  No database access');
    $system->createDirective('mode', 'LIVE');

    $system->createComment('There are three status levels available which controll things like log output');
    $system->createComment(' and payment processing:');
    $system->createComment('PRODUCTION - Live jazzee site open to the public');
    $system->createComment('PREVIEW - Open to testers and internal users only');
    $system->createComment('DEVELOPMENT - Open only to developers.  Logs and other dangerous information available on screen');
    $system->createDirective('status', 'PRODUCTION');
    
    $system->createComment('Force HTTPS connection and secure cookies.  Should only be disabled on internal development sites when SSL is not available.');
    $system->createComment('On productions systems it is also a good idea to reidrect non-secure traffic at the webserver level to ensure no data is transmitted in the clear.');
    $system->createComment(' and payment processing:');
    $system->createDirective('forceSSL', null);
    
    $system->createComment('Doctrine DSN');
    $system->createComment('eg mysql://user:pass@localhost/databasename');
    $system->createComment('Info at http://www.doctrine-project.org/documentation/manual/1_2/en/introduction-to-connections');
    $system->createDirective('dsn', null);
    
    $system->createComment('Override Directory Path');
    $system->createComment('Path to a directory with override controllers, views, css, media, and js.  If present Jazzee will look here first for these objects.');
    $system->createDirective('overridePath', null);
    
    $system->createComment('Server/account to send email from');
    $system->createComment('Format: type://[username:password@]host:port');
    $system->createComment('eg smpt+ssl://user:123Pass$@mail.example.com:443');
    $system->createComment('leave null to use PHPs built in mail()');
    $system->createDirective('mailServer', null);
    $system->createComment('The FROM email address to apply by default and to system messages (like logs), if blank mail server will probably apply a default');
    $system->createDirective('mailDefaultFrom', null);
    $system->createComment('If an address was specified above you can also give it a nice name like Jazzee System which will be displayed in most mail clients');
    $system->createDirective('mailDefaultName', null);
    $system->createComment('If an email addres is specified here all mail will go to that address.  Usefull for testing and development environments');
    $system->createDirective('mailOverrideTo', null);
    
    $system->createComment('Path to the var directory where sessions, caches, logs etc are stored');
    $system->createComment('This only needs to be changed in development environments where the applicaiton root is over written regularly');
    $system->createDirective('varPath', null);
    
    $system->createComment('A comma seperated list of email addresses that critical system events should be sent to.');
    $system->createDirective('adminEmail', null);

    $system->createComment('The timezone this machine is located in.  A list of supported options can be found at http://php.net/manual/en/timezones.php');
    $system->createDirective('timezone', date_default_timezone_get());

    //hook to set the theme from the config form
    $system->createComment('The default theme to load.');
    $system->createDirective('theme', null);

    $c->setRoot($config);
    
    $c->writeConfig($path, 'INIFile');
  }
  
  /**
   * Get the config form.
   * @return Form
   */
  private function getConfigForm() {
    $form = new Form;
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
    
    $field = $form->newField(array('legend'=>'Security Settings'));
    $type = $field->newElement('RadioList', 'forceSSL', array('label'=>'Require SSL', 'value'=>1));
    $type->addItem(1, 'SSL is required');
    $type->addItem(0, 'SSL is not required');

    $field = $form->newField(array('legend'=>'Theme Settings'));
    $_element = $field->newElement('TextInput', 'theme');
    $_element->label = 'Theme Name/Directory';
    $_element->setValue('default');
    $_element->addValidator('NotEmpty');
    
    //$_element = $field->newElement('TextInput', 'theme', array('label'=>'Theme Name/Directory', 'value'=>'default'), array('NotEmpty'=>true));
    
    //Table prefixing is not yet supported in Doctrine
    //$field->newElement('TextInput', 'dbPrefix', array('label'=>'Table Prefix', 'instructions'=>'If you are using a shared database a prefix allows Jazee to keep your tables seperate'));
    
    $form->newButton('submit', 'Save Configuration and Install Database');
    
    return $form;
  }
  
  /**
   * Save user input as config file.
   * @param FormInput $input
   */
  public function saveFileConfig($input) {
    $this->newDefaultConfig(TMP_DIR . '/config.new.php');
    $c = new Config;
    $config = $c->parseConfig(TMP_DIR . '/config.new.php', 'INICommented');  
    $dsn = $input->dbBackend . '://' . $input->dbUser . ':' . $input->dbPass . '@' . $input->dnHost . '/' . $input->dbName;
    $config->getItem('section', 'system')->getItem('directive', 'dsn')->setContent($dsn);
    $config->getItem('section', 'system')->getItem('directive', 'forceSSL')->setContent($input->forceSSL);
    $config->getItem('section', 'system')->getItem('directive', 'theme')->setContent($input->theme);
    /*
    //Table prefixing is not yet supported in doctrine
    if (!empty($input['dbPrefix'])) {
      $config->getItem('section', 'system')->getItem('directive', 'dbPrefix')->setContent($input['dbPrefix'][0]);
    }
    */
    $c->writeConfig(TMP_DIR . '/config.new.php', 'INIFile');
    $this->messages[] = 'Configuration saved successfully to ' . TMP_DIR . '/config.new.php';
    
    if (!@rename(TMP_DIR . '/config.new.php', SRC_ROOT . '/etc/config.ini.php')) {
      $this->configFilePath = TMP_DIR . '/config.new.php';
      $this->messages[] = 'Could not move configuration file into ' . SRC_ROOT . ' /etc.  ' .
          'Please move ' . TMP_DIR . '/config.new.php to ' . SRC_ROOT . '/etc/config.ini.php';
      $this->messages[] = 'Problem encountered; rolling back failed installation.';
      return false;
    } else {
      $this->configFilePath = SRC_ROOT . '/etc/config.ini.php';
      $this->messages[] ='Successfully moved ' . TMP_DIR . '/config.new.php to ' . SRC_ROOT . '/etc/config.ini.php';
      return true;
    }
  }

  private function removeFileConfig() {
    if (file_exists(TMP_DIR . '/config.new.php') and is_file(TMP_DIR . '/config.new.php')) {
      try {
        unlink(TMP_DIR . '/config.new.php');
        $this->messages[] = 'Removed temporary configuration file.';
      } catch (Exception $e) {
        $this->messages[] = $e->getMessage();
      }
    }
    if (file_exists(SRC_ROOT . '/etc/config.ini.php') and is_file(SRC_ROOT . '/etc/config.ini.php')) {
      try {
        unlink(SRC_ROOT . '/etc/config.ini.php');
        $this->messages[] = 'Removed configuration file.';
      } catch (Exception $e) {
        $this->messages[] = $e->getMessage();
      }
    }
  }

  /**
   * Fill the database.
   */
  private function buildDb() {
    $c = new Config;
    $config = $c->parseConfig($this->configFilePath, 'INICommented'); 
    try {
      $conn = Doctrine_Manager::connection($config->getItem('section', 'system')->getItem('directive', 'dsn')->getContent());
      //Test the connection
      $tables = $conn->execute('SHOW TABLES')->fetchAll();
      if (!empty($tables)) {
        $this->messages[] = 'This database is not empty so we will not do anything.  Jazzee setup must be run on an empty database.';
        $this->messages[] = 'Problem encountered; rolling back failed installation (leaving existing database intact.)';
        return false;
      }
      //fill the database tables
      Doctrine::createTablesFromModels(SRC_ROOT . '/app/models');
      Doctrine::loadData(SRC_ROOT . '/dev/fixtures');
      $this->messages[] = 'Database built successfully';
      return true;
    } catch (Doctrine_Exception $e) {
      $this->messages[] = $e->getMessage();
      $this->messages[] = 'Problem encountered; rolling back failed installation.';
      return false;
    }
  }

  /**
   * Empty out the database.
   */
  private function destroyDb() {
    $c = new Config;
    $config = $c->parseConfig($this->configFilePath, 'INICommented'); 
    try {
      $conn = Doctrine_Manager::connection($config->getItem('section', 'system')->getItem('directive', 'dsn')->getContent());
      //Test the connection
      $tables = $conn->execute('SHOW TABLES')->fetchAll();
      $conn->execute('SET FOREIGN_KEY_CHECKS=0');
      foreach ($tables as $table) {
        $conn->execute('DROP TABLE ' . $table[0]);
      }
      $conn->execute('SET FOREIGN_KEY_CHECKS=1');
      $this->messages[] = 'Database emptied.';
    } catch (Doctrine_Exception $e) {
      $this->messages[] = $e->getMessage();
      $this->messages[] = 'Could not empty the database! You must empty it manually before re-attempting installation.';
    }
  }

  /**
   * Set some required defaults so the system can function.
   */
  private function setDefaults() {
    try {
      $c = new ConfigManager;
      $c->session_lifetime = 3600;
      $c->session_name = 'JazzeeOnlineApplication';
      $c->pretty_urls = false;
      $c->theme_path = SRC_ROOT . '/themes/dusk/dusk.theme.php';
      return true;
    } catch (Exception $e) {
      $this->messages[] = $e->getMessage();
      return false;
    }
  }

  /**
   * Create the first user.
   * @param FormInput $input
   */
  private function createUser($input) {
    try {
      $user = new User;
      $user->email = $input->email;
      $user->password = $input->password;
      $role = $user->Roles->get(null);
      $role->roleID = 1;
      $user->save();
      $this->messages[] = "First Account Added Successfully";
      return true;
    } catch (Exception $e) {
      $message = $e->getMessage();
      $this->messages[] = $message;
      if (preg_match('/validator failed on password \(length\)/', $message)) {
        $this->messages[] = '(Hint: Try changing default values on \'password\' column from 40 to 60 in app/models/doctrine/base/BaseUser.php.)';
      }
      $this->messages[] = 'Problem encountered; rolling back failed installation.';
      return false;
    }
  }
}

?>
