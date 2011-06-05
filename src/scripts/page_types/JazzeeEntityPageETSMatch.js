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
  $('#workspace-right-top').append(this.selectListBlock('answerStatusDisplay', 'Answer Status is', {0:'Not Shown',1:'Shown'}));
  $('#workspace-right-top').append(this.selectListBlock('isRequired', 'This page is', {1:'Required',0:'Optional'}));
  
  var min = {null: 'No Minimum'};
  for(var i = 1; i<=50;i++){
    min[i] = i;
  }
  $('#workspace-right-top').append(this.selectListBlock('min','Minimum Scores Required:', min));
  var max = {null: 'No Maximum'};
  for(var i = 1; i<=50;i++){
    max[i] = i;
  }
  $('#workspace-right-top').append(this.selectListBlock('max','Maximum Scores Allowed:', max));
};