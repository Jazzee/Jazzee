/**
 * Javascript for the apply_page controller
 * Everything in hear needs to be value added so JS isn't necessary for applicants
 */
$(document).ready(function(){
  //Add the datepicker to the DateInput element
  $('div.form input.DateInput').datepicker({
		showOn: "button",
		buttonImage: "resource/foundation/media/icons/calendar_edit.png",
		buttonImageOnly: true
	});
  
  $('div.field p.instructions').each(function(i){
    var p = $(this);
    p.hide();
    var label = $('label', $(this).siblings('div.element').first()).first();
    var img = $('<img>').attr('src', 'resource/foundation/media/icons/information.png').attr('title', p.html());
    img.click(function(e){
      p.toggle('slide',{direction: 'up'});
      $(this).hide();
    });
    label.append(img);
  });
});