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
  this.parseReloads();
  this.parseDecisions();
  this.parseDuplicates();
  $('a#actas').click(function(e){
    $.get($(e.target).attr('href'),function(json){
      window.open(json.data.result.link);
    });
    return false;
  });
  $('a#move').click(function(e){
    $.get($(e.target).attr('href'),function(json){
      var obj = {
        display: function(json){
          alert('Applicant moved successfully');
          window.location.reload();
        }
      };
      self.createForm(json.data.form, obj);
    });
    return false;
  });
  $('#pages div.page').each(function(){
    var id = $(this).attr('id').substr(4);
    self.parsePage(id);
  });
  $('#sirPages div.page').each(function(){
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
  $('input.DateInput', div).each(function(i){
    if(!$(this).hasClass('required') && $(this).val().length < 1){
      applicant.datePickerEmpty($(this));
    } else {
      applicant.datePicker($(this));
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
 * Setup the data picker on an input element
 */
Applicant.prototype.datePicker = function(input){
  var self = this;
  if(!input.hasClass('required')){
    var button = $('<button>').html('Clear');
    button.button({
      icons: {
        primary: 'ui-icon-trash'
      }
    });
    button.bind('click', function(e){
      var input = $('input', $(this).parent());
      input.val('');
      input.AnyTime_noPicker();
      $(this).remove();
      self.datePickerEmpty(input);
      return false;
    });
    input.after(button);
  }
  input.AnyTime_noPicker().AnyTime_picker(
    {format: "%Y-%m-%dT%T%:",
          formatUtcOffset: "%: (%@)",
          hideInput: true,
          placement: "inline"}
  );

};

/**
 * Setup the data picker on an input element
 */
Applicant.prototype.datePickerEmpty = function(input){
  var self = this;
  var button = $('<button>').html('Pick Date');
  button.button({
    icons: {
      primary: 'ui-icon-plus'
    }
  });
  button.bind('click', function(e){
    input.show();
    self.datePicker(input);
    $(this).remove();
  });
  input.after(button);
  input.hide();
};

/**
 *Parse the duplicates section and activate links
 */
Applicant.prototype.parseDuplicates = function(){
  var self = this;
  $('#duplicates a.ignoreDuplicate').click(function(e){
    $.get($(e.target).attr('href'),function(json){
      $('#duplicates').empty().hide();
      if(json.data.result.duplicates.length > 0){
        var ul = $('<ul>');
        for(var i = 0; i < json.data.result.duplicates.length; i++){
          var obj = json.data.result.duplicates[i];
          ul.append($('<li>').html("<em>"+obj.name+"</em> " + obj.complete + " % completed in " + obj.program).append($('<a>').addClass('ignoreDuplicate').attr('href', self.baseUrl + '/ignoreDuplicate/'+obj.id).html('ignore').before(' (').after(')')));
        }
        var fieldset = $('<fieldset>');
        fieldset.append($('<legend>').html('Possible Duplicate Applicants (' + json.data.result.duplicates.length + ')'));
        fieldset.append(ul);
        $('#duplicates').append(fieldset);
        $('#duplicates').show();
        self.parseDuplicates();
      }
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
    var a = $('<a>').attr('href', this.baseUrl + '/updateBio').html(' (edit)').attr('id','updateBio');
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
  $('#actions').append('<br />');
  var text = json.externalId?json.externalId:'not set';
  $('#actions').append('External ID: ');
  if(json.allowEditExternalId){
    var a = $('<a>').attr('href', this.baseUrl + '/editExternalId').html(text);
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
          $.get(self.baseUrl + '/refreshSirPage/',function(html){
            var div = $('<div>');
            div.html(html);
            var pageId = $(div).attr('id');
            $('#sirPages').append(div);
          });
        }
      };
      self.createForm(json.data.form, obj);
    });
    return false;
  });

  $('#decisions a.action').click(function(e){
    $.get($(e.target).attr('href'),function(json){
      var id = $(e.target).attr('id');
      if(id == 'decisionundoAcceptOffer' || id == 'decisionundoDeclineOffer'){
        $('#sirPages').empty();
      }
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
      {title: 'Accept Offer', action: 'acceptOffer', className: 'actionForm'},
      {title: 'Decline Offer', action: 'declineOffer', className: 'actionForm'},
      {title: 'Undo Offer Response', action: 'undoAcceptOffer', className: 'action'},
      {title: 'Undo Offer Response', action: 'undoDeclineOffer', className: 'action'}
    ];
    for(var i = 0; i < types.length; i++){
      if(json['allow'+types[i].action]){
        var a = $('<a>').attr('id', 'decision'+types[i].action).attr('href', this.baseUrl + '/' + types[i].action).addClass(types[i].className).html(types[i].title +'<br />');
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
  var currentTags = [];
  $('#tags').empty();
  var ul = $('<ul>');
  for(var i=0; i<json.tags.length; i++){
    var li = $('<li>').html(json.tags[i].title).attr('id', 'tag'+json.tags[i].id);
    currentTags.push(json.tags[i].title);
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
    if($.inArray(value, currentTags) != -1){
      var div = $('<div>').html('"' + value + '" has already been applied to this applicant and cannot be applied again.');
      $(div).dialog({
        modal: true,
        buttons: {
          Ok: function() {
            $( this ).dialog( "close" );
          }
        }
      });
    } else {
      $.post(self.baseUrl + '/addTag',{tagTitle: value},function(json){
        self.refreshTags(json.data.result.tags);
      });
    }
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
  this.parseLonganswertext($('#page'+pageId));
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

/**
 * Parse Reloads
 * Some links do something and then reload the page
 */
Applicant.prototype.parseReloads = function(){
  var self = this;
  $('a.reload').click(function(e){
    $.get($(e.target).attr('href'),function(json){
      if(json.data.result.message.length > 0){
        var div = $('<div>');
        div.append($('<p>').html(json.data.result.message));
        div.dialog({
          modal: true,
          autoOpen: true,
          position: 'center',
          width: 800,
          overlay: {
            backgroundColor: '#fff',
            opacity: 0.8
          },
          buttons: {
            "OK": function() {
                $( this ).dialog( "close" );
            }
          },
          close: function() {
            div.dialog("destroy").remove();
            $('#container').fadeOut(500);
            window.location.href = json.data.result.path;
          }
        });
      }
    });
    return false;
  });
};

/**
 * Parse long text
 * Look in applican answers for long text and format it for easier reading
 * @param {jQuery} obj
 */
Applicant.prototype.parseLonganswertext = function(obj){
  var self = this;
  $('div.answers tbody td.answerElement', obj).each(function(i){
    var td = $(this);
    var string = td.text();
    if(string.length > 125){
      //use the HTML representation for the actual conversion otherwise special
      //html entites cause problems.
      var string = td.html();
      var shortString = string.substr(0, 100);
      td.data('fullText', string);
      td.addClass('truncated-text');
      td.html(shortString + '&hellip;').append($('<br>'));
      td.append($('<a>').attr('href', '#').html('(click for more)').bind('click', function(e){
        //expand all the string on this row
        $('td.truncated-text', $(e.target).parent().parent()).each(function(i){
          $(this).html($(this).data('fullText'));
        });
        return false;
      }));
    }
  });
};