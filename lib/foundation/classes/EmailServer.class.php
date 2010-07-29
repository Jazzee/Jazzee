<?php
/**
 * Wrapper for sending emails
 * Standardizes interface to Pear Mail or some other mail system
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 */
require_once('Foundation.class.php');

class EmailServer extends Foundation {
  /**
   * Holds the Pear Mail class
   * @var Mail
   */
  private $_mail;

  /**
   * Prepended to the subject of every message
   * @var string
   */
  private $_subjectPrefix = '';
  
  /**
   * The default address to send from if another is not specified
   * @var string
   */
  private $_defaultFrom  = '';
  
  /**
   * Used in testing to ignore the addressee and send everythign to the admin
   * @var string
   */
  private $_overrideTo = null;
  
  
  /**
   * Parse the connection string to load options
   * @param $cnString string connection information type://user:password@host:port
   */
  public function __construct(){
    $cn = array('type'=>'', 'username'=>'', 'password'=>'', 'host'=>'', 'port'=>'');
    if(empty($cnString) OR $cnString == 'mail'){
      $cn['type'] = 'mail';
    } else if ($cnString == 'sendmail'){
      $cn['type'] = 'sendmail';
    } else {
      if(!preg_match('#^[a-z+]+://([a-z0-9@.]+:(.*)@)?[a-z0-9\.]+(:?[0-9]*)$#i', $cnString)){
        throw new Foundation_Exception('Bad email host string ' . $cnString);
      }
      $split = preg_split( '-://-',$cnString, 2); 
      $cn['type'] = $split[0];
      if(preg_match('#@#', $split[1])){
        $split = preg_split('-:-',$split[1], 2);
        $cn['username'] = $split[0];
        $cn['password'] = substr($split[1], 0, -strlen(strrchr($split[1], '@')));
        $split[1] = strrchr($split[1], '@');
      }
      $split = explode(':',$split[1]);
      
      $cn['host'] = ltrim($split[0], '@');
      $cn['port'] = !empty($split[1])?$split[1]:'';
    }
    switch($cn['type']){
      case 'mail':
        $this->_mail = Mail::factory('mail');
      break;
      case 'sendmail':
        $this->_mail = Mail::factory('sendmail');
      break;
      case 'smtp+ssl':
        $cn['host'] = 'ssl://' . $cn['host'];
      case 'smtp':
        $params = array('host' => $cn['host']);
        if(!empty($cn['port'])){
          $params['port'] = $cn['port'];
        }
        if(!empty($cn['username'])){
          $params['auth'] = true;
          $params['username'] = $cn['username'];
        }
        if(!empty($cn['password'])){
          $params['password'] = $cn['password'];
        }
        
        $this->_mail = Mail::factory('smtp',$params);
      break;
      default:
        throw new Foundation_Exception('Unknown mail connection type ' . $cn['type']);
    }
  }
  
  /**
   * The message is from
   * @param string $address email address
   * @param string $name the Name of the sender
   */
  public function defaultFrom($address, $name = ''){
    $this->_defaultFrom = "{$name} <{$address}>";
  }
  
    
  /**
   * Override the recipients and only send to this address
   * Userd fr testing systems to ensure mail doesn't leak out
   * @param string $address email address
   * @param string $name the name of the recipient
   */
  public function overrideTo($address, $name=''){
    $this->_overrideTo = "{$name} <{$address}>";
  }
  
  /**
   * Add a subject prefix
   * The prefix is added to every email composed in this class
   * @param string $prefix
   */
   public function subjectPrefix($prefix){
     $this->_subjectPrefix = $prefix;
   }
  
  /**
   * Send an email message
   * @param EmailMessage $message
   */
  public function send(EmailMessage $message){
    $headers = $message->headers();
    if(empty($headers['From'])){
      $headers['From'] = $this->_defaultFrom;
    }
    if($this->_overrideTo){
      $to = $this->_overrideTo;
    } else {
      $to = $message->recipients();
    }
    return $this->_mail->send($to, $headers, $message->body);
  }
  
}
?>
