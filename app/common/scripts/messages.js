function Messages(){
  var self = this;
  
  this.create = function(type, text){
    $('#messages').append($('<p>').addClass(type).html(text).slideDown('slow').delay(10000).fadeOut('slow').slideUp('slow'));
  }
}