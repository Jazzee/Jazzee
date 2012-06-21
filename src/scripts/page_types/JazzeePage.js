/**
 * Initialize the \\Jazzee\Interfaces\Page Abstract Type
  @class The base class for all page types
  @property {String} status the pages status This is sent to the server so we can decide to create a new page or modify an existing one
  @property {boolean} isModified Is the page modified or new Pages will only be saved back to the server if this is true
 */
function JazzeePage(){
  this.pageBuilder;
  this.pageId;
  this.uuid;
  this.applicationPageId;
  this.typeName;
  this.typeId;
  this.typeClass;
  this.kind;
  this.isGlobal;
  this.title;
  this.min = 0;
  this.max = 0;
  this.isRequired;
  this.instructions;
  this.leadingText;
  this.trailingText;
  this.answerStatusDisplay;
  this.weight;
  this.hasAnswers;
  this.hasCycleAnswers;

  this.variables;
  this.elements;
  this.deletedElements;
  this.children;
  this.deletedChildren;
  
  this.globalPage = {};
  this.globalPage.title;
  this.globalPage.min;
  this.globalPage.max;
  this.globalPage.isRequired;
  this.globalPage.instructions;
  this.globalPage.leadingText;
  this.globalPage.trailingText;
  this.globalPage.answerStatusDisplay;
  
  this.status;
  this.isModified;
  
  this.editorDefaults = {
    rmUnusedControls: true,
    autoSave: true,
    autoGrow: true,
    maxHeight: '500px',
    controls: {
        bold: {visible: true},
        italic: {visible: true},
        underline: {visible: true},
        subscript: {visible: true},
        superscript: {visible: true},
        undo: {visible: true},
        redo: {visible: true},
        insertOrderedList: {visible: true},
        insertUnorderedList: {visible: true},
        createLink: {visible: true},
        h2: {visible: true},
        h3: {visible: true},
        paragraph: {visible: true},
        cut: {visible: true},
        copy: {visible: true},
        paste: {visible: true},
        html: {visible: true},
        removeFormat: {visible: true},
        insertTable: {visible: true}
    }
  };
}
/**
 * Initialize the object
 * @param {Object} pageObject
 * @param {PageStore} pageStore
 */
JazzeePage.prototype.init = function(pageObject, pageBuilder){
  this.pageBuilder = pageBuilder;
  this.id = pageObject.id;
  this.uuid = pageObject.uuid;
  this.typeId = pageObject.typeId;
  this.typeName = pageObject.typeName;
  this.typeClass = pageObject.typeClass;
  this.kind = pageObject.kind;
  this.title = pageObject.title;
  this.isGlobal = pageObject.isGlobal;
  this.min = pageObject.min;
  this.max = pageObject.max;
  this.isRequired = (pageObject.isRequired)?1:0;
  this.instructions = pageObject.instructions;
  this.leadingText = pageObject.leadingText;
  this.trailingText = pageObject.trailingText;
  this.answerStatusDisplay = (pageObject.answerStatusDisplay)?1:0;
  this.weight = pageObject.weight;
  this.hasAnswers = pageObject.hasAnswers;
  this.hasCycleAnswers = pageObject.hasCycleAnswers;

  this.variables = {};
  this.elements = [];
  this.deletedElements = [];
  this.children = {};
  this.deletedChildren = [];
  this.globalPage = {};
  
  if(pageObject.globalPage != undefined){
    this.globalPage.title = pageObject.globalPage.title;
    this.globalPage.min = pageObject.globalPage.min;
    this.globalPage.max = pageObject.globalPage.max;
    this.globalPage.isRequired = (pageObject.globalPage.isRequired)?1:0;
    this.globalPage.instructions = pageObject.globalPage.instructions;
    this.globalPage.leadingText = pageObject.globalPage.leadingText;
    this.globalPage.trailingText = pageObject.globalPage.trailingText;
    this.globalPage.answerStatusDisplay = (pageObject.globalPage.answerStatusDisplay)?1:0;
  }
  this.status = '';
  this.isModified = false;
};

/**
 * Create a new object with good default page values
 * @param {String} id the id to use
 * @returns {JazzeePage}
 */
