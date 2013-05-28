/**
 * The JazzeePageEducation type
  @extends JazzeePage
 */
function JazzeePageEducation(){}
JazzeePageEducation.prototype = new JazzeePage();
JazzeePageEducation.prototype.constructor = JazzeePageEducation;

JazzeePageEducation.prototype.workspace = function(){
  var pageClass = this;
  JazzeePage.prototype.workspace.call(this);
  if(Object.keys(this.children).length == 2){
    JazzeePage.prototype.workspace.call(this);
    $('#pageToolbar').append(this.pagePropertiesButton());
    $('#workspace').append(this.editChildPageButton(2));
    $('#workspace').append(this.editChildPageButton(4));
  } else {
    $('#elements').html('<h2>You must save this page before elements can be edited.</h2>');
  }
};

JazzeePageEducation.prototype.getSchoolListElement = function(){
  var element = this.getElementByFixedId(2);
  if(!element){
    console.log('Unable to get element by fixed id: ' + 2);
  }

  return element;
};

/**
 * Create the page properties dropdown
*/
JazzeePageEducation.prototype.pageProperties = function(){
  var pageClass = this;

  var div = $('<div>');
  div.append(this.isRequiredButton());
  div.append(this.editNameButton());
  var slider = $('<div>');
  slider.slider({
    value: this.min,
    min: 0,
    max: 20,
    step: 1,
    slide: function( event, ui ) {
      pageClass.setProperty('min', ui.value);
      $('#minValue').html(pageClass.min == 0?'No Minimum':pageClass.min);
    }
  });
  div.append($('<p>').html('Minimum Answers Required ').append($('<span>').attr('id', 'minValue').html(this.min == 0?'No Minimum':this.min)));
  div.append(slider);


  var slider = $('<div>');
  slider.slider({
    value: this.max,
    min: 0,
    max: 20,
    step: 1,
    slide: function( event, ui ) {
      pageClass.setProperty('max', ui.value);
      $('#maxValue').html(pageClass.max == 0?'No Maximum':pageClass.max);
    }
  });
  div.append($('<p>').html('Maximum Answers Allowed ').append($('<span>').attr('id', 'maxValue').html(this.max == 0?'No Maximum':this.max)));
  div.append(slider);
  
  div.append(this.manageSchoolListButton());

  return div;
};

/**
 * Manage School List
 * @return {jQuery}
 */
JazzeePageEducation.prototype.manageSchoolListButton = function(){
  var pageClass = this;
  var button = $('<button>').html('Manage Schools').bind('click',function(){
    $('.qtip').qtip('api').hide();
    var div = pageClass.createDialog();
    div.append(pageClass.manageSchoolListBlock());
    var button = $('<button>').html('Close').bind('click',function(){
      div.dialog("destroy").remove();
      return false;
    }).button({
      icons: {
        primary: 'ui-icon-disk'
      }
    });
    div.append(button);
    div.dialog('open');
  }).button({
    icons: {
      primary: 'ui-icon-arrow-1-nw'
    }
  });
  return button;
};

/**
 * Manage School List
 * @return {jQuery}
 */
JazzeePageEducation.prototype.manageSchoolListBlock = function(){
  var pageClass = this;
  var element = this.getSchoolListElement();
  var div = $('<div>').attr('id', 'manageSchoolListBlock');
  div.append(this.newSchoolButton());
  div.append(this.importSchoolsButton());
  //ui cant really accomodate more than a few items, use a search box if there are more than 25 schools
  if(element.listItems.length < 25){
    var list = $('<ul>').addClass('elementListItems');
    for(var i = 0; i< element.listItems.length; i++){
      var item = element.listItems[i];
      var singleItem = this.singleSchool(element, item);
      list.append(singleItem);
    }
    var listDiv = $('<div>').html('<h5>List Items</h5>').append(list).addClass('yui-u first');
    div.append(listDiv);
  } else {
    div.append($('<p>').html('You current have ' + element.listItems.length + ' schools.'));
    var schools = [];
    for(var i = 0; i< element.listItems.length; i++){
      var item = element.listItems[i];
      schools.push({item: item, search: item.name+item.title+item.getVariable('searchTerms')});
    }
    var input = $('<input>').attr('type', 'text').bind('change keyup', function(){
      $('div', div).remove();   
      var matcher = new RegExp($.ui.autocomplete.escapeRegex($(this).val()), "i");
      var results = results.concat($.grep(schools, function(item,index){
        return matcher.test(item.search);
      }));
      
      if (results.length < 25) {
        var list = $('<ul>').addClass('elementListItems');
        for(var i = 0; i< results.length; i++){
          var singleItem = pageClass.singleSchool(element, results[i].item);
          list.append(singleItem);
        }
        var listDiv = $('<div>').html('<h5>List Items</h5>').append(list).addClass('yui-u first');
        div.append(listDiv);
      } else {
        div.append($('<div>').html('Too many results, keep typing to narrow your choices.'));
      }
    });
    div.append($('<span>').html('Search Schools: ').append(input));
  }
  
  return div;
};

