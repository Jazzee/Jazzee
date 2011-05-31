/**
 * The JazzeeEntityElementCheckboxList type
  @extends List
 */
function JazzeeEntityElementCheckboxList(){}
JazzeeEntityElementCheckboxList.prototype = new List();
JazzeeEntityElementCheckboxList.prototype.constructor = JazzeeEntityElementCheckboxList;

JazzeeEntityElementCheckboxList.prototype.avatar = function(){
  return $('<input>').attr('type', 'checkbox').attr('disabled', true);
};