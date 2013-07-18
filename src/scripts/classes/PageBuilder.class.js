/**
 * Clientside page builder
 * Gets extended by the page builder controllers to provide specific functionality
 * @param {jQuery} canvas
 * @param {Status} status
 */
function PageBuilder(canvas, status){
  this.services = new Services;
  this.deletedPages = [];
  this.pages = {};
  this.pageTypes = [];
  this.elementTypes = [];
  this.paymentTypes = [];
  this.isModified = false;
  this.canvas = canvas;
  this.status = status;
  //make sure we don't have any collisions with new page and element Ids
  this.IdCounter = 0;
  this.controllerPath = '';
  this.currentPage = false;

  //are we editing global pages or application pages
  this.editGlobal = false;
  var pageBuilder = this;
  $(window).bind('beforeunload', function(){
    if(pageBuilder.isModified){
      return 'Are you sure you want to leave?  If you do all of your changes will be lost.';
    }
  });
};

/**
 * Setup the workspace layout
 */
PageBuilder.prototype.setup = function(){
  var pageBuilder = this;
  $.ajax({
    type: 'GET',
    url: this.controllerPath + '/listPageTypes',
    async: false,
    success: function(json){
      pageBuilder.pageTypes = [];
      $(json.data.result).each(function(i){
        pageBuilder.pageTypes.push(this);
      });
    }
  });
  $.ajax({
    type: 'GET',
    url: this.controllerPath + '/listElementTypes',
    async: false,
    success: function(json){
      pageBuilder.elementTypes = [];
      $(json.data.result).each(function(i){
        pageBuilder.elementTypes.push(this);
      });
    }
  });
  $.ajax({
    type: 'GET',
    url: this.controllerPath + '/listPaymentTypes',
    async: false,
    success: function(json){
      pageBuilder.paymentTypes = [];
      $(json.data.result).each(function(i){
        pageBuilder.paymentTypes.push(this);
      });
    }
  });
  var button = $('<button>').html('Save Changes');
  button.button({'disabled': true});
  button.bind('click', function(){
    var overlay = $('<div>').attr('id', 'savepageoverlay');
    overlay.dialog({
      height: 90,
      modal: true,
      autoOpen: true,
      open: function(event, ui){
        $(".ui-dialog-titlebar", ui.dialog).hide();
        if(!pageBuilder.checkNames()){
          overlay.dialog('destroy').remove();
        } else {
          var toSave = [];
          for(var i in pageBuilder.pages){
            if(pageBuilder.pages[i].checkIsModified()){
              toSave.push(pageBuilder.pages[i].getDataObject());
            }
          }
          for(var i = 0; i < pageBuilder.deletedPages.length; i++){
            toSave.push(pageBuilder.deletedPages[i].getDataObject());
          }
          pageBuilder.deletedPages = [];
          var label = $('<div>').addClass('label').html('Saving pages...').css('float', 'left').css('margin','10px 5px');
          var progressbar = $('<div>').addClass('progress').append(label);
          progressbar.data('pageBuilder', pageBuilder);
          overlay.append(progressbar);
          progressbar.progressbar({
            max: toSave.length,
            value: 0,
            complete: function(event,ui){
              var bar = $('#savepageoverlay div.progress');
              var pageBuilder = bar.data('pageBuilder');
              bar.progressbar('destroy');
              bar.empty();
              bar.append($('<div>').addClass('label').html('Downloading new page data...'));
              bar.progressbar({
                value: false,
                create: function(){
                  pageBuilder.setup();
                  overlay.dialog('destroy').remove();
                }
              });
            }
          });
          for(var i = 0; i < toSave.length; i++){
            $.post(pageBuilder.controllerPath + '/savePage/' + toSave[i].id,{data: $.toJSON(toSave[i])}, function(){
              var value = $('#savepageoverlay div.progress').progressbar('value');
              $('#savepageoverlay div.progress').progressbar("value", value+1);
            });
          }
          
        }
      }
    });
    
    return false;
  });
  $('#save', this.canvas).empty().append(button);
};