/**
 * Edit List Item button
 * @param obj item
 * @return {jQuery}
 */
JazzeePageEducation.prototype.singleSchool = function(element, item){
  var value = ($.trim(item.value).length > 0)?item.value:'[blank]';
  var name = ($.trim(item.name).length > 0)?' (' + item.name + ')':'';
  var li = $('<li>').html(value+name).data('item', item).data('element', element).addClass('ui-state-default');
  var tools = $('<span>').addClass('tools');
  if(item.isActive){
    tools.append(this.hideSchoolButton());
  } else {
    li.addClass('inactive');
    tools.append(this.displaySchoolButton());
  }
  tools.append(this.editSchoolButton());
  tools.append(this.deleteSchoolButton());
  li.append(tools)

  return li;
};

/**
 * Edit List Item button
 * @return {jQuery}
 */
JazzeePageEducation.prototype.editSchoolButton = function(){
  var pageClass = this;
  var button = $('<button>').html('Edit').bind('click',function(){
    var li = $(this).parent().parent();
    var item = li.data('item');
    var elementClass = li.data('element');
    var obj = new FormObject();
    var field = obj.newField({name: 'legend', value: 'Edit ' + item.value});
    var element = field.newElement('TextInput', 'schoolName');
    element.label = 'School Name';
    element.value = item.value;
    element.required = true;
    var element = field.newElement('TextInput', 'schoolCode');
    element.label = 'Unique Code';
    element.value = item.name;
    element.required = true;
    var element = field.newElement('Textarea', 'searchterms');
    element.label = 'Additional Search Terms';
    element.value = item.getVariable('searchTerms');
    var dialog = pageClass.displayForm(obj);
    pageClass.pageBuilder.addNameTest($('input[name="name"]', dialog));
    $('form', dialog).bind('submit',function(e){
      var schoolName = $('input[name="schoolName"]', this).val();
      var schoolCode = $('input[name="schoolCode"]', this).val();
      var error = false;
      if(schoolName.length == 0){
        $('input[name="schoolName"]', this).parent().append($('<p>').addClass('message').html('School Name is Required and you left it blank.'));
        error = true;
      }
      if(schoolCode.length == 0){
        $('input[name="schoolCode"]', this).parent().append($('<p>').addClass('message').html('Unique Code is Required and you left it blank.'));
        error = true;
      }
      if(!error){
        item.setProperty('value', schoolName);
        item.setProperty('name', schoolCode);
        item.setVariable('searchTerms', $('textarea[name="searchterms"]', this).val());
        dialog.dialog("destroy").remove();
        li.replaceWith(pageClass.singleSchool(elementClass, item));
      }
      return false;
    });//end submit
    dialog.dialog('open');
    return false;
  }).button({icons: {primary: 'ui-icon-pencil'}});
  return button;
};

/**
 * Active List Item button
 * @return {jQuery}
 */
JazzeePageEducation.prototype.displaySchoolButton = function(){
  var pageClass = this;
  var button = $('<button>').html('Display').bind('click',function(){
    var li = $(this).parent().parent();
    var item = li.data('item');
    var element = li.data('element');
    item.setProperty('isActive', true);
    li.replaceWith(pageClass.singleSchool(element, item));
    return false;
  }).button({icons: {primary: 'ui-icon-plus'}});
  return button;
};

/**
 * Active List Item button
 * @return {jQuery}
 */
JazzeePageEducation.prototype.hideSchoolButton = function(){
  var pageClass = this;
  var button = $('<button>').html('Hide').bind('click',function(){
    var li = $(this).parent().parent();
    var item = li.data('item');
    var element = li.data('element');
    item.setProperty('isActive', false);
    li.replaceWith(pageClass.singleSchool(element, item));
    return false;
  }).button({icons: {primary: 'ui-icon-cancel'}});
  return button;
};

/**
 * Delete List Item button
 * @return {jQuery}
 */
JazzeePageEducation.prototype.deleteSchoolButton = function(){
  var button = $('<button>').html('Delete').button({icons: {primary: 'ui-icon-trash'}});
  if(this.hasAnswers){
    button.addClass('ui-button-disabled ui-state-disabled');
    button.attr('title', 'This item cannot be deleted because there is applicant information associated with it.');
    button.qtip();
  } else {
    button.bind('click', function(e){
      var li = $(this).parent().parent();
      var item = li.data('item');
      item.setProperty('isActive', false);
      item.setProperty('status','delete');
      li.hide('explode');
      return false;
    });
  }
  return button;
};

/**
 * Add new school items button
 * @return {jQuery}
 */
