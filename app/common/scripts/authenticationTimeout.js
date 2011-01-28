function AuthenticationTimeout(cookieName){
  var self = this;
  this.cookieName = cookieName;
  this.dialog;
  this.interval;
  
  this.start = function(){
    this.interval = setInterval(this.checkStatus,30000);
    this.dialog = $('<div>').append($('<p>'));
    this.dialog.append($('<input type="button" value="Yes">').bind('click', function(){
      $.get(document.location.href);
      self.dialog.dialog('close');
    }));
    this.dialog.append($('<input type="button" value="No">').bind('click', function(){
      self.dialog.dialog('close');
    }));
    this.dialog.dialog({width: 500, autoOpen: false, modal: true });
  }
  
  this.checkStatus = function(){
    var cookieTimeout = $.cookie(self.cookieName);
    var currentTime = Math.round(new Date().getTime() / 1000);
    var timeleft = cookieTimeout - currentTime;
    if(timeleft < -10 ){
      location.reload(true);
    } else if(timeleft < 300){
      self.showWarning(timeleft);
    } else {
      self.hideWarning();
    }
  }
  
  this.showWarning = function(secondsLeft){
    if(secondsLeft <= 60) var timeLeft = 'less than one minute';
    else var timeLeft = 'about ' + Math.round(secondsLeft/60) + ' minutes';
    var text = 'Your session will expire in ' + timeLeft + '. <br />Do you want to stay logged in?';
    $('p', this.dialog).html(text);
    this.dialog.dialog('open');
  }
  
  this.hideWarning = function(){
    $('p', this.dialog).html('');
    this.dialog.dialog('close');
  }
}