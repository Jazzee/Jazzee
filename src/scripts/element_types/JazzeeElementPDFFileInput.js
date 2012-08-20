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
  element.setProperty('max', page.pageBuilder.getElementType('PDFFileInput').configurationVariables.defaultApplicantFileUploadSize);
  return element;
};

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