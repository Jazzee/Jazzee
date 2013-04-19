
/**
 * The JazzeeElementSchoolChooser type
  @extends JazzeeElement
 */
function JazzeeElementSchoolChooser(){}
JazzeeElementSchoolChooser.prototype = new JazzeeElement();
JazzeeElementSchoolChooser.prototype.constructor = JazzeeElementSchoolChooser;

JazzeeElementSchoolChooser.prototype.avatar = function(){
  return $('<input type="text" disabled="true">');
};

/**
 * Override JazzeeElement new page to set max default
 * @param {String} id the id to use
 * @returns {JazzeeElementRankingList}
 */
JazzeeElementSchoolChooser.prototype.newElement = function(id,title,typeId,typeName,typeClass,status,page){
  var element = JazzeeElement.prototype.newElement.call(this,id,title,typeId,typeName,typeClass,status,page);
  element.max = 255;

  /*
  $(document).ready(function() {
      try{
	  console.log("have element "+element+" with id "+element.id);
	  var availableTags = [  ];
	  $('#'+element.id).autocomplete({
		  source: availableTags
		      });
      }catch(ex){
	  console.log("Unable to create autocomplete: "+ex);
      }
      });
  */
  return element;
};

/**
 * Add minimum and maximum sliders
 * @returns {jQuery}
 */
JazzeeElementSchoolChooser.prototype.elementProperties = function(){
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
    max: 255,
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