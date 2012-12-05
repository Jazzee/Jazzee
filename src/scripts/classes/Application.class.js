/**
 * Application Structure
 * Takes application JSON and adds some nice methods for accessing the data
 * 
 * @param {} obj
 */
function Application(obj){
  this.application = obj;
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