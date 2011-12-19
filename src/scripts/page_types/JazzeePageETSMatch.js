/**
 * The ETSMatchPage type
  @extends ApplyPage
 */
function JazzeePageETSMatch(){}
JazzeePageETSMatch.prototype = new JazzeePage();
JazzeePageETSMatch.prototype.constructor = JazzeePageETSMatch;

/**
 * Create the ETSMatchPage workspace
 */
JazzeePageETSMatch.prototype.workspace = function(){
  JazzeePage.prototype.workspace.call(this);
  $('#workspace-right-top').append(this.selectListBlock('answerStatusDisplay', 'Answer Status is', {0:'Not Shown',1:'Shown'}));
  $('#workspace-right-top').append(this.selectListBlock('isRequired', 'This page is', {1:'Required',0:'Optional'}));
  
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