JazzeePage.prototype.newPage = function(id,title,typeId,typeName,typeClass,status,pageBuilder){
  var obj = {
    id: id,
    uuid: null,
    typeId: typeId,
    typeName: typeName,
    typeClass: typeClass,
    title: title,
    min: 0,
    max: 0,
    isRequired: 1,
    answerStatusDisplay: 0,        
    instructions: null,
    leadingText: null,
    trailingText: null,
    weight: 0,
    kind: null,
    hasAnswers: 0,
    hasCycleAnswers: 0
  };
  var page = new window[typeClass]();
  page.init(obj, pageBuilder);
  page.status = status;
  page.markModified();
  return page;
};

/**
 * Check to see if the JazzeePage has been modified
 * @returns {Boolean}
 */
JazzeePage.prototype.checkIsModified = function(){
  if(this.isModified) return true;
  for(var i in this.children) {
    if(this.children[i].checkIsModified()) return true;
  }
  for(var i = 0; i < this.elements.length; i++){
    if(this.elements[i].checkIsModified()) return true;
  }
  return false;
};

/**
 * Mark this page as modified
 */
JazzeePage.prototype.markModified = function(){
  this.isModified = true;
  this.pageBuilder.markModified();
};

/**
 * Add an element to the page
 * @param {ApplyElement} the ApplyElement object
 * @returns {ApplyElement}
 */
JazzeePage.prototype.addElement = function(element){
  this.elements.push(element);
  return element;
};

/**
 * Delete an element from the page
 * @param {ApplyElement} the element to delete
 * @returns {boolean}
 */
JazzeePage.prototype.deleteElement = function(element){
  for(var i = 0; i < this.elements.length; i++){
    if(this.elements[i] == element){ 
      this.elements.splice(i,1);
      break;
    }
  }
  this.markModified();
  this.deletedElements.push(element);
};
  
/**
 * Add a child to the page
 * @param {JazzeePage} page
 * @returns {JazzeePage}
 */
JazzeePage.prototype.addChild = function(page){
  this.children[page.id] = page;
  return page;
};

/**
 * Delete a child page
 * @param {JazzeePage} page
 */
JazzeePage.prototype.deleteChild = function(page){
  delete this.children[page.id];
  this.markModified();
  page.status = 'delete';
  this.deletedChildren.push(page);
  console.log('delete child page '+ page.title);
};
  
  
/**
 * Set a page variable
 * @param {String} name
 * @param {String} value
 * @returns {Object} the varialbe we created
 */
JazzeePage.prototype.setVariable = function(name, value){
  //only set the variable and mark as modified if it is new or different
  if(typeof this.variables[name] == 'undefined'  || this.variables[name].value !== value){
    console.log('setting variable ' + name + ' to ' + value);
    this.variables[name] = {name : name, value: value};
    this.markModified();
  }
  return this.variables[name];
};

/**
 * Get a variable value
 * @param {String} name
 * @returns {String|Null}
 */
JazzeePage.prototype.getVariable = function(name){
  if(name in this.variables) return this.variables[name].value;
  console.log(name + ' is not a set variable');
  return null;
};

/**
 * Set a property and mark the page as modified
 * @param {String} name
 * @param {Mixed} value
 * @return {Mixed}
 */
JazzeePage.prototype.setProperty = function(name, value){
  if(typeof this[name] == 'undefined'){
    console.log('Attempting to set JazzePage "' + name + '" property to ' + value + ' but it is not defined');
  } else if(this[name] != value){
    console.log('setting property ' + name + ' to ' + value);
    this[name] = value;
    this.markModified();
    this.pageBuilder.synchronizePageList();
  }
  return this[name];
};

/**
 * Button for deleting the current page
 * @returns {jQuery}
 */
JazzeePage.prototype.deletePageButton = function(){
  var pageClass = this;
  var button = $('<button>').html('Delete').addClass('delete');
  button.button({
    icons: {
      primary: 'ui-icon-trash'
    }
  });
  if((this.pageBuilder.editGlobal && this.isGlobal && pageClass.hasAnswers) || (this.isGlobal && pageClass.hasCycleAnswers) || (!this.isGlobal && pageClass.hasAnswers)){
    button.addClass('ui-button-disabled ui-state-disabled');
    button.attr('title', 'This page cannot be deleted because there is applicant information associated with it.');
    button.qtip();
  } else {
    button.bind('click', function(e){
      $('#editPage').effect('explode',500);
      pageClass.status = 'delete';
      pageClass.pageBuilder.deletePage(pageClass);
    });
  }
  return button;
};

