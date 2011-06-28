/**
 * Javascript for the apply_page controller
 * Everything in hear needs to be value added so JS isn't necessary for applicants
 */
$(document).ready(function(){
  var tags = [];
  $('table').each(function(i){
    var id = $(this).attr('id');
    var title = $('caption', this).text();
    tags.push({id: id, title: title});
  });
  var ul = $('<ul>');
  for(var i=0; i < tags.length; i++){
    var li = $('<li>').html(tags[i].title).prepend($('<input>').attr('type', 'checkbox').attr('checked', true).data('tableId', tags[i].id));
    ul.append(li);
  }
  $('#selectors').append(ul);
  $('#selectors input').bind('click', function(e){
    if($(e.target).is(':checked')){
      $('#' + $(e.target).data('tableId')).show();
    } else {
      $('#' + $(e.target).data('tableId')).hide();
    }
  });
});