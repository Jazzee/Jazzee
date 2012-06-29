/**
 * Initialize the JazzeeElement
  @class The base class for all element types
  @property {String} status the element status This is sent to the server so we can decide to create a new element or modify an existing one
  @property {boolean} isModified Only modified elements will be saved back to the server
 */
function JazzeeElement(){
  this.page;
  this.id;
  this.fixedId;
  this.typeId;
  this.typeName;
  this.typeClass;
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
  this.fixedId = obj.fixedId;
  this.typeId = obj.typeId;
  this.typeName = obj.typeName;
  this.typeClass = obj.typeClass;
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
 * @param {String} title the title to use
 * @param {Integer} typeId the database id for the type
 * @param {String} typeName nice name for the type
 * @param {String} typeClass the name of the class
 * @param {String} status the elements status
 * @param {JazzeePage} page the JazzeePage we belong to
 * @returns {JazzeeElement}
 */
JazzeeElement.prototype.newElement = function(id,title,typeId,typeName,typeClass,status,page){
  var obj = {
    id: id,
    fixedId: null,
    title: title,
    typeId: typeId,
    typeName: typeName,
    typeClass: typeClass,
    format: null,
    instructions: null,
    defaultValue: '',
    isRequired: 1,
    min: null,
    max: null,
    weight: null
  };
  var element = new window[typeClass]();
  element.init(obj, page);
  element.status = status;
  element.markModified();
  return element;
};

/**
 * Check to see if the JazzeeElement has been modified
 * @returns {Boolean}
 */
JazzeeElement.prototype.checkIsModified = function(){
  return this.isModified;
};

/**
 * Mark this element as modified
 */
JazzeeElement.prototype.markModified = function(){
  this.isModified = true;
  this.page.markModified();
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
    this.markModified();
  }
  return this[name];
};

/**
 * Get an object suitable for json
 * @returns {Object}
 */
