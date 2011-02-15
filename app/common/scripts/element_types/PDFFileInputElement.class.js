/**
 * The PDFFileInputElement type
  @extends ApplyElement
 */
function PDFFileInputElement(){}
PDFFileInputElement.prototype = new ApplyElement();
PDFFileInputElement.prototype.constructor = PDFFileInputElement;

PDFFileInputElement.prototype.avatar = function(){
  return $('<input>').attr('type', 'file').attr('disabled', true);
};