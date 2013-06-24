/**
 * The JazzeePageRecommenders type
  @extends JazzeePage
 */
function JazzeePageRecommenders(){}
JazzeePageRecommenders.prototype = new JazzeePage();
JazzeePageRecommenders.prototype.constructor = JazzeePageRecommenders;

/**
 * Create a new RecommendersPage with good default values
 * @param {String} id the id to use
 * @returns {RecommendersPage}
 */
JazzeePageRecommenders.prototype.newPage = function(id,title,typeId,typeName,typeClass,status,pageBuilder){
  var page = JazzeePage.prototype.newPage.call(this, id,title,typeId,typeName,typeClass,status,pageBuilder);
  page.setVariable('lorDeadline', '');
  page.setVariable('lorDeadlineEnforced', 0);
  page.setVariable('lorWaitDays', 14);
  page.setVariable('recommenderEmailText', "Dear _RECOMMENDER_FIRST_NAME_ _RECOMMENDER_LAST_NAME_,\n"
      + "_APPLICANT_NAME_ has requested a letter of recommendation from you in support of their application for admission to our program. \n"
      + "We use an online system to collect letters of recommendation.  You have been assigned a unique URL for accessing this system.  Please save this email so that you can return to your letter at a later date. \n"
      + "Click the following link to access the online system; or, you may need to copy and paste this link into your browser. \n"
      + "_LINK_ \n");
  return page;
};

JazzeePageRecommenders.prototype.workspace = function(){
  JazzeePage.prototype.workspace.call(this);
  var pageClass = this;
  $('#pageToolbar').append(this.pagePropertiesButton());

  $('#workspace').append(this.editLorPage());
};

/**
 * Create the page properties dropdown
*/
JazzeePageRecommenders.prototype.pageProperties = function(){
  var pageClass = this;

  var div = $('<div>');
  div.append(this.isRequiredButton());
  div.append(this.showAnswerStatusButton());
  div.append(this.editNameButton());

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
  div.append($('<p>').html('Minimum Recommendations Required ').append($('<span>').attr('id', 'minValue').html(this.min == 0?'No Minimum':this.min)));
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
  div.append($('<p>').html('Maximum Recommendations Allowed ').append($('<span>').attr('id', 'maxValue').html(this.max == 0?'No Maximum':this.max)));
  div.append(slider);

  var slider = $('<div>');
  slider.slider({
    value: this.getVariable('lorWaitDays'),
    min: 0,
    max: 20,
    step: 1,
    slide: function( event, ui ) {
      pageClass.setVariable('lorWaitDays', ui.value);
      $('#lorWaitDays').html(pageClass.getVariable('lorWaitDays') == 0?'No Wait':pageClass.getVariable('lorWaitDays'));
    }
  });
  div.append($('<p>').html('Days the applicant must wait to send reminder email ').append($('<span>').attr('id', 'lorWaitDays').html(this.getVariable('lorWaitDays') == 0?'No Wait':this.getVariable('lorWaitDays'))));
  div.append(slider);

  if(!this.isGlobal || this.pageBuilder.editGlobal) div.append(this.editLOREmailButton());
  if(!this.isGlobal || this.pageBuilder.editGlobal) div.append(this.deadlineEnforcedButton());
  if(!this.isGlobal || this.pageBuilder.editGlobal) div.append(this.deadlineButton());
  return div;
};

/**
 * Edit the recommender email
 * @return {jQuery}
 */
