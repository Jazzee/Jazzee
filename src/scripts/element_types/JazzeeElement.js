/**
 * Initialize the JazzeeElement
  @class The base class for all element types
  @property {String} status the element status This is sent to the server so we can decide to create a new element or modify an existing one
  @property {boolean} isModified Only modified elements will be saved back to the server
 */
function JazzeeElement(){
  this.page;
  this.id;
  this.classId;
  this.className;
  this.title;
  this.format;
  this.instructions;
  this.defaultValue;
  this.isRequired;
  this.min;
  this.max;
  this.weight;
  this.listItems;

  this.status;
  this.isModified;
}

/**
 * Initialize the object
 * @param {Object} obj
 * @param {ApplyPage} page
 */
JazzeeElement.prototype.init = function(obj, page){
  this.page = page;
  this.id = obj.id;
  this.classId = obj.classId;
  this.className = obj.className;
  this.title = obj.title;
  this.instructions = obj.instructions;
  this.format = obj.format;
  this.defaultValue = obj.defaultValue;
  this.isRequired = (obj.isRequired)?1:0;
  this.min = obj.min;
  this.max = obj.max;
  this.weight = obj.weight;
  this.listItems = [];
  
  this.status = '';
  this.isModified = false;
};
  
/**
 * Create a new element object with good default values
 * @param {String} id the id to use
 * @returns {JazzeeElement}
 */
JazzeeElement.prototype.newElement = function(id,title,classId,className,status,page){
  var obj = {
    id: id,
    title: title,
    classId: classId,
    className: className,
    format: '',
    instructions: '',
    defaultValue: '',
    isRequired: 1,
    min: null,
    max: null,
    weight: null
  };
  var element = new window[className]();
  element.init(obj, page);
  element.status = status;
  element.isModified = true;
  return element;
};

/**
 * Check to see if the JazzeeElement has been modified
 * @returns {Boolean}
 */
JazzeeElement.prototype.checkModified = function(){
  return this.isModified;
};

/**
 * Set a property and mark the element as modified
 * @param {String} name
 * @param {Mixed} value
 * @return {Mixed}
 */
JazzeeElement.prototype.setProperty = function(name, value){
  if(typeof this[name] == 'undefined' || this[name] !== value){
    this[name] = value;
    this.isModified = true;
  }
  return this[name];
};

/**
 * A generic text input block for editing properties
 * @param {String} propertyName
 * @para, {String} valueIfBlank what do display if the property isn't set
 * @return {jQuery}
 */
JazzeeElement.prototype.textInputBlock = function(propertyName, valueIfBlank){
  var elementClass = this;
  var field = $('<input>').attr('value',(this[propertyName]))
  .bind('change',function(){
    elementClass.setProperty(propertyName, $(this).val());
  })
  .bind('blur', function(){
    $(this).parent().replaceWith(elementClass.textInputBlock(propertyName, valueIfBlank));
  }).hide();
  var p = $('<p>').addClass('edit').addClass(propertyName).html(((this[propertyName] == '')?valueIfBlank:this[propertyName])).bind('click', function(){
    $(this).hide();
    $(this).parent().children('input').eq(0).show().focus();
  });
  return $('<div>').append(p).append(field);
};

/**
 * A generic block for editng properties using a dropdown
 * @param {String} propertyName
 * @param {String} description
 * @param {Object} options
 * @returns {jQuery}
 */
JazzeeElement.prototype.selectListBlock = function(propertyName, description, options){
  var elementClass = this;
  var p = $('<p>').addClass('edit').html(description + ' ').append($('<span>').html(options[this[propertyName]]).bind('click',function(e){
    $(this).unbind('click');
    var select = $('<select>');
    $.each(options,function(value, text){
      var option = $('<option>').attr('value', value).html(text);
      if(elementClass[propertyName] == value) option.attr('selected', true);
      select.append(option);
    });
    select.bind('change', function(e){
      elementClass.setProperty(propertyName, $(this).val());
    });
    select.bind('blur', function(e){
      $(this).parent().parent().replaceWith(elementClass.selectListBlock(propertyName,description, options));
    });
    $(this).empty().append(select);
  }));
  return p;
};

