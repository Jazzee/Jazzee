/**
 * Javascript for the applicants_create controller
 */
$(document).ready(function(){
  $('input.DateInput').each(function(i){
    var input = $(this);
    if(input.val().length < 1){
      jazzeeApplicantsCreateShowButton(input);
    } else {
      jazzeeApplicantsCreateDatePicker(input);
    }
  });
});

/**
 * Setup the data picker on an input element
 */
function jazzeeApplicantsCreateDatePicker(input){
  var button = $('<button>').html('Clear');
  button.button({
    icons: {
      primary: 'ui-icon-trash'
    }
  });
  button.bind('click', function(e){
    var input = $('input', $(this).parent());
    input.val('');
    input.AnyTime_noPicker();
    $(this).remove();
    jazzeeApplicantsCreateShowButton(input);
    return false;
  });
  input.after(button);
  input.AnyTime_noPicker().AnyTime_picker(
    {format: "%Y-%m-%dT%T%:",
          formatUtcOffset: "%: (%@)",
          hideInput: true,
          placement: "inline"}
  );
  
};

/**
 * Setup the data picker on an input element
 */
function jazzeeApplicantsCreateShowButton(input){
  var button = $('<button>').html('Pick Date');
  button.button({
    icons: {
      primary: 'ui-icon-plus'
    }
  });
  button.bind('click', function(e){
    input.show();
    jazzeeApplicantsCreateDatePicker(input);
    $(this).remove();
  });
  input.after(button);
  input.hide();
};