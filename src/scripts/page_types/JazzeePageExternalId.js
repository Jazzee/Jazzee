/**
 * The JazzeePageExternalId type
  @extends JazzeePage
 */
function JazzeePageExternalId(){}
JazzeePageExternalId.prototype = new JazzeePage();
JazzeePageExternalId.prototype.constructor = JazzeePageExternalId;

/**
 * Create a new ExternalIdPage with good default values
 * @param {String} id the id to use
 * @returns {ExternalIdPage}
 */
JazzeePageExternalId.prototype.newPage = function(id,title,typeId,typeName,typeClass,status,pageBuilder){
  var page = JazzeePage.prototype.newPage.call(this, id,title,typeId,typeName,typeClass,status,pageBuilder);
  return page;
};

JazzeePageExternalId.prototype.workspace = function(){
  JazzeePage.prototype.workspace.call(this);
  var pageClass = this;
};

/**
 * Create the page properties dropdown
*/
JazzeePageExternalId.prototype.pageProperties = function(){
  var pageClass = this;

  var div = $('<div>');
  return div;
};

