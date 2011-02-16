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
  public function actionSavePage($pageId);
  public function actionPreviewPage();
  public function actionListElementTypes();
}

?>