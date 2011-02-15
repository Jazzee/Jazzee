/**
 * The ETSMatchPage type
  @extends ApplyPage
 */
function ETSMatchPage(){}
ETSMatchPage.prototype = new ApplyPage();
ETSMatchPage.prototype.constructor = ETSMatchPage;

/**
 * Create the ETSMatchPage workspace
 */
ETSMatchPage.prototype.workspace = function(){
  this.clearWorkspace();
  $('#workspace-left-top').parent().addClass('form');
  $('#workspace-left-top').append(this.titleBlock());
  $('#workspace-left-top').append(this.textAreaBlock('Leading Text','leadingText'));
  $('#workspace-left-top').append(this.textAreaBlock('Instructions','instructions'));
  $('#workspace-left-bottom-left').append(this.textAreaBlock('Trailing Text','trailingText'));
  
  $('#workspace-right-top').append(this.previewPageBlock());
  $('#workspace-right-top').append(this.selectListBlock('optional', 'This page is', {0:'Required',1:'Optional'}));
  $('#workspace-right-top').append(this.selectListBlock('showAnswerStatus', 'Answer Status is', {0:'Not Shown',1:'Shown'}));
  
  $('#workspace-right-bottom').append(this.deletePageBlock());
  
};