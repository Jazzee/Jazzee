/**
 * Clientside page store
 * @param {String} baseUrl the location we are sending requests to
 * @param {String} index whether to use applicationPageId or pageId as our primary index for pages
 * @param {jQuery} workspace
 */
function PageStore(baseUrl,index, workspace){
  this.baseUrl = baseUrl;
  this.index = index;
  this.pages = {};
  this.deletedPages = [];
  this.workspace = workspace;
  $(this.workspace).append($('<ol>').addClass('page-list'));
  
  //make sure we don't have any collisions with new page and element Ids
  this.IdCounter = 0;

  this.refreshPageList();
};

/**
 * Refresh the list of pages from the server
 */
PageStore.prototype.refreshPageList = function(){
  var pageStore = this;
  $.get(this.baseUrl + 'listPages',function(json){  
    $('#workspace').hide();
    $('li',$(pageStore.workspace)).remove();
    pageStore.pages = {};
    $(json.data.result).each(function(i){
      var page = pageStore.createPageObject(this);
      pageStore.addPage(page);
    });
  });
};

/**
 * Create an ApplyPage object
 * Calls itself recursivly for child pages
 * @param {Object} obj
 * @returns {ApplyPage}
 */
PageStore.prototype.createPageObject = function(obj){
  var pageStore = this;
  var page = new window[obj.className]();
  page.init(obj, pageStore);
  $(obj.elements).each(function(i,element){
    var Element = new window[element.className]();
    Element.init(element, page);
    $(element.list).each(function(){
      Element.addListItem(this);
    });
    page.addElement(Element);
  });
  $(obj.variables).each(function(){
    page.setVariable(this.name, this.value);
  });
  $(obj.children).each(function(){
    page.addChild(pageStore.createPageObject(this));
  });
  return page;
};
  
/**
 * Add a page to the store
 * @param {PageStore} page
 */
PageStore.prototype.addPage = function(page){ 
  this.pages[page[this.index]] = page;
  var li = $('<li>').html(page.title).attr('id', 'page-' + page[this.index]);
  $(li).data('page', page);
  $('ol', this.workspace).append(li);
  this.synchronizePageList();
};

/**
 * Delete a page from the store
 * @param {ApplyPage} page
 */
PageStore.prototype.deletePage = function(page){
  this.deletedPages.push(page);
  delete this.pages[page[this.index]];
  $('#page-' + page[this.index]).remove();
};

/**
 * Copy a page
 * @param {ApplyPage} page
 */
PageStore.prototype.copyPage = function(page){
  this.addPage(this.createPageCopy(page.getDataObject()));
};

/**
 * Make a copy of a page
 * Need to seperate this function so it can be used recursivly on child pages
 * @param {Object} obj
 * @returns {ApplyPage}
 */
PageStore.prototype.createPageCopy = function(obj){
  var id = 'newpage' + this.getUniqueId();
  obj.pageId = id;
  obj.applicationPageId = id;
  obj.title = 'Copy of ' + obj.title;
  var copy = new window[obj.className]();
  copy.init(obj, this);
  copy.status = 'new';
  copy.isModified = true;
  for(var i=0; i<obj.elements.length; i++){
    var e = obj.elements[i];
    e.id = 'newelement' + this.getUniqueId();
    var Element = new window[e.className]();
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

///**
// * Get a preview of the page
// * @param {ApplyPage} page
// * @returns {jQuery}
// */
//PageStore.prototype.getPagePreview = function(pageID){
//  var div = $('<div>');
//  $.ajax({
//    url: this.baseUrl + 'previewPage/' +pageID,
//    async: false,
//    success: function(html){
//      div.html(html);
//    }
//  });
//  return div;
//};

/**
 * Get the unique ID and incirement the counter
 * @returns {Integer}
 */
PageStore.prototype.getUniqueId = function(){
  return this.IdCounter++;
};

/**
 * Save any changes in the store
 */
PageStore.prototype.save = function(){
  for(var i in this.pages){
    var page = this.pages[i];
    if(page.checkModified()){
      this.savePage(page);
    }
  }
  for(var i = 0; i < this.deletedPages.length; i++){
    this.savePage(this.deletedPages[i]);
  }
};

/**
 * Post the changes to a page back
 * @param {ApplyPage} page
 */
PageStore.prototype.savePage = function(page){
  var pageStore = this;
  var obj = page.getDataObject();
  $.post(this.baseUrl + 'savePage/' + obj[this.index],{data: $.toJSON(obj)},function(){
    pageStore.refreshPageList();
  });
};

/**
 * Synchonizes the workspace list with the objects in this.pageList
 * This gets called anytime the page list is changed
 */
PageStore.prototype.synchronizePageList = function(){
  var pageStore = this;
  $('li', this.workspace).each(function(i){
    var li = $(this);
    var page = li.data('page');
    page.setProperty('weight',i+1);
    li.html(page.title);
    li.unbind('click');
    li.bind('click', function(e){
      $(this).parent().children('li').removeClass('active');
      $(this).addClass('active');
      $(this).data('page').workspace();
    });
  });
  var ol = $('ol', this.workspace);
  ol.sortable();
  ol.bind("sortupdate", function(event, ui) {
    pageStore.synchronizePageList();
  });
};