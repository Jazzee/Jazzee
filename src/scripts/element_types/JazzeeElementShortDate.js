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
 * Dates return different data depending on type
 */
JazzeeElementShortDate.prototype.gridData = function(data, type, full){
  var dates = [];
  $.each(data, function(){
    dates.push(moment('01 ' + this.displayValue));
  });
  if(dates.length == 0){
    return '';
  }
  //For some reason datatables didn't like the time stamp for short dates, this seems to work though
  if(type == 'sort'){
    return dates[0].format('YYYYMM');
  }
  if(dates.length == 1){
    return dates[0].format('MM/YYYY')
  }
  var ol = $('<ol>');
  $.each(dates, function(){
    ol.append($('<li>').html(this.format('MM/YYYY')));
  });
  return ol.clone().wrap('<p>').parent().html();
  
};