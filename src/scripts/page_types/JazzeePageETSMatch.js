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
  $('#pageToolbar').append(this.pagePropertiesButton());
};

/**
 * Create the page properties dropdown
*/
JazzeePageETSMatch.prototype.pageProperties = function(){
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
  div.append($('<p>').html('Minimum Scores Required ').append($('<span>').attr('id', 'minValue').html(this.min == 0?'No Minimum':this.min)));
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
  div.append($('<p>').html('Maximum Scores Allowed ').append($('<span>').attr('id', 'maxValue').html(this.max == 0?'No Maximum':this.max)));
  div.append(slider);

  return div;
};

/**
 * List all a pages elements
 */
JazzeePageETSMatch.prototype.listDisplayElements = function(){
  var elements = [];
  
  $(this.elements).each(function(){
    elements.push({name: this.id, title: this.title, type: 'element'});
  });
  elements.push({name: 'greRegistrationNumber', type: 'page', title: 'GRE Registration Number', pageId: this.id});
  elements.push({name: 'greDepartmentName', type: 'page', title: 'GRE Department Name', pageId: this.id});
  elements.push({name: 'greTestDate', type: 'page', title: 'GRE Test Date', pageId: this.id});
  elements.push({name: 'greTestName', type: 'page', title: 'GRE Test Name', pageId: this.id});
  elements.push({name: 'greScore1', type: 'page', title: 'GRE Score 1', pageId: this.id});
  elements.push({name: 'greScore2', type: 'page', title: 'GRE Score 2', pageId: this.id});
  elements.push({name: 'greScore3', type: 'page', title: 'GRE Score 3', pageId: this.id});
  elements.push({name: 'greScore4', type: 'page', title: 'GRE Score 4', pageId: this.id});
  
  elements.push({name: 'toeflRegistrationNumber', type: 'page', title: 'TOEFL Registration Number', pageId: this.id});
  elements.push({name: 'toeflNativeCountry', type: 'page', title: 'TOEFL Native Country', pageId: this.id});
  elements.push({name: 'toeflNativeLanguage', type: 'page', title: 'TOEFL Native Language', pageId: this.id});
  elements.push({name: 'toeflTestDate', type: 'page', title: 'TOEFL Test Date', pageId: this.id});
  elements.push({name: 'toeflTestType', type: 'page', title: 'TOEFL Test Type', pageId: this.id});
  elements.push({name: 'toeflListening', type: 'page', title: 'TOEFL Listening', pageId: this.id});
  elements.push({name: 'toeflWriting', type: 'page', title: 'TOEFL Writing', pageId: this.id});
  elements.push({name: 'toeflReading', type: 'page', title: 'TOEFL Reading', pageId: this.id});
  elements.push({name: 'toeflEssay', type: 'page', title: 'TOEFL Essay', pageId: this.id});
  elements.push({name: 'toeflTotal', type: 'page', title: 'TOEFL Total', pageId: this.id});

  elements.push({name: 'attachment', type: 'page', title: this.title + ' Attachment', pageId: this.id});
  
  return elements;
};

/**
 * Dispaly applicant data in a grid
 */
