<?php
/**
 * render the attachment piece of a single answer
 * Displayes the png preview and the delete link
 */
if ($attachment = $answer->getAttachment()) {
  $base = $answer->getPage()->getTitle() . '_attachment_' . $answer->getId();
  //remove slashes in path to fix an apache issues with encoding slashes in redirects
  $base = str_replace(array('/', '\\'),'slash' , $base);
  $pdfName = $base . '.pdf';
  $pngName = $base . 'preview.png';
  if (!$pdfFile = \Jazzee\Globals::getStoredFile($pdfName) or $pdfFile->getLastModified() < $answer->getUpdatedAt()) {
    \Jazzee\Globals::storeFile($pdfName, $attachment->getAttachment());
  }
  if (!$pngFile = \Jazzee\Globals::getStoredFile($pngName) or $pngFile->getLastModified() < $answer->getUpdatedAt()) {
    $blob = $attachment->getThumbnail();
    if (empty($blob)) {
      $blob = file_get_contents(realpath(\Foundation\Configuration::getSourcePath() . '/src/media/default_pdf_logo.png'));
    }
    \Jazzee\Globals::storeFile($pngName, $blob);
  }
  ?>
  <a href="<?php print $this->path('file/' . \urlencode($pdfName)); ?>"><img src="<?php print $this->path('file/' . \urlencode($pngName)); ?>" /></a>
  <?php if ($this->controller->checkIsAllowed('applicants_single', 'deleteAnswerPdf')) { ?>
    <br /><a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/deleteAnswerPdf/' . $answer->getId()); ?>' class='action'>Delete PDF</a><?php
  }
} else if ($this->controller->checkIsAllowed('applicants_single', 'attachAnswerPdf')) {
  ?>
  <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/attachAnswerPdf/' . $answer->getId()); ?>' class='actionForm'>Attach PDF</a><?php
}