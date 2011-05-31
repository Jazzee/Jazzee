/**
 * The JazzeeEntityPageBranching type
  @extends ApplyPage
 */
function JazzeeEntityPageBranching(){}
JazzeeEntityPageBranching.prototype = new JazzeePage();
JazzeeEntityPageBranching.prototype.constructor = JazzeeEntityPageBranching;

/**
 * Override AplyPage::newPage to set varialbe defaults
 * @param {String} id the id to use
 * @returns {JazzeeEntityPageBranching}
 */
JazzeeEntityPageBranching.prototype.newPage = function(id,title,pageType,pageClass,status,pageStore){
  var page = ApplyPage.prototype.newPage.call(this, id,title,pageType,pageClass,status,pageStore);
  page.setVariable('branchingElementLabel', title);
  return page;
};

JazzeeEntityPageBranching.prototype.workspace = function(){
  //call the parent workspace method
  JazzeePage.prototype.workspace.call(this);
  $('#workspace-right-top').append(this.selectListBlock('showAnswerStatus', 'Answer Status is', {0:'Not Shown',1:'Shown'}));
  $('#workspace-right-top').append(this.selectListBlock('optional', 'This page is', {0:'Required',1:'Optional'}));
  
  $('#workspace-left-middle-left').show();
  $('#workspace-left-middle-left').append(this.textInputVariableBlock('branchingElementLabel', 'Branching Element Label: ', 'click to edit'));
  $('#workspace-left-middle-left').append(this.listJazzeeEntityPageBranchingsBlock());
};

JazzeeEntityPageBranching.prototype.listJazzeeEntityPageBranchingsBlock = function(){
  var div = $('<div>').append($('<h5>').html('Branched Pages'));
  var pageClass = this;
  var ol = $('<ol>').addClass('page-list');
  for(var i in this.children){
    var branch = this.children[i];
    var li = $('<li>').html(branch.title);
    li.data('page', branch);
    $(li).bind('click',function(){
      var page = $(this).data('page');
      page.workspace();
      //get rid of the delete pages box and add a delete branch box
      var deletep = $('<p>Delete this branch</p>').addClass('delete').bind('click',{branch: page}, function(e){
          $('#workspace').effect('explode',500);
          pageClass.deleteChild(e.data.branch);
          pageClass.workspace();
      });
      $('#workspace-right-bottom p.delete').remove();
      $('#workspace-right-bottom').append(deletep);
    });
    ol.append(li);
  }
  var p = $('<p>').addClass('add').html('New Branch').bind('click',function(){
    var branch = new StandardPage.prototype.newPage('newpage' + pageClass.pageStore.getUniqueId(),'New Branch','StandardPage','new',pageClass.pageStore);
    pageClass.addChild(branch);
    div.replaceWith(pageClass.listJazzeeEntityPageBranchingsBlock());
  });
  return div.append(ol).append(p);
};