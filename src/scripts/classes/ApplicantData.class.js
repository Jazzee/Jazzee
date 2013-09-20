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

ApplicantData.prototype.hasStatus = function(statusName){
      var hasStatus = false;
      var hasDecision = this["decision"];
      if(hasDecision){
	  // check if we have a status that matches the bound statusName
	  //	  console.log("decision is ");
	  //console.log(full["decision"]);
	  //console.log("applicant data is ");
	  //console.log(data);
	  if(statusName == 'status_declined'){
	      hasStatus = (this["decision"]["declineOffer"] != null);
	  }else if(statusName == 'status_admitted'){
	      hasStatus = (this["decision"]["finalAdmit"] != null);
	  }else if(statusName == 'status_denied'){
	      hasStatus = (this["decision"]["finalDeny"] != null);
	  }else if(statusName == 'status_accepted'){
	      hasStatus = (this["decision"]["acceptOffer"] != null);

	  }

      }
    return hasStatus ? "<img src='resource/foundation/media/icons/tick.png'>" : "";
};

ApplicantData.prototype.hasTag = function(tagName){
      var hasTag = false;
      var hasTags = this["tags"].length > 0;
      if(hasTags){
	  // check if we have a tag that matches the bound tagName
	  $.each(this["tags"], function(){
		  if(tagName == this.id) hasTag = true;
	      });
      }
    return hasTag ? "<img src='resource/foundation/media/icons/tick.png'>" : "";
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
 * @param elementId
 * 
 * @return []
 */
ApplicantData.prototype.getAnswersForElement = function(elementId){
  var answers = [];
  for(var i = 0; i < this.pages.length; i++){
    for(var j = 0; j < this.pages[i].answers.length; j++){
      answers = answers.concat(this.getAnswersFromAnswerForElement(this.pages[i].answers[j],elementId));
    }
  }

  return answers;
};

/**
 * Get values for a page answer block
 * Calls itself recursivly for children
 * @param {pageId}answer}
 * @param elementId
 * 
 * @return []
 */
ApplicantData.prototype.getAnswersFromAnswerForElement = function(answer, elementId){
  var answers = [];
  for(var i = 0; i < answer.elements.length; i++){
    var element = answer.elements[i];
    if(element.id == elementId){
      answers.push(element);
    }
  }

  if(answer.children != undefined){
    for(var i = 0; i < answer.children.length; i++){
      answers = answers.concat(this.getAnswersFromAnswerForElement(answer.children[i],elementId));
    }
  }

  return answers;
};
