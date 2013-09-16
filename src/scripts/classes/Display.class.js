/**
 * Displays class
 * Takes an applicant display json and adds convienece methods for getting
 * info
 * 
 * @param {} obj
 * @param {Application} application
 */
function Display(obj, application){
  var self = this;
  this.display = obj;
  this.application = application;
};

/**
 * Get the display objext
 */
Display.prototype.getObj = function(){
  return this.display;
};

/**
 * Get the display objext
 */
Display.prototype.getApplication = function(){
  return this.application;
};

/**
 * List the elements
 */
Display.prototype.listElements = function(){
  this.display.elements.sort(function(a, b) { 
    return a.weight - b.weight;
  });
  return this.display.elements;
};

/**
 *  Loop through application tags, filtering for those in our maximum display.
 */
Display.prototype.listTags = function(){
    var tags = [];
    var self = this;
    // we get tag objects from the server and need to add type and name properties
    // so that they can function as display elements when we pass them to
    // the this.displayElement(...) method.
    $.each(this.getApplication().listTags(), function(){
	    this.type = "applicant";
	    this.name = this.id;
	    if(self.displayElement(this)){
		tags.push(this);
	    }
	});
    return tags;
};


/**
 * Get the display name
 */
Display.prototype.getName = function(){
  return this.display.name;
};

/**
 * Set the display name
 * @param String name
 */
Display.prototype.setName = function(name){
  this.display.name = name;
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
 * Get the display name
 */
Display.prototype.getClass = function(){
  return this.display.class;
};

/**
 * Check if a page should be displayed
 */
Display.prototype.displayPage = function(pageId){
  return ($.inArray(pageId, this.display.pages) > -1);
};

/**
 * Check if an element should be displayed
 * @param type
 * @param name
 */
Display.prototype.displayElement = function(obj){
  var matches = $.grep(this.display.elements, function(element) {

    switch(obj.type){
      case 'applicant':
      case 'element':
        return element.type == obj.type && element.name == obj.name;
        break;
      case 'page':
        return element.type == obj.type && element.name == obj.name && element.pageId == obj.pageId;
        break;
        
    }
    
  });

  return (matches.length > 0);
};

/**
 * Check if an element should be displayed
 */
Display.prototype.addElement = function(obj){
  if(this.displayElement(obj)){
    this.removeElement(obj);
  }
  this.display.elements.push(obj);
};

/**
 * Remove an element from the display
 */
Display.prototype.removeElement = function(obj){
  if(this.displayElement(obj)){
    this.display.elements = $.grep(this.display.elements, function(element) {
      switch(obj.type){
      case 'applicant':
      case 'element':
        return element.type != obj.type || element.name != obj.name;
        break;
      case 'page':
        return element.type != obj.type || element.name != obj.name || element.pageId != obj.pageId;
        break;
        
    }
      
    });
  }
};

/**
 * Get the title for an elements page
 */
Display.prototype.getElementPageTitle = function(obj){
  if(this.displayElement(obj)){
    if(obj.type == 'element'){
      var element = this.application.getElementClassById(obj.name);
      var applicationPage = this.application.getApplicationPageByPageId(element.page.id);
    } else if(obj.type == 'page'){
      var applicationPage = this.application.getApplicationPageByPageId(obj.pageId);
    }
    return applicationPage.title;
  }
  return '';
};