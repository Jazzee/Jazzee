/**
 * The ETSMatchPage type
  @extends ApplyPage
 */
function JazzeeEntityPageETSMatch(){}
JazzeeEntityPageETSMatch.prototype = new JazzeePage();
JazzeeEntityPageETSMatch.prototype.constructor = JazzeeEntityPageETSMatch;

/**
 * Create the ETSMatchPage workspace
 */
JazzeeEntityPageETSMatch.prototype.workspace = function(){
  JazzeePage.prototype.workspace.call(this);
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