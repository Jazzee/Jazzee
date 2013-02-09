/**
 * Manage a display
 * 
 * @param {Display} display
 * @param {Application} display
 */
function DisplayManager(display, application){
  this.display = display;
  this.application = application;
};

/**
 * Draw the display manager
 * 
 * @param {jQuery} canvas
 */
DisplayManager.prototype.init = function(canvas){
  var container = $('<div>').addClass('yui-g');
  var left = $('<div>').addClass('yui-u first').attr('id', 'chooser');
  var right = $('<div>').addClass('yui-u').attr('id', 'chosen');
  container.append(left);
  container.append(right);
  canvas.append(container);
  this.drawChooser();
  this.drawChosen();
};

/**
 * Draw the chooser
 * 
 * @param {jQuery} canvas
 */
DisplayManager.prototype.drawChooser = function(){
  var self = this;
  var div = $('<div>');
  div.append($('<h3>').html('Available Elements'));
  div.append(this.shrinkButton('Applicant', this.applicantBox()));
  
  $.each(this.application.listApplicationPages(), function(){
    div.append(self.shrinkButton(this.title, self.pageBox(this)));
  });
  
  $('.shrink_button', div).click(function() {
    $(this).next().toggle('slow');
    return false;
  }).next().hide();

  $('#chooser').empty().append(div);
};

/**
 * Draw the displayed
 * 
 * @param {jQuery} canvas
 */
DisplayManager.prototype.drawChosen = function(canvas){
  var div = $('<div>');
  div.append($('<h3>').html('Selected Items for ' + this.display.getName() + ' Display'));
  var self = this;
  var list = $('<ol>').addClass('block_list');
  var arr = DisplayManager.listApplicantElements();
  $.each(arr,function(){
    if(self.display['is' + this.control + 'Displayed']()){
      var li = $('<li>').addClass('item').html(this.title).data('page', 'applicant').data('element', this.element).data('control', this.control);
      li.addClass('selected');
      li.bind('click', function(){
        self.display['hide' + $(this).data('control')]();
        self.drawChosen();
      });
      list.append(li);
    }
  });

  $.each(self.application.listApplicationPages(), function(){
    var applicationPage = this;
    $.each(self.application.listPageElements(applicationPage.page.id), function(){
      if(self.display.displayElement(this.id)){
        var li = $('<li>').addClass('item').html(this.title).data('page', applicationPage.page.id).data('element', this);
        li.addClass('selected');
        li.bind('click', function(){
          self.display.removeElement($(this).data('element').id);
          self.drawChosen();
        });
        list.append(li);
      }
    });
  });
  div.append(list);
  $('#chosen').empty().append(div);
};

/**
 * Draw the applicant box
 * 
 * @param {jQuery} canvas
 */
DisplayManager.prototype.applicantBox = function(){
  var self = this;
  var list = $('<ul>').addClass('block_list');
  var arr = DisplayManager.listApplicantElements();
  $.each(arr,function(){
    var li = $('<li>').addClass('item').html(this.title).data('page', 'applicant').data('element', this.element).data('control', this.control);
    if(self.display['is' + this.control + 'Displayed']()){
      li.addClass('selected');
      li.bind('click', function(){
        self.display['hide' + $(this).data('control')]();
        list.replaceWith(self.applicantBox());
        self.drawChosen();
      });
    } else {
      li.bind('click', function(){
        self.display['show' + $(this).data('control')]();
        list.replaceWith(self.applicantBox());
        self.drawChosen();
      });
    }
    list.append(li);
  });
  
  return $('<div>').append(list);
};

DisplayManager.listApplicantElements = function(){
  return [
    {title: 'First Name', element: 'firstName', control: 'FirstName'},
    {title: 'Last Name', element: 'lastName', control: 'LastName'},
    {title: 'Email', element: 'email', control: 'Email'},
    {title: 'Last Update', element: 'updatedAt', control: 'UpdatedAt'},
    {title: 'Progress', element: 'percentComplete', control: 'PercentComplete'},
    {title: 'Last Login', element: 'lastLogin', control: 'LastLogin'},
    {title: 'Account Created', element: 'createdAt', control: 'CreatedAt'},
    {title: 'Locked', element: 'isLocked', control: 'IsLocked'},
    {title: 'Paid', element: 'hasPaid', control: 'HasPaid'}
  ];
}

/**
 * Draw the applicant box
 * 
 * @param String title
 * @param {jquery} content
 */
DisplayManager.prototype.shrinkButton = function(title, content){
  var div = $('<div>').addClass('shrinkable');
  div.append($('<div>').addClass('shrink_button').append($('<p>').html(title)));
  div.append($('<div>').addClass('shrink_list').append(content));
  
  return div;
};

/**
 * Draw the applicant box
 * 
 * @param {} applicationPage
 */
DisplayManager.prototype.pageBox = function(applicationPage){
  var self = this;
  var list = $('<ul>').addClass('block_list');
  var arr = DisplayManager.listApplicantElements();
  $.each(this.application.listPageElements(applicationPage.page.id), function(){
    var li = $('<li>').addClass('item').html(this.title).data('page', applicationPage.page.id).data('element', this);
    if(self.display.displayElement(this.id)){
      li.addClass('selected');
      li.bind('click', function(){
        self.display.removeElement($(this).data('element').id);
        list.replaceWith(self.pageBox(applicationPage));
        self.drawChosen();
      });
    } else {
      li.bind('click', function(){
        self.display.addElement($(this).data('element').id);
        list.replaceWith(self.pageBox(applicationPage));
        self.drawChosen();
      });
    }
    list.append(li);
  });
  
  return $('<div>').append(list);
};

/**
 * Draw the applicant box
 * 
 * @param {} applicationPage
 */
DisplayManager.save = function(url, display){
  $.ajax({
    url: url + '/save',
    type: 'POST',
    data: {display: $.toJSON(display.getObj())},
    async: false
  });
};

/**
 * Draw the applicant box
 * 
 * @param {} applicationPage
 */
DisplayManager.remove = function(url, display){
  $.ajax({
    url: url + '/delete',
    type: 'POST',
    data: {display: $.toJSON(display.getObj())},
    async: false
  });
};