/**
 * The JazzeePageText type
  @extends JazzeePage
 */
function JazzeePageText(){}
JazzeePageText.prototype = new JazzeePage();
JazzeePageText.prototype.constructor = JazzeePageText;

/**
 * Create the page workspace
 * For text pages we don't have a lot of options
 */
JazzeePageText.prototype.workspace = function(){
  JazzeePage.prototype.workspace.call(this);
  $('div.form', '#workspace').remove();
};