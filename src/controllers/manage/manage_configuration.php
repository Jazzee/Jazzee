<?php
/**
 * Manage Configuration
 * Settings stored in JazzeeConfig table
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage manage
 */
class ManageConfigurationController extends ManageController {
  const MENU = 'Manage';
  const TITLE = 'Configuration';
  const PATH = 'manage/configuration';
  
  /**
   * Form for editing variables
   */
  public function actionIndex(){
    $form = new Form;
    $form->action = $this->path("manage/configuration/");
    $field = $form->newField(array('legend'=>"Jazzee Configuration"));
    
    $lifetime = $field->newElement('TextInput','session_lifetime');
    $lifetime->label = 'Session Lifetime (in seconds)';
    $lifetime->addValidator('NotEmpty');
    $lifetime->addValidator('Integer');
    
    $name = $field->newElement('TextInput','session_name');
    $name->label = 'Session Name';
    //From php.net
    $name->instructions = 'The session name references the session id in cookies and URLs. It should contain only alphanumeric characters; it should be short and descriptive (i.e. for users with enabled cookie warnings).';
    $name->addValidator('NotEmpty');
    $name->addValidator('Regex', '#^[a-z]+[0-9]*$#i');
    
    $pretty = $field->newElement('RadioList','pretty_urls');
    $pretty->label = 'Pretty URLs';
    $pretty->addValidator('NotEmpty');
    $pretty->addItem(0,'No');
    $pretty->addItem(1,'Yes');

    $cp_public = $field->newElement('TextInput','captcha_public_key');
    $cp_public->label = 'Recaptcha Public Key';
    $cp_public->instructions = 'A CAPTCHA is a program that can tell whether its user is a human or a computer.  Create an account and get key at <a href="http://recaptcha.net/">http://recaptcha.net/</a> to use this service.';
    
    $cp_private = $field->newElement('TextInput','captcha_private_key');
    $cp_private->label = 'Recaptcha Private Key';
    
    $pdfLib = $field->newElement('TextInput','pdflib_key');
    $pdfLib->label = 'PDFlib+PDI License Key';
    $pdfLib->instructions = 'PDFlib+PDI is used to generate Applicant PDFs and to combine uploaded documents.  A license key can be purchased from http://www.pdflib.com/.  The library is not included in jazzee and will need to be installed seperately on your server.';
    
    $maxApplyFileSize = $field->newElement('TextInput','max_apply_file_size');
    $maxApplyFileSize->label = 'Maximum File size for applicant uploads';
    $maxApplyFileSize->instructions = 'The database can hold any file under 4GB.  Obviously this is too big so you should set some more sensible upload limitis for applicants.' .
        '<br />Current PHP setup has the following limits: ' .
        '<br />upload_max_filesize = ' . convertBytesToString(convertIniShorthandValue(ini_get('upload_max_filesize'))) .
        '<br />post_max_size = ' . convertBytesToString(convertIniShorthandValue(ini_get('post_max_size')));
    
    $form->newButton('submit', 'Save Changes');
    $this->setVar('form', $form);
    if($input = $form->processInput($this->post)){
      $this->config->session_lifetime = $input->session_lifetime;
      $this->config->session_name = $input->session_name;
      $this->config->pretty_urls = (bool)$input->pretty_urls;
      $this->config->captcha_public_key = $input->captcha_public_key;
      $this->config->captcha_private_key = $input->captcha_private_key;
      $this->config->pdflib_key = $input->pdflib_key;
      $this->config->max_apply_file_size = convertIniShorthandValue($input->max_apply_file_size);
      $this->messages->write('success', 'Configuration Saved');
    }
    $lifetime->value = $this->config->session_lifetime;
    $name->value = $this->config->session_name;
    $pretty->value = $this->config->pretty_urls;
    $cp_public->value = $this->config->captcha_public_key;
    $cp_private->value = $this->config->captcha_private_key;
    $pdfLib->value = $this->config->pdflib_key;
    $maxApplyFileSize->value = convertBytesToString($this->config->max_apply_file_size);
  }
  
  public static function getControllerAuth(){
    $auth = new ControllerAuth;
    $auth->name = 'Manage Configuration';
    $auth->addAction('index', new ActionAuth('Edit'));
    return $auth;
  }
}
?>