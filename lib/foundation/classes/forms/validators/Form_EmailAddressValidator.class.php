<?php
/**
 * Check to see if the email address is valid
 * Inspiration from http://www.linuxjournal.com/article/9585
 * If the ruleSet is set to true we will do a domain lookup to check if the email address domain is valid
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package foundation
 * @subpackage forms
 */
class Form_EmailAddressValidator extends Form_Validator{
  /**
   * The error string
   * @const string
   */
  const ERROR = 'Invlid Email address';
  
  public function validate(FormInput $input){
    if(!is_null($input->{$this->e->name})){
      $value = $input->{$this->e->name};
      //get the last @ sign
      $lastAtSign = strrpos($value, '@');
      if($lastAtSign === false){
        $this->addError(self::ERROR);
        return false;
      }
      $localPart = substr($value, 0, $lastAtSign);
      $domainPart = substr($value, $lastAtSign+1);
      
      //check the lengths of the parts first becuase it is fast
      if (
        strlen($localPart) < 1 OR 
        strlen($localPart) > 64 OR
        strlen($domainPart) < 1 OR 
        strlen($domainPart) > 255
        ){
          $this->addError(self::ERROR);
          return false;
      }
      
      //then check the content of the local part
      if(
        !preg_match(
          //allowed localPart expressions
          '/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
          //get ride of double backslashes to our regular expression is easier to understand
          str_replace("\\\\","",$localPart)
        )
      ){
      //The local part must be quoted
      if (
        !preg_match(
          //allowed localPart expressions when the local part is quoted
          '/^"(\\\\"|[^"])+"$/', 
          //get ride of double backslashes so our regular expression is easier to understand
          str_replace("\\\\","",$localPart)))
        {
          $this->addError(self::ERROR);
          return false;
        }
      }
      //dot isn't the first or last charecter
      if($localPart[0] == '.' OR $localPart[strlen($localPart)-1] == '.'){
        $this->addError(self::ERROR);
        return false;
      }
      //no two consecutive dots
      if (preg_match('/\\.\\./', $localPart)){
        $this->addError(self::ERROR);
        return false;
      }
      if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domainPart)){
        $this->addError(self::ERROR);
        return false;
      }
      if (preg_match('/\\.\\./', $domainPart)){
        $this->addError(self::ERROR);
        return false;
      }
      if($this->ruleSet){
        if(
          //check that the domain is valid
          !(
            checkdnsrr($domainPart,"MX") OR
            checkdnsrr($domainPart, "A") //only check the A if MX is invalid
          )
        ){
          $this->addError(self::ERROR);
          return false;
        }
      }
    }
    return true;
  }
}
?>
