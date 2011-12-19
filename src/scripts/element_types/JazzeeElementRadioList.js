/**
 * The JazzeeElementRadioList type
  @extends JazzeeElement
 */
function JazzeeElementRadioList(){}
JazzeeElementRadioList.prototype = new List();
JazzeeElementRadioList.prototype.constructor = JazzeeElementRadioList;

JazzeeElementRadioList.prototype.avatar = function(){
  return $('<input>').attr('type', 'radio').attr('disabled', true);
};