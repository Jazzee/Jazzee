/**
 * The JazzeeElementCheckboxList type
  @extends List
 */
function JazzeeElementCheckboxList(){}
JazzeeElementCheckboxList.prototype = new List();
JazzeeElementCheckboxList.prototype.constructor = JazzeeElementCheckboxList;

JazzeeElementCheckboxList.prototype.avatar = function(){
  var ol = $('<ol>');
  for(var i = 0; i < this.listItems.length; i++){
    if(this.listItems[i].isActive){
      var li = $('<li>');
      li.append($('<input>').attr('type', 'checkbox').attr('disabled', true));
      li.append($('<label>').html(this.listItems[i].value));
      ol.append(li);
    }
  }
  return ol;
};