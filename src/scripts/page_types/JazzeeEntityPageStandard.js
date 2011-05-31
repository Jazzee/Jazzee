/**
 * The JazzeeEntityPageStandard type
  @extends ApplyPage
 */
function JazzeeEntityPageStandard(){}
JazzeeEntityPageStandard.prototype = new JazzeePage();
JazzeeEntityPageStandard.prototype.constructor = JazzeeEntityPageStandard;

JazzeeEntityPageStandard.prototype.workspace = function(){
  JazzeePage.prototype.workspace.call(this);
  var pageClass = this;
  $('#workspace-right-top').append(this.selectListBlock('isRequired', 'This page is', {0:'Required',1:'Optional'}));
  $('#workspace-right-top').append(this.showAnswerStatusBlock());
  
  var min = {0: 'No Minimum'};
  for(var i = 1; i<=50;i++){
    min[i] = i;
  }
  $('#workspace-right-top').append(this.selectListBlock('min','Minimum Answers Required:', min));
  var max = {0: 'No Maximum'};
  for(var i = 1; i<=50;i++){
    max[i] = i;
  }
  $('#workspace-right-top').append(this.selectListBlock('max','Maximum Answers Allowed:', max));
  
  
  
  $('#workspace-left-middle').show();
  for(var i = 0; i < this.elements.length; i++){
    this.elements[i].workspace();
  }
  this.synchronizeElementList();
  $('#workspace-left-middle-left div.field:first').trigger('click');
  
  $.get(pageClass.pageStore.baseUrl + '/listElementTypes',function(json){
    var div = $('<div>').addClass('new-elements').append($('<h5>').html('New Elements'));
    var ol = $('<ol>').addClass('add-list');
    $(json.data.result).each(function(i){
      var elementType = this;
      var li = $('<li>').html(elementType.name);
      $(li).bind('click',function(e){
        var element = new window[elementType.className].prototype.newElement('new' + pageClass.pageStore.getUniqueId(),'New ' + elementType.name + ' Element',elementType.id,elementType.className,'new',pageClass);
        pageClass.addElement(element);
        element.workspace();
        pageClass.synchronizeElementList();
      });
      ol.append(li);
    });
    div.append(ol);
    $('#workspace-right-middle').append(div);
  });
};

/**
 * Add a copy of an element to the page
 * @param {ApplyElement} element
 */
JazzeeEntityPageStandard.prototype.copyElement = function(e){
  var obj = e.getDataObject();
  obj.id = 'newelement' + this.pageStore.getUniqueId();
  obj.title = 'Copy of ' + obj.title;
  var element = new window[obj.className]();
  element.init(obj, this);
  element.status = 'new';
  element.isModified = true;
  for(var i = 0; i < obj.list.length; i++){
    element.newListItem(obj.list[i].value);
  }
  this.addElement(element);
  element.workspace();
  this.synchronizeElementList();
};

/**
 * Synchronize the element list after it has been created
 * Walk through the elements and make sure they are all have the right weight and click functionality
 */
JazzeeEntityPageStandard.prototype.synchronizeElementList = function(){
  var pageClass = this;
  $('#workspace-left-middle-left div.field').unbind('click');
  $('#workspace-left-middle-left div.field').each(function(i){
    $(this).bind('click', function(){
      $('#workspace-left-middle-right div').hide();
      $('#workspace-left-middle-left div.selected').removeClass('selected');
      $('#element-'+$(this).data('element').id).addClass('selected');
      $('#element-options-'+$(this).data('element').id).show().children().show();
    });
    $(this).data('element').setProperty('weight',i+1);
  });
  var list = $('#workspace-left-middle-left');
  list.sortable();
  list.bind("sortupdate", function(event, ui) {
    pageClass.synchronizeElementList();
  });
};

/**
 * Answer status Block
 * @returns {jQuery}
 */
JazzeeEntityPageStandard.prototype.showAnswerStatusBlock = function(){
  var pageClass = this;
  var value = (this.showAnswerStatus == 1)?'shown':'not shown';
  var p = $('<p>').addClass('edit showAnswerStatus').html('Answer status ').append($('<span>').html(value)).bind('click', function(e){
    var obj = new FormObject();
    var field = obj.newField({name: 'legend', value: 'Answer Status'});
    var element = field.newElement('RadioList', 'showAnswerStatus');
    element.label = 'Show status on this page?';
    element.required = true;
    element.addItem('Yes', 1);
    element.addItem('No', 0);
    element.value = (pageClass.showAnswerStatus == 1)?1:0;
    var element = field.newElement('TextInput', 'title');
    element.label = 'Title';
    element.value = pageClass.getVariable('answerStatusTitle');
    var element = field.newElement('TextInput', 'text');
    element.label = 'Text';
    element.value = pageClass.getVariable('answerStatusText');
    element.instructions = 'The following will be replaced with the applicant input on this answer:';
    for(var i in pageClass.elements){
      var el = pageClass.elements[i];
      element.instructions += '<br />' + el.replacementTitle() + ': ' + el.title;
    }
    
    var form = new Form();
    var formObject = form.create(obj);
    $('form',formObject).append($('<button type="submit" name="submit">').html('Save'));
    
    var div = $('<div>');
    div.css("overflow-y", "auto");
    div.html(formObject);
    div.dialog({
      modal: true,
      autoOpen: true,
      position: 'center',
      width: 800,
      close: function() {
        div.dialog("destroy").remove();
      }
    });
    $('form', div).unbind().bind('submit',function(e){
      var show = $('input[name=showAnswerStatus]:checked', this).val();
      pageClass.setProperty('showAnswerStatus', show);
      if(show == 1){
        var title = $('input[name=title]', this).val();
        var text = $('input[name=text]', this).val();
      } else {
        var title = null;
        var text = null;
      }
      pageClass.setVariable('answerStatusTitle', title);
      pageClass.setVariable('answerStatusText', text);
      div.dialog("destroy").remove();
      p.replaceWith(pageClass.showAnswerStatusBlock());
      return false;
    });//end submit
  });
  return p;
};