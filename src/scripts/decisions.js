function toggleAllBoxes(masterId, slaveClass){
  $('#'+masterId).bind('change', function(e){
    if($(e.target).attr('checked')){
      $('input.'+slaveClass).each(function(){
        if(!$(this).attr('checked')){
          $(this).attr('checked', true);
          $(this).trigger('change');
        }
      });
      $(e.target).attr('checked', true);
    } else {
      $('input.'+slaveClass).each(function(){
        if($(this).attr('checked')){
          $(this).removeAttr('checked');
          $(this).trigger('change');
        }
      });
    }
  });
  
  $('input.'+slaveClass).bind('change', function(e){
    if(!$(e.target).attr('checked')){
      //if we uncheck then uncheck the master box all
      $('#'+masterId).removeAttr('checked');
    }
  });
}

function preventDoubleCheck(className){
  $('input.'+className).bind('change',function(e){
    if($(e.target).attr('checked')){
      //uncheck anything else in the table row
      $('input',$(e.target).parent().parent()).each(function(){
        var box = $(this);
        if(box.attr('checked')){
          box.removeAttr('checked');
          box.trigger('change');
        }
      });
      //recheck the one that we unchecked
      $(e.target).attr('checked', true);
    }
  });
}

$(document).ready(function(){
  preventDoubleCheck('preliminaryAdmit');
  preventDoubleCheck('preliminaryDeny');
  preventDoubleCheck('undoPreliminaryAdmit');
  preventDoubleCheck('finalAdmit');
  preventDoubleCheck('undoPreliminaryDeny');
  preventDoubleCheck('finalDeny');
  toggleAllBoxes('preliminaryAdmitAll', 'preliminaryAdmit');
  toggleAllBoxes('preliminaryDenyAll', 'preliminaryDeny');
  toggleAllBoxes('undoPreliminaryAdmitAll', 'undoPreliminaryAdmit');
  toggleAllBoxes('finalAdmitAll', 'finalAdmit');
  toggleAllBoxes('undoPreliminaryDenyAll', 'undoPreliminaryDeny');
  toggleAllBoxes('finalDenyAll', 'finalDeny');
});