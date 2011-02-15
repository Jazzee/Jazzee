/**
 * The TextareaElement type
  @extends ApplyElement
 */
function TextareaElement(){}
TextareaElement.prototype = new ApplyElement();
TextareaElement.prototype.constructor = TextareaElement;

TextareaElement.prototype.avatar = function(){
  return $('<textarea>').attr('disabled', true);
};