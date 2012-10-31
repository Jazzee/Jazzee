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
  var cookie = $.cookie('applicant_list_tags');
  if(null == cookie){
    cookie = '';
    $.cookie('applicant_list_tags', cookie);
  }
  var selectedTags = cookie.split(';');
  for(var i=0; i < tags.length; i++){
    var tr = $('<tr>');
    var input = $('<input>').attr('id', 'selectTag_'+tags[i].id).attr('type', 'checkbox').data('tableId', tags[i].id);
    if(selectedTags.length <= 1 || $.inArray(tags[i].id, selectedTags) > -1){
      input.attr('checked', true);
      $('#'+tags[i].id).show();
    } else {
      input.attr('checked', false);
      $('#'+tags[i].id).hide();
    }
    tr.append($('<td>').append(input));
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
    var arr = [];
    $('#selectors tr>td>input:checked').each(function(){
      arr.push($(this).data('tableId'));
    });
    var str = arr.join(';');
    $.cookie('applicant_list_tags', str);
  });
  var tr = $('<tr>');
  var input = $('<input>').attr('type', 'checkbox').bind('click', function(e){
    $('#selectors tr>td>input').each(function(){
      $(this).attr('checked', ($(e.target).is(':checked')));
    });
    $('#selectors tr>td>input').trigger('change');
  });
  tr.append($('<th>').append(input));
  tr.append($('<th>').html('Tags'));
  table.prepend(tr);
});