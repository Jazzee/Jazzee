/**
 * The JazzeeElementDate type
  @extends JazzeeElement
 */
function JazzeeElementDate(){}
JazzeeElementDate.prototype = new JazzeeElement();
JazzeeElementDate.prototype.constructor = JazzeeElementDate;

JazzeeElementDate.prototype.avatar = function(){
  return $('<input type="text" disabled="true">');
};

/**
 * Dispaly applicant data in a grid
 */
JazzeeElementDate.prototype.getDisplayValues = function(data){
  var values = [];
  $.each(data, function(){
    var date = new Date(this.displayValue);
    values.push(date.toLocaleDateString());
  });
  return values;
};