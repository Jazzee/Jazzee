/**
 * The ApplyPage class
 * Standardizes client side handling of a page doesn't communicate with server
 * @return
 */

function ApplyPage(){
  this.pageStore;
  this.id;
  this.title;
  this.min;
  this.max;
  this.optional;
  this.instructions;
  this.leadingText;
  this.trailingText;
  this.weight;
  this.variables;
  this.elements;
  
  this.isModified;
  
  //the html element where we are displaying controls
  this.canvas;
  
  this.init = function(obj, pageStore){
    this.pageStore = pageStore;
    this.id = obj.id;
    this.title = obj.title;
    this.min = obj.min;
    this.max = obj.max;
    this.optional = obj.optional;
    this.instructions = obj.instructions;
    this.leadingText = obj.leadingText;
    this.trailingText = obj.trailingText;
    this.weight = obj.weight;
    
    this.elements = [];
    this.isModified = false;
  }
  
  this.workspace = function(canvas){
    $(canvas).html('No canvas for this element type has been defined');
  }
  
  this.addElement = function(obj){
    this.elements.push(obj);
  }
  
  this.setProperty = function(property, value){
    this[property] = value;
    this.isModified = true;
  }
  
  this.deletePageBlock = function(){
    var p = $('<p>Delete this page</p>').addClass('deletePage').bind('click', {pageClass: this}, this.deletePage);
    return p;
  }
  
  this.previewPageBlock = function(){
    var p = $('<p>Preview the page</p>').addClass('previewPage').bind('click', {pageClass: this}, function(e){
      var preview = e.data.pageClass.pageStore.getPagePreview(e.data.pageClass.id);
      $('form', preview).bind('submit', function(){alert('bad idea'); return false;});
      $('fieldset.buttons ', preview).remove();
      $(preview).dialog({ width: 800 });
    });
    return p;
  }
  
  this.editBlock = function(control, name, title, callback){
    if(this[name]) var value = this[name];
    else var value = 'click to edit...';
    var p = $('<p>').addClass(name).html((title+': ' + value)).bind('click', {pageClass: this, control: control, name: name, callback: callback}, function(e){
      $(this).unbind('click');
      switch(control){
        case 'large':
          var field = $('<textarea>').html(e.data.pageClass[name]);
          break;
        case 'small':
          var field = $('<input type="text">').attr('value',e.data.pageClass[name]);
          break;
      }
      field.bind('change', {pageClass: e.data.pageClass, name: name}, function(e){
        e.data.pageClass.setProperty(e.data.name, $(this).val());
      }).bind('blur', {pageClass: e.data.pageClass, callback: callback}, function(e){
        $(this).parent().replaceWith(e.data.pageClass[e.data.callback]());
      });
      $(this).empty().append(field);
      $(field).trigger('focus');
    });
    return p;
  }
  
  this.titleBlock = function(){
    return this.editBlock('small', 'title', 'Title', 'titleBlock');
  }
  
  this.leadingTextBlock = function(){
    return this.editBlock('large', 'leadingText', 'Leading Text', 'leadingTextBlock');
  }
  
  this.instructionsBlock = function(){
    return this.editBlock('large', 'instructions', 'Instructions', 'instructionsBlock');
  }
  
  this.trailingTextBlock = function(){
    return this.editBlock('large', 'trailingText', 'Trailing Text', 'trailingTextBlock');
  }
  
  this.optionalBlock = function(){
    var value = 'required';
    if(this.optional == 1) value = 'optional';
    var p = $('<p>').addClass('optional').html('This page is ').append($('<span>').html(value).bind('click', {pageClass: this}, function(e){
      $(this).unbind('click');
      var field = $('<select>');
      var optional = $('<option>').attr('value', 1).html('Optional');
      if(e.data.pageClass.optional == 1) optional.attr('selected', true);
      field.append(optional);
      var required = $('<option>').attr('value', 0).html('Required');
      if(e.data.pageClass.optional == 0) required.attr('selected', true);
      field.append(required);
      field.bind('change', {pageClass: e.data.pageClass}, function(e){
        e.data.pageClass.setProperty('optional', $(this).val());
      });
      field.bind('blur', {pageClass: e.data.pageClass}, function(e){
        $(this).parent().parent().html(e.data.pageClass.optionalBlock());
      });
      $(this).empty().append(field);
    }));
    return p;
  }
  
  this.minBlock = function(){
    var value = 'No minimum';
    if(this.min > 0) value = this.min;
    var p = $('<p>').addClass('min').append($('<span>').html(value).bind('click', {pageClass: this}, function(e){
      $(this).unbind('click');
      var field = $('<select>');
      var option = $('<option>').attr('value', 0).html('No minimum');
      if(e.data.pageClass.min == 0) option.attr('selected', true);
      field.append(option);
      for(var i=1; i < 50; i++){
        var option = $('<option>').attr('value', i).html(i);
        if(e.data.pageClass.min == i) option.attr('selected', true);
        field.append(option);
      }
      field.bind('change', {pageClass: e.data.pageClass}, function(e){
        e.data.pageClass.setProperty('min', $(this).val());
      });
      field.bind('blur', {pageClass: e.data.pageClass}, function(e){
        $(this).parent().parent().html(e.data.pageClass.minBlock());
      });
      $(this).empty().append(field);
    })).append(' answer(s) required on this page');
    return p;
  }
  
  this.maxBlock = function(){
    var value = 'Unlimited';
    if(this.max > 0) value = this.max;
    var p = $('<p>').addClass('max').append($('<span>').html(value).bind('click', {pageClass: this}, function(e){
      $(this).unbind('click');
      var field = $('<select>');
      var option = $('<option>').attr('value', 0).html('Unlimited');
      if(e.data.pageClass.max == 0) option.attr('selected', true);
      field.append(option);
      for(var i=1; i < 50; i++){
        var option = $('<option>').attr('value', i).html(i);
        if(e.data.pageClass.max == i) option.attr('selected', true);
        field.append(option);
      }
      field.bind('change', {pageClass: e.data.pageClass}, function(e){
        e.data.pageClass.setProperty('max', $(this).val());
      });
      field.bind('blur', {pageClass: e.data.pageClass}, function(e){
        $(this).parent().parent().html(e.data.pageClass.maxBlock());
      });
      $(this).empty().append(field);
    })).append(' answer(s) allowed on this page');
    return p;
  }
  
  this.savePageBlock = function(){
    var p = $('<p>Save this page</p>').addClass('savePage').bind('click', {pageClass: this}, function(e){
      e.data.pageClass.save();
    });
    return p;
  }
  
  this.checkControlStatus = function(){
    if(this.isModified)  $('#workspace-controls').show();
    if(!this.isModified)  $('#workspace-controls').hide();
  }
  
  this.getDataObject = function(){
    var obj = {
        id: this.id,
        title: this.title,
        min: this.min,
        max: this.max,
        optional: this.optional,
        instructions: this.instructions,
        leadingText: this.leadingText,
        trailingText: this.trailingText,
        weight: this.weight,
        elements: []
    };
    $(this.elements).each(function(){
      obj.elements.push(this.getDataObject());
    });
    return obj;
  }
  
  this.save = function(){
    this.pageStore.save(this.id);
    this.isModified = false;
    $('div', this.canvas).effect('highlight',500);
  }
  
  this.deletePage = function(e){
    var page = e.data.pageClass;
    page.pageStore.deletePage(page.id);
    $('div', page.canvas).effect('explode',500);
  }
  
}

