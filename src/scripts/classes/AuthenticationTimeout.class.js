function AuthenticationTimeout(cookieName){
  this.cookieName = cookieName;
  this.dialog;
  this.interval;
};

AuthenticationTimeout.prototype.start = function(){
  var self = this;
  this.interval = setInterval(function(){
    self.checkStatus();
  },30000);
  this.dialog = $('<div>').append($('<p>'));
  this.dialog.append($('<input type="button" value="Yes">').bind('click', function(){
    $.get(document.location.href);
    self.dialog.dialog('close');
  }));
  this.dialog.append($('<input type="button" value="No">').bind('click', function(){
    self.dialog.dialog('close');
  }));
  this.dialog.dialog({width: 500, autoOpen: false, modal: true });
};
  
AuthenticationTimeout.prototype.checkStatus = function(){
  var cookieTimeout = $.cookie(this.cookieName);
  var currentTime = Math.round(new Date().getTime() / 1000);
  var timeleft = cookieTimeout - currentTime;
  if(timeleft < -10 ){
    location.reload(true);
  } else if(timeleft < 300){
    this.showWarning(timeleft);
  } else {
    this.hideWarning();
  }
};

AuthenticationTimeout.prototype.showWarning = function(secondsLeft){
  if(secondsLeft <= 60) var timeLeft = 'less than one minute';
  else var timeLeft = 'about ' + Math.round(secondsLeft/60) + ' minutes';
  var text = 'Your session will expire in ' + timeLeft + '. <br />Do you want to stay logged in?';
  $('p', this.dialog).html(text);
  this.dialog.dialog('open');
};

AuthenticationTimeout.prototype.hideWarning = function(){
  $('p', this.dialog).html('');
  this.dialog.dialog('close');
};