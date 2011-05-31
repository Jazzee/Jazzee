/**
 * The JazzeeEntityElementShortDate type
  @extends JazzeeElement
 */
function JazzeeEntityElementShortDate(){}
JazzeeEntityElementShortDate.prototype = new JazzeeElement();
JazzeeEntityElementShortDate.prototype.constructor = JazzeeEntityElementShortDate;

JazzeeEntityElementShortDate.prototype.avatar = function(){
  return $('<input type="text" disabled="true">');
};