JazzeePageETSMatch.prototype.gridData = function(data, type, full){
  var answers = data.applicant.getAnswersForPage(this.id);
  var values = [];
  var answers = data.applicant.getAnswersForPage(this.id);
  switch(data.displayElement.name){
    case 'greRegistrationNumber':
      $(answers).each(function(){
        if(this.greScore != null){
          values.push(this.greScore.registrationNumber);
        }
      });
    break;
    case 'greDepartmentName':
      $(answers).each(function(){
        if(this.greScore != null){
          values.push(this.greScore.departmentName);
        }
      });
    break;
    case 'greTestDate':
      $(answers).each(function(){
        if(this.greScore != null){
          var date = new Date(this.greScore.testDate.date);
          values.push(date.toLocaleDateString());
        }
      });
    break;
    case 'greTestName':
      $(answers).each(function(){
        if(this.greScore != null){
          values.push(this.greScore.testName);
        }
      });
    break;
    case 'greScore1':
      $(answers).each(function(){
        if(this.greScore != null){
          values.push(this.greScore.score1Type + ' '+ this.greScore.score1Converted + ' ' + this.greScore.score1Percentile + '%');
        }
      });
    break;
    case 'greScore2':
      $(answers).each(function(){
        if(this.greScore != null && this.greScore.score2Type != null){
          values.push(this.greScore.score2Type + ' '+ this.greScore.score2Converted + ' ' + this.greScore.score2Percentile + '%');
        }
      });
    break;
    case 'greScore3':
      $(answers).each(function(){
        if(this.greScore != null && this.greScore.score3Type != null){
          values.push(this.greScore.score3Type + ' '+ this.greScore.score3Converted + ' ' + this.greScore.score3Percentile + '%');
        }
      });
    break;
    case 'greScore4':
      $(answers).each(function(){
        if(this.greScore != null && this.greScore.score4Type != null){
          values.push(this.greScore.score4Type + ' '+ this.greScore.score4Converted + ' ' + this.greScore.score4Percentile + '%');
        }
      });
    break;
    case 'toeflRegistrationNumber':
      $(answers).each(function(){
        if(this.toeflScore != null){
          values.push(this.toeflScore.registrationNumber);
        }
      });
    break;
    case 'toeflNativeCountry':
      $(answers).each(function(){
        if(this.toeflScore != null){
          values.push(this.toeflScore.nativeCountry);
        }
      });
    break;
    case 'toeflNativeLanguage':
      $(answers).each(function(){
        if(this.toeflScore != null){
          values.push(this.toeflScore.nativeLanguage);
        }
      });
    break;
    case 'toeflTestDate':
      $(answers).each(function(){
        if(this.toeflScore != null){
          var date = new Date(this.toeflScore.testDate.date);
          values.push(date.toLocaleDateString());
        }
      });
    break;
    case 'toeflTestType':
      $(answers).each(function(){
        if(this.toeflScore != null){
          values.push(this.toeflScore.testType);
        }
      });
    break;
    case 'toeflListening':
      $(answers).each(function(){
        if(this.toeflScore != null){
          if(this.toeflScore.listening != null){
            values.push(this.toeflScore.listening);
          }
          if(this.toeflScore.ibtListening != null){
            values.push(this.toeflScore.ibtListening);
          }
        }
      });
    break;
    case 'toeflReading':
      $(answers).each(function(){
        if(this.toeflScore != null){
          if(this.toeflScore.reading != null){
            values.push(this.toeflScore.reading);
          }
          if(this.toeflScore.ibtReading != null){
            values.push(this.toeflScore.ibtReading);
          }
        }
      });
    break;
    case 'toeflWriting':
      $(answers).each(function(){
        if(this.toeflScore != null){
          if(this.toeflScore.writing != null){
            values.push(this.toeflScore.writing);
          }
          if(this.toeflScore.ibtWriting != null){
            values.push(this.toeflScore.ibtWriting);
          }
        }
      });
    break;
    case 'toeflSpeaking':
      $(answers).each(function(){
        if(this.toeflScore != null && this.toeflScore.ibtSpeaking != null){
          values.push(this.toeflScore.ibtSpeaking);
        }
      });
    break;
    case 'toeflEssay':
      $(answers).each(function(){
        if(this.toeflScore != null && this.toeflScore.essay != null){
          values.push(this.toeflScore.essay);
        }
      });
    break;
    case 'toeflTotal':
      $(answers).each(function(){
        if(this.toeflScore != null){
          if(this.toeflScore.total != null){
            values.push(this.toeflScore.total);
          }
          if(this.toeflScore.ibtTotal != null){
            values.push(this.toeflScore.ibtTotal);
          }
        }
      });
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