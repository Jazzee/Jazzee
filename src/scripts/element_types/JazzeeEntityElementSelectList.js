/**
 * The JazzeeEntityElementSelectList type
  @extends List
 */
function JazzeeEntityElementSelectList(){}
JazzeeEntityElementSelectList.prototype = new List();
JazzeeEntityElementSelectList.prototype.constructor = JazzeeEntityElementSelectList;

JazzeeEntityElementSelectList.prototype.avatar = function(){
  var select = $('<select>');
  for(var i in this.listItems){
    select.append($('<option>').html(this.listItems[i].value));
  }
  return select;
};