/**
 * Javascript for the setup_pages controller
 */
$(document).ready(function(){
  var status = new Status($('#ajaxstatus'));
  $(document).ajaxError(function(e, xhr, settings, exception) {
    status.addMessage('error','There was an error with your request, please try again.');
  });
  
  $(document).ajaxComplete(function(e, xhr, settings) {
    if(xhr.getResponseHeader('Content-Type') == 'application/json'){
      eval("var json="+xhr.responseText);
      $(json.messages).each(function(i){
        status.addMessage(this.type, this.text);
      });
    }
  });
  //Ajax activity indicator bound to ajax start/stop document events
  $(document).ajaxStart(function(){
    status.start();
  }).ajaxStop(function(){
    status.end();
  });
  var changeProgram = new ChangeProgram(document.location.href + '/../../changeprogram');
  changeProgram.init();
  
  $('#pendingPayments .applicant').each(function(i){
    if(changeProgram.check($(this).attr('programId'))){
      var value = $(this).html();
      var a = $('<a>').attr('href', document.location.href + '/../../applicants/single/' + $(this).attr('applicantId')).html(value).data('programId', $(this).attr('programId'));
      a.bind('click', function(){
        changeProgram.changeTo($(this).data('programId'));
        return true;
      });
      $(this).html(a);
    }
  });
});