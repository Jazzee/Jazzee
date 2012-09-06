<?php
namespace Jazzee\Element;

/**
 * PDF File Element
 *
 * @author  Jon Johnson  <jon.johnson@ucsf.edu>
 * @license http://jazzee.org/license BSD-3-Clause
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PDFFileInput extends AbstractElement
{

  const PAGEBUILDER_SCRIPT = 'resource/scripts/element_types/JazzeeElementPDFFileInput.js';

  public function addToField(\Foundation\Form\Field $field)
  {
    if (!ini_get('file_uploads')) {
      throw new \Jazzee\Exception('File uploads are not turned on for this system and a PDFFileInputElement is being created', E_ERROR);
    }
    $element = $field->newElement('FileInput', 'el' . $this->_element->getId());
    $element->setLabel($this->_element->getTitle());
    $element->setInstructions($this->_element->getInstructions());
    $element->setFormat($this->_element->getFormat());
    $element->setDefaultValue($this->_element->getDefaultValue());
    if ($this->_element->isRequired()) {
      $validator = new \Foundation\Form\Validator\NotEmpty($element);
      $element->addValidator($validator);
    }
    $element->addValidator(new \Foundation\Form\Validator\Virusscan($element));
    $element->addValidator(new \Foundation\Form\Validator\PDF($element));
    $element->addValidator(new \Foundation\Form\Validator\PDFNotEncrypted($element));
    $element->addFilter(new \Foundation\Form\Filter\Blob($element));

    $config = $this->_controller->getConfig();
    $max = $config->getMaximumApplicantFileUploadSize();
    if ($this->_element->getMax() and \Foundation\Utility::convertIniShorthandValue($this->_element->getMax()) < $max) {
      $max = $this->_element->getMax();
    } else {
      $max = $config->getDefaultApplicantFileUploadSize();
    }
    $element->addValidator(new \Foundation\Form\Validator\MaximumFileSize($element, $max));

    return $element;
  }

  public function getElementAnswers($input)
  {
    $elementAnswers = array();
    if (!is_null($input)) {
      $elementAnswer = new \Jazzee\Entity\ElementAnswer;
      $elementAnswer->setElement($this->_element);
      $elementAnswer->setPosition(0);
      $elementAnswer->setEBlob($input);
      $elementAnswers[] = $elementAnswer;

      $elementAnswer = new \Jazzee\Entity\ElementAnswer;
      $elementAnswer->setElement($this->_element);
      $elementAnswer->setPosition(1);
      $elementAnswers[] = $elementAnswer;
    }

    return $elementAnswers;
  }

  public function displayValue(\Jazzee\Entity\Answer $answer)
  {
    $elementAnswers = $answer->getElementAnswersForElement($this->_element);
    if (isset($elementAnswers[0])) {
      $base = $answer->getApplicant()->getFullName() . ' ' . $this->_element->getTitle() . '_' . $answer->getApplicant()->getId() . $elementAnswers[0]->getId();
      $pdfName = $base . '.pdf';
      $pngName = $base . 'preview.png';
      if (!$pdfFile = $this->_controller->getStoredFile($pdfName) or $pdfFile->getLastModified() < $answer->getUpdatedAt()) {
        $this->_controller->storeFile($pdfName, $elementAnswers[0]->getEBlob());
      }
      if (!$pngFile = $this->_controller->getStoredFile($pngName) or $pngFile->getLastModified() < $answer->getUpdatedAt()) {
        $blob = $elementAnswers[1]->getEBlob();
        if (empty($blob)) {
          $blob = file_get_contents(realpath(\Foundation\Configuration::getSourcePath() . '/src/media/default_pdf_logo.png'));
        }
        $this->_controller->storeFile($pngName, $blob);
      }

      return '<a href="' . $this->_controller->path('file/' . \urlencode($pdfName)) . '"><img src="' . $this->_controller->path('file/' . \urlencode($pngName)) . '" /></a>';
    }

    return null;
  }

  public function rawValue(\Jazzee\Entity\Answer $answer)
  {
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if (isset($elementsAnswers[0])) {
      return base64_encode($elementsAnswers[0]->getEBlob());
    }

    return null;
  }

  public function pdfValue(\Jazzee\Entity\Answer $answer, \Jazzee\ApplicantPDF $pdf)
  {
    $elementsAnswers = $answer->getElementAnswersForElement($this->_element);
    if (isset($elementsAnswers[0])) {
      $pdf->addPdf($elementsAnswers[0]->getEBlob());

      return 'Attached';
    }

    return null;
  }

  public function formValue(\Jazzee\Entity\Answer $answer)
  {
    return false;
  }

  /**
   * Get the answer value as an xml element
   * Add the PDF size as an attribute
   * @param \DomDocument $dom
   * @param \Jazzee\Entity\Answer $answer
   * @return \DomElement
   */
  public function getXmlAnswer(\DomDocument $dom, \Jazzee\Entity\Answer $answer)
  {
    $eXml = $dom->createElement('element');
    $eXml->setAttribute('elementId', $this->_element->getId());
    $eXml->setAttribute('title', htmlentities($this->_element->getTitle(), ENT_COMPAT, 'utf-8'));
    $eXml->setAttribute('name', htmlentities($this->_element->getName(), ENT_COMPAT, 'utf-8'));
    $eXml->setAttribute('type', htmlentities($this->_element->getType()->getClass(), ENT_COMPAT, 'utf-8'));
    $eXml->setAttribute('weight', $this->_element->getWeight());
    if ($value = $this->rawValue($answer)) {
      $eXml->setAttribute('size', strlen($value));
      $eXml->appendChild($dom->createCDATASection(preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $value)));
    }
    return $eXml;
  }

  /**
   * Render PDF Previews
   * @param AdminCronController $cron
   */
  public static function runCron(\AdminCronController $cron)
  {
    $count = 0;
    $start = time();
    $type = $cron->getEntityManager()->getRepository('\Jazzee\Entity\ElementType')->findOneBy(array('class' => '\Jazzee\Element\PDFFileInput'));
    if ($type) {
      $blankPreviewElementAnswers = $cron->getEntityManager()->getRepository('\Jazzee\Entity\ElementAnswer')->findByType($type, array('position' => 1, 'eBlob' => null), 500);
      $imagick = new \imagick;
      foreach ($blankPreviewElementAnswers as $blankPreviewElementAnswer) {
        $blobElementAnswer = $blankPreviewElementAnswer->getAnswer()->getElementAnswersForElementByPosition($blankPreviewElementAnswer->getElement(), 0);
        try {
          $blob = $blobElementAnswer->getEBlob();
          //use a temporary file so we can use the image magic shortcut [0]
          //to load only the first page, otherwise the whole file gets loaded into memory and takes forever
          $handle = tmpfile();
          fwrite($handle, $blob);
          $arr = stream_get_meta_data($handle);
          $imagick->readimage($arr['uri'] . '[0]');
          $imagick->setImageFormat("png");
          $imagick->thumbnailimage(100, 150, true);
          fclose($handle);
        } catch (ImagickException $e) {
          $imagick = new \imagick;
          $imagick->readimage(realpath(\Foundation\Configuration::getSourcePath() . '/src/media/default_pdf_logo.png'));
          $imagick->thumbnailimage(100, 150, true);
        }
        $blankPreviewElementAnswer->setEBlob($imagick->getimageblob());
        $cron->getEntityManager()->persist($blankPreviewElementAnswer);
        $cachedFileName = $blankPreviewElementAnswer->getAnswer()->getApplicant()->getFullName() . ' ' . $blankPreviewElementAnswer->getElement()->getTitle() . '_' . $blankPreviewElementAnswer->getAnswer()->getApplicant()->getId() . $blobElementAnswer->getId() . 'preview.png';
        $cron->removeStoredFile($cachedFileName);
        $count++;
        $imagick->clear();
      }
      unset($imagick);
    }
    if ($count) {
      $cron->log("Generated {$count} PDFFileInput thumbnail(s) in " . (time() - $start) . ' seconds.');
    }
  }

  /**
   * PDF File configuration varialbes
   * @param \Jazzee\Configuration $configuration
   * @return array
   */
  public static function getConfigurationVariables(\Jazzee\Configuration $configuration)
  {
    return array(
      'defaultApplicantFileUploadSize' => $configuration->getDefaultApplicantFileUploadSize(),
      'maximumApplicantFileUploadSize' => $configuration->getMaximumApplicantFileUploadSize()
    );
  }

}