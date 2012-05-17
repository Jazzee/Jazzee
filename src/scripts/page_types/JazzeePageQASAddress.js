/**
 * The JazzeePageQASAddress type
  @extends JazzeePage
 */
function JazzeePageQASAddress(){}
JazzeePageQASAddress.prototype = new JazzeePage();
JazzeePageQASAddress.prototype.constructor = JazzeePageQASAddress;

/**
 * Create a new RecommendersPage with good default values
 * @param {String} id the id to use
 * @returns {RecommendersPage}
 */
JazzeePageQASAddress.prototype.newPage = function(id,title,typeId,typeName,typeClass,status,pageBuilder){
  var page = JazzeePage.prototype.newPage.call(this, id,title,typeId,typeName,typeClass,status,pageBuilder);
  page.setVariable('wsdlAddress', '');
  page.setVariable('validatedCountries', '');
  return page;
};

JazzeePageQASAddress.prototype.workspace = function(){
  JazzeePage.prototype.workspace.call(this);
  $('#pageToolbar').append(this.pagePropertiesButton());
};

/**
 * Create the page properties dropdown
*/
JazzeePageQASAddress.prototype.pageProperties = function(){
  var div = $('<div>');
  if(!this.isGlobal || this.pageBuilder.editGlobal) div.append(this.editQASVariablesButton());
  return div;
};

/**
 * Create the page properties dropdown
*/
JazzeePageQASAddress.prototype.editQASVariablesButton = function(){
  var pageClass = this;
  var obj = new FormObject();
  var field = obj.newField({name: 'legend', value: 'Edit QAS Server Address'});
  
  var element = field.newElement('TextInput', 'wsdlAddress');
  element.label = 'Server Address';
  element.required = true;
  element.value = this.getVariable('wsdlAddress');
  
  var element = field.newElement('TextInput', 'validatedCountries');
  element.label = 'List of Countries to Validate';
  element.format = 'USA,GBR';
  element.instructions = 'Comma Seperated list of QAS country codes';
  element.required = true;
//  element.value = this.getVariable('validatedCountries');
  
  var dialog = this.displayForm(obj);
  $('form', dialog).bind('submit',function(e){
    pageClass.setVariable('wsdlAddress', $('input[name="wsdlAddress"]', this).val());
    pageClass.setVariable('validatedCountries', $('input[name="validatedCountries"]', this).val());
    pageClass.workspace();
    dialog.dialog("destroy").remove();
    return false;
  });//end submit
  var button = $('<button>').html('Edit QAS Server Address').bind('click',function(){
    $('.qtip').qtip('api').hide();
    dialog.dialog('open');
  }).button({
    icons: {
      primary: 'ui-icon-pencil'
    }
  });
  return button;
};