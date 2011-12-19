/**
 * The JazzeeElementTextarea type
  @extends JazzeeElement
 */
function JazzeeElementTextarea(){}
JazzeeElementTextarea.prototype = new JazzeeElement();
JazzeeElementTextarea.prototype.constructor = JazzeeElementTextarea;

JazzeeElementTextarea.prototype.avatar = function(){
  return $('<textarea>').attr('disabled', true);
};