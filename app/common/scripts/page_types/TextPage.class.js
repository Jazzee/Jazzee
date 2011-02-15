/**
 * The TextPage type
  @extends ApplyPage
 */
function TextPage(){}
TextPage.prototype = new ApplyPage();
TextPage.prototype.constructor = TextPage;

/**
 * Create the page workspace
 * For text pages we don't have a lot of options
 */
TextPage.prototype.workspace = function(){
  this.clearWorkspace();
  $('#workspace-left-top').parent().addClass('form');
  $('#workspace-left-top').append(this.titleBlock());
  $('#workspace-left-top').append(this.textAreaBlock('Leading Text','leadingText'));
  $('#workspace-left-bottom-left').append(this.textAreaBlock('Trailing Text','trailingText'));
  
  $('#workspace-right-top').append(this.previewPageBlock());
  $('#workspace-right-bottom').append(this.deletePageBlock());
  
};