/**
 * Create an JazzeePage object
 * Calls itself recursivly for child pages
 * @param {Object} obj
 * @returns {JazzeePage}
 */
PageBuilder.prototype.createPageObject = function(obj){
  var pageBuilder = this;
  var page = new window[obj.typeClass]();
  page.init(obj, pageBuilder);
  $(obj.elements).each(function(i,element){
    var Element = new window[element.typeClass]();
    Element.init(element, page);
    $(element.list).each(function(){
      Element.addListItem(this);
    });
    page.addElement(Element);
  });
  $(obj.variables).each(function(){
    page.variables[this.name] = {name : this.name, value: this.value};
  });
  $(obj.children).each(function(){
    page.addChild(pageBuilder.createPageObject(this));
  });
  page.isModified = false; //reset isModified now that we have added cildren and varialbes
  return page;
};

/**
 * Refresh the pages
 */
PageBuilder.prototype.refreshPages = function(){
  var pageBuilder = this;
  var overlay = $('<div>').attr('id', 'refreshpagesoverlay');
    overlay.dialog({
      height: 90,
      modal: true,
      autoOpen: true,
      open: function(event, ui){
        $(".ui-dialog-titlebar", ui.dialog).hide();
        var label = $('<div>').addClass('label').html('Updating page list...').css('float', 'left').css('margin','10px 5px');
        var progressbar = $('<div>').addClass('progress').append(label);
        progressbar.data('pageBuilder', pageBuilder);
        overlay.append(progressbar);
        progressbar.progressbar({
          max: 100,
          value: 25,
          complete: function(event, ui){
            $('#refreshpagesoverlay').dialog('destroy').remove();
          },
          create: function(event,ui){
            var pageBuilder = $(this).data('pageBuilder');
            $.get(pageBuilder.controllerPath + '/listPages',function(json){
              $('#refreshpagesoverlay div.progress').progressbar("value", 50);
              pageBuilder.pages = {};
              $(json.data.result).each(function(i){
                var page = pageBuilder.createPageObject(this);
                pageBuilder.pages[page.id] = page;
                $('#refreshpagesoverlay div.progress').progressbar('value', i/json.data.result.length*.25*100+50);
              });
              $('#refreshpagesoverlay div.progress').progressbar('value', 75);
              pageBuilder.synchronizePageList();
              var currentPageLi = $('#'+pageBuilder.currentPage);
              if(currentPageLi.length){
                currentPageLi.click();
              } else {
                $('#pages li', this.canvas).first().click();
              }
              pageBuilder.isModified = false;
              $('#refreshpagesoverlay div.progress').progressbar('value', 100);
              
            });
          }
        });
      }
    });
    
  
  
};

/**
 * Get an ordered list object of all the pages in the list
 * @param integer kind
 * @return {jQuery} ol
 */
PageBuilder.prototype.getPagesList = function(kind){
  var pageBuilder = this;
  var ol = $('<ol>');
  for(var i in this.pages){
    var page = this.pages[i];
    if(page.kind == kind){
      var li = $('<li>');
      li.html(page.title).attr('id', 'page-' + page.id);
      li.data('page', page);
      li.unbind('click');
      li.bind('click', function(e){
        $(this).parent().children('li').removeClass('active');
        $(this).addClass('active');
        $(this).data('page').workspace();
        pageBuilder.currentPage = $(this).attr('id');
      });
      if(page.isGlobal && !this.editGlobal){
        li.addClass('global');
      }
      $(ol).append(li);
    }
  }
  return ol;
};

/**
 * Add a page to the store
 * @param {JazzeePage} page
 */
PageBuilder.prototype.addPage = function(page){
  this.pages[page.id] = page;
  this.synchronizePageList();
  this.markModified();
};

/**
 * Get the unique ID and incirement the counter
 * @returns {Integer}
 */
PageBuilder.prototype.getUniqueId = function(){
  return this.IdCounter++;
};

/**
 * Mark the modified
 */
PageBuilder.prototype.markModified = function(){
  this.isModified = true;
  $('#save button', this.canvas).button( "option", "disabled", false );
};

