/**
 * The JazzeeEntityElementDate type
  @extends JazzeeElement
 */
function JazzeeEntityElementDate(){}
JazzeeEntityElementDate.prototype = new JazzeeElement();
JazzeeEntityElementDate.prototype.constructor = JazzeeEntityElementDate;

JazzeeEntityElementDate.prototype.avatar = function(){
  return $('<input type="text" disabled="true">');
};