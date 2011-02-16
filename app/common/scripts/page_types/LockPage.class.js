/**
 * The LockPage type
  @extends ApplyPage
 */
function LockPage(){}
LockPage.prototype = new ApplyPage();
LockPage.prototype.constructor = LockPage;

/**
 * Create the LockPage workspace
 */
LockPage.prototype.workspace = function(){
  this.clearWorkspace();
  $('#workspace-left-top').parent().addClass('form');
  $('#workspace-left-top').append(this.titleBlock());
  $('#workspace-left-top').append(this.textAreaBlock('Leading Text','leadingText'));
  $('#workspace-left-top').append(this.textAreaBlock('Instructions','instructions'));
  $('#workspace-left-bottom-left').append(this.textAreaBlock('Trailing Text','trailingText'));
  
  $('#workspace-right-top').append(this.copyPageBlock());
  $('#workspace-right-top').append(this.previewPageBlock());
  
  $('#workspace-right-bottom').append(this.deletePageBlock());
  
};