/**
 * Copy a page
 * @param {Object} obj
 */
PageBuilder.prototype.copyPage = function(obj){
  var copiedPage = this.pageFromObject(obj, 'Copy of '+ obj.title, 'copy');
  if(copiedPage !== false){
    this.addPage(copiedPage);
  }
};

/**
 * Import a page
 * @param {Object} obj
 */
PageBuilder.prototype.importPage = function(obj){
  var error = false;
  for(var i in this.pages){
    var page = this.pages[i];
    if(obj.uuid == page.uuid){
      error = true;
      this.status.addMessage('error', 'That page cannot be imported, it has the same UUID as ' + page.title + '.  It should be copied instead.');
    }
  }
  if(!error){
    var newPageObj = this.pageFromObject(obj, obj.title, 'import');
    if(newPageObj !== false){
      this.addPage(newPageObj);
    }
  }
};

/**
 * Import a page
 * @param {Object} obj
 * @param String title
 * @param String status
 * @return {JazzeePage}
 */
PageBuilder.prototype.pageFromObject = function(obj, title, status){
  var hasType = false;
  $.each(this.pageTypes, function(){
     if(this.typeClass == obj.typeClass){
       obj.typeId = this.id;
       hasType = true;
       return;
     }
  });
  if(!hasType){
    this.status.addMessage('error','Canot create this page becuase ' + obj.typeName + ' is not a recognized page type on this system.');
    return false;
  }
  if(window[obj.typeClass] == undefined){
    this.status.addMessage('error','Canot create this page becuase ' + obj.typeClass + ' is not present on this system.');
    return false;
  }
  var id = 'newpage' + this.getUniqueId();
  obj.id = id;
  obj.title = title;
  var page = new window[obj.typeClass]();
  page.init(obj, this);
  page.status = status;
  page.isModified = true;
  for(var i=0; i<obj.elements.length; i++){
    var e = obj.elements[i];
    e.id = 'newelement' + this.getUniqueId();
    var Element = new window[e.typeClass]();
    Element.init(e, page);
    Element.status = 'new';
    Element.isModified = true;
    for(var j = 0; j < e.list.length; j++){
      var item = Element.newListItem(e.list[j].value);
      item.setProperty('isActive',e.list[j].isActive);
      item.setProperty('weight',e.list[j].weight);
      item.setProperty('name',e.list[j].name);
      for( var name in e.list[j].variables){
        item.setVariable(e.list[j].variables[name].name, e.list[j].variables[name].value);
      };
      item.setProperty('status','new');
    }
    page.addElement(Element);
  }
  for(var property in obj.variables){
    page.setVariable(property, obj.variables[property].value);
  }
  for(var i=0; i<obj.children.length; i++){
    var childPageObj = this.pageFromObject(obj.children[i], obj.children[i].title, status);
    if(childPageObj === false ){
      return false;
    } else {
      page.addChild(childPageObj);
    }
  }
  return page;
};

/**
 * Delete a page from the store
 * @param {ApplyPage} page
 */
PageBuilder.prototype.deletePage = function(page){
  this.deletedPages.push(page);
  delete this.pages[page.id];
  $('#page-' + page.id).remove();
  this.markModified();
};

/**
 * Get a preview of the page
 * @param {ApplyPage} page
 * @returns {jQuery}
 */
PageBuilder.prototype.getPagePreview = function(page){
  var div = $('<div>');
  $.ajax({
    type: 'POST',
    url: this.controllerPath + '/previewPage',
    data: {data: $.toJSON(page.getDataObject())},
    async: false,
    success: function(html){
      div.html(html);
    }
  });
  return div;
};

/**
 * Get an element type by its class name
 * @param {String} typeClassName
 * @returns {}
 */
PageBuilder.prototype.getElementType = function(typeClassName){
  for(var i = 0; i < this.elementTypes.length; i++){
    if(this.elementTypes[i].typeClass == typeClassName ) {
      return this.elementTypes[i];
    }
  }
  return false;
};

/**
 * Test an element to ensure it is a valid name
 * @param {jQuery}
 */
