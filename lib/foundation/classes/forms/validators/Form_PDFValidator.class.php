<?php
/**
 * Ensure the uploaded file is a pdf
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form_PDFValidator extends Form_Validator{
  public function validate(FormInput $input){
    if(!is_null($input->{$this->e->name})){
      $validMimeTypes = array('application/pdf',
                              'application/pdf; charset=binary',
                              'application/x-pdf',
                              'application/acrobat',
                              'applications/vnd.pdf',
                              'text/pdf',
                              'text/x-pdf');
      $fileArr = $input->{$this->e->name};
      //simplest check, however the type is sent by the browser and can be forged
      if(!in_array($fileArr['type'], $validMimeTypes)){
        $this->addError("Your browser is reporting that this is a file of type {$fileArr['type']} which is not a valid PDF.");
        return false;
      }
      //obviously easily changed but check the extension
      $arr =explode('.', $fileArr['name']);
      $extension = array_pop($arr);
      if(strtolower($extension) != 'pdf'){
        $this->addError("This is a file has the extension .{$extension} .pdf is required.");
        return false;
      }
      //FileInfo is a pecl extension that is present by default in php 5.3+
      if(function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME);
        $mimetype = finfo_file($finfo, $fileArr['tmp_name']);
        finfo_close($finfo);
        if(!in_array($mimetype, $validMimeTypes)){
          $this->addError("This is a file of type {$mimetype} which is not a valid PDF.");
          return false;
        }
      } else {
        //if FileInfo isn't around we can call the unix file command directly
        $filePath = exec('which file');
        if($filePath){
          $mimetype = exec("{$filePath} -bi {$fileArr['tmp_name']}");
          if(!in_array($mimetype, $validMimeTypes)){
            $this->addError("This is a file of type {$mimetype} which is not a valid PDF.");
            return false;
          }
        } else {
          trigger_error('No accurate file verifiation methods were found on the server.  PHP file type can not be verified.');
        }
      }
    }
    return true;
  }
}
?>
