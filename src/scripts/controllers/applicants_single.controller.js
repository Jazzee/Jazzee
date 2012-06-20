/**
 * Javascript for the setup_pages controller
 */
$(document).ready(function(){
  //force the browser to not cache results
  $.ajaxSetup({ cache: false });
  
  var status = new Status($('#ajaxStatus'), $('#content'));
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
  var applicant = new Applicant($('#container'));
  applicant.init();
});