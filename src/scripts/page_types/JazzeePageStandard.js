/**
 * The JazzeePageStandard type
  @extends ApplyPage
 */
function JazzeePageStandard(){}
JazzeePageStandard.prototype = new JazzeePage;
JazzeePageStandard.prototype.constructor = JazzeePageStandard;

JazzeePageStandard.prototype.workspace = function(){
  JazzeePage.prototype.workspace.call(this);
  var pageClass = this;
  $('#pageToolbar').append(this.pagePropertiesButton());
  if(!this.isGlobal || this.pageBuilder.editGlobal){
    var dropdown = $('<ul>');
    for(var i = 0; i < this.pageBuilder.elementTypes.length; i++){
      var item = $('<a>').html(this.pageBuilder.elementTypes[i].typeName).attr('href', '#').data('elementTypes', this.pageBuilder.elementTypes[i]);
      item.bind('click', function(e){
        var elementType = $(e.target).data('elementTypes');
        var element = new window[elementType.typeClass].prototype.newElement('new' + pageClass.pageBuilder.getUniqueId(),'New ' + elementType.typeName + ' Element',elementType.typeId,elementType.typeName,elementType.typeClass,'new',pageClass);
        element.workspace();
        pageClass.addElement(element);
        pageClass.markModified();
        pageClass.synchronizeElementList();
        return false;
      });
      dropdown.append($('<li>').append(item));
    }
    var button = $('<button>').html('New Element').button({
      icons: {
        primary: 'ui-icon-plus',
        secondary: 'ui-icon-carat-1-s'
      }
    });
    button.qtip({
      position: {
        my: 'top-left',
        at: 'bottom-left'
      },
      show: {
        event: 'click'
      },
      hide: {
        event: 'unfocus click',
        fixed: true
      },
      content: {
        text: dropdown,
        title: {
          text: 'Choose element type',
          button: true
        }
      }
    });
    $('#pageToolbar').append(button);

    for(var i = 0; i < this.elements.length; i++){
      this.elements[i].workspace();
    }
    this.synchronizeElementList();
  }
};

/**
 * Create the page properties dropdown
*/
JazzeePageStandard.prototype.pageProperties = function(){
  var pageClass = this;

  var div = $('<div>');
  div.append(this.isRequiredButton());
  div.append(this.showAnswerStatusButton());
  if(!this.isGlobal || this.pageBuilder.editGlobal){
    if(pageClass.answerStatusDisplay == 1){
      var button = $('<button>').html('Edit Answer Status Display').attr('id', 'editDisplayButton').bind('click', function(e){
        $('.qtip').qtip('api').hide();
        pageClass.displayAnswerStatusForm();
      });
      button.button({
        icons: {
          primary: 'ui-icon-newwin'
        }
      });
      div.append(button);
    }

    $('#answerStatusDisplayButton input', div).bind('change',function(e){
      //rebuild the tooltip so the edit status display button will show up or be hidden
      div.replaceWith(pageClass.pageProperties());
      return true;
    });
  }

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

  return div;
};

/**
 * Add a copy of an element to the page
 * @param {ApplyElement} element
 */
JazzeePageStandard.prototype.copyElement = function(e){
  var obj = e.getDataObject();
  obj.id = 'newelement' + this.pageBuilder.getUniqueId();
  obj.title = 'Copy of ' + obj.title;
  var element = new window[obj.typeClass]();
  element.init(obj, this);
  element.status = 'new';
  element.markModified();
  for(var i = 0; i < obj.list.length; i++){
    element.newListItem(obj.list[i].value);
  }
  this.addElement(element);
  element.workspace();
  this.markModified();
  this.synchronizeElementList();
};

/**
 * Synchronize the element list after it has been created
 * Walk through the elements and make sure they are all have the right weight and click functionality
 */
JazzeePageStandard.prototype.synchronizeElementList = function(){
  var pageClass = this;

  $('#elements > div').each(function(i){
    $(this).data('element').setProperty('weight',i+1);
  });
  var list = $('#elements');
  list.sortable();
  list.bind("sortupdate", function(event, ui) {
    pageClass.markModified();
    pageClass.synchronizeElementList();
  });
};

/**
 * Display the form for setting up answer status
 * @returns {jQuery}
 */
JazzeePageStandard.prototype.displayAnswerStatusForm = function(){
  var pageClass = this;

  var obj = new FormObject();
  var field = obj.newField({name: 'legend', value: 'Answer Status'});

  var element = field.newElement('TextInput', 'title');
  element.label = 'Title';
  element.required = true;
  element.value = pageClass.getVariable('answerStatusTitle');
  var element = field.newElement('Textarea', 'text');
  element.label = 'Text';
  element.required = true;
  element.value = pageClass.getVariable('answerStatusText');
  element.instructions = 'The following will be replaced with the applicant input on this answer:';
  for(var i in pageClass.elements){
    var el = pageClass.elements[i];
    var text = el.title.replace(/\s+/, '_');
    text = '_' + text.toUpperCase() + '_';
    element.instructions += '<br />' + text + ': ' + el.title;
  }

  var form = new Form();
  var formObject = form.create(obj);
  $('form',formObject).append($('<button type="submit" name="submit">').html('Save'));
  var dialog = pageClass.displayForm(obj);
  $('form', dialog).unbind().bind('submit',function(e){
    pageClass.setVariable('answerStatusTitle',  $('input[name=title]', this).val());
    pageClass.setVariable('answerStatusText', $('textarea[name=text]', this).val());
    dialog.dialog("destroy").remove();
    return false;
  });//end submit
  dialog.dialog('open');
};