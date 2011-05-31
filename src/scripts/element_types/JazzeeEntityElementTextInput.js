/**
 * The JazzeeEntityElementTextInput type
  @extends JazzeeElement
 */
function JazzeeEntityElementTextInput(){}
JazzeeEntityElementTextInput.prototype = new JazzeeElement();
JazzeeEntityElementTextInput.prototype.constructor = JazzeeEntityElementTextInput;

JazzeeEntityElementTextInput.prototype.avatar = function(){
  return $('<input type="text" disabled="true">');
};