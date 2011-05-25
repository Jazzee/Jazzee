/**
 * The RadioListElement type
  @extends ApplyElement
 */
function RadioListElement(){}
RadioListElement.prototype = new ListElement();
RadioListElement.prototype.constructor = RadioListElement;

RadioListElement.prototype.avatar = function(){
  return $('<input>').attr('type', 'radio').attr('disabled', true);
};