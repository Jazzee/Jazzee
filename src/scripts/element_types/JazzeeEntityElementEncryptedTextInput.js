/**
 * The JazzeeEntityElementEncryptedTextInput type
  @extends JazzeeElement
 */
function JazzeeEntityElementEncryptedTextInput(){}
JazzeeEntityElementEncryptedTextInput.prototype = new JazzeeElement();
JazzeeEntityElementEncryptedTextInput.prototype.constructor = JazzeeEntityElementEncryptedTextInput;

JazzeeEntityElementEncryptedTextInput.prototype.avatar = function(){
  return $('<input type="text" disabled="true">');
};