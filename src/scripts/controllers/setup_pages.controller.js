/**
 * Javascript for the setup_pages controller
 */
$(document).ready(function(){

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
  var pageBuilder = new ApplicationPageBuilder($('#canvas'), status);
  pageBuilder.setup();
});

/**
 * The ApplicationPageBuilder class
  @extends PageBuilder
 */
function ApplicationPageBuilder(canvas, status){
  PageBuilder.call(this, canvas, status);
  this.controllerPath = this.services.getControllerPath('setup_pages');
  this.editGlobal = false;
  this.globalPages = {};
  this.sirAcceptPages = {};
  this.sirDeclinePages = {};
  this.applicationPageKinds = {
    'application': 2,
    'sirAccept': 4,
    'sirDecline': 8
  }
};

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
  var pageBuilder = this;
  var div = $('#pages', this.canvas);
  div.empty();
  div.append($('<h5>').html('Application Pages'));
  var ol = this.getPagesList(this.applicationPageKinds.application);
  div.append(ol);
  div.append(this.addNewPageControl('New Page', this.applicationPageKinds.application, 'Jazzee\\Interfaces\\Page'));
  div.append(this.addNewGlobalPageControl('Add Global Page', this.applicationPageKinds.application, 'Jazzee\\Interfaces\\Page'));
  div.append(this.importPageControl());

  div.append($('<h5>').html('SIR Accept Page'));
  var ol = this.getPagesList(this.applicationPageKinds.sirAccept);
  div.append(ol);
  if($('li',ol).length == 0){
    ol.append(this.addNewPageControl('Set Page', this.applicationPageKinds.sirAccept, 'Jazzee\\Interfaces\\SirPage'));
    ol.append(this.addNewGlobalPageControl('Set Global Page', this.applicationPageKinds.sirAccept, 'Jazzee\\Interfaces\\SirPage'));
  }

  div.append($('<h5>').html('SIR Decline Page'));
  var ol = this.getPagesList(this.applicationPageKinds.sirDecline);
  div.append(ol);
  if($('li',ol).length == 0){
    ol.append(this.addNewPageControl('Set Page', this.applicationPageKinds.sirDecline, 'Jazzee\\Interfaces\\SirPage'));
    ol.append(this.addNewGlobalPageControl('Set Global Page', this.applicationPageKinds.sirDecline, 'Jazzee\\Interfaces\\SirPage'));
  }
  $('ol', div).each(function(i){
    var ol = $(this);
    $('li',ol).sort(function(a,b){
      return $(a).data('page').weight > $(b).data('page').weight ? 1 : -1;
    }).appendTo(ol);
    ol.sortable();
    ol.bind("sortupdate", function(e, ui) {
      $('li',$(e.target).parent()).each(function(i){
        $('#'+$(this).attr('id')).data('page').setProperty('weight',i);
      });
    });
  });
};

/**
 * Create a control for adding new page
 * @param String title
 * @param integer kind
 * @return {jQuery}
 */
ApplicationPageBuilder.prototype.addNewPageControl = function(title, kind, inter){
  var pageBuilder = this;
  var dropdown = $('<ul>');
  for(var i = 0; i < this.pageTypes.length; i++){
    if($.inArray(inter, this.pageTypes[i].interfaces) > -1){
      var item = $('<a>').html(this.pageTypes[i].typeName).attr('href', '#').data('pageType', this.pageTypes[i]);
      item.bind('click', function(e){
        var pageType = $(e.target).data('pageType');
        var page = new window[pageType.typeClass].prototype.newPage('newpage' + pageBuilder.getUniqueId(),'New ' + pageType.typeName + ' Page',pageType.id,pageType.typeName,pageType.typeClass,'new',pageBuilder);
        page.kind = kind;
        if($('#pages li').length > 0) page.weight = parseInt($('#pages li').last().data('page').weight)+1;
        else page.weight = 0; //this is the only page so it gets a weight of 0
        pageBuilder.addPage(page);
        return false;
      });
      dropdown.append($('<li>').append(item));
    }
  }
  var button = $('<button>').html(title).button();
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
 * @param String title
 * @param integer kind
 * @return {jQuery}
 */
ApplicationPageBuilder.prototype.addNewGlobalPageControl = function(title, kind, inter){
  var pageBuilder = this;
  var dropdown = $('<ul>');
  for(var i in this.globalPages){
    var globalPage = this.globalPages[i];
    //only if the global page isn't already being used
    if(pageBuilder.pages[globalPage.id] == undefined && $.inArray(inter, globalPage.interfaces) > -1){
      var item = $('<a>').html(globalPage.title).attr('href', '#').data('page', globalPage);
      item.bind('click', function(e){
        var globalPage = $(e.target).data('page');
        var page = new window[globalPage.typeClass].prototype.newPage(globalPage.id,globalPage.title,globalPage.typeId,globalPage.typeName,globalPage.typeClass,'new-global',pageBuilder);
        page.kind = kind;
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
  var button = $('<button>').html(title).button();
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

/**
 * Create a control for importing a page
 * @param String title
 * @param integer kind
 * @return {jQuery}
 */
ApplicationPageBuilder.prototype.importPageControl = function(){
  var pageBuilder = this;

  var button = $('<button>').html('Import Page').button();
  button.click(function(e){

    var obj = new FormObject();
    var field = obj.newField({name: 'legend', value: 'Import Page'});

    var element = field.newElement('Textarea', 'pageJson');
    element.label = 'Page';
    element.required = true;

    var form = new Form();
    var formObject = form.create(obj);
    $('form',formObject).append($('<button type="submit" name="submit">').html('Apply'));

    var div = $('<div>');
    div.css("overflow-y", "auto");
    div.dialog({
      modal: true,
      autoOpen: false,
      position: 'center',
      width: 800
    });
    div.html(formObject);
    $('form', div).unbind().bind('submit',function(e){
      e.preventDefault();
      var json = $('textarea[name=pageJson]', this).val();
      try{
        var obj = $.parseJSON(json);
        pageBuilder.copyPage(obj);
        div.dialog("close");
      } catch(e){
        pageBuilder.status.addMessage('error', 'Cannot import this page, there is something wrong with the exported page structure.  You will need to re-export and try importing this page again.');
      }
      return false;
    });//end submit
    div.dialog('open');
    return false;
  });
  return button;
};