PageBuilder.prototype.addNameTest = function(element){
    $(element).filter_input({
      regex:'[a-zA-Z0-9_\r]',
      feedback: function(wrongChar) {
        var div = $(this).parent();
        var message = $('<p>').addClass('message').appendTo(div);
        message.html('"' + wrongChar + '" is not allowed.');
        message.delay(1000).fadeOut(2000);
      }
  });
};

/**
 * Test an element to ensure it is a valid name
 * @param {jQuery}
 */
PageBuilder.prototype.addNumberTest = function(element){
    $(element).filter_input({
      regex:'[0-9\r]',
      feedback: function(wrongChar) {
        var div = $(this).parent();
        var message = $('<p>').addClass('message').appendTo(div);
        message.html('"' + wrongChar + '" is not allowed.');
        message.delay(1000).fadeOut(2000);
      }
  });
};

/**
 * Check all pages elements and lists to ensure there are no name collisions
 * @return boolean
 */
PageBuilder.prototype.checkNames = function(){
  var pageNames = [];
  var messages = [];
  var conflict = false;
  for(var i in this.pages){
    var page = this.pages[i];

    if(page.name != null){
      if($.inArray(page.name, pageNames) > -1 ){
        conflict = true;
        messages.push('The name "' + page.name + '" is used for more than one page.');
      }
      pageNames.push(page.name);
    }
    var elementNames = [];
    for(var j in page.elements){
      var element = page.elements[j];
      if(element.name != null){
        if($.inArray(element.name, elementNames) > -1 ){
          conflict = true;
          messages.push('The name "' + element.name + '" is used in more than once on the ' + page.title + ' page.');
        }
        elementNames.push(element.name);
      }
      var itemNames = [];
      for(var k in element.listItems){
        var item = element.listItems[k];
        if(item.name != null){
          if($.inArray(item.name, itemNames) > -1 ){
            conflict = true;
            messages.push('The name "' + item.name + '" is used more than once in the ' + element.title + ' element on the ' + page.title + ' page.');
          }
          itemNames.push(item.name);
        }
      }
    }
  }

  if(conflict){
    var unique = [];
    for(var i = 0; i < messages.length; ++i){
      if($.inArray(messages[i], unique) == -1 ){
        unique.push(messages[i]);
        this.status.addMessage('error', 'There was an error saving: ' + messages[i]);
      }
    }
    return false;
  }
  return true;
};

/**
 * Complete a special page action
 * @param String pageId
 * @param String actionName
 * @param {} data
 * @return {}
 */
PageBuilder.prototype.specialPageAction = function(pageClass, actionName, data){
  var result = null;
  $.ajax({
    type: 'POST',
    url: this.controllerPath + '/specialPageAction',
    data: {
      data: $.toJSON(data),
      actionName: actionName,
      className: pageClass},
    async: false,
    success: function(json){
      result = json.data.result;
    }
  });
  return result;
};

/**
 * Complete a special element action
 * @param String elementClass
 * @param String actionName
 * @param {} data
 * @return {}
 */
PageBuilder.prototype.specialElementAction = function(elementClass, actionName, data){
  var result = null;
  $.ajax({
    type: 'POST',
    url: this.controllerPath + '/specialElementAction',
    data: {
      data: $.toJSON(data),
      className: elementClass,
      actionName: actionName},
    async: false,
    success: function(json){
      result = json.data.result;
    }
  });
  return result;
};

/**
 * Get a page by id
 * @param integer pageId
 * @return {JazzeePage}
 */
PageBuilder.prototype.getPageById = function(id){
  return this.findPageId(id, this.pages);
};

/**
 * Look for a page ID in child pages
 * @param integer pageId
 * @param {} children
 * @return {JazzeePage}
 */
PageBuilder.prototype.findPageId = function(id, obj){
  if(typeof obj[id] != 'undefined'){
    return obj[id];
  }
  var page;
  for(var i in obj){
    if(typeof obj[i].children == 'object'){
      if(page = this.findPageId(id, obj[i].children)){
        return page;
      }
    }
  }
  return false;
};
