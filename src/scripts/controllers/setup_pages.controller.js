/**
 * Javascript for the setup_pages controller
 */
$(document).ready(function(){
  var timeout = new AuthenticationTimeout('JazzeeAdminLoginTimeout');
  timeout.start();
  
  var status = new Status($('#status'));
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
  var baseUrl = document.location.href;
  var pageStore = new PageStore(baseUrl, $('#pages'));
  $('#save-pages').bind('click', function(){
    pageStore.save();
  });
  $.get(baseUrl + '/listPageTypes',function(json){  
    var ol = $('<ol>').addClass('add-list');
    $(json.data.result).each(function(i){
      var pageType = this;
      pageStore.addPageType(pageType);
      var li = $('<li>').html(pageType.name);
      li.bind('click', function(){
        var page = new window[pageType.className].prototype.newPage('newpage' + pageStore.getUniqueId(),'New ' + pageType.name + ' Page',pageType.id,pageType.className,'new',pageStore);
        pageStore.addPage(page);
      });
      ol.append(li);
    });
    $('#new-pages').append(ol);
  });
  
  $.get(baseUrl + '/listGlobalPages',function(json){  
    var ol = $('<ol>').addClass('add-list');
    $(json.data.result).each(function(i){
      var globalPage = this;
      var li = $('<li>').html(globalPage.title);
      li.bind('click', function(){
        var page = new window[globalPage.className].prototype.newPage(globalPage.id,globalPage.title,globalPage.classId,globalPage.className,'new-global',pageStore);
        page.isGlobal = true;
        page.title = globalPage.title;
        page.min = globalPage.min;
        page.max = globalPage.max;
        page.isRequired = globalPage.isRequired;
        page.instructions = globalPage.instructions;
        page.leadingText = globalPage.leadingText;
        page.trailingText = globalPage.trailingText;
        pageStore.addPage(page);
      });
      ol.append(li);
    });
    $('#global-pages').append(ol);
  });
});