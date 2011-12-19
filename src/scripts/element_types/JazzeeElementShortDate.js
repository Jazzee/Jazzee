/**
 * The JazzeeElementShortDate type
  @extends JazzeeElement
 */
function JazzeeElementShortDate(){}
JazzeeElementShortDate.prototype = new JazzeeElement();
JazzeeElementShortDate.prototype.constructor = JazzeeElementShortDate;

JazzeeElementShortDate.prototype.avatar = function(){
  return $('<input type="text" disabled="true">');
};