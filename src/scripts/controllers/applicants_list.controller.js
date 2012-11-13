/**
 * Javascript for the apply_page controller
 * Everything in hear needs to be value added so JS isn't necessary for applicants
 */
$(document).ready(function(){
  var services = new Services;
  var cookieName = 'applicant_list_tags' + services.getCurrentApplicationId();
  var tags = [];
  $('table').each(function(i){
    var id = $(this).attr('id');
    var title = $('caption span', this).text();
    var volume = $('tr', this).length;
    //put a modifier on Not Locked otherwise it gums up the cloud
    if(title == 'Not Locked'){
      var volume = volume = 1;
    }
    tags.push({id: id, title: title, volume: volume});
  });
  var list = $('<ul>');
  var selectedTags = $.cookie(cookieName);
  if(selectedTags){
    selectedTags = selectedTags.split('&;&');
  }
  var cloud = $('<div>');
  for(var i=0; i < tags.length; i++){
    var piece = $('<span>')
      .css('margin', '.5em')
      .css('cursor', 'pointer')
      .html(tags[i].title)
      .attr('rel', tags[i].volume)
      .data('tableId', tags[i].id);
    if(!selectedTags || $.inArray(tags[i].id, selectedTags) > -1){
      piece.css('color', 'blue');
      piece.data('selected', true);
      $('#'+tags[i].id).show();
    } else {
      piece.css('color', 'grey');
      piece.data('selected', false);
      $('#'+tags[i].id).hide();
    }
    piece.bind('click', function(){
      var selected = !$(this).data('selected');
      if(selected){
        $(this).css('color', 'blue');
        $('#'+$(this).data('tableId')).show();
      } else {
        $(this).css('color', 'grey');
        $('#'+$(this).data('tableId')).hide();
      }
      $(this).data('selected', selected);
    var arr = [];
    $('#selectors span[rel]').each(function(){
      if($(this).data('selected')){
        arr.push($(this).data('tableId'));
      }
    });
    if(arr.length > 0){
      $.cookie(cookieName, arr.join('&;&'));
    } else {
      $.cookie(cookieName, null);
    }
    });
    cloud.append(piece);
  }
  
  var piece = $('<span>')
    .css('margin', '.5em')
    .css('cursor', 'pointer')
    .css('color', 'blue')
    .html('Select All');
  piece.bind('click', function(){
    $('#selectors span[rel]').each(function(){
      if(!$(this).data('selected')){
        $(this).click();
      }
    });
  });
  cloud.append(piece);
  
  var piece = $('<span>')
    .css('margin', '.5em')
    .css('cursor', 'pointer')
    .css('color', 'grey')
    .html('Select None');
  piece.bind('click', function(){
    $('#selectors span[rel]').each(function(){
      if($(this).data('selected')){
        $(this).click();
      }
    });
  });
  cloud.append(piece);
  
  $('#selectors').append(cloud);
  $('#selectors span').tagcloud({size: {start: .9, end: 2, unit: 'em'}})
});