/**
 * Button for copying the current page
 * @returns {jQuery}
 */
JazzeePage.prototype.copyPageButton = function(){
  var pageClass = this;
  var button = $('<button>').html('Copy').addClass('copy').bind('click', function(e){
    var obj = pageClass.getDataObject();
    pageClass.pageBuilder.copyPage(obj);
  });
  button.button({
    icons: {
      primary: 'ui-icon-copy'
    }
  });
  return button;
};

/**
 * Button for previewwing the current page
 * @returns {jQuery}
 */
JazzeePage.prototype.previewPageButton = function(){
  var pageClass = this;
  var button = $('<button>').html('Preview').addClass('preview').bind('click', function(e){
    var preview = pageClass.pageBuilder.getPagePreview(pageClass);
    $('form', preview).bind('submit', function(){return false;});
    $('fieldset.buttons ', preview).remove();
    $(preview).dialog({width: 800});
  });
  button.button({
    icons: {
      primary: 'ui-icon-info'
    }
  });
  return button;
};

/**
 * Button for previewwing the current page
 * @returns {jQuery}
 */
JazzeePage.prototype.exportPageButton = function(){
  var pageClass = this;
  var button = $('<button>').html('Export').addClass('export').bind('click', function(e){
    var dialog = pageClass.createDialog();
    var obj = pageClass.getDataObject();
    obj.id = null;
    var textarea = $('<textarea>').val($.toJSON(obj));
    dialog.append(textarea);
    dialog.append($('<p>').html('Copy this text into the import utility.'));
    dialog.dialog('open');
    textarea.select();
  });
  button.button({
    icons: {
      primary: 'ui-icon-clipboard'
    }
  });
  return button;
};



/**
 * Create a dialog
 * @param {FormObject} formObj
 * @returns {jQuery}
 */
JazzeePage.prototype.createDialog = function(){
  var pageClass = this;
  
  var div = $('<div>');
  div.css("overflow-y", "auto");
  div.dialog({
    modal: true,
    autoOpen: false,
    position: 'center',
    width: 800,
    close: function() {
      div.dialog("destroy").remove();
    }
  });
  
  return div;
};

/**
 * Display a form for editing
 * @param {FormObject} formObj
 * @returns {jQuery}
 */
JazzeePage.prototype.displayForm = function(formObj){
  var pageClass = this;
  var form = new Form();
  var formObject = form.create(formObj);
  $('form',formObject).append($('<button type="submit" name="submit">').html('Save').button({
    icons: {
      primary: 'ui-icon-disk'
    }
  }));

  var div = this.createDialog();
  div.html(formObject);
  return div;
};

/**
 * Button for setting the isRequired property
 * @return {jQuery}
 */
JazzeePage.prototype.isRequiredButton = function(){
  var pageClass = this;
  var span = $('<span>');
  span.append($('<input>').attr('type', 'radio').attr('name', 'isRequired').attr('id', 'required').attr('value', '1').attr('checked', this.isRequired==1)).append($('<label>').html('Required').attr('for', 'required'));
  span.append($('<input>').attr('type', 'radio').attr('name', 'isRequired').attr('id', 'optional').attr('value', '0').attr('checked', this.isRequired==0)).append($('<label>').html('Optional').attr('for', 'optional'));
  span.buttonset();
  
  $('input', span).bind('change', function(e){
    $('.qtip').qtip('api').hide();
    pageClass.setProperty('isRequired', $(e.target).val());
  });
  
  return span;
};

/**
 * Button for setting the showAnswerStatus property
 * @return {jQuery}
 */
JazzeePage.prototype.showAnswerStatusButton = function(){
  var pageClass = this;
  var span = $('<span>').attr('id', 'answerStatusDisplayButton');
  span.append($('<input>').attr('type', 'radio').attr('name', 'answerStatusDisplay').attr('id', 'shown').attr('value', '1').attr('checked', this.answerStatusDisplay==1)).append($('<label>').html('Show Answer Status').attr('for', 'shown'));
  span.append($('<input>').attr('type', 'radio').attr('name', 'answerStatusDisplay').attr('id', 'notshown').attr('value', '0').attr('checked', this.answerStatusDisplay==0)).append($('<label>').html('No Answer Status').attr('for', 'notshown'));
  span.buttonset();
  $('input', span).bind('change', function(e){
    $('.qtip').qtip('api').hide();
    pageClass.setProperty('answerStatusDisplay', $(e.target).val());
  });
  
  return span;
};

