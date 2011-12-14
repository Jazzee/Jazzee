/**
 * Display a single applicant
 * @param {jQuery} workspace
 */
function Applicant(workspace){
  this.workspace = workspace;
  this.baseUrl = document.location.href;
};

Applicant.prototype.init = function(){
  var self = this;
  this.parseBio();
  this.parseActions();
  this.parseDecisions();
  $('a#actas').click(function(e){
    $.get($(e.target).attr('href'),function(json){
      window.open(json.data.result.link);
    });
    return false;
  });
  $('#pages div.page').each(function(){
    var id = $(this).attr('id').substr(4);
    self.parsePage(id);
  });
  $.get(this.baseUrl+'/refreshTags',function(json){
    self.refreshTags(json.data.result.tags);
  });
  this.parseAttachments();
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
    var statusP = $('<p>').addClass('status').html('<img src="resource/foundation/media/ajax-bar.gif" />').hide();
    div.prepend(statusP);
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
      $('p.status', div).fadeIn();
      //give our iframe a unique name from the timestamp
      var iFrameName = "iFrame" + (new Date().getTime());
      var iFrame = $("<iframe name='" + iFrameName + "' src='about:blank' />").insertAfter('body');
      iFrame.css("display", "none");
      e.target.target = iFrameName;
      iFrame.load(function(e){
        var json = eval("(" + $(this).contents().find('textarea').get(0).value + ")");
        div.dialog("destroy").remove();
        if(json.status == 'success'){
          callback.display(json);
        } else {
          applicant.createForm(json.data.form, callback);
        }
      }); //end iFrame Load
    });//end submit
};

/**
 *Parse the bio section and activate links
 */
Applicant.prototype.parseBio = function(){
  var self = this;
  $('#bio #updateBio').click(function(e){
    $.get($(e.target).attr('href'),function(json){
      var obj = {
        display: function(json){
          self.refreshBio(json.data.result.bio);
        }
      };
      self.createForm(json.data.form, obj);
    });
    return false;
  });
};

/**
 * Display Biographic information
 * @param Object json
 */
Applicant.prototype.refreshBio = function(json){
  $('#bio').empty().hide();
  var h1 = $('<h1>').html(json.name);
  if(json.allowEdit){
    var a = $('<a>').attr('href', this.baseUrl + '/updateBio').html(' (edit)');
    h1.append(a);
  }
  $('#bio').append(h1);
  $('#bio').append($('<h4>').html(json.email));
  this.parseBio();
  $('#bio').show();
};

/**
 * Parse Actions
 */
Applicant.prototype.parseActions = function(){
  var self = this;
  $('#actions a').click(function(e){
    $.get($(e.target).attr('href'),function(json){
      var obj = {
        display: function(json){
          self.refreshActions(json.data.result.actions);
        }
      };
      self.createForm(json.data.form, obj);
    });
    return false;
  });
};

/**
 * Display Actions
 * @param Object json
 */
Applicant.prototype.refreshActions = function(json){
  $('#actions').empty();
  var self = this;
  $('#actions').html(
    'Account Created: ' + json.createdAt.date + '<br />' + 
    'Last Update: ' + json.updatedAt.date + '<br />' + 
    'Last Login: ' + json.lastLogin.date + '<br />'
  );
  var text = json.deadlineExtension?json.deadlineExtension.date:'none';
  $('#actions').append('Deadline Extension: ');
  if(json.allowExtendDeadline){
    var a = $('<a>').attr('href', this.baseUrl + '/extendDeadline').html(text);
    $('#actions').append(a);
  } else {
    $('#actions').append(text);
  }
  this.parseActions();
};



/**
 * Parse Decisions
 */
Applicant.prototype.parseDecisions = function(){
  var self = this;
  $('#decisions a.actionForm').click(function(e){
    $.get($(e.target).attr('href'),function(json){
      var obj = {
        display: function(json){
          self.refreshDecisions(json.data.result.decisions);
        }
      };
      self.createForm(json.data.form, obj);
    });
    return false;
  });
    
  $('#decisions a.action').click(function(e){
    $.get($(e.target).attr('href'),function(json){
      self.refreshDecisions(json.data.result.decisions);
    });
    return false;
  });
};

/**
 * Refresh Decisions
 * @param Object json
 */
Applicant.prototype.refreshDecisions = function(json){
  $('#decisions').empty();
  var self = this;
  if(json.isLocked){
    $('#decisions').html('Status: ' + json.status + '<br />');
    var types = [
      {title: 'Nominate for Admission', action: 'nominateAdmit', className: 'action'},
      {title: 'Undo Nomination', action: 'undoNominateAdmit', className: 'action'},
      {title: 'Nominate for Deny', action: 'nominateDeny', className: 'action'}, 
      {title: 'Undo Nomination', action: 'undoNominateDeny', className: 'action'}, 
      {title: 'Admit Applicant', action: 'finalAdmit', className: 'actionForm'}, 
      {title: 'Undo Decision', action: 'undoFinalAdmit', className: 'action'}, 
      {title: 'Deny Applicant', action: 'finalDeny', className: 'actionForm'}, 
      {title: 'Undo Decision', action: 'undoFinalDeny', className: 'action'}, 
      {title: 'Accept Offer', action: 'acceptOffer', className: 'action'}, 
      {title: 'Decline Offer', action: 'declineOffer', className: 'action'},
      {title: 'Undo Offer Response', action: 'undoAcceptOffer', className: 'action'}, 
      {title: 'Undo Offer Response', action: 'undoDeclineOffer', className: 'action'}        
    ];
    for(var i = 0; i < types.length; i++){
      if(json['allow'+types[i].action]){
        var a = $('<a>').attr('href', this.baseUrl + '/' + types[i].action).addClass(types[i].className).html(types[i].title +'<br />');
        $('#decisions').append(a);
      }
    }
  } else {$('#decisions').html('Status: Not Complete<br />');}
  if(json.allowUnlock && json.isLocked){
    var a = $('<a>').attr('href', this.baseUrl + '/unlock').addClass('action').html('Unlock Application <br />');
    $('#decisions').append(a);
  }
  if(json.allowLock && !json.isLocked){
    var a = $('<a>').attr('href', this.baseUrl + '/lock').addClass('action').html('Lock Application <br />');
    $('#decisions').append(a);
  }
  this.parseDecisions();
};

