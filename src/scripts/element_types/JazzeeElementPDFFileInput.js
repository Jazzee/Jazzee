/**
 * The JazzeeElementPDFFileInput type
  @extends FileInput
 */
function JazzeeElementPDFFileInput(){}
JazzeeElementPDFFileInput.prototype = new FileInput();
JazzeeElementPDFFileInput.prototype.constructor = JazzeeElementPDFFileInput;


/**
 * Override JazzeeElement new to set max default
 * @param {String} id the id to use
 * @returns {JazzeeElementPDFFileInput}
 */
JazzeeElementPDFFileInput.prototype.newElement = function(id,title,typeId,typeName,typeClass,status,page){
  var element = FileInput.prototype.newElement.call(this,id,title,typeId,typeName,typeClass,status,page);
  element.setProperty('max', page.pageBuilder.getElementType(typeClass).configurationVariables.defaultApplicantFileUploadSize);
  return element;
};

/**
 * Add maximum file size
 * @returns {jQuery}
 */
JazzeeElementPDFFileInput.prototype.elementProperties = function(){
  var elementClass = this;
  var div = JazzeeElement.prototype.elementProperties.call(this);
  div.append(this.createSlider('max', 'Maximum'));
  return div;
};

/**
 * Seperate out the slider function
 * @returns {jQuery}
 */
JazzeeElementPDFFileInput.prototype.createSlider = function(property, title){
  var elementClass = this;
  var div = $('<div>').attr('id', property + 'slider'+elementClass.id);
  var value = (elementClass[property]/1048576).toFixed(1);
  var link = $('<a>').attr('href', '#').attr('id', property + 'Value'+elementClass.id).html(value).bind('click', function(){
    $('#' + property + 'slider'+elementClass.id).replaceWith(elementClass.createInput(property, title));
    return false;
  });
  div.append($('<p>').html(title + ' File Size ').append(link).append('mb'));
  
  var slider = $('<div>');
  slider.slider({
    value: value,
    min: 0,
    max: 25,
    step: .5,
    slide: function( event, ui ) {
      elementClass.setProperty(property, ui.value * 1048576);
      $('#' + property + 'Value'+elementClass.id).html(ui.value);
    }
  });
  div.append(slider);
  return div;
};

/**
 * Seeprate out the inptu function
 * @returns {jQuery}
 */
JazzeeElementPDFFileInput.prototype.createInput = function(property, title){
  var elementClass = this;
  var div = $('<div>').attr('id', property + 'slider'+elementClass.id);
  var link = $('<a>').attr('href', '#').attr('id', property + 'Value'+elementClass.id).html(elementClass[property]).bind('click', function(){
    $('#' + property + 'slider'+elementClass.id).replaceWith(elementClass.createSlider(property, title));
    return false;
  });
  div.append($('<p>').html(title + ' File Size ').append(link).append('bytes'));
  
  var input = $('<input>').attr('type', 'text'). attr('size', 7).attr('id', property + 'Input'+elementClass.id).attr('value', elementClass[property]);
  input.bind('change', function(){
    elementClass.setProperty(property, $(this).val());
    $('#' + property + 'Value'+elementClass.id).html(elementClass[property]);
  });
  div.append(input);
  this.page.pageBuilder.addNumberTest(input);
  return div;
};

/**
 * Dispaly applicant data in a grid
 */
JazzeeElementPDFFileInput.prototype.gridData = function(data, type, full){
  var values = [];
  
  var img = $('<img>').attr('src', 'resource/foundation/media/default_pdf_logo.png').attr('title', 'Download ' + this.title + ' PDF').attr('alt', 'PDF Logo');
  img.css('height', '1em');
  $.each(data, function(){
    var a = $('<a>').attr('href', this.filePath).addClass('dialog_file').append(img.clone());
    values.push(a);
  });
  if(values.length == 0){
    return '';
  }
  if(values.length == 1){
    return values[0].clone().wrap('<p>').parent().html();
  }
  if(type == 'display'){
    var ol = $('<ol>');
    $.each(values, function(){
      ol.append($('<li>').append(this));
    });
    return ol.clone().wrap('<p>').parent().html();
  }
  //forsorting and filtering return a blank 
  return '';
};