<?php
/**
 * Abstract error observer class
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license.txt
 * @package foundation
 * @subpackage error
 * 
*/
abstract class ErrorObserver extends Foundation {
  /**
   * Takes the update from the observed
   * @param $error ErrorMessage
   */
  abstract public function update(ErrorMessage $error);
}
?>