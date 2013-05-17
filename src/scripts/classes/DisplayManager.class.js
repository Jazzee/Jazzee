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
 */
DisplayManager.prototype.drawChosen = function(){
  var div = $('<div>');
  div.append($('<h3>').html('Selected Items for ' + this.display.getName() + ' Display'));
  var self = this;
  var list = $('<ol>').addClass('block_list');
  $.each(this.display.listElements(),function(){
    var li = $('<li>').addClass('item').html(this.title).data('element', this);
    li.prepend($('<span>').addClass('handle ui-icon ui-icon-arrowthick-2-n-s'));
    li.bind('click', function(){
//      self.drawChosen();
    });
    list.append(li);
  });
  $('li',list).sort(function(a,b){
    return $(a).data('element').weight > $(b).data('element').weight ? 1 : -1;
  }).appendTo(list);
  list.sortable({
    handle: '.handle'
  });
  list.bind("sortupdate", function(e, ui) {
    $('li',$(ui.item).parent()).each(function(i){
      var element = $(this).data('element');
      element.weight = i;
      self.display.addElement(element);
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
  var arr = [
    {type:'applicant', title: 'First Name', name: 'firstName'},
    {type:'applicant', title: 'Last Name', name: 'lastName'},
    {type:'applicant', title: 'Email', name: 'email'},
    {type:'applicant', title: 'Last Update', name: 'updatedAt'},
    {type:'applicant', title: 'Progress', name: 'percentComplete'},
    {type:'applicant', title: 'Last Login', name: 'lastLogin'},
    {type:'applicant', title: 'Account Created', name: 'createdAt'},
    {type:'applicant', title: 'Locked', name: 'isLocked'},
    {type:'applicant', title: 'Paid', name: 'hasPaid'}
  ];
  $.each(arr,function(){
    var li = $('<li>').addClass('item').html(this.title).data('element',this);
    if(self.display.displayElement(this)){
      li.addClass('selected');
      li.bind('click', function(){
        self.display.removeElement($(this).data('element'));
        list.replaceWith(self.applicantBox());
        self.drawChosen();
      });
    } else {
      li.bind('click', function(){
        var element = $(this).data('element');
        element.weight = self.nextWeight();
        self.display.addElement(element);
        list.replaceWith(self.applicantBox());
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
 * @param String title
 * @param {jquery} content
 */
DisplayManager.prototype.shrinkButton = function(title, content){
  var div = $('<div>').addClass('shrinkable');
  if(content === false ){
    return div;
  }
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
  var pageClass = this.application.getPageClassById(applicationPage.page.id);
  var pageDisplayElements = pageClass.listDisplayElements();
  if(pageDisplayElements.length == 0){
    return false;
  }
  $.each(pageDisplayElements, function(){
    var li = $('<li>').addClass('item').html(this.title).data('element', this);
    if(self.display.displayElement(this)){
      li.addClass('selected');
      li.bind('click', function(){
        var element = $(this).data('element');
        self.display.removeElement(element);
        list.replaceWith(self.pageBox(applicationPage));
        self.drawChosen();
      });
    } else {
      li.bind('click', function(){
        var element = $(this).data('element');
        element.weight = self.nextWeight();
        self.display.addElement(element);
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
 * @param url string
 * @param display Display
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

/**
 * Get the weight for the next piece
 * 
 * @return integer
 */
DisplayManager.prototype.nextWeight = function(){
  return $('#chosen li').length+1;
};