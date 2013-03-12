/**
 * Javascript for the setup_comparchanges controller
 */
$(document).ready(function(){

  var status = new Status($('#ajaxstatus'), $('#content'));
  $(document).ajaxError(function(e, xhr, settings, exception) {
    status.addMessage('error','There was an error with your request, please try again.');
  });

  $(document).ajaxComplete(function(e, xhr, settings) {
    if(xhr.getResponseHeader('Content-Type') == 'application/json'){
      eval("var json="+xhr.responseText);
      $(json.messages).each(function(i){
        status.addMessage(this.type, this.text);
      });
    }
  });
  //Ajax activity indicator bound to ajax start/stop document events
  $(document).ajaxStart(function(){
    status.start();
  });
  $(document).ajaxComplete(function(){
    status.end();
  });
  var compare = new CompareApplications($('#canvas'));
  compare.init();
});

/**
 * The CompareApplications class
 *
 */
function CompareApplications(canvas){
  this.services = new Services;
  this.canvas = canvas;
  this.controllerPath = this.services.getControllerPath('setup_comparechanges');
};

/**
 * Get a list of cycles to choose from
 */
CompareApplications.prototype.init = function(){
  var compare = this;
  $('a', this.canvas).bind('click', function(e){
    $.ajax({
      type: 'GET',
      url: $(e.target).attr('href'),
      success: function(json){
        $('#comparison').empty();
        $('#comparison').append($('<h5>').html('Differences between ' + json.data.result.thisCycle + ' and ' + json.data.result.otherCycle));
        compare.display(json.data.result.differences, $('#comparison'));
      }
    });
    return false;
  });
};

/**
 * Display Comparison
 * @param {} obj
 * @param {jQuery} target
 */
CompareApplications.prototype.display = function(obj, target){
  var compare = this;
  var div = $('<div>');
  $(obj.properties).each(function(i){
    div.append($('<h5>').html(this.name));
    var method = 'display' + this.type;
    if(compare[method] != undefined){
      div.append(compare[method](this));
    } else {
      console.log('No diff type for ' + this.type);
      div.append('<div></div>');
    }
  });
  div.accordion({
    autoHeight: false
  });
  target.append($('<h3>').html('Properties'));
  target.append(div);

  var ul = $('<ul>');
  $(obj.pages['new']).each(function(i){
    ul.append($('<li>').html(this.valueOf()));
  });
  target.append($('<h3>').html('New Pages'));
  target.append(ul);

  var ul = $('<ul>');
  $(obj.pages['removed']).each(function(i){
    ul.append($('<li>').html(this.valueOf()));
  });
  target.append($('<h3>').html('Removed Pages'));
  target.append(ul);

  target.append($('<h3>').html('Changed Pages'));
  var tabs = $('<div>');
  var tabsList = $('<ul>');
  $(obj.pages['changed']).each(function(i){
    var tab = compare.processChangedPage(this);
    tab.attr('id', 'changedpage'+i);
    tabsList.append($('<li>').append($('<a>').attr('href', '#changedpage'+i).html(this.title)));
    tabs.append(tab);
  });
  var div = $('<div>').append(tabsList).append(tabs);
  div.tabs();
  target.append(div);
};

/**
 * Differences for a changed page
 * @param {} obj
 * @return {jQuery}
 */
