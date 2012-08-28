/**
 * The List is an abstract for other list elements
  @extends JazzeeElement
 */
function List(){}
List.prototype = new JazzeeElement();
List.prototype.constructor = List;

/**
 * Add a list manager to the options block
 */
List.prototype.elementProperties = function(){
  var element = this;
  var div = JazzeeElement.prototype.elementProperties.call(this);
  div.append(this.newListItemsButton());
  div.append(this.manageListItemsButton());

  return div;
};

/**
 * Add new list items button
 * @return {jQuery}
 */
List.prototype.newListItemsButton = function(){
  var elementClass = this;
  var obj = new FormObject();
  var field = obj.newField({name: 'legend', value: 'New List Items'});
  var element = field.newElement('Textarea', 'text');
  element.label = 'New Items';
  element.instructions = 'One new item per line';
  var dialog = this.page.displayForm(obj);
  $('form', dialog).bind('submit',function(e){
    var values = $('textarea[name="text"]', this).val().split("\n");
    for(var i = 0;i<values.length; i++){
      if($.trim(values[i]).length > 0){
        elementClass.newListItem(values[i]);
      }
    }
    dialog.dialog("destroy").remove();
    elementClass.workspace();
    return false;
  });//end submit
  var button = $('<button>').html('Add List Items').bind('click',function(){
    $('.qtip').qtip('api').hide();
    dialog.dialog('open');
  }).button({
    icons: {
      primary: 'ui-icon-plus'
    }
  });
  return button;
};

/**
 * Manage List Items button
 * @return {jQuery}
 */
List.prototype.manageListItemsButton = function(){
  var elementClass = this;

  var button = $('<button>').html('Manage Items').bind('click',function(){
    $('.qtip').qtip('api').hide();
    var div = elementClass.page.createDialog();
    var list = $('<ul>').addClass('elementListItems');
    for(var i = 0; i< elementClass.listItems.length; i++){
      var item = elementClass.listItems[i];
      list.append(elementClass.singleItem(item));
    }
    var listDiv = $('<div>').html('<h5>List Items</h5>').append(list).addClass('yui-u first');
    div.append(listDiv);
    $('ul',listDiv).sortable({handle: '.handle'});

    $('h5', listDiv).after(elementClass.filterItemsInput(list));

    var text = $('<a>').attr('href','#').html(' (sort desc) ').bind('click',function(){
      $('li',list).sort(function(a,b){
        return a.innerHTML.toUpperCase() < b.innerHTML.toUpperCase() ? 1 : -1;
      }).appendTo(list);
      return false;
    });
    $('h5',listDiv).append(text);

    var text = $('<a>').attr('href','#').html(' (sort asc) ').bind('click',function(){
      $('li',list).sort(function(a,b){
        return a.innerHTML.toUpperCase() > b.innerHTML.toUpperCase() ? 1 : -1;
      }).appendTo(list);
      return false;
    });
    $('h5',listDiv).append(text);

    var button = $('<button>').html('Save').bind('click',function(){
      var orderedItems = [];
      $('li', listDiv).each(function(i){
        var item = $(this).data('item');
        item.weight = i+1;
        orderedItems.push(item);
      });

      elementClass.markModified();
      elementClass.listItems = orderedItems;
      div.dialog("destroy").remove();
      elementClass.workspace();
      return false;
    }).button({
      icons: {
        primary: 'ui-icon-disk'
      }
    });
    div.append(button);

    div.dialog('open');
  }).button({
    icons: {
      primary: 'ui-icon-arrow-1-nw'
    }
  });
  return button;
};

/**
 * Edit List Item button
 * @param obj item
 * @return {jQuery}
 */
List.prototype.singleItem = function(item){
  var elementClass = this;
  var value = ($.trim(item.value).length > 0)?item.value:'[blank]';
  var name = ($.trim(item.name).length > 0)?' (' + item.name + ')':'';
  var li = $('<li>').html(value+name).data('item', item).addClass('ui-state-default');
  var handle = $('<span>').addClass('handle ui-icon ui-icon-arrowthick-2-n-s');
  li.prepend(handle);
  var tools = $('<span>').addClass('tools');
  if(item.isActive){
    tools.append(this.hideListItemButton());
  } else {
    li.addClass('inactive');
    tools.append(this.displayListItemButton());
  }
  tools.append(this.editListItemButton());
  tools.append(this.deleteListItemButton());
  li.append(tools)

  return li;
};

/**
 * Edit List Item button
 * @return {jQuery}
 */
