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
  div.append(this.editNameButton());
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

/**
 * List all a pages elements
 */
JazzeePageBranching.prototype.listDisplayElements = function(){
  var self = this;
  var elements = [];
  elements.push({name: 'branchingPageSelection', type: 'page', title: this.getVariable('branchingElementLabel')?this.getVariable('branchingElementLabel'):this.title, pageId: this.id});
  for(var i in this.children){
    $(this.children[i].listDisplayElements()).each(function(){
      if(this.type != 'page'  && (this.name != 'attachment' || this.name != 'answerPublicStatus' || this.name != 'answerPublicStatus')){
        var title = self.children[i].title + ' ' + this.title; 
        elements.push({name: this.name, title: title, type: this.type});
      }   
    });
    
  }
  elements.push({name: 'attachment', type: 'page', title: this.title + ' Attachment', pageId: this.id});
  elements.push({name: 'publicAnswerStatus', type: 'page', title: this.title + ' Public Answer Status', pageId: this.id, sType: 'numeric'});
  elements.push({name: 'privateAnswerStatus', type: 'page', title: this.title + ' Private Answer Status', pageId: this.id, sType: 'numeric'});

  return elements;
};

/**
 * Dispaly applicant data in a grid
 */
JazzeePageBranching.prototype.gridData = function(data, type, full){
  var values = [];
  switch(data.displayElement.name){
    case 'branchingPageSelection':
      var answers = data.applicant.getAnswersForPage(this.id);
      $(answers).each(function(){
        $(this.elements).each(function(){
          if(this.id == 'branching'){
            values.push(this.values[0].value);
          }
        });
      });
    break;
    case 'attachment':
      var answers = data.applicant.getAnswersForPage(this.id);
      values = values.concat(this.gridAnswerAttachment(answers));
    break;
    case 'publicAnswerStatus':
      var answers = data.applicant.getAnswersForPage(this.id);
      var hasStatus = 0;
      $(answers).each(function(){
        if(this.publicStatus != null){
          hasStatus++;
          values.push(this.publicStatus.name);
        } else {
          values.push('');
        }
      });
      if(type == 'sort'){
        var per = hasStatus/values.length;
        //if 100% are set then use the total set
        if(per == 1){
          return hasStatus+1;
        }
        return per;
      }
    break;
    case 'privateAnswerStatus':
      var answers = data.applicant.getAnswersForPage(this.id);
      var hasStatus = 0;
      $(answers).each(function(){
        if(this.privateStatus != null){
          hasStatus++;
          values.push(this.privateStatus.name);
        } else {
          values.push('');
        }
      });
      if(type == 'sort'){
        var per = hasStatus/values.length;
        //if 100% are set then use the total set
        if(per == 1){
          return hasStatus+1;
        }
        return per;
      }
    break;
  }
  if(values.length == 0){
    return '';
  }
  if(values.length == 1){
    return values[0];
  }
  if(type == 'display'){
    var ol = $('<ol>');
    $.each(values, function(){
      ol.append($('<li>').html(this.toString()));
    });
    return ol.clone().wrap('<p>').parent().html();
  }
  //forsorting and filtering return the raw data
  return values.join(' ');
};

/**
 * Ensure we don't set answerStatusDisplay
 * Since this page type descends from StandardPage it is allowed to set
 * this variable because it inherits the interface
 * @returns {Object}
 */
JazzeePageBranching.prototype.getDataObject = function(){
  var obj = JazzeePage.prototype.getDataObject.call(this);
  obj.answerStatusDisplay = 0;
  return obj;
};