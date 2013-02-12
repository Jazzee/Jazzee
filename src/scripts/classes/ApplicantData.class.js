/**
 * Applicant Data class
 * Takes applicant JSOn and adds some nice methods for accessing the data
 * 
 * @param {} obj
 */
function ApplicantData(obj){
  for(var key in obj){
    this[key] = obj[key];
  }
};

/**
 * Get answers for an applicant page
 */
ApplicantData.prototype.getAnswersForPage = function(pageId){
  for(var i = 0; i < this.pages.length; i++){
      var page = this.pages[i];
    if(page.id == pageId){

      return page.answers;
    }
  }
  
  return [];
};

ApplicantData.prototype.getPage = function(pageId){
  for(var i = 0; i < this.pages.length; i++){
    var page = this.pages[i];
    if(page.id == pageId){
      return page;
    }
  }
  
  return null;
};

/**
 * Get answers for an applicant page
 */
ApplicantData.prototype.hasAnswersForPage = function(pageId){
  for(var i = 0; i < this.pages.length; i++){
      var page = this.pages[i];
    if(page.id == pageId){
      return page.answers.length > 0;
    }
  }

  return false;
};

/**
 * List the tags for an applicant
 * 
 * @return []
 */
ApplicantData.prototype.listTags = function(){
  var tags = [];
  $.each(this.tags, function(){
    tags.push(this.title);
  });
  
  return tags;
};

/**
 * List the tags for an applicant
 * 
 * @return []
 */
ApplicantData.prototype.listTagIds = function(){
  var tags = [];
  $.each(this.tags, function(){
    tags.push(this.id);
  });
  
  return tags;
};

/**
 * List the tags for an applicant
 * 
 * @return []
 */
ApplicantData.prototype.listTagTitles = function(){
  var tags = [];
  $.each(this.tags, function(){
    tags.push(this.title);
  });
  
  return tags;
};

/**
 * Get values for a page element
 * @param pageId
 * @param elementId
 * 
 * @return []
 */
ApplicantData.prototype.getDisplayValuesForPageElement = function(pageId, elementId){
  var values = [];
  for(var i = 0; i < this.pages.length; i++){
    var page = this.pages[i];
    if(page.id == pageId){
      for(var j = 0; j < page.answers.length; j++){
        var answer = page.answers[j];
        for(var k = 0; k < answer.elements.length; k++){
          var element = answer.elements[k];
          if(element.id == elementId){
            values.push(element.displayValue);
          }
        }
      }
    }
  }

  return values;
};
