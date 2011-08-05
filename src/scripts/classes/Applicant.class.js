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
  $.get(this.baseUrl + '/refresh',function(json){
    self.workspace
      .append($('<div>').attr('id', 'bio'))
      .append($('<div>').attr('id', 'status'))
      .append($('<div>').attr('id', 'pages'))
      .append($('<div>').attr('id', 'attachments'));
    var statusTable = $('<table>').attr('id', 'statusTable');
    statusTable.append($('<thead>').append('<tr><th>Actions</th><th>Admission Status</th><th>Tags</th></tr>'));
    statusTable.append($('<tbody>').append('<tr><td id="actions"></td><td id="decisions"></td><td id="tags"></td></tr>'));
    $('#status').html(statusTable);
    self.displayBio(json.data.result.bio);
    self.displayActions(json.data.result.actions);
    self.displayDecisions(json.data.result.decisions);
    self.displayTags(json.data.result.tags);
    self.displayPages(json.data.result.pages);
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
          callback.display(json);
        } else {
          applicant.createForm(json.data.form, callback);
        }
      }); //end iFrame Load
    });//end submit
};

/**
 * Display Biographic information
 * @param Object json
 */
Applicant.prototype.displayBio = function(json){
  $('#bio').empty().hide();
  var self = this;
  var h1 = $('<h1>').html(json.name);
  if(json.allowEdit){
    var a = $('<a>').attr('href', this.baseUrl + '/updateBio').html(' (edit)');
    a.click(function(e){
      $.get($(e.target).attr('href'),function(json){
        var obj = {
          display: function(json){
            self.displayBio(json.data.result.bio);
          }
        };
        self.createForm(json.data.form, obj);
      });
      return false;
    });
    h1.append(a);
  }
  $('#bio').append(h1);
  $('#bio').append($('<h4>').html(json.email));
  $('#bio').show();
};

/**
 * Display Actions
 * @param Object json
 */
Applicant.prototype.displayActions = function(json){
  $('#actions').empty();
  var self = this;
  $('#actions').html(
    'Account Created: ' + json.createdAt.date + '<br />' + 
    'Last Update: ' + json.updatedAt.date + '<br />' + 
    'Last Login: ' + json.lastLogin.date + '<br />'
  );
};



/**
 * Display Actions
 * @param Object json
 */
Applicant.prototype.displayDecisions = function(json){
  $('#decisions').empty();
  var self = this;
  if(json.isLocked){
    $('#decisions').html('Status: ' + json.status + '<br />');
    if(json.allowfinalAdmit){
      var a = $('<a>').attr('href', this.baseUrl + '/finalAdmit').html('Admit Applicant<br />');
      a.click(function(e){
        $.get($(e.target).attr('href'),function(json){
          var obj = {
            display: function(json){
              self.displayDecisions(json.data.result.decisions);
            }
          };
          self.createForm(json.data.form, obj);
        });
        return false;
      });
      $('#decisions').append(a);
    }
    if(json.allowfinalDeny){
      var a = $('<a>').attr('href', this.baseUrl + '/finalDeny').html('Deny Applicant<br />');
      a.click(function(e){
        $.get($(e.target).attr('href'),function(json){
          var obj = {
            display: function(json){
              self.displayDecisions(json.data.result.decisions);
            }
          };
          self.createForm(json.data.form, obj);
        });
        return false;
      });
      $('#decisions').append(a);
    }
    var types = [
      {title: 'Nominate for Admission', action: 'nominateAdmit'},
      {title: 'Undo Nomination', action: 'undoNominateAdmit'},
      {title: 'Nominate for Deny', action: 'nominateDeny'}, 
      {title: 'Undo Nomination', action: 'undoNominateDeny'}, 
      {title: 'Undo Decision', action: 'undoFinalAdmit'}, 
      {title: 'Undo Decision', action: 'undoFinalDeny'}, 
      {title: 'Undo Offer Response', action: 'undoAcceptOffer'}, 
      {title: 'Undo Offer Response', action: 'undoDeclineOffer'}        
    ];
    for(var i = 0; i < types.length; i++){
      if(json['allow'+types[i].action]){
        var a = $('<a>').attr('href', this.baseUrl + '/' + types[i].action).html(types[i].title +'<br />');
        a.click(function(e){
          $.get($(e.target).attr('href'),function(json){
            self.displayDecisions(json.data.result.decisions);
          });
          return false;
        });
        $('#decisions').append(a);
      }
    }
  } else {$('#decisions').html('Status: Not Complete<br />');}
  if(json.allowUnlock && json.isLocked){
    var a = $('<a>').attr('href', this.baseUrl + '/unlock').html('Unlock Application <br />');
    a.click(function(e){
      $.get($(e.target).attr('href'),function(json){
        self.displayDecisions(json.data.result.decisions);
      });
      return false;
    });
    $('#decisions').append(a);
  }
  if(json.allowLock && !json.isLocked){
    var a = $('<a>').attr('href', this.baseUrl + '/lock').html('Lock Application <br />');
    a.click(function(e){
      $.get($(e.target).attr('href'),function(json){
        self.displayDecisions(json.data.result.decisions);
      });
      return false;
    });
    $('#decisions').append(a);
  }
};

/**
 * Display Tags
 * @param Object tags
 */
Applicant.prototype.displayTags = function(json){
  var self = this;
  $('#tags').empty();
  var ul = $('<ul>');
  for(var i=0; i<json.tags.length; i++){
    var li = $('<li>').html(json.tags[i].title).data('tagId', json.tags[i].id);
    if(json.allowRemove){
      li.prepend($('<img>').attr('src',self.baseUrl + '/resource/foundation/media/icons/delete.png').click(function(e){
        $.post(self.baseUrl + '/removeTag',{tagId: $(e.target).parent().data('tagId')},function(json){
          self.displayTags(json.data.result.tags);
        });
      })
      );
    }
    ul.append(li);
  }
  $('#tags').append(ul);
  if(json.allowAdd){
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
        self.displayTags(json.data.result.tags);
      });
      return false;
    });
    $('#tags').append(form);
  }
};

/**
 * Display Pages
 * @param Object json
 */
Applicant.prototype.displayPages = function(json){
  var self = this;
  $('#pages').empty();
  for(var i=0; i<json.pages.length; i++){
    var div = $('<div>').attr('id','page'+json.pages[i].id).data('pageId', json.pages[i].id);
    div.html(json.pages[i].content);
    $('#pages').append(div);
    this.catchPageLinks(json.pages[i].id);
  }
};

/**
 * Display Pages
 * @param Int pageId
 */
Applicant.prototype.displayPage = function(pageId){
  var self = this;
  $.get(this.baseUrl + '/refreshPage/' + pageId,function(html){
    $('#page'+pageId).html(html);
    self.catchPageLinks(pageId);
  });
};

/**
 * Catch the links on a page
 * @param Int pageId
 */
Applicant.prototype.catchPageLinks = function(pageId){
  var self = this;
  $('#page'+pageId + ' a.actionForm').click(function(e){
    $.get($(e.target).attr('href'),function(json){
      var obj = {
        display: function(json){
          self.displayPage(pageId);
        }
      };
      self.createForm(json.data.form, obj);
    });
    return false;
  });
  $('#page'+pageId + ' a.action').click(function(e){
    $.get($(e.target).attr('href'),function(json){
      self.displayPage(pageId);
    });
    return false;
  });
};

