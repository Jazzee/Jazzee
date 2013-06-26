/**
 * The JazzeeElementTextarea type
  @extends JazzeeElement
 */
function JazzeeElementTextarea(){}
JazzeeElementTextarea.prototype = new JazzeeElement();
JazzeeElementTextarea.prototype.constructor = JazzeeElementTextarea;

JazzeeElementTextarea.prototype.avatar = function(){
  return $('<textarea>').attr('disabled', true);
};

/**
 * Add minimum and maximum sliders
 * @returns {jQuery}
 */
JazzeeElementTextarea.prototype.elementProperties = function(){
  var elementClass = this;
  var div = JazzeeElement.prototype.elementProperties.call(this);
  
  div.append(this.createSlider('min', 'Minimum'));
  div.append(this.createSlider('max', 'Maximum'));
  
  return div;
};

/**
 * Seperate out the slider function
 * @returns {jQuery}
 */
JazzeeElementTextarea.prototype.createSlider = function(property, title){
  var elementClass = this;
  var div = $('<div>').attr('id', property + 'slider' + elementClass.id);
  var link = $('<a>').attr('href', '#').attr('id', property + 'Value' + elementClass.id).html(elementClass[property]).bind('click', function(){
    $('#' + property + 'slider' + elementClass.id).replaceWith(elementClass.createInput(property, title));
    return false;
  });
  div.append($('<p>').html(title + ' Length ').append(link));
  
  var slider = $('<div>');
  slider.slider({
    value: elementClass[property],
    min: 0,
    max: 50000,
    step: 100,
    slide: function( event, ui ) {
      elementClass.setProperty(property, ui.value);
      $('#' + property + 'Value' + elementClass.id).html(elementClass[property]);
    }
  });
  div.append(slider);
  return div;
};

/**
 * Seeprate out the inptu function
 * @returns {jQuery}
 */
JazzeeElementTextarea.prototype.createInput = function(property, title){
  var elementClass = this;
  var div = $('<div>').attr('id', property + 'slider' + elementClass.id);
  var link = $('<a>').attr('href', '#').attr('id', property + 'Value' + elementClass.id).html(elementClass[property]).bind('click', function(){
    $('#' + property + 'slider' + elementClass.id).replaceWith(elementClass.createSlider(property, title));
    return false;
  });
  div.append($('<p>').html(title + ' Length ').append(link));
  
  var minInput = $('<input>').attr('type', 'text'). attr('size', 5).attr('id', property + 'Input').attr('value', elementClass[property]);
  minInput.bind('change', function(){
    elementClass.setProperty(property, $(this).attr('value'));
    $('#' + property + 'Value' + elementClass.id).html(elementClass[property]);
  });
  div.append(minInput);
  this.page.pageBuilder.addNumberTest(minInput);
  return div;
};