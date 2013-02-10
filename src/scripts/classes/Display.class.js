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
  return this.display.elements;
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
 * @param type
 * @param name
 */
Display.prototype.displayElement = function(type, name){
  var matches = $.grep(this.display.elements, function(element) {
    return element.type == type && element.name == name;
  });

  return (matches.length > 0);
};

/**
 * Check if an element should be displayed
 */
Display.prototype.addElement = function(type, title, weight, name){
  if(this.displayElement(type, name)){
    this.removeElement(type, name);
  }
  this.display.elements.push({type: type, title: title, weight: weight, name: name});
};

/**
 * Remove an element from the display
 */
Display.prototype.removeElement = function(type, name){
  if(this.displayElement(type, name)){
    this.display.elements = $.grep(this.display.elements, function(element) {
      return element.type != type || element.name != name;
    });
  }
};