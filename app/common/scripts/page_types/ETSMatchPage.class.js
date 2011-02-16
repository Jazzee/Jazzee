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
  ApplyPage.prototype.workspace.call(this);
  $('#workspace-right-top').append(this.selectListBlock('showAnswerStatus', 'Answer Status is', {0:'Not Shown',1:'Shown'}));
  $('#workspace-right-top').append(this.selectListBlock('optional', 'This page is', {0:'Required',1:'Optional'}));
  
  var min = {0: 'No Minimum'};
  for(var i = 1; i<=50;i++){
    min[i] = i;
  }
  $('#workspace-right-top').append(this.selectListBlock('min','Minimum Scores Required:', min));
  var max = {0: 'No Maximum'};
  for(var i = 1; i<=50;i++){
    max[i] = i;
  }
  $('#workspace-right-top').append(this.selectListBlock('max','Maximum Scores Allowed:', max));
};