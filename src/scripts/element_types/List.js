/**
 * The List is an abstract for other list elements
  @extends JazzeeElement
 */
function List(){}
List.prototype = new JazzeeElement();
List.prototype.constructor = List;

/**
 * Add a list manager to the options block
 */
List.prototype.elementProperties = function(){
  var element = this;
  var div = JazzeeElement.prototype.elementProperties.call(this);
  div.append(this.newListItemsButton());
  div.append(this.manageListItemsButton());
  div.append(this.exportListItemsButton());
  div.append(this.importListItemsButton());

  return div;
};

/**
 * Add new list items button
 * @return {jQuery}
 */
List.prototype.newListItemsButton = function(){
  var elementClass = this;
  var button = $('<button>').html('Add List Items').bind('click',function(){
    $('.qtip').qtip('api').hide();
    var obj = new FormObject();
    var field = obj.newField({name: 'legend', value: 'New List Items'});
    var element = field.newElement('Textarea', 'text');
    element.label = 'New Items';
    element.instructions = 'One new item per line';
    var dialog = elementClass.page.displayForm(obj);
    $('form', dialog).bind('submit',function(e){
      var values = $('textarea[name="text"]', this).val().split("\n");
      for(var i = 0;i<values.length; i++){
        if($.trim(values[i]).length > 0){
          elementClass.newListItem(values[i]);
        }
      }
      dialog.dialog("destroy").remove();
      elementClass.workspace();
      return false;
    });//end submit
    dialog.dialog('open');
  }).button({
    icons: {
      primary: 'ui-icon-plus'
    }
  });
  return button;
};

/**
 * Manage List Items button
 * @return {jQuery}
 */
