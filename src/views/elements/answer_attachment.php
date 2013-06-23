<?php
/**
 * render the attachment piece of a single answer
 * Displayes the png preview and the delete link
 */
if ($attachment = $answer->getAttachment()) {
  $base = $answer->getPage()->getTitle() . '_attachment_' . $attachment->getId();
  //remove slashes in path to fix an apache issues with encoding slashes in redirects
  $base = str_replace(array('/', '\\'),'slash' , $base);
  $pdfName = $base . '.pdf';
  $pngName = $base . 'preview.png';
  \Jazzee\Globals::getFileStore()->createSessionFile($pdfName, $attachment->getAttachmentHash());
  if($attachment->getThumbnailHash() != null){
    \Jazzee\Globals::getFileStore()->createSessionFile($pngName, $attachment->getThumbnailHash());
    $thumbnailPath = \Jazzee\Globals::path('file/' . \urlencode($pngName));
  } else {
    $thumbnailPath = \Jazzee\Globals::path('resource/foundation/media/default_pdf_logo.png');
  }
  ?>
  <a href="<?php print $this->path('file/' . \urlencode($pdfName)); ?>"><img src="<?php print $thumbnailPath; ?>" /></a>
  <?php if ($this->controller->checkIsAllowed('applicants_single', 'deleteAnswerPdf')) { ?>
    <br /><a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/deleteAnswerPdf/' . $answer->getId()); ?>' class='action'>Delete PDF</a><?php
  }
} else if ($this->controller->checkIsAllowed('applicants_single', 'attachAnswerPdf')) {
  ?>
  <a href='<?php print $this->path('applicants/single/' . $answer->getApplicant()->getId() . '/attachAnswerPdf/' . $answer->getId()); ?>' class='actionForm'>Attach PDF</a><?php
}