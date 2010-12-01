<?php
/**
 * Interface for the Pages form builder
 * Ensures that JS can stay in sync between global page builder and program page builder
 * @author Jon Johnson <jon.johnson@ucsf.edu>
 * @package jazzee
 * @subpackage admin
 */
interface PagesInterface{
  public function actionListPages();
  public function actionListPageTypes();
  public function actionAddPage();
  public function actionDeletePage($id);
  public function actionSavePage($id);
  public function actionPreviewPage($id);
  
  public function actionListElementTypes();
  public function actionAddElement($pageID);
  public function actionDeleteElement($pageID);
  
}

?>