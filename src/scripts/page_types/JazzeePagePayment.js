/**
 * The JazzeePagePayment type
  @extends ApplyPage
 */
function JazzeePagePayment(){}
JazzeePagePayment.prototype = new JazzeePage();
JazzeePagePayment.prototype.constructor = JazzeePagePayment;

/**
 * Create a new object with good default page values
 * @param {String} id the id to use
 * @returns {PaymentPage}
 */
JazzeePagePayment.prototype.newPage = function(id,title,typeId,typeName,typeClass,status,pageBuilder){
  var page = JazzeePage.prototype.newPage.call(this, id,title,typeId,typeName,typeClass,status,pageBuilder);
  page.setVariable('amounts', 0);
  page.setVariable('allowedPaymentTypes', '');
  return page;
};

/**
 * Create the JazzeePagePayment workspace
 */
JazzeePagePayment.prototype.workspace = function(){
  JazzeePage.prototype.workspace.call(this);
  var pageClass = this;
  $('#pageToolbar').append(this.pagePropertiesButton());
};

/**
 * Create the page properties dropdown
*/
JazzeePagePayment.prototype.pageProperties = function(){
  var pageClass = this;
  var div = $('<div>');
  div.append(this.isRequiredButton());
  div.append(this.showAnswerStatusButton());
  div.append(this.editNameButton());
  if(!this.isGlobal || this.pageBuilder.editGlobal) div.append(this.acceptedPaymentTypesButton());
  if(!this.isGlobal || this.pageBuilder.editGlobal) div.append(this.editPaymentAmountButton());
  if(!this.isGlobal || this.pageBuilder.editGlobal) div.append(this.newPaymentAmountButton());
  return div;
};

/**
 * Accepted Payment types button
 * @return {jQuery}
 */
JazzeePagePayment.prototype.acceptedPaymentTypesButton = function(){
  var pageClass = this;
  var button = $('<button>').html('Set Accepted Payment Types').bind('click', function(e){
    $('.qtip').qtip('api').hide();

    var obj = new FormObject();
    var field = obj.newField({name: 'legend', value: 'Payment Types'});
    field.instructions = 'These are the types visible to applicants.  All active types are available to administrators.';
    var element = field.newElement('CheckboxList', 'types');
    element.label = 'Accepted Payment Types';
    element.required = true;
    var allowedPaymentTypes = pageClass.getVariable('allowedPaymentTypes');
    element.value = allowedPaymentTypes == null?[]:allowedPaymentTypes.split(',');
    for(var i = 0; i < pageClass.pageBuilder.paymentTypes.length; i++){
      var paymentType = pageClass.pageBuilder.paymentTypes[i];
      element.addItem(paymentType.name, paymentType.id);
    }
    var dialog = pageClass.displayForm(obj);
    $('form', dialog).bind('submit',function(e){
      var types = [];
      $('input:checked', $(this)).each(function(i){
        types.push($(this).val());
      });
      pageClass.setVariable('allowedPaymentTypes', types.join(','));
      dialog.dialog("destroy").remove();
      return false;
    });//end submit
    dialog.dialog('open');
  }).button();

  return button;
};

/**
 * Edit Payment Aomunt button
 * @return {jQuery}
 */
JazzeePagePayment.prototype.editPaymentAmountButton = function(){
  var pageClass = this;
  var ul = $('<ul>');
  for(var i = 1; i <= this.getVariable('amounts'); i++){
    var a = $('<a>').attr('href','#').html(this.getVariable('description'+i) + ' $' + this.getVariable('amount'+i)).data('amountid', i).bind('click', function(e){
      $('.qtip').qtip('api').hide();
      var id = $(this).data('amountid');
      var obj = new FormObject();
      var field = obj.newField({name: 'legend', value: 'Edit Amount'});
      var element = field.newElement('TextInput', 'description');
      element.label = 'Description';
      element.required = true;
      element.value = pageClass.getVariable('description'+id);
      var element = field.newElement('TextInput', 'amount');
      element.label = 'Amount';
      element.required = true;
      element.value = pageClass.getVariable('amount'+id);
      var dialog = pageClass.displayForm(obj);
      $('form', dialog).bind('submit',function(e){
        pageClass.setVariable('description'+id,$('input[name="description"]', this).val());
        pageClass.setVariable('amount'+id,$('input[name="amount"]', this).val());
        dialog.dialog("destroy").remove();
        return false;
      });//end submit
      dialog.dialog('open');
      return false;
    });
    ul.append($('<li>').append(a));

  }
  var button = $('<button>').html('Edit Payment Amount');
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
 * Add new payment amount button
 * @return {jQuery}
 */
JazzeePagePayment.prototype.newPaymentAmountButton = function(){
  var pageClass = this;
  var obj = new FormObject();
  var field = obj.newField({name: 'legend', value: 'New Payment Amount'});
  var element = field.newElement('TextInput', 'description');
  element.label = 'Description';
  element.required = true;
  var element = field.newElement('TextInput', 'amount');
  element.label = 'Amount';
  element.required = true;
  var dialog = this.displayForm(obj);
  $('form', dialog).bind('submit',function(e){
    var id = parseInt(pageClass.getVariable('amounts'))+1;
    pageClass.setVariable('amounts', id);
    pageClass.setVariable('description'+id,$('input[name="description"]', this).val());
    pageClass.setVariable('amount'+id,$('input[name="amount"]', this).val());
    dialog.dialog("destroy").remove();
    return false;
  });//end submit
  var button = $('<button>').html('New Payment Amount').bind('click',function(){
    $('.qtip').qtip('api').hide();
    dialog.dialog('open');
  }).button({
    icons: {
      primary: 'ui-icon-plus'
    }
  });
  return button;
};