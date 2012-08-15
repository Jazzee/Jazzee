<?php
/**
 * render the attachment piece of a single answer
 * Displayes the png preview and the delete link
 */
if ($attachment = $answer->getAttachment()) {
  $pdfName = $answer->getPage()->getTitle() . '_attachment_' . $answer->getId() . '.pdf';
  $pngName = $answer->getPage()->getTitle() . '_attachment_' . $answer->getId() . 'preview.png';
  if (!$pdfFile = $this->controller->getStoredFile($pdfName) or $pdfFile->getLastModified() < $answer->getUpdatedAt()) {
    $this->controller->storeFile($pdfName, $attachment->getAttachment());
  }
  if (!$pngFile = $this->controller->getStoredFile($pngName) or $pngFile->getLastModified() < $answer->getUpdatedAt()) {
    $blob = $attachment->getThumbnail();
    if (empty($blob)) {
      $blob = file_get_contents(realpath(\Foundation\Configuration::getSourcePath() . '/src/media/default_pdf_logo.png'));
    }
    $this->controller->storeFile($pngName, $blob);
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