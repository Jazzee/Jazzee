/**
 * The JazzeeEntityElementEmailAddress type
  @extends JazzeeElement
 */
function JazzeeEntityElementEmailAddress(){}
JazzeeEntityElementEmailAddress.prototype = new JazzeeElement();
JazzeeEntityElementEmailAddress.prototype.constructor = JazzeeEntityElementEmailAddress;

JazzeeEntityElementEmailAddress.prototype.avatar = function(){
  return $('<input type="text" disabled="true">');
};