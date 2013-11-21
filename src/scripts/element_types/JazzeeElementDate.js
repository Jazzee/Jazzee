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
    //we extract just the date FF was picky about the datestring format
    var dateString = this.values[0].value.substr(0,10);
    dates.push(new Date(dateString));
  });
  if(dates.length == 0){
    return '';
  }
  if(type == 'sort'){
    return dates[0].getTime();
  }
  if(type == 'display'){
    if(dates.length == 1){
      return dates[0].toLocaleDateString();
    }
    var ol = $('<ol>');
    $.each(dates, function(){
      ol.append($('<li>').html(this.toLocaleDateString()));
    });
    return ol.clone().wrap('<p>').parent().html();
  }
  var values = [];
  $.each(dates, function(){
    values.push(this.toLocaleDateString());
  });
  //forsorting and filtering return the raw data
  return values.join(' ');
};