/**
 * Block for editing the element title
 * @param {String} title
 * @param {String} propertyName
 * @return {jQuery}
 */
JazzeeElement.prototype.editTitleBlock = function(){
  var elementClass = this;
  var field = $('<input>').attr('value', this.title).attr('type', 'text')
    .bind('change',function(){
      elementClass.setProperty('title', $(this).val());
    })
    .bind('blur', function(){
      $(this).parent().replaceWith(elementClass.editTitleBlock());
    }).hide();
  
  var p = $('<p>').addClass('edit').html('<legend>' + ((this.title == '')?'click to edit':this.title) + ':</legend>').bind('click', function(){
    $(this).hide();
    $(this).parent().children('input').eq(0).show().focus();
  });
  return $('<div>').addClass('yui-u first').append(p).append(field);
};

/**
 * Get an object suitable for json
 * @returns {Object}
 */
JazzeeElement.prototype.getDataObject = function(){
  var obj = {
    id: this.id,
    classId: this.classId,
    className: this.className,
    status: this.status,
    title: this.title,
    format: this.format,
    instructions: this.instructions,
    defaultValue: this.defaultValue,
    isRequired: this.isRequired,
    min: this.min,
    max: this.max,
    weight: this.weight,
    list: []
  };
  for(var i = 0; i < this.listItems.length; i++){
    obj.list.push(this.listItems[i]);
  }
  return obj;
};

/**
 * Create the element workspace
 */
JazzeeElement.prototype.workspace = function(){
  var elementClass = this;
  var field = $('#element-'+this.id);
  if(field.length == 0){
    $('#workspace-left-middle-left').append($('<div>').attr('id','element-'+this.id).addClass('field'));
    var field = $('#element-'+this.id);
    field.data('element', this);
  }
  field.empty();
  field.append(this.textInputBlock('instructions', 'click to edit'));
  var element = $('<div>').addClass('element yui-gf');
  element.append(this.editTitleBlock());
  var control = $('<div>').addClass('yui-u control').append(this.avatar());
  control.append(this.textInputBlock('format', 'click to edit'));
  element.append(control);
  field.append(element);
  field.data('options', this.optionsBlock());
};

/**
 * Create the options workspace for the element
 */
JazzeeElement.prototype.optionsBlock = function(){
  var elementClass = this;
  var div = $('#element-options-'+this.id);
  if(div.length == 0){
    $('#workspace-left-middle-right').append($('<div>').attr('id', 'element-options-'+this.id));
    div = $('#element-options-'+this.id);
  }
  div.empty();
  var p = $('<p>Copy this element</p>').addClass('copy').bind('click', function(e){
    elementClass.page.copyElement(elementClass);
  });
  div.append(p);
  var p = $('<p>Delete this element</p>').addClass('delete').bind('click', function(e){
    elementClass.isModified = true;
    elementClass.status = 'delete';
    elementClass.page.deleteElement(elementClass);
    $('#element-options-'+elementClass.id).remove();
    $('#element-'+elementClass.id).effect('explode',500);
    $('#workspace-left-middle-left div.field:first').trigger('click');
  });
  div.append(p);
  div.append(this.selectListBlock('isRequired', 'This element is', {0:'Optional',1:'Required'}));
  div.hide();
  return div;
};

/**
 * An avatar to display in the workspace
 * Always overridden by the actual element
 * @returns {jQuery}
 */
JazzeeElement.prototype.avatar = function(){
  return $('<span>').html('default avatar');
};

/**
 * Replacement Text
 * Get a string representation of the element which can be used to represent the element
 * @returns {String}
 */
JazzeeElement.prototype.replacementTitle = function(){
  var text = this.title.replace(/\s+/, '_');
  text = '%' + text.toUpperCase() + '%';
  return text;
};