/**
 * The DisplayChooser widget for picking a display
 * 
 * @param string displayTypeName usually the controller name that the display shoudl apply to
 */
function DisplayChooser(displayTypeName){
  this.services = new Services;
  this.div = $('<div>').attr('id', 'display_chooser');
  $('#widgets').append(this.div);
  this.preferenceName = 'currentDisplay' + displayTypeName;
  
  //Bind functions to changes in the display
  this.callbacks = $.Callbacks();

};

/**
 * Initialize the display
 */
DisplayChooser.prototype.init = function(){
  var self = this;
  this.div.empty();
  this.div.append($('<label>').html('Display: ').attr('for', 'displayChooserSelect'));
  this.div.append(this.dropdown());
  this.chooseDisplay(this.services.getPreference(this.preferenceName));
};

/**
 * Initialize the display
 */
DisplayChooser.prototype.dropdown = function(){
  var self = this;
  var displays = this.services.getDisplays();
  var dropdown = $('<select>').attr('id', 'displayChooserSelect');
  $.each(displays, function(){
    dropdown.append($('<option>').html(this.getName()).attr('value', this.getId()).data('display', this));
  });
  dropdown.bind('change', function(e){
    var display = self.getCurrentDisplay();
    self.services.setPreference(self.preferenceName, display.getId());
    self.chooseDisplay(display.getId());
  });
  
  return dropdown;
};

/**
 * Bind a function to the display change
 * @param {} fn
 */
DisplayChooser.prototype.bind = function(fn){
  this.callbacks.add(fn);
};

/**
 * Get the current display
 * @return {Display}
 */
DisplayChooser.prototype.getCurrentDisplay = function(){
  return $('option', this.div).filter(':selected').data('display');
};

/**
 * Create the link to edit a display
 * 
 * @return {jQuery}
 */
DisplayChooser.prototype.editLink = function(){
  var self = this;
  var a = $('<a>').attr('href', '#').html('edit');
  a.on('click', function(){
    var overlay = $('<div>').attr('id', 'loaddisplayoverlay');
    overlay.dialog({
      height: 90,
      modal: true,
      autoOpen: false,
      create: function(event, ui){
        $(".ui-dialog-titlebar", ui.dialog).hide();
        var label = $('<div>').addClass('label').html('Loading Display...').css('float', 'left').css('margin','10px 5px');
        var progressbar = $('<div>').addClass('progress').append(label);
        overlay.append(progressbar);
        progressbar.progressbar({value: false});
        overlay.dialog('open');
      },
      open: function(event, ui){
        var displayManagerDiv = $('<div>').attr('id', 'displaymanagercontainer');
        displayManagerDiv.css("overflow-y", "auto");
        displayManagerDiv.dialog({
          modal: true,
          autoOpen: false,
          position: 'center',
          width: '90%',
          height: 500,
          close: function(event, ui) {
            displayManagerDiv.dialog("destroy").remove();
          },
          create: function(event, ui) {
            //use a setTimeout here to take this out of the document flow so the overlay gets built and displayed first
            setTimeout(function(){
              var displayManager = new DisplayManager(self.getCurrentDisplay(), self.services.getCurrentApplication());
              displayManager.init($('#displaymanagercontainer'));
              $('#displaymanagercontainer').dialog('open');
            }, 1); 
          },
          open: function(event, ui) {
            overlay.dialog('destroy').remove();
          },
          buttons: [ 
            {
              text: "Save", click: function() { 
                var display = self.getCurrentDisplay();
                DisplayManager.save(self.services.getControllerPath('admin_managedisplays'),display);
                $('#displayChooserSelect').replaceWith(self.dropdown());
                $(this).dialog("destroy").remove();
                self.chooseDisplay(display.getId());
            }},
            {
              text: "Delete Display", click: function() { 
                DisplayManager.remove(self.services.getControllerPath('admin_managedisplays'),self.getCurrentDisplay());
                $('#displayChooserSelect').replaceWith(self.dropdown());
                self.chooseDisplay('min');
                $(this).dialog("destroy").remove();
            }}
          ]
        });
        
      }
    });
    
    return false;
  });
  
  return a;
};

/**
 * Choose a display programatically
 * 
 * @param Integer displayId
 */
DisplayChooser.prototype.chooseDisplay = function(displayId){
  if($('#displayChooserSelect').val() != displayId){
    $('#displayChooserSelect').val(displayId);
  };
  var display = this.getCurrentDisplay();
  this.callbacks.fire(display);
  $('a', this.div).remove();
  if(display.getType() == 'user'){
    this.div.append(this.editLink());
  }
  this.div.append(this.newLink());
};

/**
 * Create a new display
 * 
 * @return {jQuery}
 */
DisplayChooser.prototype.newLink = function(){
  var self = this;
  var a = $('<a>').attr('href', '#').html('new');
  a.data('chooser', this);
  a.bind('click', function(){
    $.get(self.services.getControllerPath('admin_managedisplays') + '/new', function(json){
      self.init();
      self.chooseDisplay(json.data.result);
      $('a', self.div).first().click();
    });
    return false;
  });

  return a;
};