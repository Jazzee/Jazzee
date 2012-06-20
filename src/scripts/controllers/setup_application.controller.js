/**
 * Javascript for the setup_application controller
 */
$(document).ready(function(){
  $('input.DateInput').each(function(i){
    var input = $(this);
    if(input.val().length < 1){
      jazzeeSetupApplicationShowButton(input);
    } else {
      jazzeeSetupApplicationDatePicker(input);
    }
  });
});

/**
 * Setup the data picker on an input element
 */
function jazzeeSetupApplicationDatePicker(input){
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
    jazzeeSetupApplicationShowButton(input);
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
function jazzeeSetupApplicationShowButton(input){
  var button = $('<button>').html('Pick Date');
  button.button({
    icons: {
      primary: 'ui-icon-plus'
    }
  });
  button.bind('click', function(e){
    input.show();
    jazzeeSetupApplicationDatePicker(input);
    $(this).remove();
  });
  input.after(button);
  input.hide();
};