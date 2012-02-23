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
  var pageBuilder = new ApplicationPageBuilder($('#canvas'));
  pageBuilder.setup();
});

/**
 * The ApplicationPageBuilder class
  @extends PageBuilder
 */
function ApplicationPageBuilder(canvas){
  PageBuilder.call(this, canvas);
  this.controllerPath = this.services.getControllerPath('setup_pages');
  this.editGlobal = false;
  this.globalPages = {};
}

ApplicationPageBuilder.prototype = new PageBuilder();
ApplicationPageBuilder.prototype.constructor = ApplicationPageBuilder;


ApplicationPageBuilder.prototype.setup = function(){
  PageBuilder.prototype.setup.call(this);
  var pageBuilder = this;
  $.ajax({
    type: 'GET',
    url: this.controllerPath + '/listGlobalPages',
    async: false,
    success: function(json){
      pageBuilder.globalPages = {};
      $(json.data.result).each(function(i){
        pageBuilder.globalPages[this.id] = this;
      });
    }
  });
  this.refreshPages();
};

ApplicationPageBuilder.prototype.synchronizePageList = function(){
  var div = $('#pages', this.canvas);
  div.empty();
  div.append($('<h5>').html('Application Pages'));
  var ol = this.getPagesList();
  $('li',ol).sort(function(a,b){  
    return $(a).data('page').weight > $(b).data('page').weight ? 1 : -1;  
  }).appendTo(ol);
  ol.sortable();
  ol.bind("sortupdate", function(e, ui) {
    $('li',$(e.target).parent()).each(function(i){
      $('#'+$(this).attr('id')).data('page').setProperty('weight',i);
    });
  });
  div.append(ol);
  
  div.append(this.addNewPageControl());
  div.append(this.addNewGlobalPageControl());
};

/**
 * Create a control for adding new page
 * @return {jQuery}
 */
ApplicationPageBuilder.prototype.addNewPageControl = function(){
  var pageBuilder = this;
  var dropdown = $('<ul>');
  for(var i = 0; i < this.pageTypes.length; i++){
    var item = $('<a>').html(this.pageTypes[i].typeName).attr('href', '#').data('pageType', this.pageTypes[i]);
    item.bind('click', function(e){
      var pageType = $(e.target).data('pageType');
      var page = new window[pageType.typeClass].prototype.newPage('newpage' + pageBuilder.getUniqueId(),'New ' + pageType.typeName + ' Page',pageType.id,pageType.typeName,pageType.typeClass,'new',pageBuilder);
      page.weight = parseInt($('#pages li').last().data('page').weight)+1;
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
};

/**
 * Create a control for adding new global page
 * @return {jQuery}
 */
ApplicationPageBuilder.prototype.addNewGlobalPageControl = function(){
  var pageBuilder = this;
  var dropdown = $('<ul>');
  for(var i in this.globalPages){
    var globalPage = this.globalPages[i];
    //only if the global page isn't already being used
    if(pageBuilder.pages[globalPage.id] == undefined){
      var item = $('<a>').html(globalPage.title).attr('href', '#').data('page', globalPage);
      item.bind('click', function(e){
        var globalPage = $(e.target).data('page');
        var page = new window[globalPage.typeClass].prototype.newPage(globalPage.id,globalPage.title,globalPage.typeId,globalPage.typeName,globalPage.typeClass,'new-global',pageBuilder);
        page.isGlobal = true;
        page.title = globalPage.title;
        page.min = globalPage.min;
        page.max = globalPage.max;
        page.isRequired = globalPage.isRequired;
        page.instructions = globalPage.instructions;
        page.leadingText = globalPage.leadingText;
        page.trailingText = globalPage.trailingText;
        page.weight = parseInt($('#pages li').last().data('page').weight)+1;
        
        pageBuilder.addPage(page);
        return false;
      });
      dropdown.append($('<li>').append(item));
    }
  }
  var button = $('<button>').html('New Gobal Page').button();
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
        text: 'Choose a global page',
        button: true
      }
    }
  });
  return button;
};