/**
 * Javascript for the admin_managedisplays controller
 */
$(document).ready(function(){

  var status = new Status($('#ajaxstatus'), $('#ajaxstatus'));
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
  var displayForm = $('#displayManager');
  if(displayForm.length){
    $('#account input').button();
    $('#pages input').button();
    $('input[type=submit]').button();
    $('#pages > ol > li').each(function(){
      if(!$('input', this).attr('checked')){
        $('.elements', this).hide();
      }
    });
    $('#pages > ol > li > input').bind('change', function(){
      if($(this).attr('checked')){
        $('.elements', $(this).parent()).show();
      } else {
        $('.elements', $(this).parent()).hide();
      }
    });
    var largest = 0;
    $('#pages > ol > li > label.ui-button').each(function(){
      if($(this).outerWidth() > largest) largest = $(this).outerWidth();
    });
    $('#pages label.ui-button').css({width: largest});
    
    
  }
  
  
});
