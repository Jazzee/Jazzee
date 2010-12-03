/**
 * The ApplyElement class
 * Standardizes client side handling of a element doesn't communicate with server
 * @return
 */

function ApplyElement(){
  this.page;
  this.id;
  this.title;
  this.format;
  this.instructions;
  this.defaultValue;
  this.required;
  this.min;
  this.max;
  this.weight;
  
  //the html element where we are displaying controls
  this.canvas = null;
  
  this.init = function(obj, page){
    this.page = page;
    this.id = obj.id;
    this.title = obj.title;
    this.instructions = obj.instructions;
    this.defaultValue = obj.defaultValue;
    this.required = obj.required;
    this.min = obj.min;
    this.max = obj.max;
//    this.weight = obj.weight;
  }
  
  this.workspace = function(canvas){
    $(canvas).html('No canvas for this element type has been defined');
  }
  
  this.setProperty = function(property, value){
    this[property] = value;
    this.page.isModified = true;
  }
  
  this.titleBlock = function(){
    return this.editBlock('small', 'title', 'Title', 'titleBlock');
  }
  
  this.instructionsBlock = function(){
    return this.editBlock('large', 'instructions', 'Instructions', 'instructionsBlock');
  }
  
  this.formatBlock = function(){
    return this.editBlock('large', 'format', 'Format', 'formatBlock');
  }
  
  this.deleteElementBlock = function(){
    var div = $('<p>Delete this element</p>').addClass('deleteElement').bind('click', {elementClass: this}, this.deleteElement);
    return div;
  }
  
  this.editBlock = function(control, name, title, callback){
    if(this[name]) var value = this[name];
    else var value = 'click to edit...';
    var p = $('<p>').addClass(name).html(title+': ' + value).bind('click', {elementClass: this, control: control, name: name, callback: callback}, function(e){
      $(this).unbind('click');
      switch(control){
        case 'large':
          var field = $('<textarea>').html(e.data.elementClass[name]);
          break;
        case 'small':
          var field = $('<input type="text">').attr('value',e.data.elementClass[name]);
          break;
      }
      field.bind('change', {elementClass: e.data.elementClass, name: name}, function(e){
        e.data.elementClass.setProperty(e.data.name, $(this).val());
      }).bind('blur', {elementClass: e.data.elementClass, callback: callback}, function(e){
        $(this).parent().replaceWith(e.data.elementClass[e.data.callback]());
      });
      $(this).empty().append(field);
      $(field).trigger('focus');
    });
    return p;
  }
  
  this.requiredBlock = function(){
    var value = 'optional';
    if(this.required == 1) value = 'required';
    var p = $('<p>').addClass('required').html('This element is ').append($('<span>').html(value).bind('click', {elementClass: this}, function(e){
      $(this).unbind('click');
      var field = $('<select>');
      var optional = $('<option>').attr('value', 0).html('Optional');
      if(e.data.elementClass.required == 0) optional.attr('selected', true);
      field.append(optional);
      var required = $('<option>').attr('value', 1).html('Required');
      if(e.data.elementClass.required == 1) required.attr('selected', true);
      field.append(required);
      field.bind('change', {elementClass: e.data.elementClass}, function(e){
        e.data.elementClass.setProperty('required', $(this).val());
      });
      field.bind('blur', {elementClass: e.data.elementClass}, function(e){
        $(this).parent().parent().html(e.data.elementClass.requiredBlock());
      });
      $(this).empty().append(field);
    }));
    return p;
  }
  
  
  this.getDataObject = function(){
    var obj = {
        id: this.id,
        title: this.title,
        format: this.format,
        instructions: this.instructions,
        defaultValue: this.defaultValue,
        required: this.required,
        min: this.min,
        max: this.max,
//        weight: this.weight
        list: []
    };
    $(this.list).each(function(){
      obj.list.push(this);
    });
    return obj;
  }
  
  this.deleteElement = function(e){
    var element = e.data.elementClass;
    element.page.pageStore.deleteElement(element.id);
    $('div', element.canvas).effect('explode',500);
  }
  
  this.standardWorkspace = function(){
    var div = $('<div>').addClass('element')
      .append($('<div>').addClass('yui-u first element-left'))
      .append($('<div>').addClass('yui-u element-right'));
    return div;
  }
  
}

/**
 * The TextInputElement class
 */
function TextInputElement(){
  this.workspace = function(canvas){
    this.canvas = canvas;
    var workspace = this.standardWorkspace();
    var left = $('div.element-left', workspace);
    left.append(this.titleBlock());
    left.append(this.instructionsBlock());
    left.append(this.formatBlock());
    
    var right = $('div.element-right', workspace);
    right.append(this.deleteElementBlock());
    right.append(this.requiredBlock());
    $(this.canvas).html(workspace);
  }
}
TextInputElement.prototype = new ApplyElement();

