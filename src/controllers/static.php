<?php

/**
 * Any call to /static looks for a cached file
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 */
class StaticController extends \Jazzee\PageController
{

  public function actionGet($fileName)
  {
    $safeFileName = basename($fileName);
    $file = new \Foundation\Virtual\RealFile($safeFileName, $this->_config->getVarPath() . '/cache/public/' . $safeFileName);
    $file->output();
  }

}