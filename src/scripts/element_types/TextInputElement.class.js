/**
 * The TextInputElement type
  @extends ApplyElement
 */
function TextInputElement(){}
TextInputElement.prototype = new ApplyElement();
TextInputElement.prototype.constructor = TextInputElement;

TextInputElement.prototype.avatar = function(){
  return $('<input type="text" disabled="true">');
};