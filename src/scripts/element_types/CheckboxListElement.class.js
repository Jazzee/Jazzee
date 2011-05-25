/**
 * The CheckboxListElement type
  @extends ApplyElement
 */
function CheckboxListElement(){}
CheckboxListElement.prototype = new ListElement();
CheckboxListElement.prototype.constructor = CheckboxListElement;

CheckboxListElement.prototype.avatar = function(){
  return $('<input>').attr('type', 'checkbox').attr('disabled', true);
};