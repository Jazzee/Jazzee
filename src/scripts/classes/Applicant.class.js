/**
 * Display a single applicant
 * @param {jQuery} workspace
 */
function Applicant(workspace){
  this.workspace = workspace;
  this.baseUrl = document.location.href;
  var self = this;
  this.refreshActions();
  this.refreshTags();
  $('a.editAccount').bind('click', function(e){
	  $.get($(e.target).attr('href'),function(json){
		  var obj = {
		    reload: function(){
			    $('#container').hide('fade');
		    	window.location.reload();
		    }
		  };
		  self.createForm(json.data.form, obj);
	  });
	  return false;
  });
  
};

/**
 * Create a form from json and display it
 * 
 * @param {Object} the json for the form
 * @param {String} the location we will post to
 * @param {String} the callback function when we succeed
 */
Applicant.prototype.createForm = function(json, callback){
	var applicant = this;
	var form = new Form();
	var div = $('<div>');
    div.css("overflow-y", "auto");
    div.html(form.create(json));
    div.dialog({
      modal: true,
      autoOpen: true,
      position: 'center',
      width: 800,
      overlay: {
        backgroundColor: '#fff',
        opacity: 0.8
      },
      close: function() {
        div.dialog("destroy").remove();
      }
    });
    
    $('form', div).bind('submit',function(e){
      //give our iframe a unique name from the timestamp
      var iFrameName = "iFrame" + (new Date().getTime());
      var iFrame = $("<iframe name='" + iFrameName + "' src='about:blank' />").insertAfter('body');
      iFrame.css("display", "none");
      e.target.target = iFrameName;
      iFrame.load(function(e){
        var json = eval("(" + $(this).contents().find('textarea').get(0).value + ")");
        div.dialog("destroy").remove();
        if(json.status == 'success'){
          callback.reload();
        } else {
          applicant.createForm(json.data.form, callback);
        }
      }); //end iFrame Load
    });//end submit
}

/**
 * Refresh the status table
 */
Applicant.prototype.refreshActions = function(){
  $.get(this.baseUrl + '/updateActions',function(json){
	$('#actions').html(
		'Account Created: ' + json.data.result.createdAt + '<br />' + 
		'Last Update: ' + json.data.result.updatedAt + '<br />' + 
		'Last Login: ' + json.data.result.lastLogin + '<br />'
	);
  });
};

/**
 * Refresh decision status
 */
Applicant.prototype.refreshDecisions = function(){
  $.get(this.baseUrl + '/updateStatus',function(json){
	$('#decisions').html(
		'Status: ' + json.data.result.status + '<br />'
	);
  });
};

/**
 * Refresh tags
 */
Applicant.prototype.refreshTags = function(){
  $.get(this.baseUrl + '/updateTags',function(json){
	var ol = $('<ol>');
	$.each(json.data.result, function(i, tag){
	  ol.append($('<li>').data('tagId', tag.id).html(tag.title));		
	});
	$('#tags').html(ol);
  });
};


/**
 * Refresh a page
 * @param interger pageId
 */
Applicant.prototype.refreshPage = function(pageId){
  $.get(this.baseUrl + '/updatePage/'+pageId,function(html){
	$('#page'+pageId).html(html);
  });
};

/**
 * Refresh an answer
 * @param interger answerId
 */
Applicant.prototype.refreshAnswer = function(answerId){
  $.get(this.baseUrl + '/updateAnswer/'+answerId,function(html){
	$('#answer'+answerId).replaceWith(html);
  });
};