<?php
/**
 * Singleton for handling virtual file resources
 * Files like css, scripts, and images are given virtual paths so they can be accessed from outside the webroot
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 */
require_once('Foundation.class.php');
class Resource extends Foundation {
  /**
   * Holds the instance for singleton
   * @var Theme
   */
  private static $_instance = null;
  
  /**
   * Virtual resource paths
   * @var array
   */
  protected $paths = array();
  
  /**
   * Constructor is protected to force a singleton
   */
  protected function __construct(){}
  
  /**
   * Fetch a single instance of Resource 
   * @return Theme
   */
  public static function getInstance(){
    if(is_null(self::$_instance)){
      self::$_instance = new Resource;
    }
    return self::$_instance;
  }
  
  /**
   * Add a virtual path to a physical file
   * Multiple paths can point to the same object and paths can be overridden
   * @param string $fileSystemPath
   * @param string $virtualPath
   */
  public function add($fileSystemPath, $virtualPath){
    if(!is_readable($fileSystemPath)){
      throw new Foundation_Exception('Invalid or unreadable file system path: ' . $fileSystemPath);
    } 
    $this->paths[$virtualPath] = $fileSystemPath;
  }
  
  /**
   * Parse directory and add all files
   * @param string $directoryPath
   * @param string $virtualRoot the base virtual directory to add files to
   * @param bool $recursive should we parse recursively
   */
  public function addDirectory($directoryPath, $virtualRoot, $recursive = true){
    if(!is_readable($directoryPath) or !is_dir($directoryPath)){
      throw new Foundation_Exception('Invalid or unreadable file system path: ' . $directoryPath);
    }
    //remove any trailing slash and then add one so we always have one and never two
    $directoryPath = rtrim($directoryPath, '/') . '/';
    $virtualRoot = rtrim($virtualRoot, '/') . '/';
    $paths = array(
      $directoryPath => $virtualRoot
    );
    //whenever a subdirectory is discovered in the array continue traversing the array
    //should allow for infinite direcotry recursion
    reset ($paths);
    while (list($fileSystemPath, $virtualPath) = each ($paths)) {
      $handle = opendir($fileSystemPath);
      while (false !== ($file = readdir($handle))) {
        //if the file isn't . or .. or hidden .FOO
        if(strpos($file, '.') !== 0){
          if(is_dir($fileSystemPath . $file)){
          	if($recursive) {
              $paths[$fileSystemPath . $file . '/'] = $virtualPath . $file . '/';
          	}
          } else {
            $this->add($fileSystemPath . $file, $virtualPath . $file);
          }
        }
      }
      closedir($handle);
    }
  }
  
  /**
   * Output a resource if it is available
   * @param string $virtualPath
   */
  public function output($virtualPath){
    if(array_key_exists($virtualPath, $this->paths)){
      $realPath = $this->paths[$virtualPath];
      if(is_readable($realPath)){
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) AND (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == filemtime($realPath))) {
          // This is a cached file send the file time back with a 304 Not Modified header
          header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($realPath)) . ' GMT', true, 304);
          exit(0);
        }
        
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($realPath)) . ' GMT');
        header('Content-Type: ' . $this->getContentType($realPath));
        header('Content-Disposition: attachment; filename='. basename($virtualPath));
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: ' . filesize($realPath));
        readfile($realPath);
        exit(0);
      }
    }
    //if we fail send a 404
    header("HTTP/1.0 404 Not Found");
    print '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">'
      . "\n<html><head>"
      . "\n<title>404 Not Found</title>"
      . "\n</head><body>"
      . "\n<h1>Not Found</h1>"
      . "\n<p>The requested page was not found on this server.</p>"
      . "\n</body></html>";
    trigger_error("Unable to load resource {$virtualPath}");
    exit(0);
  }
  
  /**
   * Get the content type for a file by extension
   * @param string $path to file
   * @return string
   */
  protected function getContentType($path){
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
  
    $extension = strtolower(array_pop(explode('.',$path)));
    if (array_key_exists($extension, $mimeTypes)) {
        return $mimeTypes[$extension];
    }
    //default type
    return 'application/octet-stream';
  }
}
?>