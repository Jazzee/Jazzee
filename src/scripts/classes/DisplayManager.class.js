/**
 * Manage a display
 * 
 * @param {Display} display
 * @param {Application} display
 */
function DisplayManager(display, application){
  this.display = display;
  this.application = application;
  this.services = new Services();
};

/**
 * Draw the display manager
 * 
 * @param {jQuery} canvas
 */
DisplayManager.prototype.init = function(canvas){
  var self = this;
  var name = $('<div>').css('text-align', 'left');
  name.append($('<label>').attr('for', 'display-name').html('Display Name: '));
  name.append($('<input>').attr('id', 'display-name').val(this.display.getName()).bind('change', function(){
    self.display.setName($(this).val());
  }));
  canvas.append(name);
  var button = $('<button>').html('Select All').button();
  button.click(function(e){
    var startWeight = self.nextWeight();
    $('ul.block_list li.item', div).not('.selected').each(function(i){
      var element = $(this).data('element');
      element.weight = startWeight +i;
      self.display.addElement(element);
    });
    self.drawChosen();
    self.drawChooser();
    return false;
  });
  canvas.append(button);
  var button = $('<button>').html('Deselect All').button();
  button.click(function(e){
    var startWeight = self.nextWeight();
    $('ul.block_list li.item.selected', div).each(function(i){
      var element = $(this).data('element');
      self.display.removeElement(element);
    });
    self.drawChosen();
    self.drawChooser();
    return false;
  });
  canvas.append(button);
  var div = $('<div>').addClass('yui-g');
  var left = $('<div>').addClass('yui-u first').attr('id', 'chooser');
  var right = $('<div>').addClass('yui-u').attr('id', 'chosen');
  div.append(left);
  div.append(right);
  canvas.append(div);
  this.drawChooser();
  this.drawChosen();
  
  canvas.on('click', '.shrink_button', function() {
    $(this).next().toggle('slow');
    return false;
  });
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
  var maximumDisplay = this.services.getMaximumDisplay();

  div.append(this.shrinkButton('Applicant', this.applicantBox(maximumDisplay)));
  div.append(self.shrinkButton("Tags", self.tagBox(maximumDisplay)));

  $.each(this.application.listApplicationPages(), function(){
     div.append(self.shrinkButton(this.title, self.pageBox(this, maximumDisplay)));
  });

  $('#chooser').empty().append(div);
};

/**
 * Draw the displayed
 * 
 */
DisplayManager.prototype.drawChosen = function(){
  var self = this;
  var div = $('<div>');
  $.each(this.display.listElements(),function(){
    var displayElement = this;
    var ol = $('<ol>');
    var sourceName = displayElement.type == 'applicant'?'Applicant':self.display.getElementPageTitle(displayElement) + ' Page';
    ol.append($('<li>').html('<strong>Source:</strong> ' + sourceName).addClass('item'));
    ol.append($('<li>').html('<strong>Original Name:</strong> ' + $('#'+ this.type + this.name).data('element').title).addClass('item'));
    var elementDiv = self.shrinkButton(displayElement.title, ol);
    elementDiv.addClass('element');
    elementDiv.data('element', displayElement);
    $('.shrink_button', elementDiv).prepend($('<span>').addClass('left').addClass('handle ui-icon ui-icon-arrowthick-2-n-s'));
    
    $('.shrink_button p', elementDiv).append($('<span>').addClass('right ui-icon ui-icon-pencil'));
     $('.shrink_button p', elementDiv).editable(function(value, settings) {
        displayElement.title = value;
     },
     {
       displayElement: displayElement,
       data: displayElement.title,
       callback: function(value, setting){
         self.drawChosen();
       }
    });
    var removeSpan = $('<span>').addClass('right').addClass('ui-icon ui-icon-circle-minus');
    removeSpan.on('click', function(){
      self.display.removeElement($(this).closest('.shrinkable').data('element'));
      self.drawChosen();
      self.refreshDisplayedChooserElements();
    });
    $('.shrink_button', elementDiv).append(removeSpan);
    div.append(elementDiv);
  });
  $('div.element',div).sort(function(a,b){
    return $(a).data('element').weight > $(b).data('element').weight ? 1 : -1;
  }).appendTo(div);
  div.sortable({
    handle: '.handle'
  });
  div.bind("sortupdate", function(e, ui) {
    $('div.element',$(ui.item).parent()).each(function(i){
      var element = $(this).data('element');
      element.weight = i;
    });
  });
  var chosen = $('<div>');
  chosen.append($('<h3>').html('Selected Items for ' + this.display.getName() + ' Display'));
  chosen.append(div);
  $('#chosen').empty().append(chosen);
};

/**
 * Draw the applicant box
 * 
 * @param {Display} maximumDisplay
 */
