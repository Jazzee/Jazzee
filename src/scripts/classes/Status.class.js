function Status(canvas){
  this.counter = 0;
  this.canvas = canvas;
  $(canvas).append($('<div>').attr('id', 'status-bar').append($('<img src="./index.php?url=resource/foundation/media/ajax-bar.gif">').hide()));
  $(canvas).append($('<div>').attr('id', 'status-message'));
}

Status.prototype.start = function(){
  this.counter++;
  this.updateBar();
};

Status.prototype.end = function(){
  this.counter--;
  this.updateBar();
};

Status.prototype.updateBar = function(){
  if(this.counter > 0) $('#status-bar img').show();
  if(this.counter == 0) $('#status-bar img').fadeOut(2000);
};

Status.prototype.addMessage = function(type, text){
  $('#status-message').append($('<p>').addClass(type).html(text).slideDown('slow').delay(5000).fadeOut('slow').slideUp('slow'));
};