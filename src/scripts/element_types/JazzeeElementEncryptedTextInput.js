/**
 * The JazzeeElementEncryptedTextInput type
  @extends JazzeeElement
 */
function JazzeeElementEncryptedTextInput(){}
JazzeeElementEncryptedTextInput.prototype = new JazzeeElement();
JazzeeElementEncryptedTextInput.prototype.constructor = JazzeeElementEncryptedTextInput;

JazzeeElementEncryptedTextInput.prototype.avatar = function(){
  return $('<input type="text" disabled="true">');
};