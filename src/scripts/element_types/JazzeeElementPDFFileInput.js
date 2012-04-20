/**
 * The JazzeeElementPDFFileInput type
  @extends FileInput
 */
function JazzeeElementPDFFileInput(){}
JazzeeElementPDFFileInput.prototype = new FileInput();
JazzeeElementPDFFileInput.prototype.constructor = JazzeeElementPDFFileInput;


/**
 * Add maximum file size
 * @returns {jQuery}
 */
JazzeeElementPDFFileInput.prototype.elementProperties = function(){
  var elementClass = this;
  var div = JazzeeElement.prototype.elementProperties.call(this);
  var obj = new FormObject();
  var field = obj.newField({
    legend: 'Maximum File Size',
    instructions: 'Enter the maximum size for this PDF.  If you select a size that is greater than the system maximum, the system maximum will be used.'
  });
  var element = field.newElement('TextInput', 'max');
  element.label = 'Maximum File Size';
  element.legend = 'Value in bytes, or with optional b,k,m,g suffix';
  element.required = true;
  element.value = this.convertBytesToString(this.max);
  var dialog = this.page.displayForm(obj);
  $('form', dialog).bind('submit',function(e){
    var value = elementClass.convertShorthandValue($('input[name="max"]', this).val());
    elementClass.setProperty('max', value);
    elementClass.workspace();
    dialog.dialog("destroy").remove();
    return false;
  });//end submit
  var button = $('<button>').html('Maxium File Size').bind('click',function(){
    $('.qtip').qtip('api').hide();
    dialog.dialog('open');
  }).button({
    icons: {
      primary: 'ui-icon-pencil'
    }
  });
  div.append(button);
  return div;
};

/**
 * Convert a file size in bytes to a nice format
 * @param float bytes
 * @return String
 */
JazzeeElementPDFFileInput.prototype.convertBytesToString = function(bytes){
  var units = ['b', 'k', 'm', 'g', 't'];

  bytes = Math.max(bytes, 0);
  var pow = Math.floor((bytes ? Math.log(bytes) : 0) / Math.log(1024));
  pow = Math.min(pow, units.length - 1);

  bytes /= Math.pow(1024, pow);

  return Math.round(bytes, 2) + units[pow];
};

/**
 * Convert nice values like 2M into bytes
 * @param String string 
 * @return Integer
 */
JazzeeElementPDFFileInput.prototype.convertShorrthandValue = function(value){
  value = $.trim(value).toLowerCase();
  var last = value.charAt(value.length - 1);
  if($.inArray(last, ['g','m','k','b']) != -1){
    value = value.substring(0, value.length-1);
    switch(last) {
      //go from top to bottom and multiply every time
      case 'g':
        value *= 1024;
      case 'm':
        value *= 1024;
      case 'k':
        value *= 1024;
    }
  }
  return value;
}