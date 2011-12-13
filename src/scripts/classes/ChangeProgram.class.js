/**
 * Allow users to change programs seemlessly
 * @param String path to the change program controller
 */
function ChangeProgram(){
  this.services = new Services;
  this.allowedPrograms = {};
  this.changeProgramPath = this.services.getControllerPath('admin_changeprogram');
};

ChangeProgram.prototype.init = function(){
  var self = this;
  if(this.services.checkIsAllowed('admin_changeprogram', 'getAllowedPrograms')){
    $.ajax({
      type: 'GET',
      url: this.changeProgramPath + '/getAllowedPrograms',
      async: false,
      success: function(json){
        for(var i=0; i<json.data.result.length; i++){
          self.allowedPrograms[json.data.result[i].id] = json.data.result[i].name;
        }
      }
    });
  }
};

/**
 * Change the user to a program
 * @param Integer ProgramId
 */
ChangeProgram.prototype.changeTo = function(programId){
  if(programId in this.allowedPrograms){
    $.ajax({
      type: 'POST',
      url: this.changeProgramPath + '/changeTo',
      data: {programId: programId},
      async: false
    });
  }
};

/**
 * Check if a user can change into that program
 * @param Integer ProgramId
 */
ChangeProgram.prototype.check = function(programId){
  return (programId in this.allowedPrograms);
};