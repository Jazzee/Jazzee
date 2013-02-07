/**
 * Javascript for the applicants_grid controller
 */
$(document).ready(function(){
  var services = new Services;
  var status = new Status($('#ajaxstatus'), $('#content'));
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
  
  var displayChooser = new DisplayChooser('applicant_grid');
  displayChooser.init();
  
  var replaceGrid = function(display){
    var path = services.getControllerPath('applicants_grid');
    $.get(path + '/listApplicants', function(json){
      var grid = new Grid(display,json.data.result, $('#grid'), path);
      grid.init();
    });
  };
  replaceGrid(displayChooser.getCurrentDisplay());
  displayChooser.bind(replaceGrid);
});