/**
 * Javascript for the applicants_decisions controller
 */
$(document).ready(function(){
  var link = $('<a>').html(' (check all)').bind('click', function(e){
    $(':checkbox', $(this).parent().parent().parent()).prop("checked", true);
  });
  $('label[for="applicants"]').append(link);
});