/**
 * The JazzeePageEducation type
  @extends JazzeePage
 */
function JazzeePageEducation(){}
JazzeePageEducation.prototype = new JazzeePageStandard();
JazzeePageEducation.prototype.constructor = JazzeePageEducation;

/**
 * Create a new Education with good default values
 * @param {String} id the id to use
 * @returns {RecommendersPage}
 */
JazzeePageEducation.prototype.newPage = function(id,title,typeId,typeName,typeClass,status,pageBuilder){
  var page = JazzeePage.prototype.newPage.call(this, id,title,typeId,typeName,typeClass,status,pageBuilder);
  page.setVariable('schoolListType', 'full');
  page.setVariable('partialSchoolList', '');
  return page;
};

/**
 * List all a pages elements
 */
JazzeePageEducation.prototype.listDisplayElements = function(){
  var self = this;
  var elements = [];
  elements.push({name: 'schoolName', type: 'page', title: 'Selected School', pageId: this.id});
  elements.push({name: 'schoolType', type: 'page', title: 'School Type', pageId: this.id});
  $(this.elements).each(function(){
    elements.push({name: this.id, title: this.title, type: 'element'});
  });
  elements.push({name: 'locationSummary', type: 'page', title: 'School Location', pageId: this.id});
  elements.push({name: 'attachment', type: 'page', title: this.title + ' Attachment', pageId: this.id});
  elements.push({name: 'publicAnswerStatus', type: 'page', title: this.title + ' Public Answer Status', pageId: this.id, sType: 'numeric'});
  elements.push({name: 'privateAnswerStatus', type: 'page', title: this.title + ' Private Answer Status', pageId: this.id, sType: 'numeric'});

  return elements;
};

/**
 * Dispaly applicant data in a grid
 */
JazzeePageEducation.prototype.gridData = function(data, type, full){
  var values = [];
  switch(data.displayElement.name){
    case 'schoolName':
    case 'schoolType':
    case 'locationSummary':
      var answers = data.applicant.getAnswersForPage(this.id);
      $(answers).each(function(){
        $(this.elements).each(function(){
          if(this.id == data.displayElement.name){
            values.push(this.displayValue != null?this.displayValue:'');
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