/**
 * Get an object suitable for json
 * @returns {Object}
 */
JazzeePage.prototype.getDataObject = function(){
  var obj = {
    typeId: this.typeId,
    typeName: this.typeName,
    typeClass: this.typeClass,
    kind: this.kind,
    id: this.id,
    uuid: this.uuid,
    status: this.status,
    title: this.title,
    min: this.min,
    max: this.max,
    isRequired: this.isRequired,
    answerStatusDisplay: this.answerStatusDisplay,        
    instructions: this.instructions,
    leadingText: this.leadingText,
    trailingText: this.trailingText,
    weight: this.weight,
    variables: this.variables,
    elements: [],
    children: []
  };
  for(var i in this.elements){
    obj.elements.push(this.elements[i].getDataObject());
  }
  for(var i=0;i<this.deletedElements.length; i++){
    obj.elements.push(this.deletedElements[i].getDataObject());
  }
  for(var i in this.children){
    obj.children.push(this.children[i].getDataObject());
  }
  for(var i=0;i<this.deletedChildren.length; i++){
    obj.children.push(this.deletedChildren[i].getDataObject());
  }
  return obj;
};

/**
 * Title display and editing workspace
 */
JazzeePage.prototype.titleWorkspace = function(){
  var pageClass = this;
  var div = $('<div>');
  var obj = new FormObject();
  var field = obj.newField({name: 'legend', value: 'Page Title'});
  var element = field.newElement('TextInput', 'title');
  element.label = 'Page Title';
  element.required = true;
  element.value = this.title;
  var dialog = this.displayForm(obj);
  $('form', dialog).bind('submit',function(e){
    pageClass.setProperty('title', $('input[name="title"]', this).val());
    div.replaceWith(pageClass.titleWorkspace());
    dialog.dialog("destroy").remove();
    return false;
  });//end submit
  var button = $('<button>').bind('click',function(){
    dialog.dialog('open');
  }).button({
    text: false,
    label: 'Edit Page Title',
    icons: {
      primary: 'ui-icon-pencil'
    }
  });
  var title = '<strong>'+this.title+'</strong>';
  div.append($('<p>').html(title).prepend(button));
  return div;
};

/**
 * Instructions display and editing workspace
 */
JazzeePage.prototype.instructionsWorkspace = function(){
  var pageClass = this;
  var div = $('<div>');
  var obj = new FormObject();
  var field = obj.newField({name: 'legend', value: 'Form Instructions'});
  var element = field.newElement('Textarea', 'instructions');
  element.label = 'Form Instructions';
  element.value = this.instructions;
  var dialog = this.displayForm(obj);
  if(this.isGlobal && !this.pageBuilder.editGlobal && this.instructions != this.globalPage.instructions){
    var text = $('<pre>').text(this.globalPage.instructions);
    dialog.prepend($('<div>').html('<h5>Global Instructions</h5>').append(text));
  } 
  $('form', dialog).bind('submit',function(e){
    var value = $('textarea[name="instructions"]', this).val()==''?null:$('textarea[name="instructions"]', this).val();
    pageClass.setProperty('instructions', value);
    dialog.dialog("destroy").remove();
    div.replaceWith(pageClass.instructionsWorkspace());
    return false;
  });//end submit
  var button = $('<button>').bind('click',function(){
    dialog.dialog('open');
    $('textarea', dialog).wysiwyg(pageClass.editorDefaults);
  }).button({
    text: this.instructions == null?true:false,
    label: 'Edit Form Instructions',
    icons: {
      primary: 'ui-icon-pencil'
    }
  });
  div.append($('<p>').html(this.instructions).prepend(button).addClass('instructions'));
  return div;
};

/**
 * Instructions display and editing workspace
 */
