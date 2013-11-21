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
    //we extract just the date FF was picky about the datestring format
    var dateString = '01 ' + this.displayValue;
    dates.push(new Date(dateString));
  });
  if(dates.length == 0){
    return '';
  }
  //For some reason datatables didn't like the time stamp for short dates, this seems to work though
  if(type == 'sort'){
    return dates[0].getFullYear() + '' + dates[0].getMonth();
  }
  if(type == 'display'){
    if(dates.length == 1){
      return dates[0].getMonth()+1 + '/' + dates[0].getFullYear();
    }
    var ol = $('<ol>');
    $.each(dates, function(){
      ol.append($('<li>').html(this.getMonth()+1 + '/' + this.getFullYear()));
    });
    return ol.clone().wrap('<p>').parent().html();
  }
  var values = [];
  $.each(dates, function(){
    values.push(this.getMonth()+1 + '/' + this.getFullYear());
  });
  //filtering return the raw data
  return values.join(' ');
};