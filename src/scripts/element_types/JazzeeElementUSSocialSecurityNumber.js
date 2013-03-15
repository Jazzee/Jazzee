/**
 * The JazzeeElementUSSocialSecurityNumber type
  @extends JazzeeElement
 */
function JazzeeElementUSSocialSecurityNumber(){}
JazzeeElementUSSocialSecurityNumber.prototype = new JazzeeElement();
JazzeeElementUSSocialSecurityNumber.prototype.constructor = JazzeeElementUSSocialSecurityNumber;


/**
 * Override JazzeeElement new to set max and format default
 * @param {String} id the id to use
 * @returns {JazzeeElementPDFFileInput}
 */
JazzeeElementUSSocialSecurityNumber.prototype.newElement = function(id,title,typeId,typeName,typeClass,status,page){
  var element = JazzeeElementEncryptedTextInput.prototype.newElement.call(this,id,title,typeId,typeName,typeClass,status,page);
  element.setProperty('min', 9);
  element.setProperty('max', 9);
  element.setProperty('format', '');
  return element;
};

JazzeeElementUSSocialSecurityNumber.prototype.avatar = function(){
  return $('<input type="text" disabled="true">');
};