JazzeeElement.prototype.getDataObject = function(){
  var obj = {
    id: this.id,
    fixedId: this.fixedId,
    typeId: this.typeId,
    typeName: this.typeName,
    typeClass: this.typeClass,
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
 * Button for setting the isRequired property
 * @return {jQuery}
 */
JazzeeElement.prototype.isRequiredButton = function(){
  var elementClass = this;
  var span = $('<span>');
  span.append($('<input>').attr('type', 'radio').attr('name', 'isRequired').attr('id', 'required').attr('value', '1').attr('checked', this.isRequired==1)).append($('<label>').html('Required').attr('for', 'required'));
  span.append($('<input>').attr('type', 'radio').attr('name', 'isRequired').attr('id', 'optional').attr('value', '0').attr('checked', this.isRequired==0)).append($('<label>').html('Optional').attr('for', 'optional'));
  span.buttonset();

  $('input', span).bind('change', function(e){
    elementClass.setProperty('isRequired', $(e.target).val());
    $('.qtip').qtip('api').hide();
    elementClass.workspace();
  });

  return span;
};

/**
 * Title editing button
 * @return {jQuery}
 */
JazzeeElement.prototype.editTitleButton = function(){
  var elementClass = this;
  var obj = new FormObject();
  var field = obj.newField({name: 'legend', value: 'Element Title'});
  var element = field.newElement('TextInput', 'title');
  element.label = 'Element Title';
  element.required = true;
  element.value = this.title;
  var dialog = this.page.displayForm(obj);
  $('form', dialog).bind('submit',function(e){
    elementClass.setProperty('title', $('input[name="title"]', this).val());
    elementClass.workspace();
    dialog.dialog("destroy").remove();
    return false;
  });//end submit
  var button = $('<button>').html('Edit Title').bind('click',function(){
    $('.qtip').qtip('api').hide();
    dialog.dialog('open');
  }).button({
    icons: {
      primary: 'ui-icon-pencil'
    }
  });
  return button;
};

/**
 * Instructions editign button
 * @return {jQuery}
 */
JazzeeElement.prototype.editInstructionsButton = function(){
  var elementClass = this;
  var obj = new FormObject();
  var field = obj.newField({name: 'legend', value: 'Element Instructions'});
  var element = field.newElement('Textarea', 'text');
  element.label = 'Element Instructions';
  element.value = this.instructions;
  var dialog = this.page.displayForm(obj);
  $('form', dialog).bind('submit',function(e){
    var value = $('textarea[name="text"]', this).val()==''?null:$('textarea[name="text"]', this).val();
    elementClass.setProperty('instructions', value);
    dialog.dialog("destroy").remove();
    elementClass.workspace();
    return false;
  });//end submit
  var button = $('<button>').html('Edit Element Instructions').bind('click',function(){
    $('.qtip').qtip('api').hide();
    dialog.dialog('open');
  }).button({
    icons: {
      primary: 'ui-icon-pencil'
    }
  });
  return button;
};

/**
 * Format display and editing workspace
 * @return {jQuery}
 */
JazzeeElement.prototype.editFormatButton = function(){
  var elementClass = this;
  var obj = new FormObject();
  var field = obj.newField({name: 'legend', value: 'Element Format'});
  var element = field.newElement('Textarea', 'text');
  element.label = 'Element Format';
  element.value = this.format;
  var dialog = this.page.displayForm(obj);
  $('form', dialog).bind('submit',function(e){
    var value = $('textarea[name="text"]', this).val()==''?null:$('textarea[name="text"]', this).val();
    elementClass.setProperty('format', value);
    elementClass.workspace();
    dialog.dialog("destroy").remove();
    return false;
  });//end submit
  var button = $('<button>').html('Edit Element Format').bind('click',function(){
    $('.qtip').qtip('api').hide();
    dialog.dialog('open');
  }).button({
    icons: {
      primary: 'ui-icon-pencil'
    }
  });
  return button;
};


/**
 * Create the element workspace
 */
JazzeeElement.prototype.workspace = function(){
  var elementClass = this;
  var div = $('#element-'+this.id);
  if(div.length == 0){
    $('#elements').append($('<div>').attr('id','element-'+this.id).addClass('elements yui-gc'));
    var div = $('#element-'+this.id);
    div.data('element', this);
    div.append($('<div>').addClass('yui-u first field'));
    div.append($('<div>').addClass('yui-u options'));
  }
  var field = $('.field', div);
  field.empty();
  if(this.instructions != null){
    field.append($('<div>').append($('<p>').html(this.instructions).addClass('instructions')));
  }
  var element = $('<div>').addClass('element yui-gd');
  var label = $('<div>').addClass('yui-u first label').append($('<label>').html(this.title + ':'));
  if(this.isRequired == 1) label.addClass('required');
  element.append(label);

  var control = $('<div>').addClass('control yui-u').append(this.avatar());
  if(this.format != null){
    control.append($('<div>').append($('<p>').html(this.format).addClass('format')));
  }
  element.append(control);
  field.append(element);

  var toolbar = $('<span>').addClass('ui-widget-header ui-corner-all toolbar');
  var button = $('<button>');
  button.button({
    text: false,
    label: 'Delete Element',
    icons: {
      primary: 'ui-icon-trash'
    }
  });
  if(this.page.hasAnswers){
    button.addClass('ui-button-disabled ui-state-disabled');
    button.attr('title', 'This element cannot be deleted because there is applicant information associated with it.');
    button.qtip();
  } else {
    button.bind('click', function(e){
      $('.qtip').qtip('api').hide();
      $('#element-' + elementClass.id).effect('explode',500);
      elementClass.status = 'delete';
      elementClass.page.deleteElement(elementClass);
      elementClass.markModified();
    });
  }
  toolbar.append(button);

  var button = $('<button>').bind('click', function(e){
    $('.qtip').qtip('api').hide();
    elementClass.page.copyElement(elementClass);
  });
  button.button({
    text: false,
    label: 'Copy Element',
    icons: {
      primary: 'ui-icon-copy'
    }
  });
  toolbar.append(button);

  var button = $('<button>');
  button.button({
    text: false,
    label: 'Element Properties',
    icons: {
      primary: 'ui-icon-gear',
      secondary: 'ui-icon-carat-1-s'
    }
  });
  button.qtip({
    position: {
      my: 'top-left',
      at: 'bottom-left'
    },
    show: {
      event: 'click'
    },
    hide: {
      event: 'unfocus click mouseleave',
      delay: 500,
      fixed: true
    },
    content: {
      text: this.elementProperties(),
      title: {
        text: 'Edit Element Properties',
        button: true
      }
    }
  });
  toolbar.append(button);

  $('.options', div).html(toolbar);
};

/**
 * Properties dialog
 * @return {jQuery}
 */
JazzeeElement.prototype.elementProperties = function(){
  var div = $('<div>').attr('id', 'element-properties-'+this.id).addClass('dropdown');
  div.append(this.editTitleButton());
  div.append(this.editInstructionsButton());
  div.append(this.editFormatButton());
  div.append(this.isRequiredButton());
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