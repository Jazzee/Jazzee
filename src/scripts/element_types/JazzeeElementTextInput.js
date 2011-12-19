/**
 * The JazzeeElementTextInput type
  @extends JazzeeElement
 */
function JazzeeElementTextInput(){}
JazzeeElementTextInput.prototype = new JazzeeElement();
JazzeeElementTextInput.prototype.constructor = JazzeeElementTextInput;

JazzeeElementTextInput.prototype.avatar = function(){
  return $('<input type="text" disabled="true">');
};