<?php
namespace Jazzee\Console;

/**
 * Mail Log file summery to someone
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class MailLogs extends \Symfony\Component\Console\Command\Command
{

  /**
   * The configuration
   * @var \Jazzee\Configuration
   */
  protected $_config;

  /**
   * @var string format for dates
   */
  const DATE_FORMAT = 'M d H:i';

  /**
   * @see Console\Command\Command
   */
  protected function configure()
  {
    $this->setName('mail-logs')->setDescription('Email log file summary.');
    $this->addOption('error-log', null, \Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Should the Error Log be parsed?');
    $this->addOption('access-log', null, \Symfony\Component\Console\Input\InputOption::VALUE_NONE, 'Should the Access Log be parsed?');
    $this->addOption('from', null, \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'Start date/time yyyy-mm-dd hh:mm:ss', '2000-01-01 00:00:00');
    $this->addOption('to', null, \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED, 'End date/time yyyy-mm-dd hh:mm:ss', 'now');
    $this->addArgument('email', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'Email Address to receive logs.');
  }

  /**
   * @param \Symfony\Component\Console\Input\InputInterface $input
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   */
  protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
  {
    $this->_config = new \Jazzee\Configuration;
    $foundationConfig = new \Foundation\Configuration();
    $foundationConfig->setCacheType('array');
    $foundationConfig->setMailSubjectPrefix($this->_config->getMailSubjectPrefix());
    $foundationConfig->setMailDefaultFromAddress($this->_config->getMailDefaultFromAddress());
    $foundationConfig->setMailDefaultFromName($this->_config->getMailDefaultFromName());
    $foundationConfig->setMailOverrideToAddress($this->_config->getMailOverrideToAddress());
    $foundationConfig->setMailServerType($this->_config->getMailServerType());
    $foundationConfig->setMailServerHost($this->_config->getMailServeHost());
    $foundationConfig->setMailServerPort($this->_config->getMailServerPort());
    $foundationConfig->setMailServerUsername($this->_config->getMailServerUsername());
    $foundationConfig->setMailServerPassword($this->_config->getMailServerPassword());

    $entityManager = $this->getHelper('em')->getEntityManager();

    $body = '';
    $from = new \DateTime($input->getOption('from'));
    $to = new \DateTime($input->getOption('to'));
    if ($input->getOption('error-log')) {
      $messages = $this->parseErrorLog($from, $to);
      $body .= '<h4>Error Log</h4><p>There were ' . count($messages) . ' errors between ' . $from->format(self::DATE_FORMAT) . ' and ' . $to->format(self::DATE_FORMAT) . '</p>';
      $body .= $this->formatString($messages);
    }
    if ($input->getOption('access-log')) {
      $messages = $this->parseAccessLog($from, $to);
      $body .= '<h4>Access Log ' . $from->format(self::DATE_FORMAT) . ' to ' . $to->format(self::DATE_FORMAT) . '</h4>';
      $body .= $this->formatString($messages);
    }
    $message = new \Foundation\Mail\Message($foundationConfig);
    $message->IsHTML();
    $message->AddAddress($input->getArgument('email'));
    $message->Subject = 'Jazzee Log Summary';
    $message->Body = $body;
    $message->Send();

    $output->write("<info>Log Summary sent to {$input->getArgument('email')}.</info>" . PHP_EOL);
  }

  /**
   * Format a message array to a string
   * @param array $messages
   * @return string
   */
  protected function formatString(array $messages)
  {
    $string = '';
    foreach ($messages as $arr) {
      $string .= "<p>{$arr['message']} <br /><em>[{$arr['stamp']->format(self::DATE_FORMAT)}]";
      if ($arr['count'] > 1) {
        $string .= ' repeated ' . $arr['count'] . ' times';
      }
      $string .= '</em></p>';
    }
    return $string;
  }

  /**
   * Parse the format of the error log and get a message array
   * @param \DateTime $from
   * @param \DateTime $to
   * @return array
   */
  protected function parseErrorLog(\DateTime $from, \DateTime $to)
  {
    $messages = array();
    $singleTemplate = array(
      'count' => 0,
      'stamp' => null,
      'message' => null
    );
    if($path = $this->getPath('error_log')){
      $lines = file($path);
      foreach ($lines as $line) {
        $matches = array();
        if (preg_match('#^\[([0-9-:\s]+)\]([^\[]+)(?:[\[\]]+)[^{]+{(.*)}$#', $line, $matches)) {
          $stamp = new \DateTime($matches[1]);
          if ($stamp > $from AND $stamp < $to) {
            $message = trim($matches[2]);
            $hash = md5($message);
            if (!array_key_exists($hash, $messages)) {
              $messages[$hash] = $singleTemplate;
            }
            $arr = array();
            if (preg_match_all('#"([^"]+)":"([^"]+)",#', $matches[3], $arr)) {
              $extras = array();
              foreach (array_keys($arr[0]) as $key) {
                $extras[$arr[1][$key]] = $arr[2][$key];
              }
            }
            $messages[$hash]['stamp'] = $stamp;
            $messages[$hash]['count']++;
            $messages[$hash]['message'] = "{$message} in {$extras['file']}";
          }
        }
      }
    }
    return $messages;
  }

  /**
   * Get the path to the log directory
   * @param string $fileName
   * @return string
   */
  protected function getPath($fileName)
  {
    $path = $this->_config->getVarPath() . '/log/' . $fileName;
    if (!$realPath = \realpath($path) or !\is_readable($realPath)) {
      return false;
    }

    return $realPath;
  }

}