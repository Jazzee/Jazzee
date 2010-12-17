<?php
/**
 * A CAPTCHA Element to validate against bots
 * uses reCAPTCHA library from http://recaptcha.net/
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form_CaptchaElement extends Form_Element{
  /**
  * The reCAPTCHA server URL's
  */
  const API_SERVER = 'http://api.recaptcha.net';
  const API_SECURE_SERVER = 'https://api-secure.recaptcha.net';
  const SIGNUP_SERVER = 'http://recaptcha.net/api/getkey';
  
  /**
   * Our Private API Key
   * @var string
   */
  static private $_privateApiKey;
  
  /**
   * Our Public API Key
   * @var string
   */
  static private $_publicApiKey;
  
  /**
   * Local copy of public api key
   * @var string
   */
  public $publicKey;
  
  /**
   * The server we are connecting to
   * @var string
   */
  public $server;
  
  /**
   * Holds the error message from processing
   * @var string
   */
  public $errorString = '';
  
  /**
   * What reCaptcha theme should we use
   * @var string
   */
  protected $themeName = 'red';
  
  /**
   * Constructor
   * Check the API keys
   */
  public function __construct($field){
    if(!self::$_privateApiKey)
      throw new Foundation_Exception('Private API Key not set for reCAPTCHA library.');
    if(!self::$_publicApiKey)
      throw new Foundation_Exception('Public API Key not set for reCAPTCHA library.');
    
    //move the static keys into local space for ease of use
    $this->publicKey = self::$_publicApiKey;
    
    //use the secure server
    $this->server = self::API_SECURE_SERVER;
    
    parent::__construct($field);   
    
    $this->addValidator('Captcha', self::$_privateApiKey); 
  }
  
  /**
   * Set the api keys
   * @param string $private
   * @param string $public
   */
  public static function setKeys($private, $public){
    self::$_privateApiKey = $private;
    self::$_publicApiKey = $public;
  }
  
  /**
   * gets a URL where the user can sign up for reCAPTCHA.
   * @param string $domain The domain where the page is hosted
   * @param string $appname The name of your application
   */
  public static function signupUrl ($domain = null, $appname = null) {
    $url = self::SIGNUP_SERVER;
    if($domain){
      $url .= '?domain=' .urlencode($domain);
      if($appname){
         $url .= '&app=' . urlencode($appname);
      }
    }
    return $url;
  }
  
  /**
   * Set the theme
   * @param string $themeName
   */
  public function setTheme($themeName){
    //valid themes
    $arr = array('red','white','blackglass','clean','custom');
    if(!in_array($themeName, $arr))
      throw Foundation_Exception('Invalid reCaptch theme');
    $this->themeName = $themeName;
  }
  
  /**
   * Get the theme
   * @return string the current theme
   */
  public function getTheme(){
    return $this->themeName;
  }
   
}

/**
 * Check to see if the captcha was valid
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form_CaptchaValidator extends Form_Validator{
  /**
   * The verification server
   */
  const VERIFY_SERVER = 'api-verify.recaptcha.net';
  
  public function validate(FormInput $input){
    //discard empty submissions
    if ($input->recaptcha_challenge_field == null || strlen($input->recaptcha_challenge_field) == 0 || $input->recaptcha_response_field == null || strlen($input->recaptcha_response_field) == 0) {
      $this->e->errorString = 'incorrect-captcha-sol';
      $this->addError('');
      return false;
    }

    $response = $this->http_post (self::VERIFY_SERVER, "/verify", array (
      'privatekey' => $this->ruleSet,
      'remoteip' => $_SERVER['REMOTE_ADDR'],
      'challenge' => $input->recaptcha_challenge_field,
      'response' => $input->recaptcha_response_field
     )
    );

    $answers = explode ("\n", $response [1]);
    if (trim($answers[0]) == 'false') {
      $this->e->errorString = $answers[1];
      $this->addError('');
      return false;
    }
    return true;
  }
  
  /**
   * Encodes the given data into a query string format
   * @param $data - array of string elements to be encoded
   * @return string - encoded request
   */
  private function qsencode ($data) {
    $req = "";
    foreach ( $data as $key => $value )
      $req .= $key . '=' . urlencode( stripslashes($value) ) . '&';
    // Cut the last '&'
    $req=substr($req,0,strlen($req)-1);
    return $req;
  }
  
  /**
   * Submits an HTTP POST to a reCAPTCHA server
   * @param string $host
   * @param string $path
   * @param array $data
   * @param int port
   * @return array response
   */
  private function http_post($host, $path, $data, $port = 80) {
    $req = $this->qsencode ($data);
  
    $http_request  = "POST $path HTTP/1.0\r\n";
    $http_request .= "Host: $host\r\n";
    $http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
    $http_request .= "Content-Length: " . strlen($req) . "\r\n";
    $http_request .= "User-Agent: reCAPTCHA/PHP\r\n";
    $http_request .= "\r\n";
    $http_request .= $req;
  
    $response = '';
    if( false == ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) ) ) {
      throw new Foundation_Exception('reCaptcha could not open socket');
    }
  
    fwrite($fs, $http_request);
  
    while ( !feof($fs) )
      $response .= fgets($fs, 1160); // One TCP-IP packet
    fclose($fs);
    $response = explode("\r\n\r\n", $response, 2);
  
    return $response;
  }
}
?>