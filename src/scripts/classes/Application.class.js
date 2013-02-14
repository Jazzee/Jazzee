/**
 * Application Structure
 * Takes application JSON and adds some nice methods for accessing the data
 * 
 * @param {} obj
 */
function Application(obj){
  this.application = obj;
  this.pageTypes = {};
  this.elementTypes = {};
};

/**
 * Get pages in application
 */
Application.prototype.listApplicationPages = function(){
  return this.application.applicationPages;
};

/**
 * Get elements for a page
 */
Application.prototype.listPageElements = function(pageId){
  var applicationPage = this.getApplicationPageByPageId(pageId);
  if(applicationPage){
    return applicationPage.page.elements;
  }

  return [];
};

/**
 * Get applicationPage by page id
 * @param pageId
 */
Application.prototype.getApplicationPageByPageId = function(pageId){
  for(var i = 0; i < this.application.applicationPages.length; i++){
    if(this.application.applicationPages[i].page.id == pageId){
      return this.application.applicationPages[i];
    }
  }

  return false;
};

/**
 * List all of the tags
 */
Application.prototype.listTags = function(){
  return this.application.tags;
};

/**
 * Get page class by ID
 * @param pageId
 */
Application.prototype.getPageClassById = function(pageId){
  if(this.pageTypes['p'+pageId] == undefined){
    for(var i = 0; i < this.application.applicationPages.length; i++){
      if(this.application.applicationPages[i].page.id == pageId){
        var className = this.application.applicationPages[i].page.type['class'].replace(/\\/g, '');
        this.pageTypes['p'+pageId] = new window[className];
        this.pageTypes['p'+pageId].init(this.application.applicationPages[i].page, null);

      }
    }
  }

  return this.pageTypes['p'+pageId];
};

/**
 * Get element class by ID
 * @param elementId
 */
Application.prototype.getElementClassById = function(elementId){
  if(this.elementTypes['e'+elementId] == undefined){
    for(var i = 0; i < this.application.applicationPages.length; i++){
      for(var j = 0; j < this.application.applicationPages[i].page.elements.length; j++){
        if(this.application.applicationPages[i].page.elements[j].id == elementId){
          var className = this.application.applicationPages[i].page.elements[j].type['class'].replace(/\\/g, '');
          this.elementTypes['e'+elementId] = new window[className];
          this.elementTypes['e'+elementId].init(this.application.applicationPages[i].page.elements[j], null);
        }
      }
    }
  }

  return this.elementTypes['e'+elementId];
};