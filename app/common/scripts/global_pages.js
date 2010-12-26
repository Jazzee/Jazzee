function Pages(){
  var self = this;
  this.canvas;
  this.pageStore;
  this.currentPageID = false;
  
  this.init = function(){
    this.canvas = $('#canvas');
    $('#workspace').hide();
    this.pageStore = new PageStore;
    this.pageStore.init(document.location.href, 'pageId');
    $(document).bind("updatedPageList", self.refreshPageDisplay);
    this.refreshPageTypesDisplay();
    $('#save-pages').bind('click', self.pageStore.save);
  }
  
  this.refreshPageDisplay = function(){
    $('#workspace').hide();
    $('#pages ol').remove();
    var list = self.pageStore.getPageList();
    var ol = $('<ol>').addClass('page-list');
    $(list).each(function(i){
      var li = $('<li>').html(this.title).attr('id', 'page-' + this.applicationPageId);
      $(li).data('page', this);
      $(li).bind('click', function(e){
        self.currentPageID = $(this).data('page').pageId;
        $(this).parent().children('li').removeClass('active');
        $(this).addClass('active');
        $(this).data('page').workspace();
        $('#workspace').show('slide');
      });
      ol.append(li);
    });
    ol.sortable();
    ol.bind( "sortupdate", function(event, ui) {
      $(this).children('li').each(function(i){
        console.log('use the pageStore to set the weight not here');
      });
    });
    $('#pages').append(ol);
    if(this.currentPageID) $('#page-' + this.currentPageID).trigger('click');
    else $('#pages li:first').trigger('click');
  }
  
  this.refreshPageTypesDisplay = function(){
    var ol = $('<ol>').addClass('add-list');
    $(this.pageStore.getPageTypesList()).each(function(i){
      var type = this;
      var li = $('<li>').html(type.name);
      $(li).bind('click', function(e){
        var obj = self.pageStore.newPageObject();
        obj.pageType = type.id;
        obj.type = type.class;
        obj.title = 'New ' + type.name + ' Page';
        self.pageStore.addPage(obj, 'new');
      });
      ol.append(li);
    });
    $('#new-pages').append(ol);
  }
}
  
$(document).ready(function(){
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
  
  var pages = new Pages;
  pages.init();
});