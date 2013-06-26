function Status(canvas, messageCanvas){
  this.counter = 0;
  this.canvas = canvas;
  this.messageCanvas = messageCanvas;
  this.activeMessages = [];
  $(canvas).append($('<div>').attr('id', 'status-bar').append($('<img src="./index.php?url=resource/foundation/media/ajax-bar.gif">').hide()));
  $(canvas).append($('<div>').attr('id', 'status-message'));
  $(document).delegate('.qtip.jgrowl', 'mouseover mouseout', this.messageTimer);
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
  if(this.counter == 0) $('#status-bar img').delay(500).fadeOut(2000);
};

Status.prototype.addMessage = function(type, text){
  var self = this;
  if(text.length > 0){
    if($.inArray(text, this.activeMessages) == -1){
      this.activeMessages.push(text);
      // Use the last visible jGrowl qtip as our positioning target
      var target = $('.qtip.jgrowl:visible:last');
      // Create your jGrowl qTip...
      $(document.body).qtip({
          content: {
            text: text
          },
          position: {
            my: 'top right', // Not really important...
            at: (target.length ? 'bottom' : 'top') + ' right', // If target is window use 'top right' instead of 'bottom right'
            target: target.length ? target : $(self.messageCanvas), // Use our target declared above
            adjust: {y: 1} // Add some vertical spacing
          },
          show: {
            event: false, // Don't show it on a regular event
            ready: true, // Show it when ready (rendered)
            effect: function() {$(this).stop(0,1).fadeIn(400);}, // Matches the hide effect
            delay: 0 // Needed to prevent positioning issues

          },
          hide: {
            event: false, // Don't hide it on a regular event
            effect: function(api) {
              // Do a regular fadeOut, but add some spice!
              $(this).stop(0,1).hide('puff').queue(function() {
                // Destroy this tooltip after fading out
                api.destroy();
                var text = api.get('content.text');
                self.activeMessages = $.grep(self.activeMessages, function(value) {
                  return value != text;
                });
              });
            }
          },
          style: {
            classes: 'jgrowl ui-tooltip-light ui-tooltip-rounded '+type, // Some nice visual classes
            tip: false // No tips for this one (optional ofcourse)
          },
          events: {
            render: function(event, api) {
                // Trigger the timer (below) on render
                self.messageTimer(event);
            }
          }
      })
      .removeData('qtip');
    }
  }
};

/**
 * Destoy messages after a timeout period
 */
Status.prototype.messageTimer = function(event){
  var lifeSpan = 7000; //7 seconds
  var api = $(event.target).parent().data('qtip');
  // Otherwise, start/clear the timer depending on event type
  clearTimeout(api.timer);
  if(event.type !== 'mouseover') {
    api.timer = setTimeout(api.hide, lifeSpan);
  }
};