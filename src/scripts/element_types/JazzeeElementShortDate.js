/**
 * The JazzeeElementShortDate type
  @extends JazzeeElement
 */
function JazzeeElementShortDate(){}
JazzeeElementShortDate.prototype = new JazzeeElement();
JazzeeElementShortDate.prototype.constructor = JazzeeElementShortDate;

JazzeeElementShortDate.prototype.avatar = function(){
  return $('<input type="text" disabled="true">');
};

/**
 * Dispaly applicant data in a grid
 */
JazzeeElementShortDate.prototype.getDisplayValues = function(data){
  var values = [];
  $.each(data, function(){
    var date = new Date(this.displayValue);
    values.push(date.getMonth()+1 + '/' + date.getFullYear());
  });
  return values;
};