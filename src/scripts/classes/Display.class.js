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

Display.prototype.showCreatedAt = function(){
  this.display.isCreatedAtDisplayed = true;
};

Display.prototype.hideCreatedAt = function(){
  this.display.isCreatedAtDisplayed = false;
};

Display.prototype.isCreatedAtDisplayed = function(){
  return this.display.isCreatedAtDisplayed;
};

Display.prototype.showEmail = function(){
  this.display.isEmailDisplayed = true;
};

Display.prototype.hideEmail = function(){
  this.display.isEmailDisplayed = false;
};

Display.prototype.isEmailDisplayed = function(){
  return this.display.isEmailDisplayed;
};

Display.prototype.showFirstName = function(){
  this.display.isFirstNameDisplayed = true;
};

Display.prototype.hideFirstName = function(){
  this.display.isFirstNameDisplayed = false;
};

Display.prototype.isFirstNameDisplayed = function(){
  return this.display.isFirstNameDisplayed;
};

Display.prototype.showHasPaid = function(){
  this.display.isHasPaidDisplayed = true;
};

Display.prototype.hideHasPaid = function(){
  this.display.isHasPaidDisplayed = false;
};

Display.prototype.isHasPaidDisplayed = function(){
  return this.display.isHasPaidDisplayed;
};

Display.prototype.showLastLogin = function(){
  this.display.isLastLoginDisplayed = true;
};

Display.prototype.hideLastLogin = function(){
  this.display.isLastLoginDisplayed = false;
};

Display.prototype.isLastLoginDisplayed = function(){
  return this.display.isLastLoginDisplayed;
};

Display.prototype.showLastName = function(){
  this.display.isLastNameDisplayed = true;
};

Display.prototype.hideLastName = function(){
  this.display.isLastNameDisplayed = false;
};

Display.prototype.isLastNameDisplayed = function(){
  return this.display.isLastNameDisplayed;
};

Display.prototype.showPercentComplete = function(){
  this.display.isPercentCompleteDisplayed = true;
};

Display.prototype.hidePercentComplete = function(){
  this.display.isPercentCompleteDisplayed = false;
};

Display.prototype.isPercentCompleteDisplayed = function(){
  return this.display.isPercentCompleteDisplayed;
};

Display.prototype.showUpdatedAt = function(){
  this.display.isUpdatedAtDisplayed = true;
};

Display.prototype.hideUpdatedAt = function(){
  this.display.isUpdatedAtDisplayed = false;
};

Display.prototype.isUpdatedAtDisplayed = function(){
  return this.display.isUpdatedAtDisplayed;
};

Display.prototype.showIsLocked = function(){
  this.display.isIsLockedDisplayed = true;
};

Display.prototype.hideIsLocked = function(){
  this.display.isIsLockedDisplayed = false;
};

Display.prototype.isIsLockedDisplayed = function(){
  return this.display.isIsLockedDisplayed;
};

/**
 * Get the display objext
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
        title: this.title,
        type: this.page.type['class']
      };
      pages.push(page);
    }
  });
  
  return pages;
};

Display.prototype.getPageTitle = function(pageId){
  var self = this;
  var title = null;
  $.each(this.application.listApplicationPages(), function(){
    if(pageId == this.page.id){
	  title = this.page.title;
    }
  });
  
  return title;
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
 * Get the elements on a page
 * @param int pageId
 * @return []
 */
Display.prototype.getPageElements = function(pageId){
  var self = this;
  var elements = [];
  $.each(this.application.listPageElements(pageId), function(){
    if(self.displayElement(this.id)){
      elements.push(
        {
          title: this.title,
          type: this.type['class'],
          id: this.id
        }
      );
    }
  });
  
  return elements;
};

/**
 * Get the display name
 */
Display.prototype.getName = function(){
  return this.display.name;
};

/**
 * Get the display type
 */
Display.prototype.getType = function(){
  return this.display.type;
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
 * Check if an element should be displayed
 */
Display.prototype.displayElement = function(elementId){
  return ($.inArray(elementId, this.display.elements) > -1);
};

/**
 * Check if an element should be displayed
 */
Display.prototype.addElement = function(elementId){
  if(!this.displayElement(elementId)){
    this.display.elements.push(elementId);
  }
};

/**
 * Check if an element should be displayed
 */
Display.prototype.removeElement = function(elementId){
  if(this.displayElement(elementId)){
    this.display.elements = $.grep(this.display.elements, function(value) {
      return value != elementId;
    });
  }
};