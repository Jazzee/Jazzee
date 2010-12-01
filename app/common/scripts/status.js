function Status(canvas){
  this.counter = 0;
  this.canvas = canvas;
  
  this.start = function(){
    this.counter++;
    this.updateBar();
  }
  
  this.end = function(){
    this.counter--;
    this.updateBar();
  }
  
  this.updateBar = function(){
    if(this.counter > 0) $('#status-bar img').show();
    if(this.counter == 0) $('#status-bar img').fadeOut(2000);
  }
  
  this.addMessage = function(type, text){
    $('#status-message').append($('<p>').addClass(type).html(text).slideDown('slow').delay(5000).fadeOut('slow').slideUp('slow'));
  }
  
  $(canvas).append($('<div>').attr('id', 'status-bar').append($('<img src="resource/common/media/ajax-bar.gif">').hide()));
  $(canvas).append($('<div>').attr('id', 'status-message'));
}