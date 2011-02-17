/**
 * Javascript for the apply_page controller
 * Everything in hear needs to be value added so JS isn't necessary for applicants
 */
$(document).ready(function(){
  //Add the datepicker to the DateInput element
  $('div.form input.DateInput').datepicker();
  
  //Replace the dropdowns in ShortDateElement with a datepicker
  var div = $('div.ShortDateInput div.control');
  var hiddenInput = $('input', div);
  var input = $('<input>').attr('type', 'text').attr('name', hiddenInput.attr('name'));
  input.attr('type', 'text');
  input.datepicker( {
    changeMonth: true,
    changeYear: true,
    showButtonPanel: true,
    dateFormat: 'MM yy',
    onClose: function(dateText, inst) { 
      var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
      var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
      $(this).datepicker('setDate', new Date(year, month, 1));
      inst.dpDiv.removeClass('monthpicker');
    },
    beforeShow : function(input, inst) {
      inst.dpDiv.addClass('monthpicker');
      if ((datestr = $(this).val()).length > 0) {
        var year = datestr.substring(datestr.length-4, datestr.length);
        var month = jQuery.inArray(datestr.substring(0, datestr.length-5), $(this).datepicker('option', 'monthNames'));
        $(this).datepicker('option', 'defaultDate', new Date(year, month, 1));
        $(this).datepicker('setDate', new Date(year, month, 1));
      }
    }
  });
  if(hiddenInput.attr('value').length > 0){
    var year = hiddenInput.attr('value').substr(0,4);
    var month = (hiddenInput.attr('value').substr(5,2)*1-1);
    input.attr('value', input.datepicker('option', 'monthNames')[month] + ' ' + year);
  }
  div.html(input);
});