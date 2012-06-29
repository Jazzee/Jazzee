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
  var pageClass = this;
  var div = $('<div>');

  div.append(this.isRequiredButton());

  if(!this.isGlobal || this.pageBuilder.editGlobal) div.append(this.editQASVariablesButton());
  
  var slider = $('<div>');
  slider.slider({
    value: this.min,
    min: 0,
    max: 20,
    step: 1,
    slide: function( event, ui ) {
      pageClass.setProperty('min', ui.value);
      $('#minValue').html(pageClass.min == 0?'No Minimum':pageClass.min);
    }
  });
  div.append($('<p>').html('Minimum Answers Required ').append($('<span>').attr('id', 'minValue').html(this.min == 0?'No Minimum':this.min)));
  div.append(slider);

  var slider = $('<div>');
  slider.slider({
    value: this.max,
    min: 0,
    max: 20,
    step: 1,
    slide: function( event, ui ) {
      pageClass.setProperty('max', ui.value);
      $('#maxValue').html(pageClass.max == 0?'No Maximum':pageClass.max);
    }
  });
  div.append($('<p>').html('Maximum Answers Allowed ').append($('<span>').attr('id', 'maxValue').html(this.max == 0?'No Maximum':this.max)));
  div.append(slider);


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
  element.value = this.getVariable('validatedCountries');

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