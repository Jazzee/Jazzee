/**
 * Initialize the \\Jazzee\Page Abstract Type
  @class The base class for all page types
  @property {String} status the pages status This is sent to the server so we can decide to create a new page or modify an existing one
  @property {boolean} isModified Is the page modified or new Pages will only be saved back to the server if this is true
 */
function JazzeePage(){
  this.pageStore;
  this.pageId;
  this.applicationPageId;
  this.className;
  this.classId;
  this.isGlobal;
  this.title;
  this.min;
  this.max;
  this.isRequired;
  this.instructions;
  this.leadingText;
  this.trailingText;
  this.answerStatusDisplay;
  this.weight;

  this.variables;
  this.elements;
  this.deletedElements;
  this.children;
  this.deletedChildren;
  
  this.status;
  this.isModified;
}

/**
 * Initialize the object
 * @param {Object} pageObject
 * @param {PageStore} pageStore
 */
JazzeePage.prototype.init = function(pageObject, pageStore){
  this.pageStore = pageStore;
  this.id = pageObject.id;
  this.classId = pageObject.classId;
  this.className = pageObject.className;
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

  this.variables = {};
  this.elements = [];
  this.deletedElements = [];
  this.children = {};
  this.deletedChildren = [];
  
  this.status = '';
  this.isModified = false;
};

/**
 * Create a new object with good default page values
 * @param {String} id the id to use
 * @returns {JazzeePage}
 */
JazzeePage.prototype.newPage = function(id,title,classId,className,status,pageStore){
  var obj = {
    id: id,
    classId: classId,
    className: className,
    title: title,
    isGlobal: false,
    min: 0,
    max: 0,
    optional: false,
    showAnswerStatus: false,        
    instructions: null,
    leadingText: null,
    trailingText: null,
    weight: 0
  };
  var page = new window[className]();
  page.init(obj, pageStore);
  page.status = status;
  page.isModified = true;
  return page;
};

/**
 * Check to see if the JazzeePage has been modified
 * @returns {Boolean}
 */
JazzeePage.prototype.checkModified = function(){
  if(this.isModified) return true;
  for(var i in this.children) {
    if(this.children[i].checkModified()) return true;
  }
  for(var i = 0; i < this.elements.length; i++){
    if(this.elements[i].checkModified()) return true;
  }
  return false;
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
  this.isModified = true;
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
  delete this.children[page.pageId];
  this.isModified = true;
  page.status = 'delete';
  this.deletedChildren.push(page);
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
    this.variables[name] = {name : name, value: value};
    this.isModified = true;
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
    this[name] = value;
    this.isModified = true;
    this.pageStore.synchronizePageList();
  }
  return this[name];
};

//These are the default blocks for editing page porperties

/**
 * Block for deleting the current page
 * @returns {jQuery}
 */
JazzeePage.prototype.deletePageBlock = function(){
  var pageClass = this;
  var p = $('<p>Delete this page</p>').addClass('delete').bind('click', function(e){
    $('#workspace').effect('explode',500);
    pageClass.status = 'delete';
    pageClass.pageStore.deletePage(pageClass);
  });
  return p;
};

/**
 * Block for copying the page
 * @returns {jQuery}
 */
JazzeePage.prototype.copyPageBlock = function(){
  var pageClass = this;
  var p = $('<p>Copy this page</p>').addClass('copy').bind('click', function(e){
    pageClass.pageStore.copyPage(pageClass);
  });
  return p;
};

JazzeePage.prototype.previewPageBlock = function(){
  var pageClass = this;
  var p = $('<p>Preview the page</p>').addClass('preview').bind('click',function(e){
    var preview = pageClass.pageStore.getPagePreview(pageClass);
    $('form', preview).bind('submit', function(){return false;});
    $('fieldset.buttons ', preview).remove();
    $(preview).dialog({ width: 800 });
  });
  return p;
};

/**
 * Create the page title block
 * @returns {jQuery}
 */
JazzeePage.prototype.titleBlock = function(){
  var pageClass = this;
  var field = $('<input type="text">').attr('value',this.title)
    .bind('change',function(){
      pageClass.setProperty('title', $(this).val());
    })
    .bind('blur', function(){
      $(this).parent().replaceWith(pageClass.titleBlock());
  }).hide();
  var p = $('<p>').addClass('edit title').html((this.title)).bind('click', function(){
    $(this).hide();
    $(this).parent().children('input').eq(0).show().focus();
  });
  return $('<div>').append(p).append(field);
};

/**
 * A generic text area block for editing properties
 * @param {String} propertyName
 * @para, {String} valueIfBlank what do display if the property isn't set
 * @return {jQuery}
 */
JazzeePage.prototype.textAreaBlock = function(propertyName, valueIfBlank){
  var pageClass = this;
  var field = $('<textarea>').html(this[propertyName])
  .bind('change',function(){
    pageClass.setProperty(propertyName, $(this).val());
  })
  .bind('blur', function(){
    $(this).parent().replaceWith(pageClass.textAreaBlock(propertyName, valueIfBlank));
  }).hide();
  var p = $('<p>').addClass('edit').addClass(propertyName).html(((this[propertyName] == null)?valueIfBlank:this[propertyName])).bind('click', function(){
    $(this).hide();
    $(this).parent().children('textarea').eq(0).show().focus();
  });
  return $('<div>').append(p).append(field);
};

