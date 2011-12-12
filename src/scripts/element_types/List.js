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
List.prototype.optionsBlock = function(){
  var element = this;
  var optionsBlockDiv = JazzeeElement.prototype.optionsBlock.call(this);
  
  var div = $('<div>').addClass('listItems container').append($('<h5>').html('List Items'));
  var sort = $('<a href="#">').html('Sort').bind('click', function(){
    var ol = $('ol', $(this).parent());
    $('li',ol).sort(function(a,b){  
      return a.innerHTML.toUpperCase() > b.innerHTML.toUpperCase() ? 1 : -1;  
    }).appendTo(ol);
    ol.trigger('sortupdate');
    return false;
  });
  
  div.append(sort);
  var ol = $('<ol>');
  for(var i = 0; i<this.listItems.length; i++){
    ol.append(this.itemBlock(this.listItems[i]));
  }
  ol.sortable();
  ol.bind("sortupdate", function(event, ui) {
    $('li', this).each(function(i){
      $(this).data('item').weight = i+1;
    });
    element.isModified = true;
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
List.prototype.itemBlock = function(item){
  var element = this;
  var toggleSpan = $('<span>').addClass('listItemToggle');
  toggleSpan.bind('click', function(){
    item.isActive = !item.isActive;
    element.isModified = true;
    $(this).parent().replaceWith(element.itemBlock(item));
  });
  
  var valueSpan = $('<span>').addClass('listItemValue').html(item.value);
  valueSpan.bind('click', function(){
    $(this).unbind('click');
    var field = $('<input>').attr('type', 'text').attr('value',item.value)
      .bind('change', function(){
        item.value = $(this).val();
      }).bind('blur', function(){
        element.isModified = true;
        $(this).parent().parent().replaceWith(element.itemBlock(item));
      });
    $(this).empty().append(field);
    $(field).trigger('focus');
  });
  var li = $('<li>').addClass((item.isActive)?'active':'inactive').data('item',item);
  li.append(toggleSpan);
  li.append(valueSpan);
  return li;
};

/**
 * Add a new New item for the list
 * @param {String} value the items text
 */
List.prototype.newListItem = function(value){
  var itemId = 'new-list-item' + this.page.pageStore.getUniqueId();
  var item = {id: itemId, value: value, isActive: true, weight: this.listItems.length+1};
  this.listItems.push(item);
  this.isModified = true;
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

