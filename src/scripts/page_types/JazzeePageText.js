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
  this.clearWorkspace();
  $('#workspace-left-top').append(this.titleBlock());
  $('#workspace-left-top').append(this.textAreaBlock('leadingText', 'click to edit'));
  $('#workspace-left-bottom-left').append(this.textAreaBlock('trailingText', 'click to edit'));
  
  $('#workspace-right-top').append(this.copyPageBlock());
  $('#workspace-right-top').append(this.previewPageBlock());
  $('#workspace-right-bottom').append(this.deletePageBlock());
  $('#workspace').show('slide');
};