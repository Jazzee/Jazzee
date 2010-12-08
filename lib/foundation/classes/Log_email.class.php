<?php
/**
 * Send logs via email
 * The mail is actually sent when you close() the logger, or when the destructor
 * is called (when the script is terminated) to all logs get sent at once.
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 */
class Log_email extends Log{
  /**
   * String containing the format of a log line.
   * @var string
   */
  private $_lineFormat = '%1$s %2$s [%3$s] %4$s';

  /**
   * String containing the timestamp format.  It will be passed directly to
   * strftime().  Note that the timestamp string will generated using the
   * current locale.
   * @var string
   */
  private $_timeFormat = '%b %d %H:%M:%S';

  /**
   * String holding the mail message body.
   * @var string
   * @access private
   */
  private $_message = '';

  /**
   * Flag used to indicated that log lines have been written to the message
   * body and the message should be sent on close().
   * @var boolean
   * @access private
   */
  private $_shouldSend = false;

  /**
   * EmailMessage holds the message we will send
   * @var string
   * @access private
   */
  private $_mailServer = '';

  /**
   * Constructs a new Log_email object.
   *
   * @param string $name      The message object
   * @param string $ident     The identity string.
   * @param array  $conf      The configuration array.
   * @param int    $level     Log messages up to and including this level.
   * @access public
   */
  public function __construct(EmailMessage $name, $ident = '', $conf = array(),$level = PEAR_LOG_DEBUG){
    $this->_id = md5(microtime());
    $this->_message = $name;
    $this->_ident = $ident;
    $this->_mask = Log::UPTO($level);
    
    if (!empty($conf['lineFormat'])) {
      $this->_lineFormat = str_replace(array_keys($this->_formatMap),
                             array_values($this->_formatMap),
                             $conf['lineFormat']);
    }

    if (!empty($conf['timeFormat'])) {
      $this->_timeFormat = $conf['timeFormat'];
    }
  }

  /**
   * Destructor. Calls close().
   */
  public function __destruct(){
    $this->close();
  }

  /**
   * Starts a new mail message.
   * This is implicitly called by log(), if necessary.
   *
   * @access public
   */
  public function open(){
    if (!$this->_opened) {
      $this->_opened = true;
      $_shouldSend = false;
    }
    return $this->_opened;
  }

  /**
   * Closes the message, if it is open, and sends the mail.
   * This is implicitly called by the destructor, if necessary.
   */
  public function close(){
    if ($this->_opened) {
      if ($this->_shouldSend && !empty($this->_message->body)) {
        $this->_message->send();
        /* Clear the message string now that the email has been sent. */
        $this->_message->body = '';
        $this->_shouldSend = false;
      }
        $this->_opened = false;
    }
    return ($this->_opened === false);
  }

  /**
   * Flushes the log output by forcing the email message to be sent now.
   * Events that are logged after flush() is called will be appended to a
   * new email message.
   *
   * @access public
   * @since Log 1.8.2
   */
  function flush(){
    /*
     * It's sufficient to simply call close() to flush the output.
     * The next call to log() will cause the handler to be reopened.
     */
    return $this->close();
  }

  /**
   * Writes $message to the currently open mail message.
   * Calls open(), if necessary.
   *
   * @param mixed  $message  String or object containing the message to log.
   * @param string $priority The priority of the message.  Valid
   *                  values are: PEAR_LOG_EMERG, PEAR_LOG_ALERT,
   *                  PEAR_LOG_CRIT, PEAR_LOG_ERR, PEAR_LOG_WARNING,
   *                  PEAR_LOG_NOTICE, PEAR_LOG_INFO, and PEAR_LOG_DEBUG.
   * @return boolean  True on success or false on failure.
   * @access public
   */
  function log($message, $priority = null){
    /* If a priority hasn't been specified, use the default value. */
    if ($priority === null) {
      $priority = $this->_priority;
    }

    /* Abort early if the priority is above the maximum logging level. */
    if (!$this->_isMasked($priority)) {
      return false;
    }

    /* If the message isn't open and can't be opened, return failure. */
    if (!$this->_opened && !$this->open()) {
      return false;
    }

    /* Extract the string representation of the message. */
    $message = $this->_extractMessage($message);

    /* Append the string containing the complete log line. */
    $this->_message->body .= $this->_format($this->_lineFormat,
                              strftime($this->_timeFormat),
                              $priority, $message) . "\r\n";
    $this->_shouldSend = true;

    /* Notify observers about this log message. */
    $this->_announce(array('priority' => $priority, 'message' => $message));

    return true;
  }
}
?>