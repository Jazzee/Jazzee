/**
 * API for admin/services
 */
function Services(){
  this.basepath = Services.prototype.absoluteBasePath;
};

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
  return this.request('checkIsAllowed', {'controller':controller, 'action':action});
};

Services.prototype.getBasepath = function(){
  return this.basepath;
};

Services.prototype.getControllerPath = function(controller){
  return this.request('pathToController', {'controller': controller});
};