JazzeePage.prototype.leadingTextWorkspace = function(){
  var pageClass = this;
  var div = $('<div>');
  var obj = new FormObject();
  var field = obj.newField({name: 'legend', value: 'Leading Text'});
  var element = field.newElement('Textarea', 'text');
  element.label = 'Leading Text';
  element.value = this.leadingText;
  var dialog = this.displayForm(obj);
  if(this.isGlobal && !this.pageBuilder.editGlobal && this.leadingText != this.globalPage.leadingText){
    var text = $('<pre>').text(this.globalPage.leadingText);
    dialog.prepend($('<div>').html('<h5>Global Text</h5>').append(text));
  } 
  $('form', dialog).bind('submit',function(e){
    var value = $('textarea[name="text"]', this).val() == ''?null:$('textarea[name="text"]', this).val();
    pageClass.setProperty('leadingText', value);
    dialog.dialog("destroy").remove();
    div.replaceWith(pageClass.leadingTextWorkspace());
    return false;
  });//end submit
  var button = $('<button>').bind('click',function(){
    dialog.dialog('open');
    $('textarea', dialog).wysiwyg(pageClass.editorDefaults);
  }).button({
    text: this.leadingText == null?true:false,
    label: 'Edit Leading Text',
    icons: {
      primary: 'ui-icon-pencil'
    }
  });
  div.append($('<p>').html(this.leadingText).prepend(button));
  return div;
};

/**
 * Instructions display and editing workspace
 */
JazzeePage.prototype.trailingTextWorkspace = function(){
  var pageClass = this;
  var div = $('<div>');
  var obj = new FormObject();
  var field = obj.newField({name: 'legend', value: 'Trailing Text'});
  var element = field.newElement('Textarea', 'text');
  element.label = 'Trailing Text';
  element.value = this.trailingText;
  var dialog = this.displayForm(obj);
  if(this.isGlobal && !this.pageBuilder.editGlobal && this.trailingText != this.globalPage.trailingText){
    var text = $('<pre>').text(this.globalPage.trailingText);
    dialog.prepend($('<div>').html('<h5>Global Text</h5>').append(text));
  } 
  $('form', dialog).bind('submit',function(e){
    var value = $('textarea[name="text"]', this).val()==''?null:$('textarea[name="text"]', this).val();
    pageClass.setProperty('trailingText', value);
    dialog.dialog("destroy").remove();
    div.replaceWith(pageClass.trailingTextWorkspace());
    return false;
  });//end submit
  var button = $('<button>').bind('click',function(){
    dialog.dialog('open');
    $('textarea', dialog).wysiwyg(pageClass.editorDefaults);
  }).button({
    text: this.trailingText == null?true:false,
    label: 'Edit Trailing Text',
    icons: {
      primary: 'ui-icon-pencil'
    }
  });
  div.append($('<p>').html(this.trailingText).prepend(button));
  return div;
};

/**
 * Instructions display and editing workspace
 */
JazzeePage.prototype.pageInfo = function(){
  var pageClass = this;
  var div = $('<div>');
  div.append($('<h5>').html('Page Properties'));
  var p = $('<p>');
  p.append('Page Type: ' + this.typeName);
  if(this.isGlobal && !this.pageBuilder.editGlobal){
    p.append('<br />Global Page: ' + this.globalPage.title);
  }
  
  div.append(p);
  return div;
};

/**
 * Page properties Button
 * @return {jQuery}
 */
JazzeePage.prototype.pagePropertiesButton = function(){
  var pageClass = this;
  var button = $('<button>').html('Page Properties').addClass('properties').attr('id', 'pageProperties');
  button.button({
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
      text: pageClass.pageProperties(),
      title: {
        text: 'Edit Page Properties',
        button: true
      }
    }
  });
  return button;
}

/**
 * Default Page properties button doesn't return anything
 */
JazzeePage.prototype.pageProperties = function(){return false;}

/**
 * Create the page workspace
 * This is overridden by most page types
 */
JazzeePage.prototype.workspace = function(){
  var pageClass = this;
  $('#editPage').hide();
  $('#workspace').empty();
  $('#pageToolbar').empty();
  $('#pageInfo').empty();
  
  $('#workspace').parent().addClass('form');
  $('#workspace').append(this.titleWorkspace());
  $('#workspace').append(this.leadingTextWorkspace());
  var formDiv = $('<div>').addClass('form');
  formDiv.append(this.instructionsWorkspace());
  formDiv.append($('<div>').attr('id', 'elements'));
  $('#workspace').append(formDiv);
  $('#workspace').append(this.trailingTextWorkspace());
  $('#pageToolbar').append(this.copyPageButton());
  $('#pageToolbar').append(this.previewPageButton());
  $('#pageToolbar').append(this.deletePageButton());
  $('#pageToolbar').append(this.exportPageButton());
  
  $('#pageInfo').append(this.pageInfo());
  $('#editPage').show('slide');
};