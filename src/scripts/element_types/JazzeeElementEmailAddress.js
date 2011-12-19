/**
 * The JazzeeElementEmailAddress type
  @extends JazzeeElement
 */
function JazzeeElementEmailAddress(){}
JazzeeElementEmailAddress.prototype = new JazzeeElement();
JazzeeElementEmailAddress.prototype.constructor = JazzeeElementEmailAddress;

JazzeeElementEmailAddress.prototype.avatar = function(){
  return $('<input type="text" disabled="true">');
};