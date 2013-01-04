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
  
  JSCLASS_PATH = services.getBasepath() + 'resource/jclass/js';
  JS.Packages(function() { with(this) {
     file(services.getBasepath() + 'resource/jquery.jsrender.js').provides('jQuery.views');
     file(services.getBasepath() + 'resource/jquery.tablesorter.js').provides('jQuery.tablesorter');
     file(services.getBasepath() + 'resource/jquery.scrollto.js').provides('jQuery.scrollTo');
     file(services.getBasepath() + 'resource/jquery.uuid.js').provides('jQuery.uidGen');
     file(services.getBasepath() + 'resource/scripts/classes/Display.class.js').provides('Display');
     file(services.getBasepath() + 'resource/scripts/classes/Grid.class.js')
      .provides('Grid')
      .requires('JS.Class')
      .requires('JS.OrderedSet')
      .requires('jQuery.views')
      .requires('jQuery.tablesorter')
      .requires('jQuery.uidGen')
      .requires('jQuery.scrollTo')
	 .requires('Display') ;
  }});

  var displayChooser = new DisplayChooser('applicant_grid');
  displayChooser.init();
  
  var replaceGrid = function(display){
    JS.require('Grid', function(){
      $('#ApplicantTable').empty();
      var path = services.getControllerPath('applicants_grid');
      var grid = new ApplicantGrid("#ApplicantTable", null, display);
      $.get(path + '/listApplicants', function(json){
        loadApps(grid, json.data.result, displayChooser.getCurrentDisplay(), path);
      });
    });
  };
  
  //seperate function so it can call itself back
  var loadApps = function(grid, applicantIds, display, path){
    if(applicantIds.length){
      var limitedIds = applicantIds.splice(0, 50);
      $.post(path + '/getApplicants',{applicantIds: limitedIds, display: display.getObj()
      }, function(json){
        grid.append(json.data.result);
        loadApps(grid, applicantIds, display, path);
      });
    }
  };
  replaceGrid(displayChooser.getCurrentDisplay());
  
  displayChooser.bind(replaceGrid);
});