/**
 * The HiddenInputElement class
 */
function HiddenInputElement(){
  this.workspace = function(canvas){
    this.canvas = canvas;
    var workspace = this.standardWorkspace();
    var left = $('div.element-left', workspace);
    left.append(this.titleBlock());
    
    var right = $('div.element-right', workspace);
    right.append(this.deleteElementBlock());
    right.append(this.requiredBlock());
    $(this.canvas).html(workspace);
  }
}
HiddenInputElement.prototype = new ApplyElement();

/**
 * The TextareaElement class
 */
function TextareaElement(){}
TextareaElement.prototype = new TextInputElement();

/**
 * The PDFUploadElement class
 */
function PDFFileInputElement(){
  this.workspace = function(canvas){
    this.canvas = canvas;
    var workspace = this.standardWorkspace();
    var left = $('div.element-left', workspace);
    left.append(this.titleBlock());
    left.append(this.instructionsBlock());
    
    var right = $('div.element-right', workspace);
    right.append(this.deleteElementBlock());
    right.append(this.requiredBlock());
    right.append(this.maxFileSizeBlock());
    
    $(this.canvas).html(workspace);
  }
  
  this.maxFileSizeBlock = function(){
    return this.editBlock('small', 'max', 'Maximum File Size', 'maxFileSizeBlock');
  }
}
PDFFileInputElement.prototype = new ApplyElement();

/**
 * The ListElement class
 * Generic List functions that specific elements can inherit
 */
function ListElement(){
  this.list = [];
  
  this.addListItem = function(id, value){
    this.list.push({id: id, value: value});
  }
  
  this.workspace = function(canvas){
    this.canvas = canvas;
    var workspace = this.standardWorkspace();
    var left = $('div.element-left', workspace);
    left.append(this.titleBlock());
    left.append(this.instructionsBlock());
    
    var right = $('div.element-right', workspace);
    right.append(this.deleteElementBlock());
    right.append(this.requiredBlock());
    right.append(this.listItemsBlock());
    $(this.canvas).html(workspace);
  }
  
  this.editItemBlock = function(item){
    var li = $('<li>').html(item.value).bind('click', {elementClass: this, item: item}, function(e){
      $(this).unbind('click');
      var field = $('<input type="text">').attr('value',e.data.item.value);
      field.bind('change', {elementClass: e.data.elementClass, item: e.data.item}, function(e){
        e.data.elementClass.editListItem(e.data.item.id, $(this).val());
      }).bind('blur', {elementClass: e.data.elementClass, item: e.data.item}, function(e){
        $(this).parent().replaceWith(e.data.elementClass.editItemBlock(e.data.item));
      });
      $(this).empty().append(field);
      $(field).trigger('focus');
    });
    return li;
  }
  
  this.editListItem = function(id, value){
    for(var i = 0; i < this.list.length; i++){
      var item = this.list[i];
      if(item.id == id){
        this.list[i].value = value;
        this.page.isModified = true;
        break;
      }
    }
  }
  
  this.listItemsBlock = function(){
    var div = $('<div>').addClass('listItem container').append($('<h5>').html('List Items'));
    var ol = $('<ol>');
    for(var i = 0; i < this.list.length; i++){
      ol.append(this.editItemBlock(this.list[i]));
    }
    div.append(ol);
    
    var p = $('<p>').addClass('add-list-item').html('add item').bind('click', {elementClass: this, ol: ol}, function(e){
      var field = $('<input type="text">');
      field.bind('change', {elementClass: e.data.elementClass}, function(e){
        e.data.elementClass.page.pageStore.addListItem(e.data.elementClass.page.id, e.data.elementClass.id, $(this).val());
      }).bind('blur', {field: field}, function(e){
        $(field).remove();
      });
      $(e.data.ol).append($('<li>').append(field));
      $(field).trigger('focus');
    });
    div.append(p);
    return div;
  }
}
ListElement.prototype = new ApplyElement();
ListElement.prototype.constructor = ListElement;

/**
 * The RadioListElement class
 */
function RadioListElement(){}
RadioListElement.prototype = new ListElement();

/**
 * The SelectListElement class
 */
function SelectListElement(){}
SelectListElement.prototype = new ListElement();

/**
 * The CheckboxListElement class
 */
function CheckboxListElement(){}
CheckboxListElement.prototype = new ListElement();