JazzeePageRecommenders.prototype.editLOREmailButton = function(){
  var pageClass = this;
  var obj = new FormObject();
  var field = obj.newField({name: 'legend', value: 'Edit Email to Recommenders'});
  var replace = [
    {title: 'Applicant Name', replacement: '_APPLICANT_NAME_'},
    {title: 'Recommendation Dealine', replacement:'_DEADLINE_'},
    {title: 'Link to the Recommendation', replacement:'_LINK_'},
    {title: 'Recommender First Name', replacement:'_RECOMMENDER_FIRST_NAME_'},
    {title: 'Recommender Last Name', replacement:'_RECOMMENDER_LAST_NAME_'},
    {title: 'Recommender Institution', replacement:'_RECOMMENDER_INSTITUTION_'},
    {title: 'Recommender Phone', replacement:'_RECOMMENDER_EMAIL_'},
    {title: 'Recommender Email', replacement:'_RECOMMENDER_PHONE_'}
  ];
  field.instructions = 'The following will be replaced with the applicant input for this recommender: ';
  for(var i in replace){
    field.instructions += '<br />' + replace[i].replacement + ': ' + replace[i].title;
  }

  var element = field.newElement('Textarea', 'recommenderEmailText');
  element.label = 'Email Text';
  element.required = true;
  element.value = this.getVariable('recommenderEmailText');

  var dialog = this.displayForm(obj);
  $('form', dialog).bind('submit',function(e){
    pageClass.setVariable('recommenderEmailText', $('textarea[name="recommenderEmailText"]', this).val());
    pageClass.workspace();
    dialog.dialog("destroy").remove();
    return false;
  });//end submit
  var button = $('<button>').html('Edit Email to Recommenders').bind('click',function(){
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
 * Button for choosing to enforce teh deadline
 * @return {jQuery}
 */
JazzeePageRecommenders.prototype.deadlineEnforcedButton = function(){
  var pageClass = this;
  var span = $('<span>');
  span.append($('<input>').attr('type', 'radio').attr('name', 'lorDeadlineEnforced').attr('id', 'enforced').attr('value', '1').attr('checked', this.getVariable('lorDeadlineEnforced')==1)).append($('<label>').html('Enforced').attr('for', 'enforced'));
  span.append($('<input>').attr('type', 'radio').attr('name', 'lorDeadlineEnforced').attr('id', 'notenforced').attr('value', '0').attr('checked', this.getVariable('lorDeadlineEnforced')==0)).append($('<label>').html('Not Enforced').attr('for', 'notenforced'));
  span.buttonset();

  $('input', span).bind('change', function(e){
    $('.qtip').qtip('api').hide();
    pageClass.setVariable('lorDeadlineEnforced', $(e.target).val());
  });
  return $('<p>').html('Deadline: ').append(span);
};

/**
 * Edit the recommender deadline
 * @return {jQuery}
 */
JazzeePageRecommenders.prototype.deadlineButton = function(){
  var pageClass = this;
  var button = $('<button>').html('Set Recommendation Deadline').bind('click',function(){
    $('.qtip').qtip('api').hide();
    var dialog = pageClass.createDialog();
    dialog.append($('<div>').attr('id', 'lorDeadlineForm').addClass('yui-g'));
    pageClass.deadlineForm(pageClass.getVariable('lorDeadline')!='');
    var button = $('<button>').html('Apply').bind('click',function(){
      if($('#lorDeadlineForm input[name="hasDeadline"]:checked').val() == 1){
        pageClass.setVariable('lorDeadline', $('#lorDeadlineForm input[name="deadline"]').val());
      } else {
        pageClass.setVariable('lorDeadline', '');
      }
      pageClass.workspace();
      dialog.dialog("destroy").remove();
      return false;
    }).button({
      icons: {
        primary: 'ui-icon-disk'
      }
    });
    dialog.append(button);
    dialog.dialog('open');
  }).button({
    icons: {
      primary: 'ui-icon-pencil'
    }
  });
  return button;
};

/**
 * lorDeadline dialog content
 * @param Boolean picker
 * @return {jQuery}
 */
JazzeePageRecommenders.prototype.deadlineForm = function(picker){
  var pageClass = this;
  $('#lorDeadlineForm').empty();
  if(picker){
    var input = $('<input>').attr('type', 'text').attr('name', 'deadline').attr('id', 'deadline').attr('value', pageClass.getVariable('lorDeadline'));
    $('#lorDeadlineForm').append($('<div>').addClass('yui-u first').append(input));
    input.AnyTime_noPicker().AnyTime_picker({
      labelTitle: 'Choose Deadline',
      hideInput: true,
      placement: 'inline'
    });
  }

  var span = $('<span>');
  span.append($('<input>').attr('type', 'radio').attr('name', 'hasDeadline').attr('id', 'seperate').attr('value', '1').attr('checked', picker)).append($('<label>').html('Seperate Deadline').attr('for', 'seperate'));
  span.append($('<input>').attr('type', 'radio').attr('name', 'hasDeadline').attr('id', 'same').attr('value', '0').attr('checked', !picker)).append($('<label>').html('Same As Application').attr('for', 'same'));
  span.buttonset();

  $('input', span).bind('change', function(e){
    pageClass.deadlineForm($(e.target).val() == 1);
  });
  $('#lorDeadlineForm').append($('<div>').addClass('yui-u').append(span));
};

/**
 * Get the recommendation page (it is the first child)
 * @returns {JazzeePage} | false
 */
JazzeePageRecommenders.prototype.getRecommendationPage = function(){
  if($.isEmptyObject(this.children)) return false;
  for (var firstId in this.children) break;
  return this.children[firstId];
};

/**
 * Edit the LOR page
 */
JazzeePageRecommenders.prototype.editLorPage = function(){
  var pageClass = this;
  var div = $('<div>');

  var lorPage = this.getRecommendationPage();
  if(!lorPage){
    var dropdown = $('<ul>');
    for(var i = 0; i < this.pageBuilder.pageTypes.length; i++){
      if($.inArray('Jazzee\\Interfaces\\LorPage', this.pageBuilder.pageTypes[i].interfaces) > -1){
        var item = $('<a>').html(this.pageBuilder.pageTypes[i].typeName).attr('href', '#').data('pageType', this.pageBuilder.pageTypes[i]);
        item.bind('click', function(e){
          var pageType = $(e.target).data('pageType');
          var child = new window[pageType.typeClass].prototype.newPage('newchildpage' + pageClass.pageBuilder.getUniqueId(),'Recommendation',pageType.id,pageType.typeName,pageType.typeClass,'new',pageClass.pageBuilder);
          pageClass.addChild(child);
          pageClass.markModified();
          div.replaceWith(pageClass.editLorPage());
          return false;
        });
        dropdown.append($('<li>').append(item));
      }
    }
    var button = $('<button>').html('Select Recommendation Page Type').button();
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
    div.append(button)
  } else {
    var button = $('<button>').html('Edit Recommendation Page').data('page', lorPage).bind('click',function(){
      var page = $(this).data('page');
      page.workspace();
      //empty the toolbar becuase the delete/copy are going to be wrong
      $('#pageToolbar .copy').remove();
      $('#pageToolbar .delete').remove();
      $('#pageToolbar .properties').remove();

      var button = $('<button>').html('Delete').data('page', page).bind('click', function(e){
        $('#editPage').effect('explode',500);
        pageClass.deleteChild($(e.target).parent().data('page'));
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
    div.append(button);
  }
  return div;
};

/**
 * List all a pages elements
 */
JazzeePageRecommenders.prototype.listDisplayElements = function(){
  var self = this;
  var elements = [];
  $(this.elements).each(function(){
    elements.push({name: this.id, title: this.title, type: 'element'});
  });
  for(var i in this.children){
    $(this.children[i].listDisplayElements()).each(function(){
      if(this.type != 'page'  && (this.name != 'attachment' || this.name != 'answerPublicStatus' || this.name != 'answerPublicStatus')){
        var title = self.children[i].title + ' ' + this.title; 
        elements.push({name: this.name, title: title, type: this.type, pageId: this.pageId});
      }
    });
  }
  elements.push({name: 'lorReceived', type: 'page', title: 'Received Recommendations', pageId: this.id, sType: 'numeric'});
  elements.push({name: 'attachment', type: 'page', title: this.title + ' Attachment', pageId: this.id});

  return elements;
};

/**
 * Dispaly applicant data in a grid
 */
JazzeePageRecommenders.prototype.gridData = function(data, type, full){
  var values = [];
  switch(data.displayElement.name){
    case 'attachment':
      var answers = data.applicant.getAnswersForPage(this.id);
      values = values.concat(this.gridAnswerAttachment(answers));
    break;
    case 'lorReceived':
      var answers = data.applicant.getAnswersForPage(this.id);
      var complete = 0;
      var img = $("<img src='resource/foundation/media/icons/tick.png'>").css('height', '1em');
      $(answers).each(function(){
        if(this.children.length > 0){
          complete++;
          values.push(img.clone().wrap('<p>').parent().html());
        } else {
          values.push('');
        }
      });
      if(type == 'sort'){
        var per = complete/answers.length;
        //if all recommenders have been received then use the total to sort
        if(per == 1){
          return complete;
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
