/**
 * The ETSMatchPage type
  @extends ApplyPage
 */
function JazzeePageETSMatch(){}
JazzeePageETSMatch.prototype = new JazzeePage();
JazzeePageETSMatch.prototype.constructor = JazzeePageETSMatch;

/**
 * Create the ETSMatchPage workspace
 */
JazzeePageETSMatch.prototype.workspace = function(){
  JazzeePage.prototype.workspace.call(this);
  $('#pageToolbar').append(this.pagePropertiesButton());
};

/**
 * Create the page properties dropdown
*/
JazzeePageETSMatch.prototype.pageProperties = function(){
  var pageClass = this;

  var div = $('<div>');
  div.append(this.isRequiredButton());
  div.append(this.showAnswerStatusButton());
  div.append(this.editNameButton());
  var slider = $('<div>');
  slider.slider({
    value: this.min,
    min: 0,
    max: 20,
    step: 1,
    slide: function( event, ui ) {
      pageClass.setProperty('min', ui.value);
      $('#minValue').html(pageClass.min == 0?'No Minimum':pageClass.min);
    }
  });
  div.append($('<p>').html('Minimum Scores Required ').append($('<span>').attr('id', 'minValue').html(this.min == 0?'No Minimum':this.min)));
  div.append(slider);

  var slider = $('<div>');
  slider.slider({
    value: this.max,
    min: 0,
    max: 20,
    step: 1,
    slide: function( event, ui ) {
      pageClass.setProperty('max', ui.value);
      $('#maxValue').html(pageClass.max == 0?'No Maximum':pageClass.max);
    }
  });
  div.append($('<p>').html('Maximum Scores Allowed ').append($('<span>').attr('id', 'maxValue').html(this.max == 0?'No Maximum':this.max)));
  div.append(slider);

  return div;
};