JazzeePageEducation.prototype.newSchoolButton = function(){
  var pageClass = this;
  var obj = new FormObject();
  var field = obj.newField({name: 'legend', value: 'New School'});
  var element = field.newElement('TextInput', 'schoolName');
  element.label = 'School Name';
  element.required = true;
  var element = field.newElement('TextInput', 'schoolCode');
  element.label = 'Unique Code';
  element.required = true;
  var element = field.newElement('Textarea', 'searchterms');
  element.label = 'Additional Search Terms';
  var dialog = pageClass.displayForm(obj);
  $('form', dialog).bind('submit',function(e){
    var schoolName = $('input[name="schoolName"]', this).val();
    var schoolCode = $('input[name="schoolCode"]', this).val();
    var error = false;
    if(schoolName.length == 0){
      $('input[name="schoolName"]', this).parent().append($('<p>').addClass('message').html('School Name is Required and you left it blank.'));
      error = true;
    }
    if(schoolCode.length == 0){
      $('input[name="schoolCode"]', this).parent().append($('<p>').addClass('message').html('Unique Code is Required and you left it blank.'));
      error = true;
    }
    if(!error){
      var element = pageClass.getSchoolListElement();
      var item = element.newListItem(schoolName);
      item.setProperty('name', schoolCode);
      item.setVariable('searchTerms', $('textarea[name="searchterms"]', this).val());
      dialog.dialog("destroy").remove();
      $('#manageSchoolListBlock').replaceWith(pageClass.manageSchoolListBlock());
    }
    return false;
  });//end submit
  var button = $('<button>').html('Add School').bind('click',function(){
    $('.qtip').qtip('api').hide();
    dialog.dialog('open');
  }).button({
    icons: {
      primary: 'ui-icon-plus'
    }
  });
  return button;
};

/**
 * Edit the child page by its fixed id
 * @param Integer fixedId
 * @return {jQuery}
 */
JazzeePageEducation.prototype.editChildPageButton = function(fixedId){
  var branch = this.getChildByFixedId(fixedId);
  var button = $('<button>').html('Edit ' + branch.title + ' Page').data('page', branch).bind('click',function(){
    var page = $(this).data('page');
    page.workspace();
    //empty the toolbar becuase the delete/copy are going to be wrong
    $('#pageToolbar .copy').remove();
    $('#pageToolbar .delete').remove();
    $('#pageToolbar .properties').remove();
  }).button({
    icons: {
      primary: 'ui-icon-pencil'
    }
  });
  return button;
};

/**
 * Add new school items button
 * @return {jQuery}
 */
JazzeePageEducation.prototype.importSchoolsButton = function(){
  var pageClass = this;
  var obj = new FormObject();
  var field = obj.newField({name: 'legend', value: 'New School'});
  field.instructions = "Schools can be intered as [tab] seperated values, one per line.  Each line can have three seperate elements School Name, Unique Code, Search Data.  School Name and Unique Code are required.";
  var element = field.newElement('Textarea', 'schools');
  element.label = 'Schools';
  element.required = true;
  var dialog = pageClass.displayForm(obj);
  var progressbar = $('<div>').addClass('progress').append($('<div>').addClass('label').html('Importing Schools...'));
  progressbar.hide();
  dialog.append(progressbar);
  progressbar.progressbar({});
  $('form', dialog).bind('submit',function(e){
    var schools = $('textarea[name="schools"]', this).val();
    var error = false;
    if(schools.length == 0){
      $('textarea[name="schools"]', this).parent().append($('<p>').addClass('message').html('this element is Required and you left it blank.'));
      error = true;
    }
    if(!error){
      $('.progress', $(this).parent().parent()).show();
      var element = pageClass.getSchoolListElement();
      var lines = schools.split("\n");
      $('.progress', $(this).parent().parent()).progressbar("option", "max", lines.length);
      var total = lines.length;
      for(var i = 0;i<total; i++){
        if($.trim(lines[i]).length > 0){
          var pieces = lines[i].split("\t");
          if(pieces.length >= 2){
            var item = element.newListItem($.trim(pieces[0]));
            item.setProperty('name', $.trim(pieces[1]));
            if($.trim(pieces[2]).length > 0){
              item.setVariable('searchTerms', $.trim(pieces[2]));
            }
            console.log("Addiing School: " + i + " of " + total);
          }
        }
        $('.progress', $(this).parent().parent()).progressbar("option", "value", i);
      }
      dialog.dialog("destroy").remove();
      $('#manageSchoolListBlock').replaceWith(pageClass.manageSchoolListBlock());
    }
    return false;
  });//end submit
  var button = $('<button>').html('Import Schools').bind('click',function(){
    $('.qtip').qtip('api').hide();
    dialog.dialog('open');
  }).button({
    icons: {
      primary: 'ui-icon-plus'
    }
  });
  return button;
};