/**
 * The StandardPage class
 */
function StandardPage(){
  this.workspace = function(canvas){
    this.canvas = canvas;
    var div = $('<div>').addClass('yui-ge');
    var left = $('<div>').addClass('yui-u first yui-ge');
    left.append(this.titleBlock());
    left.append(this.leadingTextBlock());
    left.append(this.instructionsBlock());
    left.append(this.trailingTextBlock());
    left.append(this.elementsBlock());
    
    var right = $('<div>').addClass('yui-u');
    right.append(this.savePageBlock());
    right.append(this.previewPageBlock());
    right.append(this.minBlock());
    right.append(this.maxBlock());
    right.append(this.optionalBlock());
    right.append(this.newElementsBlock());
    right.append(this.deletePageBlock());
    div.append(left);
    div.append(right);
    $(this.canvas).html(div);
  }
  
  this.newElementsBlock = function(){
    var pageClass = this;
    var div = $('<div>').attr('id', 'new-elements');
    div.append($('<h5>').html('New Elements'));
    var ol = $('<ol>');
    $(this.pageStore.elementTypes).each(function(id,name){
      var li = $('<li>').html(name);
      $(li).bind('click', {pageClass: this},function(e){
        pageClass.pageStore.addElement(pageClass.id, id);
      });
      ol.append(li);
    });
    $(div).append(ol);
    return div;
  }
  
  this.elementsBlock = function(){
    var div = $('<div>').addClass('elements');
    div.append($('<h5>').html('Elements'));
    var ol = $('<ol>');
    $(this.elements).each(function(){
      var li = $('<li>');
      this.workspace(li);
      ol.append(li);
    });
    $(div).append(ol);
    return div;
  }
}
StandardPage.prototype = new ApplyPage();
StandardPage.prototype.constructor = StandardPage;
/**
 * The TextPage class
 */