List.prototype.editListItemButton = function(){
  var elementClass = this;
  var button = $('<button>').html('Edit').bind('click',function(){
    var li = $(this).parent().parent();
    var item = li.data('item');
    var obj = new FormObject();
    var field = obj.newField({name: 'legend', value: 'Edit Item'});
    var element = field.newElement('TextInput', 'value');
    element.label = 'Item Value';
    element.required = true;
    element.value = item.value;
    var element = field.newElement('TextInput', 'name');
    element.label = 'Item Name';
    element.required = false;
    element.format = 'Only letters, numbers and underscore are allowed.';
    element.value = item.name;
    var dialog = elementClass.page.displayForm(obj);
    elementClass.page.pageBuilder.addNameTest($('input[name="name"]', dialog));
    $('form', dialog).bind('submit',function(e){
      item.value = $('input[name="value"]', this).val();
      item.name = $('input[name="name"]', this).val();
      elementClass.workspace();
      dialog.dialog("destroy").remove();
      elementClass.markModified();
      li.replaceWith(elementClass.singleItem(item));
      return false;
    });//end submit
    dialog.dialog('open');
    return false;
  }).button({icons: {primary: 'ui-icon-pencil'}});
  return button;
};

/**
 * Active List Item button
 * @return {jQuery}
 */
List.prototype.displayListItemButton = function(){
  var elementClass = this;
  var button = $('<button>').html('Display').bind('click',function(){
    var li = $(this).parent().parent();
    var item = li.data('item');
    item.isActive = true;
    elementClass.markModified();
    li.replaceWith(elementClass.singleItem(item));
    return false;
  }).button({icons: {primary: 'ui-icon-plus'}});
  return button;
};

/**
 * Active List Item button
 * @return {jQuery}
 */
List.prototype.hideListItemButton = function(){
  var elementClass = this;
  var button = $('<button>').html('Hide').bind('click',function(){
    var li = $(this).parent().parent();
    var item = li.data('item');
    item.isActive = false;
    elementClass.markModified();
    li.replaceWith(elementClass.singleItem(item));
    return false;
  }).button({icons: {primary: 'ui-icon-cancel'}});
  return button;
};

/**
 * Delete List Item button
 * @return {jQuery}
 */
List.prototype.deleteListItemButton = function(){
  var elementClass = this;
  var button = $('<button>').html('Delete').button({icons: {primary: 'ui-icon-trash'}});
  if(this.page.hasAnswers){
    button.addClass('ui-button-disabled ui-state-disabled');
    button.attr('title', 'This item cannot be deleted because there is applicant information associated with it.');
    button.qtip();
  } else {
    button.bind('click', function(e){
      var li = $(this).parent().parent();
      var item = li.data('item');
      item.isActive = false;
      item.status = 'delete';
      elementClass.markModified();
      li.hide('explode');
      return false;
    });
  }
  return button;
};

/**
 * Filter list items input
 * @return {jQuery}
 */
List.prototype.filterItemsInput = function(list){
    jQuery.expr[':'].Contains = function(a,i,m){
        return (a.textContent || a.innerText || "").toUpperCase().indexOf(m[3].toUpperCase())>=0;
    };
    var input = $('<input>').attr('type', 'text').bind('change keyup', function(){
      var filter = $(this).val();
      if (filter) {
        $(list).find("li:not(:Contains(" + filter + "))").slideUp();
        $(list).find("li:Contains(" + filter + ")").slideDown();
      } else {
        $(list).find("li").slideDown();
      }
    });

    var defaultValue = 'filter input';
    input.val(defaultValue);
    input.css('color', '#bbb');
    input.focus(function() {
        var actualValue = input.val();
        input.css('color', '#000');
        if (actualValue == defaultValue) {
            input.val('');
        }
    });
    input.blur(function() {
        var actualValue = input.val();
        if (!actualValue) {
            input.val(defaultValue);
            input.css('color', '#bbb');
        }
    });

    return $('<form>').attr('action', '#').append(input);
};

/**
 * Add a new New item for the list
 * @param {String} value the items text
 */
List.prototype.newListItem = function(value){
  var itemId = 'new-list-item' + this.page.pageBuilder.getUniqueId();
  var item = {id: itemId, status: 'new', value: value, name: null, isActive: true, weight: this.listItems.length+1};
  this.addListItem(item);
  this.markModified();
  return item;
};

/**
 * Add a new item to the list
 * @param {String} value the items text
 */
List.prototype.addListItem = function(item){
  this.listItems.push(item);
  return item;
};