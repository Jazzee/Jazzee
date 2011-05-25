/**
 * The DateElement type
  @extends ApplyElement
 */
function DateElement(){}
DateElement.prototype = new ApplyElement();
DateElement.prototype.constructor = DateElement;

DateElement.prototype.avatar = function(){
  return $('<input type="text" disabled="true">');
};