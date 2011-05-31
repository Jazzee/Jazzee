/**
 * The JazzeeEntityElementRadioList type
  @extends JazzeeElement
 */
function JazzeeEntityElementRadioList(){}
JazzeeEntityElementRadioList.prototype = new List();
JazzeeEntityElementRadioList.prototype.constructor = JazzeeEntityElementRadioList;

JazzeeEntityElementRadioList.prototype.avatar = function(){
  return $('<input>').attr('type', 'radio').attr('disabled', true);
};