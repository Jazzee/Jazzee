/**
 * The JazzeeEntityElementTextarea type
  @extends JazzeeElement
 */
function JazzeeEntityElementTextarea(){}
JazzeeEntityElementTextarea.prototype = new JazzeeElement();
JazzeeEntityElementTextarea.prototype.constructor = JazzeeEntityElementTextarea;

JazzeeEntityElementTextarea.prototype.avatar = function(){
  return $('<textarea>').attr('disabled', true);
};