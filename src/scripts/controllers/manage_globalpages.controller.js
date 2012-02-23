/**
 * Javascript for the setup_pages controller
 */
$(document).ready(function(){
  var timeout = new AuthenticationTimeout('JazzeeAdminLoginTimeout');
  timeout.start();
  
  var status = new Status($('#status'), $('#content'));
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
  var pageBuilder = new GlobalPageBuilder($('#canvas'));
  pageBuilder.setup();
});

/**
 * The GlobalPageBuilder class
  @extends PageBuilder
 */
function GlobalPageBuilder(canvas){
  PageBuilder.call(this, canvas);
  this.controllerPath = this.services.getControllerPath('manage_globalpages');
  this.editGlobal = true;
}

GlobalPageBuilder.prototype = new PageBuilder();
GlobalPageBuilder.prototype.constructor = GlobalPageBuilder;


GlobalPageBuilder.prototype.setup = function(){
  PageBuilder.prototype.setup.call(this);
  var pageBuilder = this;
  this.refreshPages();
};

GlobalPageBuilder.prototype.synchronizePageList = function(){
  var div = $('#pages', this.canvas);
  div.empty();
  div.append($('<h5>').html('Global Pages'));
  div.append(this.getPagesList());
  div.append(this.addNewPageControl());
};

/**
 * Create a control for adding new page
 * @return {jQuery}
 */
GlobalPageBuilder.prototype.addNewPageControl = function(){
  var pageBuilder = this;
  var dropdown = $('<ul>');
  for(var i = 0; i < this.pageTypes.length; i++){
    var item = $('<a>').html(this.pageTypes[i].typeName).attr('href', '#').data('pageType', this.pageTypes[i]);
    item.bind('click', function(e){
      var pageType = $(e.target).data('pageType');
      var page = new window[pageType.typeClass].prototype.newPage('newpage' + pageBuilder.getUniqueId(),'New ' + pageType.typeName + ' Page',pageType.id,pageType.typeName,pageType.typeClass,'new',pageBuilder);
      pageBuilder.addPage(page);
      return false;
    });
    dropdown.append($('<li>').append(item));
  }
  var button = $('<button>').html('New Page').button();
  button.qtip({
    position: {
      my: 'bottom-left',
      at: 'bottom-right'
    },
    show: {
      event: 'click'
    },
    hide: {
      event: 'unfocus click',
      fixed: true
    },
    content: {
      text: dropdown,
      title: {
        text: 'Choose a page type',
        button: true
      }
    }
  });
  return button;
}