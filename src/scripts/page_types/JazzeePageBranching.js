/**
 * The JazzeePageBranching type
  @extends JazzeePage
 */
function JazzeePageBranching(){}
JazzeePageBranching.prototype = new JazzeePage();
JazzeePageBranching.prototype.constructor = JazzeePageBranching;

/**
 * Override JazzeePage::newPage to set variable defaults
 * @returns {JazzeePageBranching}
 */
JazzeePageBranching.prototype.newPage = function(id,title,typeId,typeName,typeClass,status,pageBuilder){
  var page = JazzeePage.prototype.newPage.call(this, id,title,typeId,typeName,typeClass,status,pageBuilder);
  page.setVariable('branchingElementLabel', title);
  return page;
};

JazzeePageBranching.prototype.workspace = function(){
  JazzeePage.prototype.workspace.call(this);
  var pageClass = this;
  $('#pageToolbar').append(this.pagePropertiesButton());
  $('#workspace').append(this.listBranchesBlock());
};

/**
 * Create the page properties dropdown
*/
JazzeePageBranching.prototype.pageProperties = function(){
  var pageClass = this;
  
  var div = $('<div>');
  div.append(this.isRequiredButton());
//  div.append(this.showAnswerStatusButton());
//  if(pageClass.answerStatusDisplay == 1){
//    var button = $('<button>').html('Edit Answer Status Display').attr('id', 'editDisplayButton').bind('click', function(e){
//      $('.qtip').qtip('api').hide();
//      pageClass.displayAnswerStatusForm();
//    });
//    button.button({
//      icons: {
//        primary: 'ui-icon-newwin'
//      }
//    });
//    div.append(button);
//  }
//  
//  $('#answerStatusDisplayButton', div).bind('click',function(e){
//    if(pageClass.answerStatusDisplay == 0){
//      pageClass.setVariable('answerStatusTitle',  null);
//      pageClass.setVariable('answerStatusText', null);
//    }
//    //rebuild the tooltip so the edit status display button will show up or be hidden
//    div.replaceWith(pageClass.pageProperties());
//  });
  if(!this.isGlobal || this.pageBuilder.editGlobal) div.append(this.editBranchingElementLabelButton());
  var slider = $('<div>');
  slider.slider({
    value: this.min,
    min: 0,
    max: 20,
    step: 1,
    slide: function( event, ui ) {
      pageClass.setProperty('min', ui.value);
      $('#minValue').html(pageClass.min == 0?'No Minimum':pageClass.min);
    }
  });
  div.append($('<p>').html('Minimum Answers Required ').append($('<span>').attr('id', 'minValue').html(this.min == 0?'No Minimum':this.min)));
  div.append(slider);
  
  
  var slider = $('<div>');
  slider.slider({
    value: this.max,
    min: 0,
    max: 20,
    step: 1,
    slide: function( event, ui ) {
      pageClass.setProperty('max', ui.value);
      $('#maxValue').html(pageClass.max == 0?'No Maximum':pageClass.max);
    }
  });
  div.append($('<p>').html('Maximum Answers Allowed ').append($('<span>').attr('id', 'maxValue').html(this.max == 0?'No Maximum':this.max)));
  div.append(slider);
  
  return div;
};

JazzeePageBranching.prototype.listBranchesBlock = function(){
  var div = $('<div>').append($('<h5>').html('Branched Pages'));
  var pageClass = this;
  var ol = $('<ol>').addClass('page-list');
  for(var i in this.children){
    var branch = this.children[i];
    var li = $('<li>').html(branch.title);
    var button = $('<button>').html('Edit').data('page', branch).bind('click',function(){
      var page = $(this).data('page');
      page.workspace();
      //empty the toolbar becuase the delete/copy are going to be wrong
      $('#pageToolbar .copy').remove();
      $('#pageToolbar .delete').remove();
      $('#pageToolbar .properties').remove();
      var button = $('<button>').html('Back to parent').bind('click', function(){
        pageClass.workspace();
      });
      button.button({
        icons: {
          primary: 'ui-icon-arrowreturnthick-1-s'
        }
      });
      $('#pageToolbar').prepend(button);
      
      var button = $('<button>').html('Delete Branch').data('page', page).bind('click', function(e){
        pageClass.deleteChild($(this).data('page'));
        $('#editPage').effect('explode',500);
        pageClass.workspace();
      });
      button.button({
        icons: {
          primary: 'ui-icon-trash'
        }
      });
      $('#pageToolbar').append(button);
    }).button({
        icons: {
          primary: 'ui-icon-pencil'
        }
      });
    if(!this.isGlobal || this.pageBuilder.editGlobal) li.append(button);
    ol.append(li);
  }
  
  var dropdown = $('<ul>');
  for(var i = 0; i < this.pageBuilder.pageTypes.length; i++){
    var item = $('<a>').html(this.pageBuilder.pageTypes[i].typeName).attr('href', '#').data('pageType', this.pageBuilder.pageTypes[i]);
    item.bind('click', function(e){
      var pageType = $(e.target).data('pageType');
      var branch = new window[pageType.typeClass].prototype.newPage('newpage' + pageClass.pageBuilder.getUniqueId(),'New ' + pageType.typeName + ' Branch',pageType.id,pageType.typeName,pageType.typeClass,'new',pageClass.pageBuilder);
      pageClass.addChild(branch);
      pageClass.markModified();
      div.replaceWith(pageClass.listBranchesBlock());
      return false;
    });
    //for now only allow the standard page
    if(this.pageBuilder.pageTypes[i].typeClass=='JazzeePageStandard')dropdown.append($('<li>').append(item));
  }
  var button = $('<button>').html('New Branch').button();
  button.qtip({
    position: {
      my: 'bottom-left',
      at: 'bottom-right'
    },
    show: {
      event: 'click'
    },
    hide: {
      event: 'unfocus click',
      fixed: true
    },
    content: {
      text: dropdown,
      title: {
        text: 'Choose a page type',
        button: true
      }
    }
  });
  div.append(ol);
  if(!this.isGlobal || this.pageBuilder.editGlobal) div.append(button);
  return div;
};

/**
 * Edit the branchign label button
 * @return {jQuery}
 */
JazzeePageBranching.prototype.editBranchingElementLabelButton = function(){
  var pageClass = this;
  var obj = new FormObject();
  var field = obj.newField({name: 'legend', value: 'Edit Branching Label'});
  var element = field.newElement('TextInput', 'branchingElementLabel');
  element.label = 'Label';
  element.required = true;
  element.value = this.getVariable('branchingElementLabel');
  var dialog = this.displayForm(obj);
  $('form', dialog).bind('submit',function(e){
    pageClass.setVariable('branchingElementLabel', $('input[name="branchingElementLabel"]', this).val());
    pageClass.workspace();
    dialog.dialog("destroy").remove();
    return false;
  });//end submit
  var button = $('<button>').html('Edit Branching Element Label').bind('click',function(){
    $('.qtip').qtip('api').hide();
    dialog.dialog('open');
  }).button({
    icons: {
      primary: 'ui-icon-pencil'
    }
  });
  return button;
};