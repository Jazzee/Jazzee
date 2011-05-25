/**
 * The ShortDateElement type
  @extends ApplyElement
 */
function ShortDateElement(){}
ShortDateElement.prototype = new ApplyElement();
ShortDateElement.prototype.constructor = ShortDateElement;

ShortDateElement.prototype.avatar = function(){
  return $('<input type="text" disabled="true">');
};