function TextPage(){
  this.workspace = function(canvas){
    this.canvas = canvas;
    var div = $('<div>').addClass('yui-ge');
    var left = $('<div>').addClass('yui-u first yui-ge');
    left.append(this.titleBlock());
    left.append(this.leadingTextBlock());
    left.append(this.trailingTextBlock());
    
    var right = $('<div>').addClass('yui-u');
    right.append(this.savePageBlock());
    right.append(this.previewPageBlock());
    right.append(this.deletePageBlock());
    
    div.append(left);
    div.append(right);
    $(this.canvas).html(div);
  }
}
TextPage.prototype = new ApplyPage();
TextPage.prototype.constructor = TextPage;

/**
 * The LockPage class
 */
function LockPage(){
  this.workspace = function(canvas){
    this.canvas = canvas;
    var div = $('<div>').addClass('yui-ge');
    var left = $('<div>').addClass('yui-u first yui-ge');
    left.append(this.titleBlock());
    left.append(this.leadingTextBlock());
    left.append(this.instructionsBlock());
    left.append(this.trailingTextBlock());
    
    var right = $('<div>').addClass('yui-u');
    right.append(this.savePageBlock());
    right.append(this.previewPageBlock());
    right.append(this.deletePageBlock());
    
    div.append(left);
    div.append(right);
    $(this.canvas).html(div);
  }
}
LockPage.prototype = new ApplyPage();
LockPage.prototype.constructor = LockPage;

/**
 * The ETSMatch class
 */
function ETSMatchPage(){
  this.workspace = function(canvas){
    this.canvas = canvas;
    var div = $('<div>').addClass('yui-ge');
    var left = $('<div>').addClass('yui-u first yui-ge');
    left.append(this.titleBlock());
    left.append(this.leadingTextBlock());
    left.append(this.instructionsBlock());
    left.append(this.trailingTextBlock());
    
    var right = $('<div>').addClass('yui-u');
    right.append(this.savePageBlock());
    right.append(this.previewPageBlock());
    right.append(this.deletePageBlock());
    
    div.append(left);
    div.append(right);
    $(this.canvas).html(div);
  }
}
ETSMatchPage.prototype = new ApplyPage();
ETSMatchPage.prototype.constructor = ETSMatchPage;

/**
 * The CitizenshipPage class
 */
function CitizenshipPage(){
  this.workspace = function(canvas){
    this.canvas = canvas;
    var div = $('<div>').addClass('yui-ge');
    var left = $('<div>').addClass('yui-u first yui-ge');
    left.append(this.titleBlock());
    left.append(this.leadingTextBlock());
    left.append(this.instructionsBlock());
    left.append(this.trailingTextBlock());
    
    var right = $('<div>').addClass('yui-u');
    right.append(this.savePageBlock());
    right.append(this.previewPageBlock());
    right.append(this.deletePageBlock());
    
    div.append(left);
    div.append(right);
    $(this.canvas).html(div);
  }
}
CitizenshipPage.prototype = new ApplyPage();
CitizenshipPage.prototype.constructor = CitizenshipPage;