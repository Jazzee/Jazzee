/**
 * The JazzeeElementPhonenumber type
  @extends JazzeeElement
 */
function JazzeeElementPhonenumber(){}
JazzeeElementPhonenumber.prototype = new JazzeeElement();
JazzeeElementPhonenumber.prototype.constructor = JazzeeElementPhonenumber;

JazzeeElementPhonenumber.prototype.avatar = function(){
  return $('<input type="text" disabled="true">');
};