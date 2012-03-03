/**
 * The JazzeeElementTextInput type
  @extends JazzeeElement
 */
function JazzeeElementTextInput(){}
JazzeeElementTextInput.prototype = new JazzeeElement();
JazzeeElementTextInput.prototype.constructor = JazzeeElementTextInput;

JazzeeElementTextInput.prototype.avatar = function(){
  return $('<input type="text" disabled="true">');
};
/**
 * Add minimum and maximum sliders
 * @returns {jQuery}
 */
JazzeeElementTextInput.prototype.elementProperties = function(){
  var elementClass = this;
  var div = JazzeeElement.prototype.elementProperties.call(this);
  
  var slider = $('<div>');
  slider.slider({
    value: elementClass.min,
    min: 0,
    max: 250,
    step: 5,
    slide: function( event, ui ) {
      elementClass.setProperty('min', ui.value);
      $('#minValue').html(elementClass.min);
    }
  });
  div.append($('<p>').html('Minimum Length ').append($('<span>').attr('id', 'minValue').html(elementClass.min)));
  div.append(slider);
  
  var slider = $('<div>');
  slider.slider({
    value: elementClass.max,
    min: 0,
    max: 250,
    step: 5,
    slide: function( event, ui ) {
      elementClass.setProperty('max', ui.value);
      $('#maxvalue').html(elementClass.max);
    }
  });
  div.append($('<p>').html('Maximum Length ').append($('<span>').attr('id', 'maxvalue').html(elementClass.max)));
  div.append(slider);
  
  return div;
};