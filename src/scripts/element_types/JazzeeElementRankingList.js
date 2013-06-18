/**
 * The JazzeeElementRankingList type
  @extends ApplyElement
 */
function JazzeeElementRankingList(){}
JazzeeElementRankingList.prototype = new List();
JazzeeElementRankingList.prototype.constructor = JazzeeElementRankingList;

/**
 * Override JazzeeElement new page to set min/max defaults
 * @param {String} id the id to use
 * @returns {JazzeeElementRankingList}
 */
JazzeeElementRankingList.prototype.newElement = function(id,title,typeId,typeName,typeClass,status,page){
  var element = JazzeeElement.prototype.newElement.call(this,id,title,typeId,typeName,typeClass,status,page);
  element.min = 1;
  element.max = 1;
  return element;
};

JazzeeElementRankingList.prototype.avatar = function(){
  var ol = $('<ol>');
  for(var i=0; i<this.max; i++){
    var select = $('<select>');
    if(i >= this.min) select.append($('<option>').html(''));
    for(var j = 0; j < this.listItems.length; j++){
      if(this.listItems[j].isActive) select.append($('<option>').html(this.listItems[j].value));
    }
    
    ol.append($('<li>').append(select));
  }
  return ol;
};

/**
 * Add required and maximum items to toolbar
 * @returns {jQuery}
 */
JazzeeElementRankingList.prototype.elementProperties = function(){
  var elementClass = this;
  var div = List.prototype.elementProperties.call(this);

  var slider = $('<div>');
  slider.slider({
    value: elementClass.min,
    min: 1,
    max: 50,
    step: 1,
    slide: function( event, ui ) {
      elementClass.setProperty('min', ui.value);
      $('#minValue'+elementClass.id).html(elementClass.min);
    }
  });
  div.append($('<p>').html('Minimum Rankings Required ').append($('<span>').attr('id', 'minValue'+elementClass.id).html(elementClass.min)));
  div.append(slider);

  var slider = $('<div>');
  slider.slider({
    value: elementClass.max,
    min: 1,
    max: 50,
    step: 1,
    slide: function( event, ui ) {
      elementClass.setProperty('max', ui.value);
      $('#maxvalue'+elementClass.id).html(elementClass.max);
    }
  });
  div.append($('<p>').html('Maximum Rankings Allowed ').append($('<span>').attr('id', 'maxvalue'+elementClass.id).html(elementClass.max)));
  div.append(slider);

  return div;
};