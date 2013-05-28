/**
 * List Item containes the pieces of a ListElement
 */
function ListItem(){
  this.id;
  this.element;
  this.weight;
  this.isActive;
  this.value;
  this.name;
  this.variables;
  
  this.status;
  this.isModified;
};

/**
 * Initialize the object
 * @param {Object} obj
 * @param {JazzeeElement} element
 */
ListItem.prototype.init = function(obj, element){
  var self = this;
  this.element = element;
  this.id = obj.id;
  this.weight = obj.weight;
  this.isActive = obj.isActive;
  this.value = obj.value;
  this.name = obj.name;
  
  this.variables = {};
  
  $(obj.variables).each(function(){
    self.variables[this.name] = {name : this.name, value: this.value};
  });
  
  this.isModified = false;
  this.status = '';
};

/**
 * Set a property and mark the item as modified
 * @param {String} name
 * @param {Mixed} value
 * @return {Mixed}
 */
ListItem.prototype.setProperty = function(name, value){
  if(typeof this[name] == 'undefined' || this[name] !== value){
    this[name] = value;
    this.markModified();
  }
  return this[name];
};

/**
 * Check to see if the JazzeeElement has been modified
 * @returns {Boolean}
 */
ListItem.prototype.checkIsModified = function(){
  return this.isModified;
};

/**
 * Mark this item as modified
 */
ListItem.prototype.markModified = function(){
  this.isModified = true;
  this.element.markModified();
};


/**
 * Set an item variable
 * @param {String} name
 * @param {String} value
 * @returns {Object} the varialbe we created
 */
ListItem.prototype.setVariable = function(name, value){
  //only set the variable and mark as modified if it is new or different
  if(typeof this.variables[name] == 'undefined'  || this.variables[name].value !== value){
    this.variables[name] = {name : name, value: value};
    this.markModified();
  }
  return this.variables[name];
};

/**
 * Get a variable value
 * @param {String} name
 * @returns {String|Null}
 */
ListItem.prototype.getVariable = function(name){
  if(name in this.variables) return this.variables[name].value;
  console.log(name + ' is not a set variable');
  return null;
};

/**
 * Get an object suitable for json
 * @returns {Object}
 */
ListItem.prototype.getDataObject = function(){
  var obj = {
    id: this.id,
    weight: this.weight,
    isActive: this.isActive,
    value: this.value,
    name: this.name,
    variables: this.variables,
    status: this.status
  };
  return obj;
};