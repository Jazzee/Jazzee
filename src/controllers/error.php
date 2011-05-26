<?php
/**
 * Output error page to users
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package jazzee
 */
class ErrorController extends \Jazzee\Controller{
  protected $layout = 'default';

	/**
	 * Error code -> error message mappings.
	 * @var array
	 * @see http://www.faqs.org/rfcs/rfc2616
	 **/
	protected static $errorString = array(
		100 => 'Continue',
		101 => 'Switching Protocols',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
	);
	
	public function afterAction(){
	  return;
	}
	
	public function actionIndex($errorNum, $message){
		if (!isset(self::$errorString[$errorNum])){
      $errorNum = '404';
		}
    $errorMsg = self::$errorString[$errorNum];
    header('HTTP/1.1 ' . $errorNum . ' ' . $errorMsg);
		$this->setLayoutVar('layoutTitle', $errorNum . ' ' . $errorMsg);
    $this->setVar('message', $message);
	}
	
  //Dont setup sessions on the error page - they are already setup
	protected function setupSession(){
	  return;
	}
	
	//No messages on errors
  public function getMessages(){
    return array();
  }
  
  /**
   * Dont setup ORM on errors
   */
  protected function setupDoctrine(){
    return;
  }
}
?>