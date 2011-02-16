/**
 * The RecommendersPage type
  @extends ApplyPage
 */
function RecommendersPage(){}
RecommendersPage.prototype = new ApplyPage();
RecommendersPage.prototype.constructor = RecommendersPage;

/**
 * Create a new RecommendersPage with good default values
 * @param {String} id the id to use
 * @returns {RecommendersPage}
 */
RecommendersPage.prototype.newPage = function(id,title,pageClass,status,pageStore){
  var page = ApplyPage.prototype.newPage.call(this, id,title,pageClass,status,pageStore);
  page.setVariable('lorDeadline', null);
  page.setVariable('lorDeadlineEnforced', 0);
  page.setVariable('recommenderEmailText', "Dear %RECOMMENDER_FIRST_NAME% %RECOMMENDER_LAST_NAME%,\n"
      + "%APPLICANT_NAME% has requested a letter of recommendation from you in support of their application for admission to our program. \n"
      + "We use an online system to collect letters of recommendation.  You have been assigned a unique URL for accessing this system.  Please save this email so that you can return to your letter at a later date. \n"
      + "Click the following link to access the online system; or, you may need to copy and paste this link into your browser. \n"
      + "%LINK% \n"
      + "Questions or comments about submitting your recommendation may be addressed to %PROGRAM_CONTACT_NAME% %PROGRAM_CONTACT_EMAIL%");
  var recommendation = new StandardPage.prototype.newPage('newpage' + pageStore.getUniqueId(),'Recommendation','StandardPage','new',pageStore);
  page.addChild(recommendation);
  return page;
};

RecommendersPage.prototype.workspace = function(){
  this.clearWorkspace();
  $('#workspace-left-top').parent().addClass('form');
  $('#workspace-left-top').append(this.titleBlock());
  $('#workspace-left-top').append(this.textInputBlock('leadingText', 'click to edit'));
  $('#workspace-left-top').append(this.textAreaBlock('instructions', 'click to edit'));
  $('#workspace-left-bottom-left').append(this.textAreaBlock('trailingText', 'click to edit'));
  
  $('#workspace-right-top').append(this.previewPageBlock());
  var min = {0: 'No Minimum'};
  for(var i = 1; i<=20;i++){
    min[i] = i;
  }
  $('#workspace-right-top').append(this.selectListBlock('min','Minimum Recommenders Required:', min));
  var max = {0: 'No Maximum'};
  for(var i = 1; i<=20;i++){
    max[i] = i;
  }
  $('#workspace-right-top').append(this.selectListBlock('max','Maximum Recommenders Allows:', max));
  $('#workspace-right-top').append(this.selectListBlock('optional', 'This page is', {0:'Required',1:'Optional'}));
  $('#workspace-right-top').append(this.selectListBlock('showAnswerStatus', 'Answer Status is', {0:'Not Shown',1:'Shown'}));
  
  $('#workspace-right-top').append(this.textInputVariableBlock('lorDeadline', 'The deadine for submitting recommendations is ', 'the same as the application'));
  $('#workspace-right-top').append(this.selectListVariableBlock('lorDeadlineEnforced', 'The deadine for recommender is', {0:'Not Enforced',1:'Enforced'}));
  $('#workspace-right-top').append(this.recommendationPageBlock());
  $('#workspace-right-top').append(this.recommenderEmailBlock());
  
  $('#workspace-right-bottom').append(this.deletePageBlock());
  
  $('#workspace').show('slide');
};

/**
 * Get the recommendation page (it is the first child)
 * @returns {ApplyPage}
 */
RecommendersPage.prototype.getRecommendationPage = function(){
  for (var firstId in this.children) break;
  return this.children[firstId];
};

/**
 * Edit the recommendation Page block
 * @returns {jQuery}
 */
RecommendersPage.prototype.recommendationPageBlock = function(){
  var pageClass = this;
  var p = $('<p>').addClass('edit lorPage').html('Edit Recommendation Page').bind('click',function(e){
    pageClass.getRecommendationPage().workspace();
    //get rid of the min/max/preview/delete controls
    $('#workspace-right-top').empty();
    $('#workspace-right-bottom').empty();
  });
  return p;
};

/**
 * Answer status Block
 * @returns {jQuery}
 */
RecommendersPage.prototype.recommenderEmailBlock = function(){
  var pageClass = this;
  var p = $('<p>').addClass('edit recommenderEmail').html('Recommender Email').append($('<br>')).append($('<em>').html(this.getVariable('emailText'))).bind('click', function(e){
    var obj = new FormObject();
    var field = obj.newField({name: 'legend', value: 'Edit Recommender Email'});
    var replace = [
      {title: 'Applicant Name', replacement: '%APPLICANT_NAME%'},
      {title: 'Recommendation Dealine', replacement:'%DEADLINE%'},
      {title: 'Link to the Recommendation', replacement:'%LINK%'},
      {title: 'Program Contact Name', replacement:'%PROGRAM_CONTACT_NAME%'},
      {title: 'Program Contact Email', replacement:'%PROGRAM_CONTACT_EMAIL%'},
      {title: 'Program Contact Phone', replacement:'%PROGRAM_CONTACT_PHONE%'},
      {title: 'Recommender First Name', replacement:'%RECOMMENDER_FIRST_NAME%'},
      {title: 'Recommender Last Name', replacement:'%RECOMMENDER_LAST_NAME%'},
      {title: 'Recommender Institution', replacement:'%RECOMMENDER_INSTITUTION%'},
      {title: 'Recommender Phone', replacement:'%RECOMMENDER_EMAIL%'},
      {title: 'Recommender Email', replacement:'%RECOMMENDER_PHONE%'}
    ];
    field.instructions = 'The following will be replaced with the applicant input for this recommender: ';
    for(var i in replace){
      field.instructions += '<br />' + replace[i].replacement + ': ' + replace[i].title;
    }
    var element = field.newElement('Textarea', 'recommenderEmailText');
    element.label = 'Email Text';
    element.value = pageClass.getVariable('recommenderEmailText');
    
    var form = new Form();
    var formObject = form.create(obj);
    $('form',formObject).append($('<button type="submit" name="submit">').html('Save'));
    
    var div = $('<div>');
    div.css("overflow-y", "auto");
    div.html(formObject);
    div.dialog({
      modal: true,
      autoOpen: true,
      position: 'center',
      width: 800,
      close: function() {
        div.dialog("destroy").remove();
      }
    });
    $('form', div).unbind().bind('submit',function(e){

      pageClass.setVariable('recommenderEmailText', $('textarea[name=recommenderEmailText]', this).val());
      div.dialog("destroy").remove();
      p.replaceWith(pageClass.recommenderEmailBlock());
      return false;
    });//end submit
  });
  return p;
};