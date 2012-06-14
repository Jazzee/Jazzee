/**
 * Javascript for the manage_roles controller
 */
$(document).ready(function(){
  var link = $('<a>').html(' (check all)').attr('href','#').bind('click', function(e){
    $(':checkbox', $(this).parent().parent().parent()).prop("checked", true);
    return false;
  });
  $('div.CheckboxList div.label label').append(link);
  
  var link = $('<a>').html('check everything').attr('href','#').bind('click', function(e){
    $(':checkbox').prop("checked", true);
    return false;
  });
  $('div.CheckboxList:first').before($('<div>').addClass('yui-gf').append($('<div>').addClass('yui-u').append(link)));
});