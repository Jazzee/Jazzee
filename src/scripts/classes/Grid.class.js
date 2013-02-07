/**
 * Grid class
 * Creats an applicant grid from a display and a set of applicant ids
 * 
 * @param {Display} display
 * @param [] applicantIds
 * @param {jQuery} target
 */
function Grid(display, applicantIds, target, controllerPath){
  this.display = display;
  this.applicantIds = applicantIds;
  this.target = target;
  this.controllerPath = controllerPath;
};

Grid.prototype.init = function(){
  var self = this;
  var columns = this.getColumns();
  
  var data = [];
  $('#grid').html( '<table cellpadding="0" cellspacing="0" border="0" class="display" id="grid-table" width="100%"></table>' );
  $('#grid-table').dataTable( {
    sScrollY: "500",
    sScrollX: "95%",
    bDeferRender: true,
    aaData: data,
    aoColumns: columns,
    bJQueryUI: true
  });
  this.loadapps(this.applicantIds, $('#grid-table').dataTable());
};

Grid.prototype.getColumns = function(){
  var self = this;
  var columns = [];
  if(this.display.showApplicantLink()){
    columns.push({sTitle: "Applicant",
    mData: 'link',
    mRender: function( data, type, full ) {
        return '<a href="' + data + '">' + 'open' + '</a>';
    }});
  }
  if(this.display.showFirstName()){
    columns.push({sTitle: "First",
    mData: 'firstName'});
  }
  if(this.display.showLastName()){
    columns.push({sTitle: "Last",
    mData: 'lastName'});
  }
  if(this.display.showEmail()){
    columns.push({sTitle: "Email",
    mData: 'email'});
  }
  if(this.display.showLastUpdate()){
    columns.push({sTitle: "Last Update",
    mData: 'updatedAt',
    mRender: Grid.formatDate});
  }
  if(this.display.showProgress()){
    columns.push({sTitle: "Progress",
    mData: 'percentComplete'});
  }
  if(this.display.showLastLogin()){
    columns.push({sTitle: "Last Login",
    mData: 'lastLogin',
    mRender: Grid.formatDate});
  }
  if(this.display.showAccountCreated()){
    columns.push({sTitle: "Created At",
    mData: 'createdAt',
    mRender: Grid.formatDate});
  }
  if(this.display.showIsLocked()){
    columns.push({sTitle: "Locked",
    mData: 'isLocked',
    mRender: Grid.formatCheckmark});
  }
  if(this.display.showIsPaid()){
    columns.push({sTitle: "Paid",
    mData: 'hasPaid',
    mRender: Grid.formatCheckmark});
  }
  
  $.each(this.display.getPages(), function(){
    $.each(self.display.getPageElements(this.id), function(){
      columns.push({
        sTitle: this.title,
        mData: 'values.' + this.id,
        mRender: Grid.formatAnswers
      });
    });
  });
  
  return columns;
};

Grid.prototype.loadapps = function(applicantIds, grid){
  var self = this;
  if(applicantIds.length){
      var limitedIds = applicantIds.splice(0, 25);
    $.post(self.controllerPath + '/getApplicants',{applicantIds: limitedIds, display: self.display.getObj()
    }, function(json){
      $.each(json.data.result.applicants, function(i){
        var applicant = new ApplicantData(this);
        var obj = applicant;
        obj.percentComplete = Math.round(obj.percentComplete * 100);
        obj.values = {};
        $.each(self.display.getPages(), function(){
          var page = this;
          $.each(self.display.getPageElements(this.id), function(){
            var element = this;
            obj.values[element.id] = applicant.getDisplayValuesForPageElement(page.id, element.id);
          });
        });
        grid.fnAddData(obj);
      });
      grid.fnAdjustColumnSizing();
      self.loadapps(applicantIds, grid);
    });
  }
};

/**
 * Format date objects
 */
Grid.formatDate = function(data, type, full){
  return data.date;
};

/**
 * Display a checkmark if true
 */
Grid.formatCheckmark = function(data, type, full){
  if(type == 'filter' || type == 'display'){
    return data === true ? "<img src='resource/foundation/media/icons/tick.png'>" : "";
  }
  return data;
};

/**
 * Display a checkmark if true
 */
Grid.formatAnswers = function(data, type, full){
  if(data.length == 0){
    return '-';
  }
  if(data.length == 1){
    return data[0];
  }
  if(type == 'display'){
    var ol = $('<ol>');
    $.each(data, function(){
      ol.append($('<li>').html(this.toString()));
    });
    return $('<span>').append(ol).html();
  }
  //forsorting and filtering return the raw data
  return data.join(' ');
};