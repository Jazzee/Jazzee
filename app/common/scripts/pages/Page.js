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
  this.children;
  
  this.isModified = false;
  this.showLeadingText = true;
  this.showTrailingText = true;
  this.showInstructions = true;
  this.showMin = true;
  this.showMax = true;
  this.showRequired = true;
  this.hasElements = true;
  
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
    this.variables = [];
    this.children = [];
  }
  
  this.addElement = function(obj){
    this.elements.push(obj);
  }
  
  this.addChild = function(obj){
    this.children.push(obj);
  }
  
  this.setVariable = function(name, value){
    this.variables[name] = value;
  }
  
  this.setProperty = function(property, value){
    this[property] = value;
    this.isModified = true;
  }
  
  this.deletePageBlock = function(){
    var p = $('<p>Delete this page</p>').addClass('delete').bind('click', {pageClass: this}, this.deletePage);
    return p;
  }
  
  this.previewPageBlock = function(){
    var p = $('<p>Preview the page</p>').addClass('preview').bind('click', {pageClass: this}, function(e){
      var preview = e.data.pageClass.pageStore.getPagePreview(e.data.pageClass.id);
      $('form', preview).bind('submit', function(){alert('bad idea'); return false;});
      $('fieldset.buttons ', preview).remove();
      $(preview).dialog({ width: 800 });
    });
    return p;
  }
  
  this.titleBlock = function(){
    var pageClass = this;
    var field = $('<input type="text">').attr('value',this.title)
      .bind('change',function(){
        pageClass.setProperty('title', $(this).val());
      })
      .bind('blur', function(){
        $(this).hide();
        $(this).parent().children('p').eq(0).html(pageClass.title);
        $(this).parent().children('p').eq(0).show();
    }).hide();
    var p = $('<p>').addClass('edit title').html((this.title)).bind('click', function(){
      $(this).hide();
      $(this).parent().children('input').eq(0).show().focus();
    });
    return $('<div>').append(p).append(field);
  }
  
  this.leadingTextBlock = function(){
    var pageClass = this;
    var field = $('<textarea>').html(this.leadingText)
      .bind('change',function(){
        pageClass.setProperty('leadingText', $(this).val());
      })
      .bind('blur', function(){
        $(this).hide();
        $(this).parent().children('p').eq(0).html(pageClass.valueOrBlank(pageClass.leadingText));
        $(this).parent().children('p').eq(0).show();
    }).hide();
    var p = $('<p>').addClass('edit').html(this.valueOrBlank(this.leadingText)).bind('click', function(){
      $(this).hide();
      $(this).parent().children('textarea').eq(0).show().focus();
    });
    return $('<div>').append(p).append(field);
  }
  
  this.instructionsBlock = function(){
    var pageClass = this;
    var field = $('<textarea>').html(this.title)
      .bind('change',function(){
        pageClass.setProperty('instructions', $(this).val());
      })
      .bind('blur', function(){
        $(this).hide();
        $(this).parent().children('p').eq(0).html(pageClass.valueOrBlank(pageClass.instructions));
        $(this).parent().children('p').eq(0).show();
    }).hide();
    var p = $('<p>').addClass('edit instructions').html(this.valueOrBlank(this.instructions)).bind('click', function(){
      $(this).hide();
      $(this).parent().children('textarea').eq(0).show().focus();
    });
    return $('<div>').append(p).append(field);
  }
  
  this.trailingTextBlock = function(){
    var pageClass = this;
    var field = $('<textarea>').html(this.trailingText)
      .bind('change',function(){
        pageClass.setProperty('trailingText', $(this).val());
      })
      .bind('blur', function(){
        $(this).hide();
        $(this).parent().children('p').eq(0).html(pageClass.valueOrBlank(pageClass.trailingText));
        $(this).parent().children('p').eq(0).show();
    }).hide();
    var p = $('<p>').addClass('edit').html(this.valueOrBlank(this.trailingText)).bind('click', function(){
      $(this).hide();
      $(this).parent().children('textarea').eq(0).show();
    });
    return $('<div>').append(p).append(field);
  }
  
  this.valueOrBlank = function(value){
    if(value == '' || value == null) return 'click to edit';
    return value;
  }
  
  this.optionalBlock = function(){
    var value = 'required';
    if(this.optional == 1) value = 'optional';
    var p = $('<p>').addClass('edit optional').html('This page is ').append($('<span>').html(value).bind('click', {pageClass: this}, function(e){
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
    var p = $('<p>').addClass('edit min').append($('<span>').html(value).bind('click', {pageClass: this}, function(e){
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
    var p = $('<p>').addClass('edit max').append($('<span>').html(value).bind('click', {pageClass: this}, function(e){
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
    var p = $('<p>Save this page</p>').addClass('save').bind('click', {pageClass: this}, function(e){
      e.data.pageClass.save();
    });
    return p;
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
    $('#workspace').effect('highlight',500);
  }
  
  this.deletePage = function(e){
    var page = e.data.pageClass;
    page.pageStore.deletePage(page.id);
    $('#workspace').effect('explode',500);
  }
  
  this.workspace = function(){
    $('#workspace-left-top').parent().addClass('form');
    $('#workspace-left-top').append(this.titleBlock());
    if(this.showLeadingText) $('#workspace-left-top').append(this.leadingTextBlock());
    if(this.showInstructions) $('#workspace-left-top').append(this.instructionsBlock());
    if(this.showTrailingText) $('#workspace-left-bottom-left').append(this.trailingTextBlock());
    
    $('#workspace-right-top').append(this.savePageBlock());
    $('#workspace-right-top').append(this.previewPageBlock());
    if(this.showMin) $('#workspace-right-top').append(this.minBlock());
    if(this.showMax) $('#workspace-right-top').append(this.maxBlock());
    if(this.showOptional) $('#workspace-right-top').append(this.optionalBlock());
    
    $('#workspace-right-bottom').append(this.deletePageBlock());
    if(this.hasElements){
      $('#workspace-left-middle').show();
      $(this.elements).each(function(){
        this.workspace();
      });
      var pageClass = this;
      $('#workspace-right-middle').append($('<h5>').html('New Elements'));
      var ol = $('<ol>').addClass('add-list');
      $(this.pageStore.elementTypes).each(function(id,name){
        var li = $('<li>').html(name);
        $(li).bind('click', {pageClass: this},function(e){
          pageClass.pageStore.addElement(pageClass.id, id);
        });
        ol.append(li);
      });
      $('#workspace-right-middle').append(ol);
    } else {$('#workspace-left-middle').hide();}
    
    $('#workspace-left-middle-left div.field:first').trigger('click');
  }
}

/**
 * The StandardPage class
 */
function StandardPage(){}
StandardPage.prototype = new ApplyPage();
StandardPage.prototype.constructor = StandardPage;

/**
 * The TextPage class
 */
function TextPage(){
  this.showInstructions = false;
  this.showMin = false;
  this.showMax = false;
  this.showRequired = false;
  this.hasElements = false;
}
TextPage.prototype = new ApplyPage();
TextPage.prototype.constructor = TextPage;

/**
 * The LockPage class
 */
function LockPage(){
  this.showMin = false;
  this.showMax = false;
  this.showRequired = false;
  this.hasElements = false;
}
LockPage.prototype = new ApplyPage();
LockPage.prototype.constructor = LockPage;

/**
 * The ETSMatchPage class
 */
function ETSMatchPage(){
  this.showMin = false;
  this.showMax = false;
  this.showRequired = false;
  this.hasElements = false;
}
ETSMatchPage.prototype = new ApplyPage();
ETSMatchPage.prototype.constructor = ETSMatchPage;