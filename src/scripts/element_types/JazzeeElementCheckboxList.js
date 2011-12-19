/**
 * The JazzeeElementCheckboxList type
  @extends List
 */
function JazzeeElementCheckboxList(){}
JazzeeElementCheckboxList.prototype = new List();
JazzeeElementCheckboxList.prototype.constructor = JazzeeElementCheckboxList;

JazzeeElementCheckboxList.prototype.avatar = function(){
  return $('<input>').attr('type', 'checkbox').attr('disabled', true);
};