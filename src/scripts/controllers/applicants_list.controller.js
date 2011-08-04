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
  var table = $('<table>');
  var tr = $('<tr>');
  tr.append($('<th>').append($('<input>').attr('type', 'checkbox').attr('checked', true).bind('click', function(e){
    $('#selectors tr>td>input').each(function(){
      $(this).attr('checked', ($(e.target).is(':checked')));
    });
    $('#selectors tr>td>input').trigger('change');
  })));
  tr.append($('<th>').html('Tags'));
  table.append(tr);
  
  for(var i=0; i < tags.length; i++){
    var tr = $('<tr>');
    tr.append($('<td>').append($('<input>').attr('type', 'checkbox').attr('checked', true).data('tableId', tags[i].id)));
    tr.append($('<td>').html(tags[i].title));
    table.append(tr);
  }
  $('#selectors').append(table);
  $('#selectors tr>td>input').bind('change', function(e){
    if($(e.target).is(':checked')){
      $('#' + $(e.target).data('tableId')).show();
    } else {
      $('#' + $(e.target).data('tableId')).hide();
    }
  });
});