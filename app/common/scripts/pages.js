function Pages(){
  var self = this;
  this.canvas;
  this.pageStore;
  this.currentPageID = false;
  
  this.init = function(){
    this.canvas = $('#canvas');
    $('#workspace').hide();
    this.pageStore = new PageStore;
    this.pageStore.init(document.location.href);
    $(document).bind("updatedPageList", self.refreshPageDisplay);
    this.refreshPageTypesDisplay();
  }
  
  this.refreshPageDisplay = function(){
    $('#workspace').hide();
    $('#application-pages ol').remove();
    var list = self.pageStore.getPageList();
    var ol = $('<ol>').addClass('page-list');
    $(list).each(function(i){
      var li = $('<li>').html(this.title).attr('id', 'application-page-' + this.id);
      $(li).data('page', this);
      $(li).bind('click', function(e){
        self.currentPageID = $(this).data('page').id;
        self.clearWorkspace();
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
        var page = $(this).data('page');
        //set the page weight to i+1 so we don't start at zero
        var weight = i+1;
        if(page.weight != weight){
          page.setProperty('weight', weight);
        }
      });
      self.pageStore.saveAll();
    });
    $('#application-pages').append(ol);
    $('#application-pages li:first').trigger('click');
  }
  
  this.refreshPageTypesDisplay = function(){
    var ol = $('<ol>').addClass('add-list');
    $(this.pageStore.pageTypes).each(function(id,name){
      var li = $('<li>').html(name);
      $(li).bind('click', function(e){
        self.pageStore.addPage(id);
      });
      ol.append(li);
    });
    $('#new-pages').append(ol);
  }
  

  
  this.clearWorkspace = function(){
    $('#workspace-left-top').empty();
    $('#workspace-left-middle-left').empty();
    $('#workspace-left-middle-right').empty();
    $('#workspace-left-bottom-left').empty();
    $('#workspace-left-bottom-left').empty();
    

    $('#workspace-right-top').empty();
    $('#workspace-right-middle').empty();
    $('#workspace-right-bottom').empty();
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