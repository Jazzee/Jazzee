/**
 * The JazzeeElementDate type
  @extends JazzeeElement
 */
function JazzeeElementDate(){}
JazzeeElementDate.prototype = new JazzeeElement();
JazzeeElementDate.prototype.constructor = JazzeeElementDate;

JazzeeElementDate.prototype.avatar = function(){
  return $('<input type="text" disabled="true">');
};