List.prototype.manageListItemsButton = function(){
  var elementClass = this;

  var button = $('<button>').html('Manage Items').bind('click',function(){
    $('.qtip').qtip('api').hide();
    var div = elementClass.page.createDialog();
    var list = $('<ul>').addClass('elementListItems');
    for(var i = 0; i< elementClass.listItems.length; i++){
      var item = elementClass.listItems[i];
      list.append(elementClass.singleItem(item));
    }
    var listDiv = $('<div>').html('<h5>List Items</h5>').append(list).addClass('yui-u first');
    div.append(listDiv);
    $('ul',listDiv).sortable({handle: '.handle'});

    $('h5', listDiv).after(elementClass.filterItemsInput(list));

    var text = $('<a>').attr('href','#').html(' (sort desc) ').bind('click',function(){
      $('li',list).sort(function(a,b){
        return a.innerHTML.toUpperCase() < b.innerHTML.toUpperCase() ? 1 : -1;
      }).appendTo(list);
      return false;
    });
    $('h5',listDiv).append(text);

    var text = $('<a>').attr('href','#').html(' (sort asc) ').bind('click',function(){
      $('li',list).sort(function(a,b){
        return a.innerHTML.toUpperCase() > b.innerHTML.toUpperCase() ? 1 : -1;
      }).appendTo(list);
      return false;
    });
    $('h5',listDiv).append(text);

    var button = $('<button>').html('Apply').bind('click',function(){
      var orderedItems = [];
      $('li', listDiv).each(function(i){
        var item = $(this).data('item');
        item.setProperty('weight',i+1);
        orderedItems.push(item);
      });

      elementClass.markModified();
      elementClass.listItems = orderedItems;
      div.dialog("destroy").remove();
      elementClass.workspace();
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
 * Export List Items button
 * @return {jQuery}
 */
List.prototype.exportListItemsButton = function(){
  var elementClass = this;

  var button = $('<button>').html('Export Items').bind('click',function(){
    $('.qtip').qtip('api').hide();
    var div = elementClass.page.createDialog();
    var string = elementClass.getListItemsCsv();
    var textarea = $('<textarea>').val(string);
    div.append(textarea);
    div.dialog('open');
  }).button({
    icons: {
      primary: 'ui-icon-arrow-1-nw'
    }
  });
  return button;
};

/**
 * Import list items button - takes format from export
 * @return {jQuery}
 */
List.prototype.importListItemsButton = function(){
  var elementClass = this;
  var button = $('<button>').html('Import List Items').bind('click',function(){
    $('.qtip').qtip('api').hide();
    var obj = new FormObject();
    var field = obj.newField({name: 'legend', value: 'Import List Items'});
    var element = field.newElement('Textarea', 'text');
    element.label = 'Items';
    element.instructions = 'Use this for importing list items that have been exported from somewhere else.';
    var dialog = elementClass.page.displayForm(obj);
    $('form', dialog).bind('submit',function(e){
      var value = $('textarea[name="text"]', this).val();
      var lines = $.csv.toObjects(value);
      for(var i in lines){
        if(lines[i].Value == undefined ||lines[i].Name == undefined){
          alert('This import is not formatted correctly or does not include the first row which should be "Value","Name"');
        }
        var item = elementClass.newListItem(lines[i].Value);
        if(lines[i].Name.length > 0){
          item.setProperty('name', lines[i].Name);
        }
      }
      dialog.dialog("destroy").remove();
      elementClass.workspace();
      return false;
    });//end submit
    dialog.dialog('open');
  }).button({
    icons: {
      primary: 'ui-icon-plus'
    }
  });
  return button;
};

/**
 * Edit List Item button
 * @param obj item
 * @return {jQuery}
 */
List.prototype.singleItem = function(item){
  var elementClass = this;
  var value = ($.trim(item.value).length > 0)?item.value:'[blank]';
  var name = ($.trim(item.name).length > 0)?' (' + item.name + ')':'';
  var li = $('<li>').html(value+name).data('item', item).addClass('ui-state-default');
  var handle = $('<span>').addClass('handle ui-icon ui-icon-arrowthick-2-n-s');
  li.prepend(handle);
  var tools = $('<span>').addClass('tools');
  if(item.isActive){
    tools.append(this.hideListItemButton());
  } else {
    li.addClass('inactive');
    tools.append(this.displayListItemButton());
  }
  tools.append(this.editListItemButton());
  tools.append(this.deleteListItemButton());
  li.append(tools)

  return li;
};

/**
 * Edit List Item button
 * @return {jQuery}
 */
List.prototype.editListItemButton = function(){
  var elementClass = this;
  var button = $('<button>').html('Edit').bind('click',function(){
    var li = $(this).parent().parent();
    var item = li.data('item');
    var obj = new FormObject();
    var field = obj.newField({name: 'legend', value: 'Edit Item'});
    var element = field.newElement('TextInput', 'value');
    element.label = 'Item Value';
    element.required = true;
    element.value = item.value;
    var element = field.newElement('TextInput', 'name');
    element.label = 'Item Name';
    element.required = false;
    element.format = 'Only letters, numbers and underscore are allowed.';
    element.value = item.name;
    var dialog = elementClass.page.displayForm(obj);
    elementClass.page.pageBuilder.addNameTest($('input[name="name"]', dialog));
    $('form', dialog).bind('submit',function(e){
      item.setProperty('value',$('input[name="value"]', this).val());
      item.setProperty('name',$('input[name="name"]', this).val());
      elementClass.workspace();
      dialog.dialog("destroy").remove();
      li.replaceWith(elementClass.singleItem(item));
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
List.prototype.displayListItemButton = function(){
  var elementClass = this;
  var button = $('<button>').html('Display').bind('click',function(){
    var li = $(this).parent().parent();
    var item = li.data('item');
    item.setProperty('isActive', true);
    li.replaceWith(elementClass.singleItem(item));
    return false;
  }).button({icons: {primary: 'ui-icon-plus'}});
  return button;
};

/**
 * Active List Item button
 * @return {jQuery}
 */
List.prototype.hideListItemButton = function(){
  var elementClass = this;
  var button = $('<button>').html('Hide').bind('click',function(){
    var li = $(this).parent().parent();
    var item = li.data('item');
    item.setProperty('isActive', false);
    li.replaceWith(elementClass.singleItem(item));
    return false;
  }).button({icons: {primary: 'ui-icon-cancel'}});
  return button;
};

/**
 * Delete List Item button
 * @return {jQuery}
 */
List.prototype.deleteListItemButton = function(){
  var elementClass = this;
  var button = $('<button>').html('Delete').button({icons: {primary: 'ui-icon-trash'}});
  if(this.page.hasAnswers){
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
 * Filter list items input
 * @return {jQuery}
 */
List.prototype.filterItemsInput = function(list){
    jQuery.expr[':'].Contains = function(a,i,m){
        return (a.textContent || a.innerText || "").toUpperCase().indexOf(m[3].toUpperCase())>=0;
    };
    var input = $('<input>').attr('type', 'text').bind('change keyup', function(){
      var filter = $(this).val();
      if (filter) {
        $(list).find("li:not(:Contains(" + filter + "))").slideUp();
        $(list).find("li:Contains(" + filter + ")").slideDown();
      } else {
        $(list).find("li").slideDown();
      }
    });

    var defaultValue = 'filter input';
    input.val(defaultValue);
    input.css('color', '#bbb');
    input.focus(function() {
        var actualValue = input.val();
        input.css('color', '#000');
        if (actualValue == defaultValue) {
            input.val('');
        }
    });
    input.blur(function() {
        var actualValue = input.val();
        if (!actualValue) {
            input.val(defaultValue);
            input.css('color', '#bbb');
        }
    });

    return $('<form>').attr('action', '#').append(input);
};

/**
 * Add a new New item for the list
 * @param {String} value the items text
 */
List.prototype.newListItem = function(value){
  var itemId = 'new-list-item' + this.page.pageBuilder.getUniqueId();
  var obj = {id: itemId, value: value, name: null, isActive: true, varaibles: {}, weight: this.listItems.length+1};
  var item = this.addListItem(obj);
  item.setProperty('status','new');
  return item;
};

/**
 * Add a new item to the list
 * @param {} obj item object
 */
List.prototype.addListItem = function(obj){
  var item = new ListItem();
  item.init(obj, this);
  this.listItems.push(item);
  return item;
};

/**
 * Get the list items as a string
 * @return {String}
 */
List.prototype.getListItemsCsv = function(){
  var string = '"Value","Name"' + "\n";
  for(var i = 0; i< this.listItems.length; i++){
    var item = this.listItems[i];
    var value = '"' + (($.trim(item.value).length > 0)?item.value.replace(/"/g,'""'):'') + '"';
    var name = '"' + (($.trim(item.name).length > 0)?item.name:'') + '"';
    string += value + ',' + name + "\n";
  }
  return string;
};


/**
 * jQuery-csv (jQuery Plugin)
 * version: 0.71 (2012-11-19)
 *
 * This document is licensed as free software under the terms of the
 * MIT License: http://www.opensource.org/licenses/mit-license.php
 *
 * Acknowledgements:
 * The original design and influence to implement this library as a jquery
 * plugin is influenced by jquery-json (http://code.google.com/p/jquery-json/).
 * If you're looking to use native JSON.Stringify but want additional backwards
 * compatibility for browsers that don't support it, I highly recommend you
 * check it out.
 *
 * A special thanks goes out to rwk@acm.org for providing a lot of valuable
 * feedback to the project including the core for the new FSM
 * (Finite State Machine) parsers. If you're looking for a stable TSV parser
 * be sure to take a look at jquery-tsv (http://code.google.com/p/jquery-tsv/).

 * For legal purposes I'll include the "NO WARRANTY EXPRESSED OR IMPLIED.
 * USE AT YOUR OWN RISK.". Which, in 'layman's terms' means, by using this
 * library you are accepting responsibility if it breaks your code.
 *
 * Legal jargon aside, I will do my best to provide a useful and stable core
 * that can effectively be built on.
 *
 * Copyrighted 2012 by Evan Plaice.
 */
RegExp.escape=function(s){return s.replace(/[-\/\\^$*+?.()|[\]{}]/g,'\\$&');};(function($){'use strict'
$.csv={defaults:{separator:',',delimiter:'"',headers:true},hooks:{castToScalar:function(value,state){var hasDot=/\./;if(isNaN(value)){return value;}else{if(hasDot.test(value)){return parseFloat(value);}else{var integer=parseInt(value);if(isNaN(integer)){return null;}else{return integer;}}}}},parsers:{parse:function(csv,options){var separator=options.separator;var delimiter=options.delimiter;if(!options.state.rowNum){options.state.rowNum=1;}
if(!options.state.colNum){options.state.colNum=1;}
var data=[];var entry=[];var state=0;var value=''
var exit=false;function endOfEntry(){state=0;value='';if(options.start&&options.state.rowNum<options.start){entry=[];options.state.rowNum++;options.state.colNum=1;return;}
if(options.onParseEntry===undefined){data.push(entry);}else{var hookVal=options.onParseEntry(entry,options.state);if(hookVal!==false){data.push(hookVal);}}
entry=[];if(options.end&&options.state.rowNum>=options.end){exit=true;}
options.state.rowNum++;options.state.colNum=1;}
function endOfValue(){if(options.onParseValue===undefined){entry.push(value);}else{var hook=options.onParseValue(value,options.state);if(hook!==false){entry.push(hook);}}
value='';state=0;options.state.colNum++;}
var escSeparator=RegExp.escape(separator);var escDelimiter=RegExp.escape(delimiter);var match=/(D|S|\n|\r|[^DS\r\n]+)/;var matchSrc=match.source;matchSrc=matchSrc.replace(/S/g,escSeparator);matchSrc=matchSrc.replace(/D/g,escDelimiter);match=RegExp(matchSrc,'gm');csv.replace(match,function(m0){if(exit){return;}
switch(state){case 0:if(m0===separator){value+='';endOfValue();break;}
if(m0===delimiter){state=1;break;}
if(m0==='\n'){endOfValue();endOfEntry();break;}
if(/^\r$/.test(m0)){break;}
value+=m0;state=3;break;case 1:if(m0===delimiter){state=2;break;}
value+=m0;state=1;break;case 2:if(m0===delimiter){value+=m0;state=1;break;}
if(m0===separator){endOfValue();break;}
if(m0==='\n'){endOfValue();endOfEntry();break;}
if(/^\r$/.test(m0)){break;}
throw new Error('CSVDataError: Illegal State [Row:'+options.state.rowNum+'][Col:'+options.state.colNum+']');case 3:if(m0===separator){endOfValue();break;}
if(m0==='\n'){endOfValue();endOfEntry();break;}
if(/^\r$/.test(m0)){break;}
if(m0===delimiter){throw new Error('CSVDataError: Illegal Quote [Row:'+options.state.rowNum+'][Col:'+options.state.colNum+']');}
throw new Error('CSVDataError: Illegal Data [Row:'+options.state.rowNum+'][Col:'+options.state.colNum+']');default:throw new Error('CSVDataError: Unknown State [Row:'+options.state.rowNum+'][Col:'+options.state.colNum+']');}});if(entry.length!==0){endOfValue();endOfEntry();}
return data;},splitLines:function(csv,options){var separator=options.separator;var delimiter=options.delimiter;if(!options.state.rowNum){options.state.rowNum=1;}
var entries=[];var state=0;var entry='';var exit=false;function endOfLine(){state=0;if(options.start&&options.state.rowNum<options.start){entry='';options.state.rowNum++;return;}
if(options.onParseEntry===undefined){entries.push(entry);}else{var hookVal=options.onParseEntry(entry,options.state);if(hookVal!==false){entries.push(hookVal);}}
entry='';if(options.end&&options.state.rowNum>=options.end){exit=true;}
options.state.rowNum++;}
var escSeparator=RegExp.escape(separator);var escDelimiter=RegExp.escape(delimiter);var match=/(D|S|\n|\r|[^DS\r\n]+)/;var matchSrc=match.source;matchSrc=matchSrc.replace(/S/g,escSeparator);matchSrc=matchSrc.replace(/D/g,escDelimiter);match=RegExp(matchSrc,'gm');csv.replace(match,function(m0){if(exit){return;}
switch(state){case 0:if(m0===separator){entry+=m0;state=0;break;}
if(m0===delimiter){entry+=m0;state=1;break;}
if(m0==='\n'){endOfLine();break;}
if(/^\r$/.test(m0)){break;}
entry+=m0;state=3;break;case 1:if(m0===delimiter){entry+=m0;state=2;break;}
entry+=m0;state=1;break;case 2:var prevChar=entry.substr(entry.length-1);if(m0===delimiter&&prevChar===delimiter){entry+=m0;state=1;break;}
if(m0===separator){entry+=m0;state=0;break;}
if(m0==='\n'){endOfLine();break;}
if(m0==='\r'){break;}
throw new Error('CSVDataError: Illegal state [Row:'+options.state.rowNum+']');case 3:if(m0===separator){entry+=m0;state=0;break;}
if(m0==='\n'){endOfLine();break;}
if(m0==='\r'){break;}
if(m0===delimiter){throw new Error('CSVDataError: Illegal quote [Row:'+options.state.rowNum+']');}
throw new Error('CSVDataError: Illegal state [Row:'+options.state.rowNum+']');default:throw new Error('CSVDataError: Unknown state [Row:'+options.state.rowNum+']');}});if(entry!==''){endOfLine();}
return entries;},parseEntry:function(csv,options){var separator=options.separator;var delimiter=options.delimiter;if(!options.state.rowNum){options.state.rowNum=1;}
if(!options.state.colNum){options.state.colNum=1;}
var entry=[];var state=0;var value='';function endOfValue(){if(options.onParseValue===undefined){entry.push(value);}else{var hook=options.onParseValue(value,options.state);if(hook!==false){entry.push(hook);}}
value='';state=0;options.state.colNum++;}
if(!options.match){var escSeparator=RegExp.escape(separator);var escDelimiter=RegExp.escape(delimiter);var match=/(D|S|\n|\r|[^DS\r\n]+)/;var matchSrc=match.source;matchSrc=matchSrc.replace(/S/g,escSeparator);matchSrc=matchSrc.replace(/D/g,escDelimiter);options.match=RegExp(matchSrc,'gm');}
csv.replace(options.match,function(m0){switch(state){case 0:if(m0===separator){value+='';endOfValue();break;}
if(m0===delimiter){state=1;break;}
if(m0==='\n'||m0==='\r'){break;}
value+=m0;state=3;break;case 1:if(m0===delimiter){state=2;break;}
value+=m0;state=1;break;case 2:if(m0===delimiter){value+=m0;state=1;break;}
if(m0===separator){endOfValue();break;}
if(m0==='\n'||m0==='\r'){break;}
throw new Error('CSVDataError: Illegal State [Row:'+options.state.rowNum+'][Col:'+options.state.colNum+']');case 3:if(m0===separator){endOfValue();break;}
if(m0==='\n'||m0==='\r'){break;}
if(m0===delimiter){throw new Error('CSVDataError: Illegal Quote [Row:'+options.state.rowNum+'][Col:'+options.state.colNum+']');}
throw new Error('CSVDataError: Illegal Data [Row:'+options.state.rowNum+'][Col:'+options.state.colNum+']');default:throw new Error('CSVDataError: Unknown State [Row:'+options.state.rowNum+'][Col:'+options.state.colNum+']');}});endOfValue();return entry;}},toArray:function(csv,options,callback){var options=(options!==undefined?options:{});var config={};config.callback=((callback!==undefined&&typeof(callback)==='function')?callback:false);config.separator='separator'in options?options.separator:$.csv.defaults.separator;config.delimiter='delimiter'in options?options.delimiter:$.csv.defaults.delimiter;var state=(options.state!==undefined?options.state:{});var options={delimiter:config.delimiter,separator:config.separator,onParseEntry:options.onParseEntry,onParseValue:options.onParseValue,state:state}
var entry=$.csv.parsers.parseEntry(csv,options);if(!config.callback){return entry;}else{config.callback('',entry);}},toArrays:function(csv,options,callback){var options=(options!==undefined?options:{});var config={};config.callback=((callback!==undefined&&typeof(callback)==='function')?callback:false);config.separator='separator'in options?options.separator:$.csv.defaults.separator;config.delimiter='delimiter'in options?options.delimiter:$.csv.defaults.delimiter;var data=[];var options={delimiter:config.delimiter,separator:config.separator,onParseEntry:options.onParseEntry,onParseValue:options.onParseValue,start:options.start,end:options.end,state:{rowNum:1,colNum:1}};data=$.csv.parsers.parse(csv,options);if(!config.callback){return data;}else{config.callback('',data);}},toObjects:function(csv,options,callback){var options=(options!==undefined?options:{});var config={};config.callback=((callback!==undefined&&typeof(callback)==='function')?callback:false);config.separator='separator'in options?options.separator:$.csv.defaults.separator;config.delimiter='delimiter'in options?options.delimiter:$.csv.defaults.delimiter;config.headers='headers'in options?options.headers:$.csv.defaults.headers;options.start='start'in options?options.start:1;if(config.headers){options.start++;}
if(options.end&&config.headers){options.end++;}
var lines=[];var data=[];var options={delimiter:config.delimiter,separator:config.separator,onParseEntry:options.onParseEntry,onParseValue:options.onParseValue,start:options.start,end:options.end,state:{rowNum:1,colNum:1},match:false};var headerOptions={delimiter:config.delimiter,separator:config.separator,start:1,end:1,state:{rowNum:1,colNum:1}}
var headerLine=$.csv.parsers.splitLines(csv,headerOptions);var headers=$.csv.toArray(headerLine[0],options);var lines=$.csv.parsers.splitLines(csv,options);options.state.colNum=1;if(headers){options.state.rowNum=2;}else{options.state.rowNum=1;}
for(var i=0,len=lines.length;i<len;i++){var entry=$.csv.toArray(lines[i],options);var object={};for(var j in headers){object[headers[j]]=entry[j];}
data.push(object);options.state.rowNum++;}
if(!config.callback){return data;}else{config.callback('',data);}},fromArrays:function(arrays,options,callback){var options=(options!==undefined?options:{});var config={};config.callback=((callback!==undefined&&typeof(callback)==='function')?callback:false);config.separator='separator'in options?options.separator:$.csv.defaults.separator;config.delimiter='delimiter'in options?options.delimiter:$.csv.defaults.delimiter;config.escaper='escaper'in options?options.escaper:$.csv.defaults.escaper;config.experimental='experimental'in options?options.experimental:false;if(!config.experimental){throw new Error('not implemented');}
var output=[];for(i in arrays){output.push(arrays[i]);}
if(!config.callback){return output;}else{config.callback('',output);}},fromObjects2CSV:function(objects,options,callback){var options=(options!==undefined?options:{});var config={};config.callback=((callback!==undefined&&typeof(callback)==='function')?callback:false);config.separator='separator'in options?options.separator:$.csv.defaults.separator;config.delimiter='delimiter'in options?options.delimiter:$.csv.defaults.delimiter;config.experimental='experimental'in options?options.experimental:false;if(!config.experimental){throw new Error('not implemented');}
var output=[];for(i in objects){output.push(arrays[i]);}
if(!config.callback){return output;}else{config.callback('',output);}}};$.csvEntry2Array=$.csv.toArray;$.csv2Array=$.csv.toArrays;$.csv2Dictionary=$.csv.toObjects;})(jQuery);