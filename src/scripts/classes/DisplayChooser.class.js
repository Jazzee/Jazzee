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
  var displays = this.services.getDisplays();
  var dropdown = $('<select>').attr('id', 'displayChooserSelect');
  $.each(displays, function(){
    dropdown.append($('<option>').html(this.getName()).attr('value', this.getId()).data('display', this));
  });
  this.div.append($('<label>').html('Display: ').attr('for', 'displayChooserSelect'));
  this.div.append(dropdown);
  
  $('#displayChooserSelect').val(this.services.getPreference(this.preferenceName));

  dropdown.bind('change', function(e){
    self.services.setPreference(self.preferenceName, $(e.target).val());
    self.callbacks.fire(self.getCurrentDisplay());
  });

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