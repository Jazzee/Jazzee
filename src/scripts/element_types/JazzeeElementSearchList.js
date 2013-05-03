/**
 * The JazzeeElementSelectList type
  @extends List
 */
function JazzeeElementSearchList(){}
JazzeeElementSearchList.prototype = new List();
JazzeeElementSearchList.prototype.constructor = JazzeeElementSearchList;

JazzeeElementSearchList.prototype.avatar = function(){
  return $('<input type="text" disabled="true">');
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
    var element = field.newElement('Textarea', 'metadata');
    element.label = 'Search Data';
    element.required = false;
    element.format = 'One entry per ling';
    element.instructions = 'Search data items will be queried when, but will not display.'
    element.value = item.metadata;
    var dialog = elementClass.page.displayForm(obj);
    elementClass.page.pageBuilder.addNameTest($('input[name="name"]', dialog));
    $('form', dialog).bind('submit',function(e){
      item.value = $('input[name="value"]', this).val();
      item.name = $('input[name="name"]', this).val();
      item.metadata = $('textarea[name="metadata"]', this).val();
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