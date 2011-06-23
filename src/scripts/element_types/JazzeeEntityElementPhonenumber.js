/**
 * The JazzeeEntityElementPhonenumber type
  @extends JazzeeElement
 */
function JazzeeEntityElementPhonenumber(){}
JazzeeEntityElementPhonenumber.prototype = new JazzeeElement();
JazzeeEntityElementPhonenumber.prototype.constructor = JazzeeEntityElementPhonenumber;

JazzeeEntityElementPhonenumber.prototype.avatar = function(){
  return $('<input type="text" disabled="true">');
};