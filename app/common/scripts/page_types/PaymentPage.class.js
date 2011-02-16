/**
 * The PaymentPage type
  @extends ApplyPage
 */
function PaymentPage(){}
PaymentPage.prototype = new ApplyPage();
PaymentPage.prototype.constructor = PaymentPage;

/**
 * Create a new object with good default page values
 * @param {String} id the id to use
 * @returns {PaymentPage}
 */
PaymentPage.prototype.newPage = function(id,title,pageType,pageClass,status,pageStore){
  var page = ApplyPage.prototype.newPage.call(this, id,title,pageType,pageClass,status,pageStore);
  page.setVariable('amounts', 0);
  return page;
};

/**
 * Create the PaymentPage workspace
 */
PaymentPage.prototype.workspace = function(){
  this.clearWorkspace();
  $('#workspace-left-top').parent().addClass('form');
  $('#workspace-left-top').append(this.titleBlock());
  
  $('#workspace-left-middle-left').append(this.paymentAmountsBlock());
  
  $('#workspace-right-top').append(this.copyPageBlock());
  $('#workspace-right-top').append(this.previewPageBlock());
  $('#workspace-right-top').append(this.selectListBlock('showAnswerStatus', 'Answer Status is', {0:'Not Shown',1:'Shown'}));
  
  $('#workspace-right-bottom').append(this.deletePageBlock());
  $('#workspace').show('slide');
};

PaymentPage.prototype.paymentAmountsBlock = function(){
  var pageClass = this;
  var div = $('<div>').append($('<h5>').html('Payment Amounts and Descriptions'));
  var amounts = this.getVariable('amounts');
  var table = $('<table>').attr('id','paymentPageAmounts').append('<tr><th>Amount</th><th>Description</th></tr>');
  div.append(table);
  if(amounts > 0){
    for(var i=1;i<=amounts;i++){
      var amount = this.getVariable('amount'+i);
      var description = this.getVariable('description'+i);
      table.append(this.amountRow(amount, description));
    }
    this.trackPaymentUpdates(table);
  }
  var p = $('<p>').addClass('add').html('New payment amount').bind('click', function(e){
    table.append(pageClass.amountRow('0', 'blank'));
    pageClass.trackPaymentUpdates(table);
  });
  
  div.append(p);
  return div;
};

PaymentPage.prototype.amountRow = function(amount, description){
  var tr = $('<tr class="amount">');
  tr.append($('<td>').append($('<input type="text" class="paymentAmount">').val(amount)));
  tr.append($('<td>').append($('<input type="text" class="paymentDescription">').val(description)));
  return tr;
};

PaymentPage.prototype.trackPaymentUpdates = function(table){
  var pageClass = this;
  $('input', table).unbind('change');
  $('input', table).bind('change', function(e){
    var count;
    $('tr.amount', table).each(function(i){
      pageClass.setVariable('amount'+(i+1), $('.paymentAmount', this).eq(0).val());
      pageClass.setVariable('description'+(i+1), $('.paymentDescription', this).eq(0).val());
      count = i+1;
    });
    pageClass.setVariable('amounts', count);
  });
};