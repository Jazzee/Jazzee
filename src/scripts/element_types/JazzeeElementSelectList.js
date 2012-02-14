/**
 * The JazzeeElementSelectList type
  @extends List
 */
function JazzeeElementSelectList(){}
JazzeeElementSelectList.prototype = new List();
JazzeeElementSelectList.prototype.constructor = JazzeeElementSelectList;

JazzeeElementSelectList.prototype.avatar = function(){
  var select = $('<select>');
  for(var i = 0; i < this.listItems.length; i++){
    if(this.listItems[i].isActive) select.append($('<option>').html(this.listItems[i].value));
  }
  return select;
};