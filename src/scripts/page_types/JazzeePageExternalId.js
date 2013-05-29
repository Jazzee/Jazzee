/**
 * The JazzeePageExternalId type
  @extends JazzeePage
 */
function JazzeePageExternalId(){}
JazzeePageExternalId.prototype = new JazzeePage();
JazzeePageExternalId.prototype.constructor = JazzeePageExternalId;

/**
 * Create a new ExternalIdPage with good default values
 * @param {String} id the id to use
 * @returns {ExternalIdPage}
 */
JazzeePageExternalId.prototype.newPage = function(id,title,typeId,typeName,typeClass,status,pageBuilder){
  var page = JazzeePage.prototype.newPage.call(this, id,title,typeId,typeName,typeClass,status,pageBuilder);
  page.setVariable('externalIdLabel', title);
  return page;
};

JazzeePageExternalId.prototype.workspace = function(){
  JazzeePage.prototype.workspace.call(this);
  $('#pageToolbar').append(this.pagePropertiesButton());
};

/**
 * Create the page properties dropdown
*/
JazzeePageExternalId.prototype.pageProperties = function(){
  var div = $('<div>');
  div.append(this.isRequiredButton());
  div.append(this.editNameButton());
  if(!this.isGlobal || this.pageBuilder.editGlobal) div.append(this.editElementLabelButton());

  return div;
};

/**
 * Edit the label button
 * @return {jQuery}
 */
JazzeePageExternalId.prototype.editElementLabelButton = function(){
  var pageClass = this;
  var obj = new FormObject();
  var field = obj.newField({name: 'legend', value: 'Edit External ID Label'});
  var element = field.newElement('TextInput', 'externalIdLabel');
  element.label = 'Label';
  element.required = true;
  element.value = this.getVariable('externalIdLabel');
  var dialog = this.displayForm(obj);
  $('form', dialog).bind('submit',function(e){
    pageClass.setVariable('externalIdLabel', $('input[name="externalIdLabel"]', this).val());
    pageClass.workspace();
    dialog.dialog("destroy").remove();
    return false;
  });//end submit
  var button = $('<button>').html('Edit Element Label').bind('click',function(){
    $('.qtip').qtip('api').hide();
    dialog.dialog('open');
  }).button({
    icons: {
      primary: 'ui-icon-pencil'
    }
  });
  return button;
};

