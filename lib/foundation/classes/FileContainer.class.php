<?php
/**
 * Class for securely holding virtual files from any source and outputting them to the browser
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 */
require_once('Foundation.class.php');
class FileContainer extends Foundation {
  /**
   * The file
   * @var blob
   */
  protected $blob;
  
  /**
   * Last modified timestamp
   * @var integer
   */
  protected $lastModified;
  
  /**
   * The type of file (extension)
   * @var string
   */
  protected $type;
  
  /**
   * The name of the file
   * @var string
   */
  protected $name;
  
  /**
   * Constructor
   * @param blob $blob the contents of the file
   * @param string $type the extension (pdf, png, etc) of the file
   * @param string $name optionally a name for the file
   */
  public function __construct($blob, $type, $name = ''){
    $this->blob = $blob; 
    $this->type = $type;
    if($name){
      $this->name = $name  . ".{$type}";
    } else {
      $this->name = uniqid() . ".{$type}";
    }
  }
  
  /**
   * Set the last modified time on the file for caching
   * @param integer $timestamp unix time stamp for last modified time
   */
  public function setLastModified($timestamp){
    $this->lastModified = $timestamp;
  }
  
  /**
   * Output the file
   */
  public function output(){
    if (!empty($this->lastModified) and isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) AND (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $this->lastModified)) {
      // This is a cached file send the file time back with a 304 Not Modified header
      header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $this->lastModified) . ' GMT', true, 304);
      exit(0);
    }
    
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $this->lastModified) . ' GMT');
    header('Content-Type: ' . $this->getContentType($this->blob));
    header('Content-Disposition: attachment; filename='. $this->name);
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . strlen($this->blob));
    print $this->blob;
    exit(0);
    //if we fail send a 404
    header("HTTP/1.0 404 Not Found");
    print '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">'
      . "\n<html><head>"
      . "\n<title>404 Not Found</title>"
      . "\n</head><body>"
      . "\n<h1>Not Found</h1>"
      . "\n<p>The requested page was not found on this server.</p>"
      . "\n</body></html>";
    trigger_error("Unable to load resource {$this->name}");
    exit(0);
  }
  
  /**
   * Get the content type for a file
   * Try the new Fileinfo (php 5.3) then use the extension to guess the type
   * @param string $path to file
   * @return string
   */
  protected function getContentType(){
    if(function_exists('finfo_open')) {
      $finfo = finfo_open(FILEINFO_MIME);
      $mimetype = finfo_buffer($finfo, $this->blob);
      finfo_close($finfo);
      return $mimetype;
    }
    $mimeTypes = array(
      'txt' => 'text/plain',
      'css' => 'text/css',
      'js' => 'application/javascript',
      'htm' => 'text/html',
      'html' => 'text/html',
      'xml' => 'application/xml',
      'swf' => 'application/x-shockwave-flash',
      'flv' => 'video/x-flv',
      'png' => 'image/png',
      'jpe' => 'image/jpeg',
      'jpeg' => 'image/jpeg',
      'jpg' => 'image/jpeg',
      'gif' => 'image/gif',
      'bmp' => 'image/bmp',
      'ico' => 'image/vnd.microsoft.icon',
      'tiff' => 'image/tiff',
      'tif' => 'image/tiff',
      'svg' => 'image/svg+xml',
      'svgz' => 'image/svg+xml',
      'pdf' => 'application/pdf'
    );
    if (array_key_exists($this->type, $mimeTypes)) {
        return $mimeTypes[$this->type];
    }
    //default type
    return 'application/octet-stream';
  }
}
?>