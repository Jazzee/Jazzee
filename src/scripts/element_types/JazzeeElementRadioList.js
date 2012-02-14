/**
 * The JazzeeElementRadioList type
  @extends JazzeeElement
 */
function JazzeeElementRadioList(){}
JazzeeElementRadioList.prototype = new List();
JazzeeElementRadioList.prototype.constructor = JazzeeElementRadioList;

JazzeeElementRadioList.prototype.avatar = function(){
  var ol = $('<pl>');
  for(var i = 0; i < this.listItems.length; i++){
    if(this.listItems[i].isActive){
      var li = $('<li>');
      li.append($('<input>').attr('type', 'radio').attr('disabled', true));
      li.append($('<label>').html(this.listItems[i].value));
      ol.append(li);
    }
  }
  return ol;
};