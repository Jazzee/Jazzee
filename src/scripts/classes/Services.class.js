/**
 * API for admin/services
 */
function Services(){
  var self = this;
  this.basepath = Services.prototype.absoluteBasePath;
  this.preferences = false;
  this.applications = [];
};

Services.prototype.currentApplication = false;
Services.prototype.allowedActions = [];
Services.prototype.controllerPaths = {};

Services.prototype.request = function(service, data){
  data.service = service;
  var result = null;
  $.ajax({
    type: 'POST',
    url: this.basepath + 'services',
    data: data,
    async: false,
    success: function(json){
      result = json.data.result;
    }
  });
  return result;
};

Services.prototype.checkIsAllowed = function(controller, action){
  if($.inArray(controller+action, Services.prototype.allowedActions) == -1){
    var result = this.request('checkIsAllowed', {'controller':controller, 'action':action});
    if(result){
      Services.prototype.allowedActions.push(controller+action);
    }
    return result;
  } else {
    return true;
  }
};

Services.prototype.getBasepath = function(){
  return this.basepath;
};

Services.prototype.getControllerPath = function(controller){
  if(!Services.prototype.controllerPaths.hasOwnProperty(controller)){
    Services.prototype.controllerPaths[controller] = this.request('pathToController', {'controller': controller})
  }
  return Services.prototype.controllerPaths[controller];
};

Services.prototype.getCurrentApplicationId = function(){
  return this.request('currentApplicationId', {});
};

Services.prototype.getDisplays = function(){
  var displays = [];
  var application = this.getCurrentApplication();
  $.each(this.request('listDisplays', {}), function(){
    displays.push(new Display(this, application));
  });
  
  return displays;
};

Services.prototype.getMaximumDisplay = function(){
  var application = this.getCurrentApplication();
  return new Display(this.request('maximumDisplay', {}), application);
};

Services.prototype.getCurrentApplication = function(){
  if(!Services.prototype.currentApplication){
    var result = this.request('currentApplication',{});
    Services.prototype.currentApplication =  new Application(result);
  }

  return Services.prototype.currentApplication;
};

Services.prototype.savePreferences = function(){
  $.post(this.basepath + 'services/savePreferences', {'preferences':$.toJSON(this.preferences)});
};

Services.prototype.fillPreferences = function(){
  var self = this;
  if(this.preferences === false){
    $.ajax({
      type: 'GET',
      url: this.basepath + 'services/getPreferences',
      async: false,
      success: function(json){
        self.preferences = json.data.result;
      }
    });
  }
};

Services.prototype.getPreference = function(name){
  this.fillPreferences();
  if(name in this.preferences){
    return this.preferences[name];
  }
  return null;
};

Services.prototype.setPreference = function(name, value){
  this.fillPreferences();
  this.preferences[name] = value;
  this.savePreferences();
};