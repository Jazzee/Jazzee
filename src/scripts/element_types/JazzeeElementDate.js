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
 * Dates return different data depending on type
 */
JazzeeElementDate.prototype.gridData = function(data, type, full){
  var dates = [];
  $.each(data, function(){
    dates.push(moment(this.values[0].value).format('L'));
  });
  if(dates.length === 0){
    return '';
  }
  if(dates.length === 1){
    return dates[0];
  }
  var ol = $('<ol>');
  $.each(dates, function(){
    ol.append($('<li>').html(this));
  });
  return ol.clone().wrap('<p>').parent().html();
};