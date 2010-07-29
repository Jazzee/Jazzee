<?php
/**
 * A File Element
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage forms
 */
class Form_FileInputElement extends Form_InputElement{
  /**
   * The maximum size in bytes
   * @var string 
   */
  protected $maxSize;
  
  public $type = 'file';
  public function __construct($field){
    parent::__construct($field);
    //set the encoding type for the parent form
    $this->field->form->enctype = 'multipart/form-data';
  }
  
  /**
   * Validate user input
   * Files need to do some initial validation to ensure they were uploaded successfully
   * @param FormInput $input
   */
  public function validate(FormInput $input){
    $fileArr = $input->{$this->name};
    //if no file was updaed
    if($fileArr['size'] == 0 AND $fileArr['name'] === ''){
      $input->{$this->name} = null;
    } else {
      //look for upload errors
      if($fileArr['error'] != UPLOAD_ERR_OK){
        switch($fileArr['error']){
          case UPLOAD_ERR_INI_SIZE:
          case UPLOAD_ERR_FORM_SIZE:
            $text = 'Your file is greater than the maximum allowed size of ' . convertBytesToString($this->maxSize);
            break;
          case UPLOAD_ERR_PARTIAL:
            $text = 'Your file upload was stopped before it completed.  This is probably a temporary problem with your connection to our server.  Please try again.';
            break;
          case UPLOAD_ERR_NO_FILE:
            $text = 'No file was uploaded';
            break;
          //the rest of the errors are configuration errors and throw exceptions
          case UPLOAD_ERR_NO_TMP_DIR:
            throw new Foundation_Exception('Unable to upload file: no temporary directory was found.', E_USER_ERROR, 'The server encountered an error uploading your file.  Please try again.');
            break;
          case UPLOAD_ERR_CANT_WRITE:
            throw new Foundation_Exception('Unable to upload file: could not write to disk.', E_USER_ERROR, 'The server encountered an error uploading your file.  Please try again.');
            brea;
          case UPLOAD_ERR_EXTENSION:
            throw new Foundation_Exception('Unable to upload file: A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to sto.', E_USER_ERROR, 'The server encountered an error uploading your file.  Please try again.');
            break;
          default:
            $text = 'There was an error uploading your file.  Please try again.';
        }
        //write any error messages to the validationSet
        $this->_validatorSet->addError(new Form_ValidationError($text));
      }
    }
    //pass the input back and let the rest of the validator set handle it
    return parent::validate($input);
  }
  
  /**
   * Get Value (no value for files)
   * @return null
   */
  public function getValue(){
    return null;
  }
  
  /**
   * Get the maxSize value
   * if no value is set return the PHP upload_max_filesize ini value
   * @return integer max size in bytes
   */
  public function getMaxSize(){
    if(is_null($this->maxSize)){
      return convertIniShorthandValue(ini_get('upload_max_filesize'));
    }
    return $this->maxSize;
  }
  
  /**
   * Set the maximum upload size
   * do some checking to make sure we aren't futilely setting the size larger than one of the ini options can use 
   * @param integer $maxSize max size in bytes
   */
  public function setMaxSize($maxSize){
    $maxSize = convertIniShorthandValue($maxSize);
    if($maxSize > convertIniShorthandValue(ini_get('upload_max_filesize')))
      throw new Foundation_Exception('Attempting to set FileInput::maxSize to a value greater than upload_max_filesize');
    if($maxSize > convertIniShorthandValue(ini_get('post_max_size')))
      throw new Foundation_Exception('Attempting to set FileInput::maxSize to a value greater than post_max_size');
    $this->maxSize = $maxSize;
  }
}
?>