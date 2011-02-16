/**
 * The BranchingPage type
  @extends ApplyPage
 */
function BranchingPage(){}
BranchingPage.prototype = new ApplyPage();
BranchingPage.prototype.constructor = BranchingPage;

BranchingPage.prototype.workspace = function(){
  //call the parent workspace method
  ApplyPage.prototype.workspace.call(this);
  $('#workspace-right-top').append(this.selectListBlock('showAnswerStatus', 'Answer Status is', {0:'Not Shown',1:'Shown'}));
  $('#workspace-right-top').append(this.selectListBlock('optional', 'This page is', {0:'Required',1:'Optional'}));
  
  $('#workspace-left-middle-left').show();
  $('#workspace-left-middle-left').html(this.listBranchingPagesBlock());
};

BranchingPage.prototype.listBranchingPagesBlock = function(){
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
    div.replaceWith(pageClass.listBranchingPagesBlock());
  });
  return div.append(ol).append(p);
};