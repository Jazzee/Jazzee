/**
 * The ListElement is an abstract for other list elements
  @extends ApplyElement
 */
function ListElement(){}
ListElement.prototype = new ApplyElement();
ListElement.prototype.constructor = ListElement;

/**
 * Add a list manager to the options block
 */
ListElement.prototype.optionsBlock = function(){
  var element = this;
  var optionsBlockDiv = ApplyElement.prototype.optionsBlock.call(this);
  
  var div = $('<div>').addClass('listItems container').append($('<h5>').html('List Items'));
  var ol = $('<ol>');
  for(var i = 0; i<this.listItems.length; i++){
    ol.append(this.itemBlock(this.listItems[i]));
  }
  ol.sortable();
  ol.bind("sortupdate", function(event, ui) {
    $('li', this).each(function(i){
      $(this).data('item').weight = i;
    });
  });
  div.append(ol);
  var form = $('<form>').bind('submit', function(){
    var value = $(this).children('input').eq(0).val();
    if(value != ''){
      var item = element.newListItem(value);
      $(this).children('input').eq(0).val('');
      ol.append(element.itemBlock(item));
      ol.trigger('sortupdate');
    }
    return false;
  }).append($('<input>').attr('type','text')).append($('<input>').attr('type', 'button').attr('name', 'submit').attr('value', 'Add'));
  var p = $('<p>').addClass('add').bind('click', function(){
    $(this).children('form').trigger('submit');
  }).append(form);
  div.append(p);
  optionsBlockDiv.append(div);
  return optionsBlockDiv;
};

/**
 * A single list item
 * @param {Object} item the item
 * @returns {jQuery}
 */
ListElement.prototype.itemBlock = function(item){
  var element = this;
  var li = $('<li>').addClass((item.active)?'active':'inactive').data('item',item).html(item.value).bind('click', function(){
  $(this).unbind('click');
    var field = $('<input>').attr('type', 'text').attr('value',item.value)
    .bind('change', function(){
      item.value = $(this).val();
    }).bind('blur', function(){
      $(this).parent().replaceWith(element.itemBlock(item));
    });
    $(this).empty().append(field);
    $(field).trigger('focus');
  });
//  var span = $('<span>').html('&nbsp;').addClass('deactivate').bind('click', function(e){
//    element.toggleItemActive(item);
//    $(this).parent().removeClass('active').removeClass('inactive').addClass((item.active)?'active':'inactive');
//  });
//  li.prepend(span);
  return li;
};

/**
 * Add a new New item for the list
 * @param {String} value the items text
 */
ListElement.prototype.newListItem = function(value){
  var itemId = 'new-list-item' + this.page.pageStore.getUniqueId();
  var item = {id: itemId, value: value, active: true};
  this.listItems.push(item);
  this.isModified = true;
  return item;
};


/**
 * Add a new item to the list
 * @param {String} value the items text
 */
ListElement.prototype.addListItem = function(item){
  this.listItems.push(item);
  return item;
};