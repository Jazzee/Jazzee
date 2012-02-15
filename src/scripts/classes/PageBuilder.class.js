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
  
  //are we editing global pages or application pages
  this.editGlobal = false;
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
    $('#pages li', this.canvas).first().click();
  });
};

/**
 * Get an ordered list object of all the pages in the list
 * @return {jQuery} ol
 */
PageBuilder.prototype.getPagesList = function(){
  var ol = $('<ol>');
  for(var i in this.pages){
    var page = this.pages[i];
    var li = $('<li>');
    li.html(page.title).attr('id', 'page-' + page.id);
    li.data('page', page);
    li.unbind('click');
    li.bind('click', function(e){
      $(this).parent().children('li').removeClass('active');
      $(this).addClass('active');
      $(this).data('page').workspace();
    });
    $(ol).append(li);
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
 * @param {ApplyPage} page
 */
PageBuilder.prototype.copyPage = function(page){
  this.addPage(this.createPageCopy(page.getDataObject()));
};

/**
 * Make a copy of a page
 * Need to seperate this function so it can be used recursivly on child pages
 * @param {Object} obj
 * @returns {ApplyPage}
 */
PageBuilder.prototype.createPageCopy = function(obj){
  var id = 'newpage' + this.getUniqueId();
  obj.id = id;
  obj.title = 'Copy of ' + obj.title;
  var copy = new window[obj.typeClass]();
  copy.init(obj, this);
  copy.status = 'new';
  copy.isModified = true;
  for(var i=0; i<obj.elements.length; i++){
    var e = obj.elements[i];
    e.id = 'newelement' + this.getUniqueId();
    var Element = new window[e.typeClass]();
    Element.init(e, copy);
    Element.status = 'new';
    Element.isModified = true;
    for(var j = 0; j < e.list.length; j++){
      Element.newListItem(e.list[j].value);
    }
    copy.addElement(Element);
  }
  for(var property in obj.variables){
    copy.setVariable(property, obj.variables[property].value);
  }
  for(var i=0; i<obj.children.length; i++){
    copy.addChild(this.createPageCopy(obj.children[i]));
  }
  return copy;
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
