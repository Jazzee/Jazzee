/**
 * The FileInput type is generic for any type of file
  @extends JazzeeElement
 */
function FileInput(){}
FileInput.prototype = new JazzeeElement();
FileInput.prototype.constructor = FileInput;

FileInput.prototype.avatar = function(){
  return $('<input>').attr('type', 'file').attr('disabled', true);
};

/**
 * Add size restrictions to the options block
 * @returns {jQuery}
 */
FileInput.prototype.optionsBlock = function(){
  var element = this;
  var optionsBlockDiv = JazzeeElement.prototype.optionsBlock.call(this);
  optionsBlockDiv.append(this.maxFileSizeBlock());
  return optionsBlockDiv;
};

/**
 * Maximum file size block
 * @return {jQuery}
 */
FileInput.prototype.maxFileSizeBlock = function(){
  var elementClass = this;
  var field = $('<input>').attr('value',(this.convertBytesToString(this.max)))
  .bind('change',function(){
    var max = ($(this).val() == '')?null:elementClass.convertShorthandValue($(this).val());
    elementClass.setProperty('max', max);
  })
  .bind('blur', function(){
    $(this).parent().replaceWith(elementClass.maxFileSizeBlock());
  }).hide();
  var p = $('<p>').addClass('edit').addClass('filesize').html('Maximum filz size: ' + ((this.max == 0 || this.max == null)?'No limit':this.convertBytesToString(this.max))).bind('click', function(){
    $(this).hide();
    $(this).parent().children('input').eq(0).show().focus();
  });
  return $('<div>').append(p).append(field);
};

/**
 * Convert byte value to meaningfull string
 * @param bytes
 * @returns {String}
 */
FileInput.prototype.convertBytesToString = function(bytes) {
  if(isNaN(bytes) || bytes == null) return '';
  var suffixes = ['b', 'k', 'm', 'g', 't'];
  //convert to base 1024 and find our position in the suffixes index
  var pow = Math.floor(Math.log(bytes)/Math.log(1024));
  return (bytes/Math.pow(1024, Math.floor(pow))).toFixed()+suffixes[pow];
};

/**
 * Convert shorthand values like 2M into bytes
 * @param {String} value 
 * @return {Integer}
 */
FileInput.prototype.convertShorthandValue = function(value){
  value = value.replace(/^\s*|\s*$/g,'');
  var number = value.slice(0, -1);
  var description = value.substr(value.length-1).toLowerCase();
  //check to make sure the description isn't actually a number in which case we just need to return the value as bytes
  var numbers = "0123456789";
  if (numbers.indexOf(description) != -1){
    return value;
  }
  switch(description) {
    //go from top to bottom and multiply every time
    case 'g':
      number *= 1024;
    case 'm':
      number *= 1024;
    case 'k':
      number *= 1024;
  }
  return number;
};