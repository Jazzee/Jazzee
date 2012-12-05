/**
 * Displays class
 * Takes an applicant display json and adds convienece methods for getting
 * info
 * 
 * @param {} obj
 * @param {Application} application
 */
function Display(obj, application){
  this.display = obj;
  this.application = application;
};

/**
 * List the pages
 */
Display.prototype.getObj = function(){
  return this.display;
};

/**
 * List the pages
 */
Display.prototype.getPages = function(){
  var self = this;
  var pages = [];
  $.each(this.application.listApplicationPages(), function(){
    if(self.displayPage(this.page.id)){
      var page = {
        id: this.page.id,
        title: this.title
      };
      pages.push(page);
    }
  });
  
  return pages;
};

/**
 * List the pages
 */
Display.prototype.listPageTitles = function(){
  var self = this;
  var titles = [];
  $.each(this.application.listApplicationPages(), function(){
    if(self.displayPage(this.page.id)){
      titles.push(this.title);
    }
  });
  
  return titles;
};

/**
 * List the pages
 */
Display.prototype.listPageElementTitles = function(pageId){
  var self = this;
  var titles = [];
  $.each(this.application.listPageElements(pageId), function(){
    if(self.displayElement(this.id)){
      titles.push(this.title);
    }
  });
  
  return titles;
};

/**
 * Get the display name
 */
Display.prototype.getName = function(){
  return this.display.name;
};

/**
 * Get the display name
 */
Display.prototype.getId = function(){
  return this.display.id;
};

/**
 * Check if a page should be displayed
 */
Display.prototype.displayPage = function(pageId){
  return ($.inArray(pageId, this.display.pages) > -1);
};

/**
 * Check if a page should be displayed
 */
Display.prototype.displayElement = function(elementId){
  return ($.inArray(elementId, this.display.elements) > -1);
};