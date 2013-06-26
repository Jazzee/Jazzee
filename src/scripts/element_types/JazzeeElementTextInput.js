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
 * Override JazzeeElement new page to set max default
 * @param {String} id the id to use
 * @returns {JazzeeElementRankingList}
 */
JazzeeElementTextInput.prototype.newElement = function(id,title,typeId,typeName,typeClass,status,page){
  var element = JazzeeElement.prototype.newElement.call(this,id,title,typeId,typeName,typeClass,status,page);
  element.max = 255;
  return element;
};

/**
 * Add minimum and maximum sliders
 * @returns {jQuery}
 */
JazzeeElementTextInput.prototype.elementProperties = function(){
  var div = JazzeeElement.prototype.elementProperties.call(this);
  div.append(this.createSlider('min', 'Minimum'));
  div.append(this.createSlider('max', 'Maximum'));

  return div;
};

/**
 * Seperate out the slider function
 * @returns {jQuery}
 */
JazzeeElementTextInput.prototype.createSlider = function(property, title){
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
    max: 255,
    step: 5,
    slide: function( event, ui ) {
      elementClass.setProperty(property, ui.value);
      $('#' + property + 'Value' + elementClass.id).html(elementClass[property]);
    }
  });
  div.append(slider);
  return div;
};

/**
 * Seeprate out the input function
 * @returns {jQuery}
 */
JazzeeElementTextInput.prototype.createInput = function(property, title){
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