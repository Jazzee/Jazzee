/**
 * The ApplyPage class
 * Standardizes client side handling of a page doesn't communicate with server
 * @return
 */

/**
 * These events are published from this class:
 * updatedPageList
 */

function ApplyPage(){
  this.pageStore;
  this.pageId;
  this.applicationPageId;
  this.pageType,
  this.title;
  this.min;
  this.max;
  this.optional;
  this.instructions;
  this.leadingText;
  this.trailingText;
  this.weight;
  this.type;
  this.variables;
  this.elements;
  this.elementsOrder;
  this.children;
  this.childrenOrder;
  
  this.isModified = false;
  this.showLeadingText = true;
  this.showTrailingText = true;
  this.showInstructions = true;
  this.showMin = true;
  this.showMax = true;
  this.showOptional = true;
  this.hasElements = true;
  
  this.init = function(obj, pageStore){
    this.pageStore = pageStore;
    this.pageId = obj.pageId;
    this.applicationPageId = obj.applicationPageId;
    this.title = obj.title;
    this.min = obj.min;
    this.max = obj.max;
    this.optional = obj.optional;
    this.instructions = obj.instructions;
    this.leadingText = obj.leadingText;
    this.trailingText = obj.trailingText;
    this.pageType = obj.pageType;
    this.weight = obj.weight;
    
    this.elements = {};
    this.elementsOrder = [];
    this.variables = [];
    this.children = {};
    this.childrenOrder = [];
  }
  
  this.checkModified = function(){
    if(this.isModified) return true;
    for(var i =0; i < this.childrenOrder.length; i++){
      if(this.childrenOrder[i] in this.children  && this.children[this.childrenOrder[i]].isModified) return true;
    }
    return false;
  }
  
  this.addElement = function(obj){
    this.elements[obj.id] = obj;
    this.elementsOrder.push(obj.id);
  }
  
  this.deleteElement = function(elementId){
    delete this.elements[elementId];
    for(var i =0; i < this.elementsOrder.length; i++){
      if(this.elementsOrder[i] == elementId) {
        this.elementsOrder.splice(i, 1);
        break;
      }
    }
    this.isModified = true;
    this.elementsWorkspace();
  }
  
  this.addChild = function(obj){
    this.children[obj.pageId] = obj;
    this.childrenOrder.push(obj.pageId);
  }
  
  this.deleteChild = function(childId){
    delete this.children[childId];
    for(var i =0; i < this.childrenOrder.length; i++){
      if(this.childrenOrder[i] == childId) {
        this.childrenOrder.splice(i, 1);
        break;
      }
    }
  }
  
  this.setVariable = function(name, value){
    this.variables[name] = value;
  }
  
  this.setProperty = function(property, value){
    this[property] = value;
    this.isModified = true;
  }
  
  this.deletePageBlock = function(){
    var page = this;
    var p = $('<p>Delete this page</p>').addClass('delete').bind('click', function(e){
      $('#workspace').effect('explode',500);
      page.pageStore.deletePage(page);
    });
    return p;
  }
  
  this.previewPageBlock = function(){
//    var p = $('<p>Preview the page</p>').addClass('preview').bind('click', {pageClass: this}, function(e){
//      var preview = e.data.pageClass.pageStore.getPagePreview(e.data.pageClass);
//      $('form', preview).bind('submit', function(){return false;});
//      $('fieldset.buttons ', preview).remove();
//      $(preview).dialog({ width: 800 });
//    });
//    return p;
    console.log('preview this page');
  }
  
  this.titleBlock = function(){
    var pageClass = this;
    var field = $('<input type="text">').attr('value',this.title)
      .bind('change',function(){
        pageClass.setProperty('title', $(this).val());
        $(document).trigger("updatedPageList");
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
  
  this.getDataObject = function(){
    var obj = {
        pageId: this.pageId,
        applicationPageId: this.applicationPageId,
        title: this.title,
        min: this.min,
        max: this.max,
        optional: this.optional,
        instructions: this.instructions,
        leadingText: this.leadingText,
        trailingText: this.trailingText,
        weight: this.weight,
        pageType: this.pageType,
        elements: [],
        children: []
    };
    for(var i =0; i < this.elementsOrder.length; i++){
      if(this.elementsOrder[i] in this.elements) obj.elements.push(this.elements[this.elementsOrder[i]].getDataObject());
    }
    for(var i =0; i < this.childrenOrder.length; i++){
      if(this.childrenOrder[i] in this.children) obj.children.push(this.children[this.childrenOrder[i]].getDataObject());
    }
    return obj;
  }
  
  this.clearWorkspace = function(){
    $('#workspace-left-top').empty();
    $('#workspace-left-middle-left').empty();
    $('#workspace-left-middle-right').empty();
    $('#workspace-left-bottom-left').empty();
    $('#workspace-left-bottom-right').empty();
    

    $('#workspace-right-top').empty();
    $('#workspace-right-middle').empty();
    $('#workspace-right-bottom').empty();
  }
  
  this.workspace = function(){
    this.clearWorkspace();
    $('#workspace-left-top').parent().addClass('form');
    $('#workspace-left-top').append(this.titleBlock());
    if(this.showLeadingText) $('#workspace-left-top').append(this.leadingTextBlock());
    if(this.showInstructions) $('#workspace-left-top').append(this.instructionsBlock());
    if(this.showTrailingText) $('#workspace-left-bottom-left').append(this.trailingTextBlock());
    
    $('#workspace-right-top').append(this.previewPageBlock());
    if(this.showMin) $('#workspace-right-top').append(this.minBlock());
    if(this.showMax) $('#workspace-right-top').append(this.maxBlock());
    if(this.showOptional) $('#workspace-right-top').append(this.optionalBlock());
    
    $('#workspace-right-bottom').append(this.deletePageBlock());
    if(this.hasElements){
      this.elementsWorkspace();
      var pageClass = this;
      $('#workspace-right-middle').append($('<h5>').html('New Elements'));
      var ol = $('<ol>').addClass('add-list');
      $(this.pageStore.getElementTypesList()).each(function(i){
        var element = this;
        var li = $('<li>').html(element.name);
        $(li).bind('click',function(e){
          pageClass.pageStore.newElement(pageClass, element);
          pageClass.isModified = true;
          pageClass.elementsWorkspace();
        });
        ol.append(li);
      });
      $('#workspace-right-middle').append(ol);
    }
  }
  
  this.elementsWorkspace = function(){
    $('#workspace-left-middle').show();
    $('#workspace-left-middle-left').empty();
    $('#workspace-left-middle-right').empty();
    for(var i =0; i < this.elementsOrder.length; i++){
      if(this.elementsOrder[i] in this.elements) this.elements[this.elementsOrder[i]].workspace();
    }
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
 * The BranchingPage class
 */
function BranchingPage(){
  this.workspace = function(){
    this.clearWorkspace();
    $('#workspace-left-top').parent().addClass('form');
    $('#workspace-left-top').append(this.titleBlock());
    if(this.showLeadingText) $('#workspace-left-top').append(this.leadingTextBlock());
    if(this.showInstructions) $('#workspace-left-top').append(this.instructionsBlock());
    if(this.showTrailingText) $('#workspace-left-bottom-left').append(this.trailingTextBlock());
    
    $('#workspace-right-top').append(this.previewPageBlock());
    $('#workspace-right-top').append(this.minBlock());
    $('#workspace-right-top').append(this.maxBlock());
    $('#workspace-right-top').append(this.optionalBlock());
    
    $('#workspace-right-bottom').append(this.deletePageBlock());
    $('#workspace-left-middle-left').append(this.listBranchingPagesBlock());
  }
  
  this.listBranchingPagesBlock = function(){
    var pageClass = this;
    var ol = $('<ol>').addClass('page-list');
    for(var i =0; i < this.childrenOrder.length; i++){
      if(this.childrenOrder[i] in this.children) {
        var branch = this.children[this.childrenOrder[i]];
        var li = $('<li>').html(branch.title);
        $(li).bind('click',{branch: branch},function(e){
          e.data.branch.workspace();
          //get rid of the delete pages box and add a delete branch box
          var deletep = $('<p>Delete this branch</p>').addClass('delete').bind('click',{branch: e.data.branch}, function(e){
            $('#workspace').effect('explode',500);
            pageClass.deleteChild(e.data.branch.pageId);
            pageClass.workspace();
            $('#workspace').show('slide');
          });
          $('#workspace-right-bottom').empty().append(deletep);
        });
        ol.append(li);
      }
    }
    var p = $('<p>').addClass('add').html('New Branch').bind('click',function(){
      var standardPage = pageClass.pageStore.getPageTypeByClassName('StandardPage');
      pageClass.pageStore.newBranchingPage(standardPage.id, pageClass);
    });
    return $('<div>').append($('<h5>').html('Branched Pages')).append(ol).append(p);
  }
}
BranchingPage.prototype = new ApplyPage();
BranchingPage.prototype.constructor = BranchingPage;

/**
 * The TextPage class
 */
function TextPage(){
  this.showInstructions = false;
  this.showMin = false;
  this.showMax = false;
  this.showOptional = false;
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
  this.showOptional = false;
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
  this.showOptional = false;
  this.hasElements = false;
}
ETSMatchPage.prototype = new ApplyPage();
ETSMatchPage.prototype.constructor = ETSMatchPage;