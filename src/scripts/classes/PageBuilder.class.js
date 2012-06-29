/**
 * Clientside page builder
 * Gets extended by the page builder controllers to provide specific functionality
 * @param {jQuery} workspace
 */
function PageBuilder(canvas){
  this.services = new Services;
  this.deletedPages = [];
  this.pages = {};
  this.pageTypes = [];
  this.elementTypes = [];
  this.paymentTypes = [];
  this.isModified = false;
  this.canvas = canvas;
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
    for(var i = 0; i < toSave.length; i++){
      $.post(pageBuilder.controllerPath + '/savePage/' + toSave[i].id,{data: $.toJSON(toSave[i])});
    }
    pageBuilder.setup();
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
  $.get(this.controllerPath + '/listPages',function(json){
    pageBuilder.pages = {};
    $(json.data.result).each(function(i){
      var page = pageBuilder.createPageObject(this);
      pageBuilder.pages[page.id] = page;
    });
    pageBuilder.synchronizePageList();
    var currentPageLi = $('#'+pageBuilder.currentPage);
    if(currentPageLi.length){
      currentPageLi.click();
    } else {
      $('#pages li', this.canvas).first().click();
    }
    pageBuilder.isModified = false;
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
  this.addPage(this.pageFromObject(obj, 'Copy of '+ obj.title, 'new'));
};

/**
 * Import a page
 * @param {Object} obj
 */
PageBuilder.prototype.importPage = function(obj){
  this.addPage(this.pageFromObject(obj, obj.title, 'import'));
};

/**
 * Import a page
 * @param {Object} obj
 * @param String title
 * @param String status
 * @return {JazzeePage}
 */
PageBuilder.prototype.pageFromObject = function(obj, title, status){
  if(window[obj.typeClass] == undefined){
    alert('Canot create this page becuase ' + obj.typeName + ' is not a recognized page type.');
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
      item.isActive = e.list[j].isActive;
      item.weight = e.list[j].weight;
    }
    page.addElement(Element);
  }
  for(var property in obj.variables){
    page.setVariable(property, obj.variables[property].value);
  }
  for(var i=0; i<obj.children.length; i++){
    page.addChild(this.pageFromObject(obj.children[i], obj.children[i].title, status));
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
