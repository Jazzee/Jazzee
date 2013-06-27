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
 * List all of the tags
 */
Application.prototype.listTemplates = function(){
  return this.application.templates;
};

/**
 * Get page class by ID
 * @param pageId
 */
Application.prototype.getPageClassById = function(pageId){
  if(this.pageTypes['p'+pageId] == undefined){
    for(var i = 0; i < this.application.applicationPages.length; i++){
      if(this.application.applicationPages[i].page.id == pageId){
        this.pageTypes['p'+pageId] = this.createPageObject(this.application.applicationPages[i].page);
      }
    }
  }

  return this.pageTypes['p'+pageId];
};

/**
 * Create an JazzeePage object
 * Calls itself recursivly for child pages
 * @param {Object} obj
 * @returns {JazzeePage}
 */
Application.prototype.createPageObject = function(page){
  var self = this;
  var className = page.type['class'].replace(/\\/g, '');
  var pageClass = new window[className]();
  pageClass.init(page, null);
  $(page.elements).each(function(i,element){
    var className = element.type['class'].replace(/\\/g, '');
    var elementClass = new window[className]();
    elementClass.init(element, pageClass);
    $(element.list).each(function(){
      elementClass.addListItem(this);
    });
    pageClass.addElement(elementClass);
  });
  $(page.variables).each(function(){
    pageClass.variables[this.name] = {name : this.name, value: this.value};
  });
  $(page.children).each(function(){
    pageClass.addChild(self.createPageObject(this));
  });
  pageClass.isModified = false; //reset isModified now that we have added cildren and varialbes
  return pageClass;
};

/**
 * Get element class by ID
 * @param elementId
 */
Application.prototype.getElementClassById = function(elementId){
  if(this.elementTypes['e'+elementId] == undefined){
    for(var i = 0; i < this.application.applicationPages.length; i++){
      var elementObject = this.findElementInPage(this.application.applicationPages[i].page, elementId);
      if(elementObject){
        this.elementTypes['e'+elementId] = elementObject;
        break;
      }
    }
  }

  return this.elementTypes['e'+elementId];
};

/**
 * Get element class by ID
 * @param elementId
 */
Application.prototype.findElementInPage = function(page, elementId){
  for(var i = 0; i < page.elements.length; i++){
    if(page.elements[i].id == elementId){
      var element = page.elements[i];
      var className = element.type['class'].replace(/\\/g, '');
      var elementObject = new window[className];
      elementObject.init(element, page);

      return elementObject;
    }
  }
  for(var i in page.children){
    var result = this.findElementInPage(page.children[i], elementId);
    if(result){
      return result;
    }
  }

  return false;
};