/**
 * The ApplyElement class
 * Standardizes client side handling of a element doesn't communicate with server
 * @return
 */

function ApplyElement(){
  this.page;
  this.id;
  this.status;
  this.elementType,
  this.title;
  this.format;
  this.instructions;
  this.defaultValue;
  this.required;
  this.min;
  this.max;
  this.weight;
  this.list;
  this.listOrder;
  
  this.init = function(obj, page){
    this.page = page;
    this.id = obj.id;
    this.elementType = obj.elementType,
    this.title = obj.title;
    this.instructions = obj.instructions;
    this.format = obj.format;
    this.defaultValue = obj.defaultValue;
    this.required = obj.required;
    this.min = obj.min;
    this.max = obj.max;
    this.list = {};
    this.listOrder = [];
//    this.weight = obj.weight;
  }
  
  this.setProperty = function(property, value){
    this[property] = value;
    this.page.isModified = true;
  }
  
  this.addListItem = function(obj){
    this.list[obj.id] = obj;
    this.listOrder.push(obj.id);
  }
  
  this.editListItem = function(id, value){
    this.list[id].value = value;
    this.page.isModified = true;
  }
  
  this.deleteListItem = function(itemId){
    delete this.list[itemId];
    for(var i =0; i < this.listOrder.length; i++){
      if(this.listOrder[i] == itemId) {
        this.listOrder.splice(i, 1);
        break;
      }
    }
    this.page.isModified = true;
  }
  
  this.legendBlock = function(){
    var pageClass = this;
    var field = $('<input type="text">').attr('value',this.title)
      .bind('change',function(){
        pageClass.setProperty('title', $(this).val());
      })
      .bind('blur', function(){
        $(this).hide();
        $(this).parent().children('legend').eq(0).html(pageClass.title+':');
        $(this).parent().children('legend').eq(0).show();
    }).hide();
    var legend = $('<legend>').addClass('edit').html((this.title)+':').bind('click', function(){
      $(this).hide();
      $(this).parent().children('input').eq(0).show().focus();
    });
    return $('<div>').addClass('yui-u first').append(legend).append(field);
  }
  
  this.instructionsBlock = function(){
    var elementClass = this;
    var field = $('<textarea>').html(elementClass.page.valueOrBlank(elementClass.instructions))
      .bind('change',function(){
        elementClass.setProperty('instructions', $(this).val());
      })
      .bind('blur', function(){
        $(this).hide();
        $(this).parent().children('p').eq(0).html(elementClass.page.valueOrBlank(elementClass.instructions));
        $(this).parent().children('p').eq(0).show();
    }).hide();
    var p = $('<p>').addClass('edit instructions').html(this.page.valueOrBlank(this.instructions)).bind('click', function(){
      $(this).hide();
      $(this).parent().children('textarea').eq(0).show().focus();
    });
    return $('<div>').append(p).append(field);
  }
  
  this.formatBlock = function(){
    var elementClass = this;
    var field = $('<textarea>').html(elementClass.page.valueOrBlank(elementClass.format))
      .bind('change',function(){
        elementClass.setProperty('format', $(this).val());
      })
      .bind('blur', function(){
        $(this).hide();
        $(this).parent().children('p').eq(0).html(elementClass.page.valueOrBlank(elementClass.format));
        $(this).parent().children('p').eq(0).show();
    }).hide();
    var p = $('<p>').addClass('edit').html(this.page.valueOrBlank(this.format)).bind('click', function(){
      $(this).hide();
      $(this).parent().children('textarea').eq(0).show().focus();
    });
    return $('<div>').addClass('format').append(p).append(field);
  }
  
  this.requiredBlock = function(){
    var value = 'optional';
    if(this.required == 1) value = 'required';
    var p = $('<p>').addClass('edit').html('This element is ').append($('<span>').html(value).bind('click', {elementClass: this}, function(e){
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
        elementType: this.elementType,
        status: this.status,
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
    for(var i=0; i < this.listOrder.length; i++){
      if(this.listOrder[i] in this.list) obj.list.push(this.list[this.listOrder[i]]);
    }
    return obj;
  }
  
  this.workspace = function(){
    var field = $('#element-'+this.id);
    if(field.length == 0){
      $('#workspace-left-middle-left').append($('<div>').attr('id','element-'+this.id).data('element', this).addClass('field'));
      var field = $('#element-'+this.id);
    }
    field.empty();
    field.append(this.instructionsBlock());
    var element = $('<div>').addClass('element yui-gf');
    element.append(this.legendBlock());
    var control = $('<div>').addClass('yui-u control').append(this.avatar());
    if(this.showFormat) control.append(this.formatBlock());
    element.append(control);
    field.append(element);
    this.optionsBlock();
    
    $('#workspace-left-middle-left div.field').bind('click', function(){
      $('#workspace-left-middle-right div').hide();
      $('#workspace-left-middle-left div.selected').removeClass('selected');
      $('#element-'+$(this).data('element').id).addClass('selected');
      $('#element-options-'+$(this).data('element').id).show().children().show();
    });
    $('#element-'+this.id).trigger('click');
  }
  
  this.optionsBlock = function(){
    var div = $('#element-options-'+this.id);
    if(div.length == 0){
      $('#workspace-left-middle-right').append($('<div>').attr('id', 'element-options-'+this.id));
      div = $('#element-options-'+this.id);
    }
    div.empty();
    var element = this;
    var p = $('<p>Delete this element</p>').addClass('delete').bind('click', function(e){
      element.page.deleteElement(element.id);
      $('#workspace-left-middle-left div.field:first').trigger('click');
    });
    div.append(p);
    if(this.hasListItems) div.append(this.listItemsBlock());
    div.append(this.requiredBlock());
    div.hide();
  }
  
  this.editItemBlock = function(item){
    var li = $('<li>').html(item.value).bind('click', {elementClass: this, item: item}, function(e){
      $(this).unbind('click');
      var field = $('<input type="text">').attr('value',e.data.item.value);
      field.bind('change', {elementClass: e.data.elementClass, item: e.data.item}, function(e){
        e.data.elementClass.editListItem(e.data.item.id,$(this).val());
        e.data.elementClass.workspace();
      }).bind('blur', {elementClass: e.data.elementClass, item: e.data.item}, function(e){
        $(this).parent().replaceWith(e.data.elementClass.editItemBlock(e.data.item));
      });
      $(this).empty().append(field);
      $(field).trigger('focus');
    });
    return li;
  }
  
  this.listItemsBlock = function(){
    var div = $('<div>').addClass('listItem container').append($('<h5>').html('List Items'));
    var ol = $('<ol>');
    for(var i =0; i < this.listOrder.length; i++){
      if(this.listOrder[i] in this.list) ol.append(this.editItemBlock(this.list[this.listOrder[i]]));
    }
    div.append(ol);
    var form = $('<form>').bind('submit', {elementClass: this}, function(e){
      var value = $(this).children('input').eq(0).val();
      if(value != ''){
        e.data.elementClass.page.pageStore.newListItem(e.data.elementClass.page, e.data.elementClass,value);
        $(this).children('input').eq(0).val('');
        e.data.elementClass.workspace();
      }
      return false;
    }).append($('<input type="text">')).append($('<input type="button" name="submit" value="Add">'));
    var p = $('<p>').addClass('add').bind('click', function(){
      $(this).children('form').trigger('submit');
    }).append(form);
    div.append(p);
    return div;
  }
  
}

/**
 * The TextInputElement class
 */
function TextInputElement(){
  this.avatar = function(){
    return $('<input type="text" disabled="true">');
  };
}
TextInputElement.prototype = new ApplyElement();
TextInputElement.prototype.constructor = TextInputElement;

/**
 * The TextareaElement class
 */
function TextareaElement(){
  this.avatar = function(){
    return $('<textarea>');
  };
}
TextareaElement.prototype = new ApplyElement();
TextareaElement.prototype.constructor = TextareaElement;

/**
 * The PDFFileInputElement class
 */
function PDFFileInputElement(){
  this.avatar = function(){
    return $('<input type="file" disabled="true">');
  };
}
PDFFileInputElement.prototype = new ApplyElement();
PDFFileInputElement.prototype.constructor = PDFFileInputElement;

/**
 * The RadioListElement class
 */
function RadioListElement(){
  this.showFormat = false;
  this.avatar = function(){
    return $('<input type="radio" disabled="true">');
  };
}
RadioListElement.prototype = new ApplyElement();
RadioListElement.prototype.constructor = RadioListElement;
/**
 * The SelectListElement class
 */
function SelectListElement(){
  this.showFormat = false;
  this.hasListItems = true;
  this.avatar = function(){
    var select = $('<select>');
    for(var i =0; i < this.listOrder.length; i++){
      if(this.listOrder[i] in this.list) select.append($('<option>').html(this.list[this.listOrder[i]].value));
    }
    return select;
  };
}
SelectListElement.prototype = new ApplyElement();
SelectListElement.prototype.constructor = SelectListElement;

/**
 * The CheckboxListElement class
 */
function CheckboxListElement(){
  this.showFormat = false;
  this.avatar = function(){
    return $('<input type="checkbox" disabled="true">');
  };
}
CheckboxListElement.prototype = new ApplyElement();
CheckboxListElement.prototype.constructor = CheckboxListElement;