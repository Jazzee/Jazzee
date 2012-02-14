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
  div.append(this.editListItemButton());
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
      elementClass.newListItem(values[i]);
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
    var activeList = $('<ul>').addClass('active connectedSortable').addClass('container');
    var inactiveList = $('<ul>').addClass('inactive connectedSortable').addClass('container');
    for(var i = 0; i< elementClass.listItems.length; i++){
      var item = elementClass.listItems[i];
      if(item.isActive){
        activeList.append($('<li>').html(item.value).data('item', item).addClass('ui-state-default'));
      } else {
        inactiveList.append($('<li>').html(item.value).data('item', item).addClass('ui-state-default'));
      }
    }
    var activeDiv = $('<div>').html('<h5>Active Items</h5>').append(activeList).addClass('yui-u first');
    div.append(activeDiv);
    div.append($('<div>').html('<h5>Inactive Items</h5>').append(inactiveList).addClass('yui-u'));
    
    $('ul',div).sortable({connectWith: '.connectedSortable'});
    
    var text = $('<a>').attr('href','#').html(' (sort desc) ').bind('click',function(){
      $('li',activeList).sort(function(a,b){  
        return a.innerHTML.toUpperCase() < b.innerHTML.toUpperCase() ? 1 : -1;  
      }).appendTo(activeList);
      return false;
    });
    $('h5',activeDiv).append(text);
    
    var text = $('<a>').attr('href','#').html(' (sort asc) ').bind('click',function(){
      $('li',activeList).sort(function(a,b){  
        return a.innerHTML.toUpperCase() > b.innerHTML.toUpperCase() ? 1 : -1;  
      }).appendTo(activeList);
      return false;
    });
    $('h5',activeDiv).append(text);


    var button = $('<button>').html('Save').bind('click',function(){
      var orderedItems = [];
      $('ul.active li', div).each(function(i){
        var item = $(this).data('item');
        item.weight = i+1;
        item.isActive = true;
        orderedItems.push(item);
      });

      $('ul.inactive li', div).each(function(i){
        var item = $(this).data('item');
        item.weight = i+100;
        item.isActive = false;
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
 * @return {jQuery}
 */
List.prototype.editListItemButton = function(){
  var elementClass = this;
  var ul = $('<ul>');
  for(var i = 0; i < this.listItems.length; i++){
    var item = this.listItems[i];
    if(item.isActive){
      var a = $('<a>').attr('href','#').html(item.value).data('item', item).bind('click', function(e){
        $('.qtip').qtip('api').hide();
        var item = $(e.target).data('item');
        var obj = new FormObject();
        var field = obj.newField({name: 'legend', value: 'Edit Item'});
        var element = field.newElement('TextInput', 'value');
        element.label = 'Item Value';
        element.required = true;
        element.value = item.value;
        var dialog = elementClass.page.displayForm(obj);
        $('form', dialog).bind('submit',function(e){
          item.value = $('input[name="value"]', this).val();
          elementClass.workspace();
          dialog.dialog("destroy").remove();
          elementClass.markModified();
          return false;
        });//end submit
        dialog.dialog('open');
        return false;
      });
      ul.append($('<li>').append(a));
    }
  }
  var button = $('<button>').html('Edit List Item');
  button.button({
    icons: {
      primary: 'ui-icon-pencil',
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
      text: ul,
      title: {
        text: 'Choose an item to edit',
        button: true
      }
    }
  });
  return button;
};

/**
 * Add a new New item for the list
 * @param {String} value the items text
 */
List.prototype.newListItem = function(value){
  var itemId = 'new-list-item' + this.page.pageBuilder.getUniqueId();
  var item = {id: itemId, value: value, isActive: true, weight: this.listItems.length+1};
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