DisplayManager.prototype.applicantBox = function(maximumDisplay){
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
    {type:'applicant', title: 'Paid', name: 'hasPaid'},
    {type:'applicant', title: 'External ID', name: 'externalId'},
    {type:'applicant', title: 'Attachments', name: 'attachments'},

    // these should really be a separate type (eg. tag or decision).
    // the name part here corresponds to keys in the FullApplication.php display.
    {type:'applicant', title: 'Declined', name: 'status_declined'},
    {type:'applicant', title: 'Admitted', name: 'status_admitted'},
    {type:'applicant', title: 'Denied', name: 'status_denied'},
    {type:'applicant', title: 'Accepted', name: 'status_accepted'},
    {type:'applicant', title: 'Nominate Admit', name: 'status_nominate_admit'},
    {type:'applicant', title: 'Nominate Deny', name: 'status_nominate_deny'}
  ];
  var hasItems = false;
  $.each(arr,function(){
	  if(maximumDisplay.displayElement(this)){
      hasItems = true;
      var li = $('<li>').addClass('item').html(this.title).data('element',this);
      li.attr('id', this.type + this.name);
      if(self.display.displayElement(this)){
          li.addClass('selected');
      }
      li.on('click', function(){
        var element = $(this).data('element');
        if(self.display.displayElement(element)){
          li.addClass('selected');
          self.display.removeElement(element);
          list.replaceWith(self.applicantBox(maximumDisplay));
          self.drawChosen();
        } else {
          li.removeClass('selected');
          element.weight = self.nextWeight();
          self.display.addElement(element);
          list.replaceWith(self.applicantBox(maximumDisplay));
          self.drawChosen();
        }
      });
      list.append(li);
            }
  });
  if(hasItems){
    return $('<div>').append(list);
  } else {
    return false;
  }
};

/**
 * Draw the shrink button
 * 
 * @param String title
 * @param {jquery} content
 */
DisplayManager.prototype.shrinkButton = function(title, content){
  var div = $('<div>').addClass('shrinkable');
  if(content === false ){
    return div;
  }
  div.append($('<div>').addClass('shrink_button').addClass('item').append($('<p>').html(title)));
  div.append($('<div>').addClass('shrink_list').append(content));
  
  return div;
};

/**
 * Draw the page box
 * 
 * @param {} applicationPage
 * @param {Display} maximumDisplay
 */
DisplayManager.prototype.pageBox = function(applicationPage, maximumDisplay){
  var self = this;
  var list = $('<ul>').addClass('block_list');
  var pageClass = this.application.getPageClassById(applicationPage.page.id);
  var pageDisplayElements = pageClass.listDisplayElements();
  if(pageDisplayElements.length == 0){
    return false;
  }
  var hasItems = false;
  $.each(pageDisplayElements, function(){
    if(maximumDisplay.displayElement(this)){
      hasItems = true;
      var li = $('<li>').addClass('item').html(this.title).data('element', this);
      li.attr('id', this.type + this.name);
      if(self.display.displayElement(this)){
          li.addClass('selected');
      }
      li.on('click', function(){
        var element = $(this).data('element');
        if(self.display.displayElement(element)){
          li.addClass('selected');
          self.display.removeElement(element);
          list.replaceWith(self.pageBox(applicationPage, maximumDisplay));
          self.drawChosen();
        } else {
          li.removeClass('selected');
          element.weight = self.nextWeight();
          self.display.addElement(element);
          list.replaceWith(self.pageBox(applicationPage, maximumDisplay));
          self.drawChosen();
        }
      });
      list.append(li);
    }
  });
  if(hasItems){
    return $('<div>').append(list);
  }

  return false;
};

DisplayManager.prototype.tagBox = function(maximumDisplay){
  var self = this;
  var list = $('<ul>').addClass('block_list');
  var hasItems = false;
  try{
      $.each(maximumDisplay.listTags(), function(){
	  this.type = 'applicant';
	  this.name = this.id;
	  hasItems = true;
      var li = $('<li>').addClass('item').html(this.title).data('element', this);
      li.attr('id', this.type + this.name);
      if(self.display.displayElement(this)){
          li.addClass('selected');
      }
      li.on('click', function(){
        var element = $(this).data('element');
        if(self.display.displayElement(element)){
          li.addClass('selected');
          self.display.removeElement(element);
          list.replaceWith(self.tagBox(maximumDisplay));
          self.drawChosen();
        } else {
          li.removeClass('selected');
          element.weight = self.nextWeight();
          console.log(element.weight);
          self.display.addElement(element);
          list.replaceWith(self.tagBox(maximumDisplay));
          self.drawChosen();
        }
      });
      list.append(li);

      });
  }catch(ex){
      console.log("unable to create tag list: "+ex);
  }

  if(hasItems){
    return $('<div>').append(list);
  }

  return false;
};

/**
 * Save the display
 * 
 * @param url string
 * @param display Display
 */
DisplayManager.save = function(url, display){
  $.ajax({
    url: url + '/saveDisplay',
    type: 'POST',
    data: {display: $.toJSON(display.getObj())},
    async: false
  });
};

/**
 * REmvoe the display
 * 
 * @param {} applicationPage
 */
DisplayManager.remove = function(url, display){
  $.ajax({
    url: url + '/deleteDisplay',
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

/**
 * Refresh the list of chooser elements to see which ones should be displayed
 * 
 * @param {}
 * @return {jQuery}
 */
DisplayManager.prototype.refreshDisplayedChooserElements = function(){
  var self = this;
  $('ul.block_list li.item', '#chooser').each(function(i){
      var element = $(this).data('element');
      if(self.display.displayElement(element)){
        $(this).addClass('selected');
      } else {
        $(this).removeClass('selected');
      }
    });
};
