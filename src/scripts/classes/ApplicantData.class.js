/**
 * Applicant Data class
 * Takes applicant JSOn and adds some nice methods for accessing the data
 * 
 * @param {} obj
 */
function ApplicantData(obj){
  this.applicant = obj;
  for(var key in obj){
    this[key] = obj[key];
  }
};

/**
 * Get answers for an applicant page
 */
ApplicantData.prototype.getAnswersForPage = function(pageId){
  for(var i = 0; i < this.applicant.pages.length; i++){
    var page = this.applicant.pages[i];
    if(page.id = pageId){
      return page.answers;
    }
  }
  
  return [];
};

/**
 * Get answers for an applicant page
 */
ApplicantData.prototype.hasAnswersForPage = function(pageId){
  for(var i = 0; i < this.applicant.pages.length; i++){
    var page = this.applicant.pages[i];
    if(page.id = pageId){
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
  $.each(this.applicant.tags, function(){
    tags.push(this.title);
  });
  
  return tags;
};