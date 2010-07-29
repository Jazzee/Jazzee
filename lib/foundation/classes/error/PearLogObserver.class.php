<?php
/**
 * Use a PEAR Log object to observer errors
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage error
 * 
*/
class PearLogObserver extends ErrorObserver {
  /**
   * @var $_pearLog holds the log object
   */
  private $_pearLog;
  /**
   * constructor
   * @param Log $object the pear Log object
   * @param integer $level the PHP Error level to submit logs in
   */
   public function __construct(Log $object){
     $this->_pearLog = $object;
   }
   
  public function update(ErrorMessage $error){
    $message = "{$error->message} in {$error->fileName} on line {$error->lineNumber}";
    $this->_pearLog->log($message, $this->phpErrorToPearLogError($error->level));
  }

  public function phpErrorToPearLogError($errNum){
    switch ($errNum) {
      case E_ERROR:
      case E_PARSE:
      case E_CORE_ERROR:
      case E_COMPILE_ERROR:
      case E_USER_ERROR:
      case E_RECOVERABLE_ERROR:
        return PEAR_LOG_CRIT;
        break;
      case E_WARNING:
      case E_CORE_WARNING:
      case E_COMPILE_WARNING:
      case E_USER_WARNING:
        return PEAR_LOG_WARNING;
        break;
      case E_NOTICE:
      case E_USER_NOTICE:
        return PEAR_LOG_NOTICE;
        break;
      case E_STRICT:
      case E_DEPRECATED:
      case E_USER_DEPRECATED:
        return PEAR_LOG_DEBUG;
        break;
    }
  } 
}
?>
