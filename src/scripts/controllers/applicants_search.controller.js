/**
 * Javascript for the apply_page controller
 * Everything in hear needs to be value added so JS isn't necessary for applicants
 */
$(document).ready(function(){
  var changeProgram = new ChangeProgram();
  changeProgram.init();
  
  $('a.applicantLink').each(function(i){
    if(changeProgram.check($(this).attr('programId'))){
      $(this).bind('click', function(){
        changeProgram.changeTo($(this).attr('programId'));
        return true;
      });
    }
  });
});