/**
 * Display Tags
 * @param Object tags
 */
Applicant.prototype.refreshTags = function(json){
  var self = this;
  $('#tags').empty();
  var ul = $('<ul>');
  for(var i=0; i<json.tags.length; i++){
    var li = $('<li>').html(json.tags[i].title).attr('id', 'tag'+json.tags[i].id);
    if(json.allowRemove){
      li.prepend($('<img>').attr('src',self.baseUrl + '/resource/foundation/media/icons/delete.png').addClass('removeTag'));
    }
    ul.append(li);
  }
  $('#tags').append(ul);
  $('#tags li img.removeTag').click(function(e){
    var tagId = $(e.target).parent().attr('id').substr(3);
    $.post(self.baseUrl + '/removeTag',{tagId: tagId},function(json){
      self.refreshTags(json.data.result.tags);
    });
    return false;  
  });

  var input = $('<input name="tag" type="text" value="add tag">');
  input.focus(function(e){
    $(e.target).attr('value', '');
  });
  input.blur(function(e){
    $(e.target).attr('value', 'add tag');
  });
  input.autocomplete({source: json.allTags});
  var form = $('<form>').append(input);

  form.submit(function(e){
    var value = $('input', e.target).first().val();
    $.post(self.baseUrl + '/addTag',{tagTitle: value},function(json){
      self.refreshTags(json.data.result.tags);
    });
    return false;
  });
  $('#tags').append(form);
};

/**
 * Display Pages
 * @param Int pageId
 */
Applicant.prototype.refreshPage = function(pageId){
  //overlay the page so you can tell it is reloading
  $('#page'+pageId+' .answers').first().hide().html('<p>Loading your changes.<br /><img src="resource/foundation/media/ajax-bar.gif"></p>').fadeIn();
  var self = this;
  $.get(this.baseUrl + '/refreshPage/' + pageId,function(html){
    var div = $('<div>').data('pageId', pageId);
    div.html(html);
    $('#page'+pageId).replaceWith(div);
    self.parsePage(pageId);
  });
};

/**
 * Catch the links on a page
 * @param Int pageId
 */
Applicant.prototype.parsePage = function(pageId){
  var self = this;
  $('#page'+pageId + ' a.actionForm').click(function(e){
    $.get($(e.target).attr('href'),function(json){
      var obj = {
        display: function(json){
          self.refreshPage(pageId);
        }
      };
      self.createForm(json.data.form, obj);
    });
    return false;
  });
  $('#page'+pageId + ' a.action').click(function(e){
    if($(e.target).hasClass('confirmDelete')){
      if(!confirm('Are you sure you want to delete?  This cannot be undone')) return false;
    }
    $.get($(e.target).attr('href'),function(json){
      self.refreshPage(pageId);
    });
    return false;
  });
};

/**
 * Refresh Attachments
 * @param Object json
 */
Applicant.prototype.refreshAttachments = function(json){
  $('#attachments').empty();
  for(var i=0; i<json.attachments.length; i++){
    var div = $('<div>').attr('id','attachment'+json.attachments[i].id).data('attachmentId', json.attachments[i].id);
    var a = $('<a>').attr('href', json.attachments[i].filePath);
    a.append($('<img>').attr('src', json.attachments[i].previewPath));
    div.append(a);
    if(json.allowDelete){
      div.append($('<a>').attr('href', this.baseUrl + '/deleteApplicantPdf/' + json.attachments[i].id).html('Delete PDF').addClass('delete'));
    }
    $('#attachments').append(div);
  }
  if(json.allowAttach){
    var a = $('<a>').attr('href', this.baseUrl + '/attachApplicantPdf').html('Attach Pdf').addClass('attach');
    $('#attachments').append(a);
  }
  this.parseAttachments();  
};

/**
 * Parse Attachments
 * @param Object json
 */
Applicant.prototype.parseAttachments = function(){
  var self = this;
  $('#attachments div a.delete').click(function(e){
    $.get($(e.target).attr('href'),function(json){
      self.refreshAttachments(json.data.result.attachments);
    });
    return false;
  });
  $('#attachments a.attach').click(function(e){
    $.get($(e.target).attr('href'),function(json){
      var obj = {
        display: function(json){
          self.refreshAttachments(json.data.result.attachments);
        }
      };
      self.createForm(json.data.form, obj);
    });
    return false;
  });
};