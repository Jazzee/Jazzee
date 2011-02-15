/**
 * The SelectListElement type
  @extends ApplyElement
 */
function SelectListElement(){}
SelectListElement.prototype = new ListElement();
SelectListElement.prototype.constructor = SelectListElement;

SelectListElement.prototype.avatar = function(){
  var select = $('<select>');
  for(var i in this.listItems){
    select.append($('<option>').html(this.listItems[i].value));
  }
  return select;
};