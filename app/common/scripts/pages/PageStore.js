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
  this.pages = [];
  //retain the page order from the server since we store by ID
  this.pageOrder = [];
  
  this.pageTypes = [];
  
  this.elementTypes = [];
  
  this.init = function(baseUrl){
    this.baseUrl = baseUrl;
    this.fillElementTypesList();
    this.fillPageTypesList();
    this.refreshPageList();
  }
  
  this.refreshPageList = function(){
    $.get(self.baseUrl + 'listPages',function(json){  
        self.pages = [];
        self.pageOrder = [];
        $(json.data.result).each(function(i){
          var Page = new window[this.type]();
          Page.init(this, self);
          $(this.elements).each(function(i,element){
            var Element = new window[element.type]();
            Element.init(element, Page);
            $(element.list).each(function(i,item){
              Element.addListItem(item.id,item.value);
            });
            Page.addElement(Element);
          });
          self.pages[this.id] = Page;
          self.pageOrder.push(this.id);
        });
        $(document).trigger("updatedPageList");
    });
  }
  
  this.fillElementTypesList = function(){
    $.ajax({
      url: self.baseUrl + 'listElementTypes',
      async: false,
      success: function(json){
        $(json.data.result).each(function(i){
          self.elementTypes[this.id] = this.name;
        });
      }
    });
  }
  
  this.fillPageTypesList = function(){
    $.ajax({
      url: self.baseUrl + 'listPageTypes',
      async: false,
      success: function(json){
        $(json.data.result).each(function(i){
          self.pageTypes[this.id] = this.name;
        });
      }
    });
  }
  
  this.addPage = function(type){
    $.post(self.baseUrl + 'addPage',{pageType: type}, self.refreshPageList);
  }
  
  this.deletePage = function(pageID){
    $.get(self.baseUrl + 'deletePage/' + pageID, self.refreshPageList);
  }
  
  this.deleteElement = function(elementID){
    $.get(self.baseUrl + 'deleteElement/' + elementID, self.refreshPageList);
  }
  
  this.getPageList = function(){
    var response = [];
    for(var i =0; i < self.pageOrder.length; i++){
      response.push(self.pages[self.pageOrder[i]]);
    }
    return response;
  }
  
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
  }
  
  this.addElement = function(pageID, elementID){
    $.post(self.baseUrl + 'addElement/' + pageID,{type: elementID}, self.refreshPageList);
  }
  
  this.addListItem = function(pageID, elementID, value){
    $.post(self.baseUrl + 'addListItem/' + pageID +'/' + elementID,{value: value}, self.refreshPageList);
  }
  
  this.checkPageExists = function(pageID){
    return (jQuery.inArray(pageID, this.pageOrder) == -1)?false:true;
  }
  
  this.save = function(pageID){
    if(this.pages[pageID].isModified){
      var obj = this.pages[pageID].getDataObject();
      $.post(self.baseUrl + 'savePage/' + pageID,{data: obj});
      $(document).trigger("updatedPageList");
    }
  }
  
  this.saveAll = function(){
    var update = false;
    $(this.pages).each(function(){
      if(this.isModified){
        update = true;
        var obj = this.getDataObject();
        $.post(self.baseUrl + 'savePage/' + this.id,{data: obj});
      }
    });
    if(update) this.refreshPageList();
  }
}