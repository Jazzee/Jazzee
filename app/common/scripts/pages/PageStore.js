/**
 * Client side PageStore model
 * Hanldes all the communication with the server
 * @return
 */

/**
 * These events are published from this class:
 * updatedPageList
 */
function PageStore(){
  var self = this;
  this.baseUrl = '';
  this.index = '';
  this.pages = {};
  //retain the page order from the server since we store by ID
  this.pageOrder = [];
  
  this.pageTypes = {};
  this.pageTypesOrder = [];
  
  this.elementTypes = {};
  this.elementTypesOrder = [];
  
  //make sure we don't have any collisions with new page and element Ids
  this.IdCounter = 0;
  
  /**
   * Initialize the pageStore
   * @param string baseUrl the location to send requests to
   * @param string index what to use as the object ID applicationPageId, or pageID
   */
  this.init = function(baseUrl, index){
    this.baseUrl = baseUrl;
    this.index = index;
    this.fillElementTypesList();
    this.fillPageTypesList();
    this.refreshPageList();
  };
  
  this.refreshPageList = function(){
    $.get(self.baseUrl + 'listPages',function(json){  
        self.pages = [];
        self.pageOrder = [];
        $(json.data.result).each(function(i){
          var Page = self.createPageObject(this);
          self.pages[this[self.index]] = Page;
          self.pageOrder.push(Page[self.index]);
        });
        $(document).trigger("updatedPageList");
    });
  };
  
  this.createPageObject = function(obj){
    var Page = new window[obj.type]();
    Page.init(obj, self);
    $(obj.elements).each(function(i,element){
      var Element = new window[element.type]();
      Element.init(element, Page);
      $(element.list).each(function(){
        Element.addListItem(this);
      });
      Page.addElement(Element);
    });
    $(obj.variables).each(function(){
      Page.setVariable(this.name, this.value);
    });
    $(obj.children).each(function(){
      Page.addChild(self.createPageObject(this));
    });
    return Page;
  };
  
  this.fillElementTypesList = function(){
    $.ajax({
      url: self.baseUrl + 'listElementTypes',
      async: false,
      success: function(json){
        $(json.data.result).each(function(i){
          self.elementTypesOrder.push(this.id);
          self.elementTypes[this.id] = this;
        });
      }
    });
  };
  
  this.fillPageTypesList = function(){
    $.ajax({
      url: self.baseUrl + 'listPageTypes',
      async: false,
      success: function(json){
        $(json.data.result).each(function(i){
          self.pageTypesOrder.push(this.id);
          self.pageTypes[this.id] = this;
        });
      }
    });
  };
  
  this.newPage = function(typeID){
    var uniqueID = 'newpage' + this.IdCounter++;
    var newPage = {
        applicationPageId: uniqueID,
        pageId: uniqueID,
        type: this.pageTypes[typeID].class,
        pageType: typeID,
        title: "New " + this.pageTypes[typeID].name + " Page",
        min: 0,
        max: 0,
        optional: false,
        instructions: '',
        leadingText: '',
        trailingText: '',
        elements: [],
        variables: [],
        children: []
    };
    var Page = this.createPageObject(newPage);
    Page.isModified = true;
    this.pages[Page[self.index]] = Page;
    this.pageOrder.push(Page[self.index]);
    $(document).trigger("updatedPageList");
  };
  
  this.deletePage = function(page){
    $.post(self.baseUrl + 'deletePage/' + page[self.index],function(){
      delete self.pages[page[self.index]];
      for(var i =0; i < self.pageOrder.length; i++){
        if(self.pageOrder[i] == page[self.index]) {
          self.pageOrder.splice(i, 1);
          break;
        }
      }
      $(document).trigger("updatedPageList");
    });
  };
  
  this.getPageList = function(){
    var response = [];
    for(var i =0; i < self.pageOrder.length; i++){
      if(self.pageOrder[i] in self.pages) response.push(self.pages[self.pageOrder[i]]);
    }
    return response;
  };
  
  this.getPageTypesList = function(){
    var response = [];
    for(var i =0; i < self.pageTypesOrder.length; i++){
      if(self.pageTypesOrder[i] in self.pageTypes) response.push(self.pageTypes[self.pageTypesOrder[i]]);
    }
    return response;
  };
  
  this.getPageTypeByClassName = function(name){
    for(var i =0; i < self.pageTypesOrder.length; i++){
      if(self.pageTypesOrder[i] in self.pageTypes && self.pageTypes[self.pageTypesOrder[i]].class == name) return self.pageTypes[self.pageTypesOrder[i]];
    }
    return false;
  };
  
  this.getElementTypesList = function(){
    var response = [];
    for(var i =0; i < self.elementTypesOrder.length; i++){
      if(self.elementTypesOrder[i] in self.elementTypes) response.push(self.elementTypes[self.elementTypesOrder[i]]);
    }
    return response;
  };
  
  this.getPagePreview = function(pageID){
    var div = $('<div>');
    $.ajax({
      url: self.baseUrl + 'previewPage/' +pageID,
      async: false,
      success: function(html){
        div.html(html);
      }
    });
    return div;
  };
  
  this.newElement = function(page, elementType){
    var newElement = {
        id: 'newElement'+this.IdCounter++,
        title: 'New ' + elementType.name,
        elementType: elementType.id,
        format: '',
        instructions: '',
        defaultValue: '',
        required: true,
        min: null,
        max: null,
        weight: null
    };
    var Element = new window[elementType.class]();
    Element.init(newElement, page);
    page.addElement(Element);
  };
  
  this.newListItem = function(page, element, value){
    element.addListItem({id:'newListItem'+this.IdCounter++,value: value, active: 1});
    page.isModified = true;
  };
  
  this.newBranchingPage = function(pageTypeID, parentPage){
    var uniqueID = 'newbranchingpage' + this.IdCounter++;
    var newPage = {
        applicationPageId: null,
        pageId: uniqueID,
        type: this.pageTypes[pageTypeID].class,
        pageType: pageTypeID,
        title: "New Branch",
        min: 0,
        max: 0,
        optional: false,
        instructions: '',
        leadingText: '',
        trailingText: '',
        elements: [],
        variables: [],
        children: []
    };
    var Branch = this.createPageObject(newPage);
    Branch.isModified = true;
    parentPage.addChild(Branch);
  };
  
  this.checkPageExists = function(id){
    return (id in this.pages);
  };
  
  this.save = function(){
    var update = false;
    for(var i =0; i < self.pageOrder.length; i++){
      var page = self.pages[self.pageOrder[i]];
      if(page.checkModified()){
        update = true;
        var obj = page.getDataObject();
        $.post(self.baseUrl + 'savePage/' + obj[self.index],{data: obj}, self.refreshPageList);
      }
    };
  };
}