CompareApplications.prototype.processChangedPage = function(obj){
  var compare = this;
  var div =  $('<div>');
  var content = $('<div>');
  $(obj.properties).each(function(i){
    content.append($('<h5>').html(this.name));
    var method = 'display' + this.type;
    if(compare[method] != undefined){
      content.append(compare[method](this));
    } else {
      console.log('No diff type for ' + this.type);
      content.append('<div></div>');
    }
  });
  content.accordion({
    autoHeight: false
  });
  div.append($('<h5>').html('Properties'));
  div.append(content);

  var ul = $('<ul>');
  $(obj.elements['new']).each(function(i){
    ul.append($('<li>').html(this.valueOf()));
  });
  div.append($('<h3>').html('New Elements'));
  div.append(ul);

  var ul = $('<ul>');
  $(obj.elements['removed']).each(function(i){
    ul.append($('<li>').html(this.valueOf()));
  });
  div.append($('<h3>').html('Removed elements'));
  div.append(ul);

  div.append($('<h3>').html('Changed Elements'));
  var tabs = $('<div>');
  var tabsList = $('<ul>');
  $(obj.elements['changed']).each(function(i){
    var tab = compare.processChangedElement(this);
    tab.attr('id', 'changedelement'+i);
    tabsList.append($('<li>').append($('<a>').attr('href', '#changedelement'+i).html(this.title)));
    tabs.append(tab);
  });
  var tabdiv = $('<div>').append(tabsList).append(tabs);
  tabdiv.tabs();
  
  var ul = $('<ul>');
  $(obj.children['new']).each(function(i){
    ul.append($('<li>').html(this.valueOf()));
  });
  div.append($('<h3>').html('New Child Pages'));
  div.append(ul);

  var ul = $('<ul>');
  $(obj.children['removed']).each(function(i){
    ul.append($('<li>').html(this.valueOf()));
  });
  div.append($('<h3>').html('Removed Child Pages'));
  div.append(ul);
  
  div.append($('<h3>').html('Changed Child Pages'));
  var tabs = $('<div>');
  var tabsList = $('<ul>');
  $(obj.children['changed']).each(function(i){
    var tab = compare.processChangedPage(this);
    tab.attr('id', 'changedchildpage'+i);
    tabsList.append($('<li>').append($('<a>').attr('href', '#changedchildpage'+i).html(this.title)));
    tabs.append(tab);
  });
  var tabdiv = $('<div>').append(tabsList).append(tabs);
  tabdiv.tabs();

  div.append(tabdiv);
  return div;
};

/**
 * Differences for a changed element
 * @param {} obj
 * @return {jQuery}
 */
CompareApplications.prototype.processChangedElement = function(obj){
  var compare = this;
  var div =  $('<div>');
  var content = $('<div>');
  $(obj.properties).each(function(i){
    content.append($('<h5>').html(this.name));
    var method = 'display' + this.type;
    if(compare[method] != undefined){
      content.append(compare[method](this));
    } else {
      console.log('No diff type for ' + this.type);
      content.append('<div></div>');
    }
  });
  content.accordion({
    autoHeight: false
  });

  div.append($('<h5>').html('Properties'));
  div.append(content);

  div.append($('<h5>').html('List Items'));
  var lists = {};
  lists['this'] = obj.thisListItems;
  lists['other'] = obj.otherListItems;
  div.append(compare.displaytextdiff(lists));

  return div;
};

/**
 * Display Text diff
 * @param {} obj
 * @return {jQuery}
 */
CompareApplications.prototype.displaytextdiff = function(obj){
  var div = $('<div>').addClass('textdiff');
  var diff = diffString(String(obj['other']), String(obj['this']));
  div.html(diff);
  return div;
};

/**
 * Display Date diff
 * @param {} obj
 * @return {jQuery}
 */
CompareApplications.prototype.displaydatediff = function(obj){
  var div = $('<div>').addClass('datediff');
  var date1 = new Date(obj['other']);
  var date2 = new Date(obj['this']);
  var html = 'Changed from <del>' + date1.toLocaleDateString() + '</del>' + ' to <ins>' + date2.toLocaleDateString() + '</ins>';
  div.html(html);
  return div;
};

/**
 * Display Date diff
 * @param {} obj
 * @return {jQuery}
 */
CompareApplications.prototype.displaybooldiff = function(obj){
  var div = $('<div>').addClass('booldiff');
  var before = obj['other']?'true':'false';
  var after = obj['this']?'true':'false';
  var html = 'Changed from <del>' + before + '</del>' + ' to <ins>' + after + '</ins>';
  div.html(html);
  return div;
};