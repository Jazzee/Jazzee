/**
 * Applicants View class for handling page features
 */
function ApplicantsView(){
  /**
   * Because variable this is so squirley ensure that we always have access to the class as self
   * @var ApplicationView 
   */
  var self = this;
  
  /**
   * init is called when the page is loaded and starts the fun
   */
  this.init = function(){
    $('a.editApplicant').unbind().bind('click', function(e){
      e.preventDefault();
      $.get($(this).attr('href'), function(json){
        var form = new Form();
        self.createForm(form.create(json.data.form));
      });
    });
    $('a.unlock').unbind().bind('click', function(e){
      e.preventDefault();
      $.get($(this).attr('href'), function(json){
        console.log(json);
        if(json.status == 'success'){
          window.location.reload();
        }
      });
    });
    $('a.decision').unbind().bind('click', function(e){
      e.preventDefault();
      $.get($(this).attr('href'), function(json){
        console.log(json);
        if(json.status == 'success'){
          window.location.reload();
        }
      });
    });
    $('a.extendDeadline').unbind().bind('click', function(e){
      e.preventDefault();
      $.get($(this).attr('href'), function(json){
        var form = new Form();
        self.createForm(form.create(json.data.form));
      });
    });
    $('a.editAnswer').unbind().bind('click', function(e){
      e.preventDefault();
      $.get($(this).attr('href'), function(json){
        var form = new Form();
        self.createForm(form.create(json.data.form));
      });
    });
    $('a.addAnswer').unbind().bind('click', function(e){
      e.preventDefault();
      $.get($(this).attr('href'), function(json){
        var form = new Form();
        self.createForm(form.create(json.data.form));
      });
    });
    $('a.verifyAnswer').unbind().bind('click', function(e){
      e.preventDefault();
      $.get($(this).attr('href'), function(json){
        var form = new Form();
        self.createForm(form.create(json.data.form));
      });
    });
    $('a.attachAnswerPDF').unbind().bind('click', function(e){
      e.preventDefault();
      $.get($(this).attr('href'), function(json){
        var form = new Form();
        self.createForm(form.create(json.data.form));
      });
    });
    $('a.deleteAnswer').unbind().bind('click', function(e){
      e.preventDefault();
      $.get($(this).attr('href'), function(json){
        console.log(json);
        if(json.status == 'success'){
          window.location.reload();
        }
      });
    });
  }
  
  /**
   * Build a modal dialog form from the forms HTML
   * @param string formHTML
   */
  this.createForm = function(formHTML){
    var div = $('<div>').insertAfter('#container');
    div.css("overflow-y", "auto");
    div.html(formHTML);
    div.dialog({
      modal: true,
      autoOpen: true,
      position: 'center',
      width: 700,
      overlay: {
        backgroundColor: '#fff',
        opacity: 0.8
      },
      close: function() {
        div.dialog("destroy").remove();
      }
    });
    $('form', div).unbind().bind('submit',function(e){
      //give our iframe a unique name from the timestamp
      var iFrameName = "iFrame" + (new Date().getTime());
      var iFrame = $("<iframe name='" + iFrameName + "' src='about:blank' />").insertAfter('#container');
      iFrame.css("display", "none");
      e.target.target = iFrameName;
      iFrame.load(function(e){
        var json = eval("(" + $(this).contents().find('textarea').get(0).value + ")");
        div.dialog("destroy").remove();
        if(json.status == 'error'){
          var form = new Form();
          var formHTML = form.create(json.data.form);
          self.createForm(formHTML);
        } else {
          window.location.reload();
        }
      }); //end iFrame Load
    });//end submit
  }
}

$(document).ready(function(){
  var status = new Status($('#status'));
  $(document).ajaxError(function(e, xhr, settings, exception) {
    status.addMessage('error','There was an error with your request, please try again.');
  });
  
  $(document).ajaxComplete(function(e, xhr, settings) {
    if(xhr.getResponseHeader('Content-Type') == 'application/json'){
      eval("var json="+xhr.responseText);
      $(json.messages).each(function(i){
        status.addMessage(this.type, this.text);
      });
    }
  });
  //Ajax activity indicator bound to ajax start/stop document events
  $(document).ajaxStart(function(){
    status.start();
  }).ajaxStop(function(){
    status.end();
  });
  
  var view = new ApplicantsView;
  view.init();
});