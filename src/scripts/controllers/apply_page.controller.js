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
});