/**
 * A generic text input block for editing properties
 * @param {String} propertyName
 * @para, {String} valueIfBlank what do display if the property isn't set
 * @return {jQuery}
 */
JazzeePage.prototype.textInputBlock = function(propertyName, valueIfBlank){
  var pageClass = this;
  var field = $('<input>').attr('value',(this[propertyName]))
  .bind('change',function(){
    pageClass.setProperty(propertyName, $(this).val());
  })
  .bind('blur', function(){
    $(this).parent().replaceWith(pageClass.textInputBlock(propertyName, valueIfBlank));
  }).hide();
  var p = $('<p>').addClass('edit').addClass(propertyName).html(((this[propertyName] == null)?valueIfBlank:this[propertyName])).bind('click', function(){
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
JazzeePage.prototype.selectListBlock = function(propertyName, description, options){
  var pageClass = this;
  var p = $('<p>').addClass('edit').html(description + ' ').append($('<span>').html(options[this[propertyName]]).bind('click',function(e){
    $(this).unbind('click');
    var select = $('<select>');
    $.each(options,function(value, text){
      var option = $('<option>').attr('value', value).html(text);
      if(pageClass[propertyName] == value) option.attr('selected', true);
      select.append(option);
    });
    select.bind('change', function(e){
      pageClass.setProperty(propertyName, $(this).val());
    });
    select.bind('blur', function(e){
     p.replaceWith(pageClass.selectListBlock(propertyName,description, options));
    });
    $(this).empty().append(select);
  }));
  return p;
};

/**
 * A generic text input block for editing variables
 * @param {String} variableName
 * @para, {String} valueIfBlank what do display if the property isn't set
 * @return {jQuery}
 */
JazzeePage.prototype.textInputVariableBlock = function(variableName, title, valueIfBlank){
  var pageClass = this;
  var field = $('<input>').attr('value',(this.getVariable(variableName)))
  .bind('change',function(){
    pageClass.setVariable(variableName, $(this).val());
  })
  .bind('blur', function(){
    $(this).parent().parent().replaceWith(pageClass.textInputVariableBlock(variableName, title, valueIfBlank));
  }).hide();
  var value = (this.getVariable(variableName) == null || this.getVariable(variableName) == '')?valueIfBlank:this.getVariable(variableName);
  var span = $('<span>').html(value).bind('click', function(){
    $(this).hide();
    $(this).parent().children('input').eq(0).show().focus();
  });
  var p = $('<p>').addClass('edit').addClass(variableName).html(title).append(span).append(field);
  return $('<div>').append(p);
};

/**
 * A generic block for editng variables using a dropdown
 * @param {String} variableName
 * @param {String} description
 * @param {Object} options
 * @returns {jQuery}
 */
JazzeePage.prototype.selectListVariableBlock = function(variableName, description, options){
  var choice = options[this.getVariable(variableName)];
  // if the choice isn't availalbe then just grab the first one off the array
  if(choice == undefined){
	for (var option in options){
	  choice = option;
	  break;
	}
  }
  var pageClass = this;
  var p = $('<p>').addClass('edit').html(description + ' ').append($('<span>').html(choice).bind('click',function(e){
    $(this).unbind('click');
    var select = $('<select>');
    $.each(options,function(value, text){
      var option = $('<option>').attr('value', value).html(text);
      if(pageClass.getVariable(variableName) == value) option.attr('selected', true);
      select.append(option);
    });
    select.bind('change', function(e){
      pageClass.setVariable(variableName, $(this).val());
    });
    select.bind('blur', function(e){
      $(this).parent().parent().replaceWith(pageClass.selectListVariableBlock(variableName,description, options));
    });
    $(this).empty().append(select);
  }));
  return p;
};

/**
 * Get an object suitable for json
 * @returns {Object}
 */
JazzeePage.prototype.getDataObject = function(){
  var obj = {
    classId: this.classId,
    className: this.className,
    id: this.id,
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
 * Clear the workspace
 */
JazzeePage.prototype.clearWorkspace = function(){
  $('#workspace').hide();
  $('#workspace-left-top').empty();
  $('#workspace-left-middle-left').empty();
  $('#workspace-left-middle-right').empty();
  $('#workspace-left-bottom-left').empty();
  $('#workspace-left-bottom-right').empty();
  

  $('#workspace-right-top').empty();
  $('#workspace-right-middle').empty();
  $('#workspace-right-bottom').empty();
};

/**
 * Create the page workspace
 * This is overridden by most page types
 */
JazzeePage.prototype.workspace = function(){
  this.clearWorkspace();
  $('#workspace-left-top').parent().addClass('form');
  $('#workspace-left-top').append(this.titleBlock());
  $('#workspace-left-top').append(this.textAreaBlock('leadingText', 'click to edit'));
  $('#workspace-left-top').append(this.textAreaBlock('instructions', 'click to edit'));
  $('#workspace-left-bottom-left').append(this.textAreaBlock('trailingText', 'click to edit'));
  $('#workspace-right-top').append(this.copyPageBlock());
  $('#workspace-right-top').append(this.previewPageBlock());
  
  $('#workspace-right-bottom').append(this.deletePageBlock());
  $('#workspace').show('slide');
};