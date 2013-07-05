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
  this.maxLoad = 100;
  this.lastClick = null;
  this.services = new Services;
};

Grid.prototype.init = function(){
  var self = this;
  var columns = this.getColumns();
  var buttons = this.getButtons();
  var data = [];
  $(this.target).empty();
  $(this.target).addClass('applicant_grid');
  $(this.target).append($('<div>').addClass('overlay'));
  var table = $('<table>').addClass('grid');
  if($.browser.mozilla){//Firefox
    table.css('MozUserSelect','none');
  }else if($.browser.msie){//IE
    table.bind('selectstart',function(){return false;});
  }else{//Opera, etc.
    table.mousedown(function(){return false;});
  }

  $(this.target).append(table);
  var grid = table.dataTable( {
    sScrollY: "500",
    sScrollX: "95%",
    sPaginationType: 'full_numbers',
    aaSorting: [[ 1, "asc" ]],
    fnDrawCallback: Grid.processDraw,
    aaData: data,
    aoColumns: columns,
    bJQueryUI: true,
    "sDom": '<"H"Tfrl>t<"F"ip>',
    "oTableTools": {
      "sRowSelect": "multi",
      "fnPreRowSelect": function(e, nodes) {
        if(e){ //e is not defined when we call the fnSelect method directly
          if (e.shiftKey && self.lastClick != null) {
            var oSettings = grid.fnSettings();
            var clickPositionOne = grid.fnGetPosition(self.lastClick);
            var clickPositionTwo = grid.fnGetPosition(nodes[0]);
            
            var displayPositionOne = -1;
            var displayPositionTwo = -1;
            for(var i = 0; i < oSettings.aiDisplay.length; i++) {
              if(oSettings.aiDisplay[i] == clickPositionOne) {
                displayPositionOne = i;
              }
              if(oSettings.aiDisplay[i] == clickPositionTwo) {
                displayPositionTwo = i;
              }
            }
            if (displayPositionOne >= 0 && displayPositionTwo >= 0 && displayPositionOne != displayPositionTwo) {
              //use min/max so we can click higher or ower than the first click and still loop corectly
              for (var i=Math.min(displayPositionTwo,displayPositionOne); i < Math.max(displayPositionTwo,displayPositionOne); i++) {
                this.fnSelect(oSettings.aoData[ oSettings.aiDisplay[i] ].nTr);
              }
            }
          }
          self.lastClick = nodes[0];
        }
        return true;
      },
      "aButtons": buttons
    }
  });
  var progressbar = $('<div>').addClass('progress').append($('<div>').addClass('label').html('Loading Grid...'));
  $('div.overlay', this.target).append(progressbar);
  progressbar.progressbar({
    max: this.applicantIds.length,
    value: 1
  });
  this.loadapps(this.applicantIds, grid);
};

Grid.prototype.getColumns = function(){
  var self = this;
  var columns = [];
  columns.push({
    sTitle: "",
    bSortable: false,
    bSearchable: false,
    sWidth: '8px',
    mData: 'id',
    mRender: function( data, type, full ) {
      return '<a class="applicantlink" href="' + data + '">' + "<img src='resource/foundation/media/icons/user_go.png'>" + '</a>';
    }
  });
  columns.push({
    sTitle: "Applicant",
    sWidth: '30px',
    mData: function(obj, type, set){
      return {lastName: obj.lastName, firstName: obj.firstName, fullName: obj.fullName, email: obj.email};
    },
    mRender: function( data, type, full ) {
      if(type == 'sort'){
        return data.lastName + data.firstName;
      }
      return data.fullName + ' ' + data.email;
    }
  });
  $.each(this.display.listElements(), function(){
    switch(this.type){
      case 'applicant':
        var column = {
          sTitle: this.title,
          mData: this.name
        };
        if(this.name == 'updatedAt' || this.name == 'createdAt' || this.name == 'lastLogin'){
          column.mRender = Grid.formatDate;
          column.sType = 'date';
        }
        if(this.name == 'isLocked' || this.name == 'hasPaid'){
          column.mRender = Grid.formatCheckmark;
        }
        if(this.name == 'attachments'){
          column.mRender = Grid.formatAttachments;
        }
        columns.push(column);
        break;
      case 'element':
        var displayElement = this;
        columns.push({
          sTitle: this.title,
          mData: function(obj, type, set){
            return {
              grid: self,
              displayElement: displayElement, 
              applicant: obj
            };
          },
          mRender: Grid.formatElementAnswers
        });
        break;
      case 'page':
        var displayElement = this;
        columns.push({
          sTitle: this.title,
          sType: ('sType' in this)?this.Stype:null,
          mData: function(obj, type, set){
            return {
              grid: self,
              displayElement: displayElement, 
              applicant: obj
            };
          },
          mRender: Grid.formatPageAnswers
        });
        break;
    }
  });
  return columns;
};

/**
 * Get the buttons for the grid based on user permissions
 * @return []
**/
Grid.prototype.getButtons = function(){
  var self = this;
  var buttons = [];
  buttons.push('select_all');
  buttons.push('select_none');
  if(self.services.checkIsAllowed('applicants_grid', 'sendMessage')){
    buttons.push({
      "sExtends": "send_messages",
      "sButtonText": "Email",
      "sUrl": "grid/sendMessage"
    });
  }
  var obj = {
    "sExtends": "download_applicants",
    "sButtonText": "Download",
    'templates': this.services.getCurrentApplication().listTemplates(),
    'downloadXls': this.services.checkIsAllowed('applicants_grid', 'downloadXls'),
    'downloadXml': this.services.checkIsAllowed('applicants_grid', 'downloadXml'),
    'downloadPdfArchive': this.services.checkIsAllowed('applicants_grid', 'downloadPdfArchive'),
    'downloadJson': this.services.checkIsAllowed('applicants_grid', 'downloadJson'),
    'basepath': this.services.getControllerPath('applicants_grid')
  };
  if(obj.downloadXls || obj.downloadXml || obj.downloadPdfArchive || obj.downloadJson){
    buttons.push(obj);
  }
  return buttons;
};

Grid.prototype.loadapps = function(applicantIds, grid){
  var self = this;
  if(applicantIds.length){

    var limitedIds = applicantIds.splice(0, self.maxLoad);

    $.post(self.controllerPath + '/getApplicants',{applicantIds: limitedIds, display: self.display.getObj()
    }, function(json){
      var applicants = [];
      var length = json.data.result.applicants.length;
      var pages = json.data.result.pages; // available pages

      while (length--) {
        applicants.push(new ApplicantData(json.data.result.applicants.splice(length, 1)[0]));
        $('div.progress', self.target).progressbar("value", $('div.progress', self.target).progressbar('value')+1);
      }
      grid.fnAddData(applicants);
      grid.fnAdjustColumnSizing();
      self.loadapps(applicantIds, grid);
	});
  } else {
    $('div:first', this.target).fadeOut();
  }
};

/**
 * Format date objects
 */
Grid.formatDate = function(data, type, full){
  if(data == null) return null;
  if(type == 'filter' || type == 'display'){
    var date = new Date(data.date);
    return date.toLocaleDateString();
  }
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
 * Display applicant attachments
 */
Grid.formatAttachments = function(data, type, full){
  var self = this;
  if(this.attachmentThumbnailImg == undefined){
    this.attachmentThumbnailImg = $('<img>').attr('src', 'resource/foundation/media/default_pdf_logo.png').attr('title', 'Download PDF').attr('alt', 'PDF Logo');
    this.attachmentThumbnailImg.css('height', '1em');
  }
  var values = [];
  $(data).each(function(){
    var a = $('<a>').attr('href', this.filePath).addClass('dialog_file').append(self.attachmentThumbnailImg.clone());
    values.push(a.clone().wrap('<p>').parent().html());
  });
  if(type == 'display'){
    if(values.length == 1){
      return values[0];
    } else {
      var ol = $('<ol>');
      $.each(values, function(){
        ol.append($('<li>').html(this.toString()));
      });
      return ol.clone().wrap('<p>').parent().html();
    }
  } else if (type == 'sort'){
    return values.length;
  }

  return '';
};

/**
 * Format page element grid data
 */
Grid.formatElementAnswers = function(data, type, full){
  var elementClass = data.grid.display.getApplication().getElementClassById(data.displayElement.name);
  var answers = data.applicant.getAnswersForElement(elementClass.id);
  return elementClass.gridData(answers, type, full);
};

/**
 * Format page element grid data
 */
Grid.formatPageAnswers = function(data, type, full){
  var pageClass = data.grid.display.getApplication().getPageClassById(data.displayElement.pageId);
  return pageClass.gridData(data, type, full);
};

Grid.processDraw = function(){
  Grid.bindApplicantLinks();
  Grid.bindDialogLinks();
};

/**
 * Overlay applicants when they are clicked
 */
Grid.bindApplicantLinks = function(){
  $('a.applicantlink').unbind().bind('click', function(){
    var div = $('<div>');
    div.css("overflow-y", "auto");
    div.dialog({
      modal: true,
      position: 'center',
      width: '90%',
      height: ($(window).height()*0.9),
      close: function() {
        div.dialog("destroy").remove();
      }
    });
//    $.get($(this).attr('href'),function(html){
//      div.html(html);
//    });
    div.html('Applicant data is not yet available.');
    return false;
  });
};

/**
 * Overlay file links when they are clicked
 */
Grid.bindDialogLinks = function(){
  $('a.dialog_file').unbind().bind('click', function(){
    var div = $('<div>');
    div.css("overflow-y", "auto");
    div.dialog({
      modal: true,
      position: 'center',
      width: '90%',
      height: ($(window).height()*0.8),
      close: function() {
        div.dialog("destroy").remove();
      }
    });
    var src = $(this).attr('href');
    var object = $('<object>').attr('data', src);
    object.append($('<param>').attr('name', 'src').attr('value', src));
    object.attr('height', '100%');
    object.attr('width', '100%');
    div.append(object);
    return false;
  });
};

// Simple Set Clipboard System
// Author: Joseph Huckaby
var ZeroClipboard_TableTools={version:"1.0.4-TableTools2",clients:{},moviePath:"",nextId:1,$:function(a){"string"==typeof a&&(a=document.getElementById(a));a.addClass||(a.hide=function(){this.style.display="none"},a.show=function(){this.style.display=""},a.addClass=function(a){this.removeClass(a);this.className+=" "+a},a.removeClass=function(a){this.className=this.className.replace(RegExp("\\s*"+a+"\\s*")," ").replace(/^\s+/,"").replace(/\s+$/,"")},a.hasClass=function(a){return!!this.className.match(RegExp("\\s*"+
a+"\\s*"))});return a},setMoviePath:function(a){this.moviePath=a},dispatch:function(a,b,c){(a=this.clients[a])&&a.receiveEvent(b,c)},register:function(a,b){this.clients[a]=b},getDOMObjectPosition:function(a){var b={left:0,top:0,width:a.width?a.width:a.offsetWidth,height:a.height?a.height:a.offsetHeight};""!=a.style.width&&(b.width=a.style.width.replace("px",""));""!=a.style.height&&(b.height=a.style.height.replace("px",""));for(;a;)b.left+=a.offsetLeft,b.top+=a.offsetTop,a=a.offsetParent;return b},
Client:function(a){this.handlers={};this.id=ZeroClipboard_TableTools.nextId++;this.movieId="ZeroClipboard_TableToolsMovie_"+this.id;ZeroClipboard_TableTools.register(this.id,this);a&&this.glue(a)}};
ZeroClipboard_TableTools.Client.prototype={id:0,ready:!1,movie:null,clipText:"",fileName:"",action:"copy",handCursorEnabled:!0,cssEffects:!0,handlers:null,sized:!1,glue:function(a,b){this.domElement=ZeroClipboard_TableTools.$(a);var c=99;this.domElement.style.zIndex&&(c=parseInt(this.domElement.style.zIndex)+1);var d=ZeroClipboard_TableTools.getDOMObjectPosition(this.domElement);this.div=document.createElement("div");var e=this.div.style;e.position="absolute";e.left="0px";e.top="0px";e.width=d.width+
"px";e.height=d.height+"px";e.zIndex=c;"undefined"!=typeof b&&""!=b&&(this.div.title=b);0!=d.width&&0!=d.height&&(this.sized=!0);this.domElement&&(this.domElement.appendChild(this.div),this.div.innerHTML=this.getHTML(d.width,d.height))},positionElement:function(){var a=ZeroClipboard_TableTools.getDOMObjectPosition(this.domElement),b=this.div.style;b.position="absolute";b.width=a.width+"px";b.height=a.height+"px";0!=a.width&&0!=a.height&&(this.sized=!0,b=this.div.childNodes[0],b.width=a.width,b.height=
a.height)},getHTML:function(a,b){var c="",d="id="+this.id+"&width="+a+"&height="+b;if(navigator.userAgent.match(/MSIE/))var e=location.href.match(/^https/i)?"https://":"http://",c=c+('<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="'+e+'download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,0,0" width="'+a+'" height="'+b+'" id="'+this.movieId+'" align="middle"><param name="allowScriptAccess" value="always" /><param name="allowFullScreen" value="false" /><param name="movie" value="'+
ZeroClipboard_TableTools.moviePath+'" /><param name="loop" value="false" /><param name="menu" value="false" /><param name="quality" value="best" /><param name="bgcolor" value="#ffffff" /><param name="flashvars" value="'+d+'"/><param name="wmode" value="transparent"/></object>');else c+='<embed id="'+this.movieId+'" src="'+ZeroClipboard_TableTools.moviePath+'" loop="false" menu="false" quality="best" bgcolor="#ffffff" width="'+a+'" height="'+b+'" name="'+this.movieId+'" align="middle" allowScriptAccess="always" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" flashvars="'+
d+'" wmode="transparent" />';return c},hide:function(){this.div&&(this.div.style.left="-2000px")},show:function(){this.reposition()},destroy:function(){if(this.domElement&&this.div){this.hide();this.div.innerHTML="";var a=document.getElementsByTagName("body")[0];try{a.removeChild(this.div)}catch(b){}this.div=this.domElement=null}},reposition:function(a){a&&((this.domElement=ZeroClipboard_TableTools.$(a))||this.hide());if(this.domElement&&this.div){var a=ZeroClipboard_TableTools.getDOMObjectPosition(this.domElement),
b=this.div.style;b.left=""+a.left+"px";b.top=""+a.top+"px"}},clearText:function(){this.clipText="";this.ready&&this.movie.clearText()},appendText:function(a){this.clipText+=a;this.ready&&this.movie.appendText(a)},setText:function(a){this.clipText=a;this.ready&&this.movie.setText(a)},setCharSet:function(a){this.charSet=a;this.ready&&this.movie.setCharSet(a)},setBomInc:function(a){this.incBom=a;this.ready&&this.movie.setBomInc(a)},setFileName:function(a){this.fileName=a;this.ready&&this.movie.setFileName(a)},
setAction:function(a){this.action=a;this.ready&&this.movie.setAction(a)},addEventListener:function(a,b){a=a.toString().toLowerCase().replace(/^on/,"");this.handlers[a]||(this.handlers[a]=[]);this.handlers[a].push(b)},setHandCursor:function(a){this.handCursorEnabled=a;this.ready&&this.movie.setHandCursor(a)},setCSSEffects:function(a){this.cssEffects=!!a},receiveEvent:function(a,b){a=a.toString().toLowerCase().replace(/^on/,"");switch(a){case "load":this.movie=document.getElementById(this.movieId);
if(!this.movie){var c=this;setTimeout(function(){c.receiveEvent("load",null)},1);return}if(!this.ready&&navigator.userAgent.match(/Firefox/)&&navigator.userAgent.match(/Windows/)){c=this;setTimeout(function(){c.receiveEvent("load",null)},100);this.ready=!0;return}this.ready=!0;this.movie.clearText();this.movie.appendText(this.clipText);this.movie.setFileName(this.fileName);this.movie.setAction(this.action);this.movie.setCharSet(this.charSet);this.movie.setBomInc(this.incBom);this.movie.setHandCursor(this.handCursorEnabled);
break;case "mouseover":this.domElement&&this.cssEffects&&this.recoverActive&&this.domElement.addClass("active");break;case "mouseout":this.domElement&&this.cssEffects&&(this.recoverActive=!1,this.domElement.hasClass("active")&&(this.domElement.removeClass("active"),this.recoverActive=!0));break;case "mousedown":this.domElement&&this.cssEffects&&this.domElement.addClass("active");break;case "mouseup":this.domElement&&this.cssEffects&&(this.domElement.removeClass("active"),this.recoverActive=!1)}if(this.handlers[a])for(var d=
0,e=this.handlers[a].length;d<e;d++){var f=this.handlers[a][d];if("function"==typeof f)f(this,b);else if("object"==typeof f&&2==f.length)f[0][f[1]](this,b);else if("string"==typeof f)window[f](this,b)}}};


/*
 * File:        TableTools.min.js
 * Version:     2.1.5
 * Author:      Allan Jardine (www.sprymedia.co.uk)
 * 
 * Copyright 2009-2012 Allan Jardine, all rights reserved.
 *
 * This source file is free software, under either the GPL v2 license or a
 * BSD (3 point) style license, as supplied with this software.
 * 
 * This source file is distributed in the hope that it will be useful, but 
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY 
 * or FITNESS FOR A PARTICULAR PURPOSE. See the license files for details.
 */
var TableTools;
(function(e,n,g){TableTools=function(a,b){!this instanceof TableTools&&alert("Warning: TableTools must be initialised with the keyword 'new'");this.s={that:this,dt:a.fnSettings(),print:{saveStart:-1,saveLength:-1,saveScroll:-1,funcEnd:function(){}},buttonCounter:0,select:{type:"",selected:[],preRowSelect:null,postSelected:null,postDeselected:null,all:!1,selectedClass:""},custom:{},swfPath:"",buttonSet:[],master:!1,tags:{}};this.dom={container:null,table:null,print:{hidden:[],message:null},collection:{collection:null,
background:null}};this.classes=e.extend(!0,{},TableTools.classes);this.s.dt.bJUI&&e.extend(!0,this.classes,TableTools.classes_themeroller);this.fnSettings=function(){return this.s};"undefined"==typeof b&&(b={});this._fnConstruct(b);return this};TableTools.prototype={fnGetSelected:function(a){var b=[],c=this.s.dt.aoData,d=this.s.dt.aiDisplay,f;if(a){a=0;for(f=d.length;a<f;a++)c[d[a]]._DTTT_selected&&b.push(c[d[a]].nTr)}else{a=0;for(f=c.length;a<f;a++)c[a]._DTTT_selected&&b.push(c[a].nTr)}return b},
fnGetSelectedData:function(){var a=[],b=this.s.dt.aoData,c,d;c=0;for(d=b.length;c<d;c++)b[c]._DTTT_selected&&a.push(this.s.dt.oInstance.fnGetData(c));return a},fnIsSelected:function(a){a=this.s.dt.oInstance.fnGetPosition(a);return!0===this.s.dt.aoData[a]._DTTT_selected?!0:!1},fnSelectAll:function(a){var b=this._fnGetMasterSettings();this._fnRowSelect(!0===a?b.dt.aiDisplay:b.dt.aoData)},fnSelectNone:function(a){this._fnGetMasterSettings();this._fnRowDeselect(this.fnGetSelected(a))},fnSelect:function(a){"single"==
this.s.select.type?(this.fnSelectNone(),this._fnRowSelect(a)):"multi"==this.s.select.type&&this._fnRowSelect(a)},fnDeselect:function(a){this._fnRowDeselect(a)},fnGetTitle:function(a){var b="";"undefined"!=typeof a.sTitle&&""!==a.sTitle?b=a.sTitle:(a=g.getElementsByTagName("title"),0<a.length&&(b=a[0].innerHTML));return 4>"\u00a1".toString().length?b.replace(/[^a-zA-Z0-9_\u00A1-\uFFFF\.,\-_ !\(\)]/g,""):b.replace(/[^a-zA-Z0-9_\.,\-_ !\(\)]/g,"")},fnCalcColRatios:function(a){var b=this.s.dt.aoColumns,
a=this._fnColumnTargets(a.mColumns),c=[],d=0,f=0,e,g;e=0;for(g=a.length;e<g;e++)a[e]&&(d=b[e].nTh.offsetWidth,f+=d,c.push(d));e=0;for(g=c.length;e<g;e++)c[e]/=f;return c.join("\t")},fnGetTableData:function(a){if(this.s.dt)return this._fnGetDataTablesData(a)},fnSetText:function(a,b){this._fnFlashSetText(a,b)},fnResizeButtons:function(){for(var a in ZeroClipboard_TableTools.clients)if(a){var b=ZeroClipboard_TableTools.clients[a];"undefined"!=typeof b.domElement&&b.domElement.parentNode&&b.positionElement()}},
fnResizeRequired:function(){for(var a in ZeroClipboard_TableTools.clients)if(a){var b=ZeroClipboard_TableTools.clients[a];if("undefined"!=typeof b.domElement&&b.domElement.parentNode==this.dom.container&&!1===b.sized)return!0}return!1},fnPrint:function(a,b){void 0===b&&(b={});void 0===a||a?this._fnPrintStart(b):this._fnPrintEnd()},fnInfo:function(a,b){var c=g.createElement("div");c.className=this.classes.print.info;c.innerHTML=a;g.body.appendChild(c);setTimeout(function(){e(c).fadeOut("normal",function(){g.body.removeChild(c)})},
b)},_fnConstruct:function(a){var b=this;this._fnCustomiseSettings(a);this.dom.container=g.createElement(this.s.tags.container);this.dom.container.className=this.classes.container;"none"!=this.s.select.type&&this._fnRowSelectConfig();this._fnButtonDefinations(this.s.buttonSet,this.dom.container);this.s.dt.aoDestroyCallback.push({sName:"TableTools",fn:function(){e(b.s.dt.nTBody).off("click.DTTT_Select","tr");e(b.dom.container).empty()}})},_fnCustomiseSettings:function(a){"undefined"==typeof this.s.dt._TableToolsInit&&
(this.s.master=!0,this.s.dt._TableToolsInit=!0);this.dom.table=this.s.dt.nTable;this.s.custom=e.extend({},TableTools.DEFAULTS,a);this.s.swfPath=this.s.custom.sSwfPath;"undefined"!=typeof ZeroClipboard_TableTools&&(ZeroClipboard_TableTools.moviePath=this.s.swfPath);this.s.select.type=this.s.custom.sRowSelect;this.s.select.preRowSelect=this.s.custom.fnPreRowSelect;this.s.select.postSelected=this.s.custom.fnRowSelected;this.s.select.postDeselected=this.s.custom.fnRowDeselected;this.s.custom.sSelectedClass&&
(this.classes.select.row=this.s.custom.sSelectedClass);this.s.tags=this.s.custom.oTags;this.s.buttonSet=this.s.custom.aButtons},_fnButtonDefinations:function(a,b){for(var c,d=0,f=a.length;d<f;d++){if("string"==typeof a[d]){if("undefined"==typeof TableTools.BUTTONS[a[d]]){alert("TableTools: Warning - unknown button type: "+a[d]);continue}c=e.extend({},TableTools.BUTTONS[a[d]],!0)}else{if("undefined"==typeof TableTools.BUTTONS[a[d].sExtends]){alert("TableTools: Warning - unknown button type: "+a[d].sExtends);
continue}c=e.extend({},TableTools.BUTTONS[a[d].sExtends],!0);c=e.extend(c,a[d],!0)}b.appendChild(this._fnCreateButton(c,e(b).hasClass(this.classes.collection.container)))}},_fnCreateButton:function(a,b){var c=this._fnButtonBase(a,b);a.sAction.match(/flash/)?this._fnFlashConfig(c,a):"text"==a.sAction?this._fnTextConfig(c,a):"div"==a.sAction?this._fnTextConfig(c,a):"collection"==a.sAction&&(this._fnTextConfig(c,a),this._fnCollectionConfig(c,a));return c},_fnButtonBase:function(a,b){var c,d,f;b?(c="default"!==
a.sTag?a.sTag:this.s.tags.collection.button,d="default"!==a.sLinerTag?a.sLiner:this.s.tags.collection.liner,f=this.classes.collection.buttons.normal):(c="default"!==a.sTag?a.sTag:this.s.tags.button,d="default"!==a.sLinerTag?a.sLiner:this.s.tags.liner,f=this.classes.buttons.normal);c=g.createElement(c);d=g.createElement(d);var e=this._fnGetMasterSettings();c.className=f+" "+a.sButtonClass;c.setAttribute("id","ToolTables_"+this.s.dt.sInstance+"_"+e.buttonCounter);c.appendChild(d);d.innerHTML=a.sButtonText;
e.buttonCounter++;return c},_fnGetMasterSettings:function(){if(this.s.master)return this.s;for(var a=TableTools._aInstances,b=0,c=a.length;b<c;b++)if(this.dom.table==a[b].s.dt.nTable)return a[b].s},_fnCollectionConfig:function(a,b){var c=g.createElement(this.s.tags.collection.container);c.style.display="none";c.className=this.classes.collection.container;b._collection=c;g.body.appendChild(c);this._fnButtonDefinations(b.aButtons,c)},_fnCollectionShow:function(a,b){var c=this,d=e(a).offset(),f=b._collection,
j=d.left,d=d.top+e(a).outerHeight(),m=e(n).height(),h=e(g).height(),k=e(n).width(),o=e(g).width();f.style.position="absolute";f.style.left=j+"px";f.style.top=d+"px";f.style.display="block";e(f).css("opacity",0);var l=g.createElement("div");l.style.position="absolute";l.style.left="0px";l.style.top="0px";l.style.height=(m>h?m:h)+"px";l.style.width=(k>o?k:o)+"px";l.className=this.classes.collection.background;e(l).css("opacity",0);g.body.appendChild(l);g.body.appendChild(f);m=e(f).outerWidth();k=e(f).outerHeight();
j+m>o&&(f.style.left=o-m+"px");d+k>h&&(f.style.top=d-k-e(a).outerHeight()+"px");this.dom.collection.collection=f;this.dom.collection.background=l;setTimeout(function(){e(f).animate({opacity:1},500);e(l).animate({opacity:0.25},500)},10);this.fnResizeButtons();e(l).click(function(){c._fnCollectionHide.call(c,null,null)})},_fnCollectionHide:function(a,b){!(null!==b&&"collection"==b.sExtends)&&null!==this.dom.collection.collection&&(e(this.dom.collection.collection).animate({opacity:0},500,function(){this.style.display=
"none"}),e(this.dom.collection.background).animate({opacity:0},500,function(){this.parentNode.removeChild(this)}),this.dom.collection.collection=null,this.dom.collection.background=null)},_fnRowSelectConfig:function(){if(this.s.master){var a=this,b=this.s.dt;e(b.nTable).addClass(this.classes.select.table);e(b.nTBody).on("click.DTTT_Select","tr",function(c){this.parentNode==b.nTBody&&null!==b.oInstance.fnGetData(this)&&(a.fnIsSelected(this)?a._fnRowDeselect(this,c):"single"==a.s.select.type?(a.fnSelectNone(),
a._fnRowSelect(this,c)):"multi"==a.s.select.type&&a._fnRowSelect(this,c))});b.oApi._fnCallbackReg(b,"aoRowCreatedCallback",function(c,d,f){b.aoData[f]._DTTT_selected&&e(c).addClass(a.classes.select.row)},"TableTools-SelectAll")}},_fnRowSelect:function(a,b){var c=this._fnSelectData(a),d=[],f,j;f=0;for(j=c.length;f<j;f++)c[f].nTr&&d.push(c[f].nTr);if(null===this.s.select.preRowSelect||this.s.select.preRowSelect.call(this,b,d,!0)){f=0;for(j=c.length;f<j;f++)c[f]._DTTT_selected=!0,c[f].nTr&&e(c[f].nTr).addClass(this.classes.select.row);
null!==this.s.select.postSelected&&this.s.select.postSelected.call(this,d);TableTools._fnEventDispatch(this,"select",d,!0)}},_fnRowDeselect:function(a,b){var c=this._fnSelectData(a),d=[],f,j;f=0;for(j=c.length;f<j;f++)c[f].nTr&&d.push(c[f].nTr);if(null===this.s.select.preRowSelect||this.s.select.preRowSelect.call(this,b,d,!1)){f=0;for(j=c.length;f<j;f++)c[f]._DTTT_selected=!1,c[f].nTr&&e(c[f].nTr).removeClass(this.classes.select.row);null!==this.s.select.postDeselected&&this.s.select.postDeselected.call(this,
d);TableTools._fnEventDispatch(this,"select",d,!1)}},_fnSelectData:function(a){var b=[],c,d,f;if(a.nodeName)c=this.s.dt.oInstance.fnGetPosition(a),b.push(this.s.dt.aoData[c]);else if("undefined"!==typeof a.length){d=0;for(f=a.length;d<f;d++)a[d].nodeName?(c=this.s.dt.oInstance.fnGetPosition(a[d]),b.push(this.s.dt.aoData[c])):"number"===typeof a[d]?b.push(this.s.dt.aoData[a[d]]):b.push(a[d])}else b.push(a);return b},_fnTextConfig:function(a,b){var c=this;null!==b.fnInit&&b.fnInit.call(this,a,b);""!==
b.sToolTip&&(a.title=b.sToolTip);e(a).hover(function(){b.fnMouseover!==null&&b.fnMouseover.call(this,a,b,null)},function(){b.fnMouseout!==null&&b.fnMouseout.call(this,a,b,null)});null!==b.fnSelect&&TableTools._fnEventListen(this,"select",function(d){b.fnSelect.call(c,a,b,d)});e(a).click(function(d){b.fnClick!==null&&b.fnClick.call(c,a,b,null,d);b.fnComplete!==null&&b.fnComplete.call(c,a,b,null,null);c._fnCollectionHide(a,b)})},_fnFlashConfig:function(a,b){var c=this,d=new ZeroClipboard_TableTools.Client;
null!==b.fnInit&&b.fnInit.call(this,a,b);d.setHandCursor(!0);"flash_save"==b.sAction?(d.setAction("save"),d.setCharSet("utf16le"==b.sCharSet?"UTF16LE":"UTF8"),d.setBomInc(b.bBomInc),d.setFileName(b.sFileName.replace("*",this.fnGetTitle(b)))):"flash_pdf"==b.sAction?(d.setAction("pdf"),d.setFileName(b.sFileName.replace("*",this.fnGetTitle(b)))):d.setAction("copy");d.addEventListener("mouseOver",function(){b.fnMouseover!==null&&b.fnMouseover.call(c,a,b,d)});d.addEventListener("mouseOut",function(){b.fnMouseout!==
null&&b.fnMouseout.call(c,a,b,d)});d.addEventListener("mouseDown",function(){b.fnClick!==null&&b.fnClick.call(c,a,b,d)});d.addEventListener("complete",function(f,e){b.fnComplete!==null&&b.fnComplete.call(c,a,b,d,e);c._fnCollectionHide(a,b)});this._fnFlashGlue(d,a,b.sToolTip)},_fnFlashGlue:function(a,b,c){var d=this,f=b.getAttribute("id");g.getElementById(f)?a.glue(b,c):setTimeout(function(){d._fnFlashGlue(a,b,c)},100)},_fnFlashSetText:function(a,b){var c=this._fnChunkData(b,8192);a.clearText();for(var d=
0,f=c.length;d<f;d++)a.appendText(c[d])},_fnColumnTargets:function(a){var b=[],c=this.s.dt;if("object"==typeof a){i=0;for(iLen=c.aoColumns.length;i<iLen;i++)b.push(!1);i=0;for(iLen=a.length;i<iLen;i++)b[a[i]]=!0}else if("visible"==a){i=0;for(iLen=c.aoColumns.length;i<iLen;i++)b.push(c.aoColumns[i].bVisible?!0:!1)}else if("hidden"==a){i=0;for(iLen=c.aoColumns.length;i<iLen;i++)b.push(c.aoColumns[i].bVisible?!1:!0)}else if("sortable"==a){i=0;for(iLen=c.aoColumns.length;i<iLen;i++)b.push(c.aoColumns[i].bSortable?
!0:!1)}else{i=0;for(iLen=c.aoColumns.length;i<iLen;i++)b.push(!0)}return b},_fnNewline:function(a){return"auto"==a.sNewLine?navigator.userAgent.match(/Windows/)?"\r\n":"\n":a.sNewLine},_fnGetDataTablesData:function(a){var b,c,d,f,j,g=[],h="",k=this.s.dt,o,l=RegExp(a.sFieldBoundary,"g"),n=this._fnColumnTargets(a.mColumns);d="undefined"!=typeof a.bSelectedOnly?a.bSelectedOnly:!1;if(a.bHeader){j=[];b=0;for(c=k.aoColumns.length;b<c;b++)n[b]&&(h=k.aoColumns[b].sTitle.replace(/\n/g," ").replace(/<.*?>/g,
"").replace(/^\s+|\s+$/g,""),h=this._fnHtmlDecode(h),j.push(this._fnBoundData(h,a.sFieldBoundary,l)));g.push(j.join(a.sFieldSeperator))}var p=k.aiDisplay;f=this.fnGetSelected();if("none"!==this.s.select.type&&d&&0!==f.length){p=[];b=0;for(c=f.length;b<c;b++)p.push(k.oInstance.fnGetPosition(f[b]))}d=0;for(f=p.length;d<f;d++){o=k.aoData[p[d]].nTr;j=[];b=0;for(c=k.aoColumns.length;b<c;b++)n[b]&&(h=k.oApi._fnGetCellData(k,p[d],b,"display"),a.fnCellRender?h=a.fnCellRender(h,b,o,p[d])+"":"string"==typeof h?
(h=h.replace(/\n/g," "),h=h.replace(/<img.*?\s+alt\s*=\s*(?:"([^"]+)"|'([^']+)'|([^\s>]+)).*?>/gi,"$1$2$3"),h=h.replace(/<.*?>/g,"")):h+="",h=h.replace(/^\s+/,"").replace(/\s+$/,""),h=this._fnHtmlDecode(h),j.push(this._fnBoundData(h,a.sFieldBoundary,l)));g.push(j.join(a.sFieldSeperator));a.bOpenRows&&(b=e.grep(k.aoOpenRows,function(a){return a.nParent===o}),1===b.length&&(h=this._fnBoundData(e("td",b[0].nTr).html(),a.sFieldBoundary,l),g.push(h)))}if(a.bFooter&&null!==k.nTFoot){j=[];b=0;for(c=k.aoColumns.length;b<
c;b++)n[b]&&null!==k.aoColumns[b].nTf&&(h=k.aoColumns[b].nTf.innerHTML.replace(/\n/g," ").replace(/<.*?>/g,""),h=this._fnHtmlDecode(h),j.push(this._fnBoundData(h,a.sFieldBoundary,l)));g.push(j.join(a.sFieldSeperator))}return _sLastData=g.join(this._fnNewline(a))},_fnBoundData:function(a,b,c){return""===b?a:b+a.replace(c,b+b)+b},_fnChunkData:function(a,b){for(var c=[],d=a.length,f=0;f<d;f+=b)f+b<d?c.push(a.substring(f,f+b)):c.push(a.substring(f,d));return c},_fnHtmlDecode:function(a){if(-1===a.indexOf("&"))return a;
var b=g.createElement("div");return a.replace(/&([^\s]*);/g,function(a,d){if("#"===a.substr(1,1))return String.fromCharCode(Number(d.substr(1)));b.innerHTML=a;return b.childNodes[0].nodeValue})},_fnPrintStart:function(a){var b=this,c=this.s.dt;this._fnPrintHideNodes(c.nTable);this.s.print.saveStart=c._iDisplayStart;this.s.print.saveLength=c._iDisplayLength;a.bShowAll&&(c._iDisplayStart=0,c._iDisplayLength=-1,c.oApi._fnCalculateEnd(c),c.oApi._fnDraw(c));if(""!==c.oScroll.sX||""!==c.oScroll.sY)this._fnPrintScrollStart(c),
e(this.s.dt.nTable).bind("draw.DTTT_Print",function(){b._fnPrintScrollStart(c)});var d=c.aanFeatures,f;for(f in d)if("i"!=f&&"t"!=f&&1==f.length)for(var j=0,m=d[f].length;j<m;j++)this.dom.print.hidden.push({node:d[f][j],display:"block"}),d[f][j].style.display="none";e(g.body).addClass(this.classes.print.body);""!==a.sInfo&&this.fnInfo(a.sInfo,3E3);a.sMessage&&(this.dom.print.message=g.createElement("div"),this.dom.print.message.className=this.classes.print.message,this.dom.print.message.innerHTML=
a.sMessage,g.body.insertBefore(this.dom.print.message,g.body.childNodes[0]));this.s.print.saveScroll=e(n).scrollTop();n.scrollTo(0,0);e(g).bind("keydown.DTTT",function(a){if(a.keyCode==27){a.preventDefault();b._fnPrintEnd.call(b,a)}})},_fnPrintEnd:function(){var a=this.s.dt,b=this.s.print,c=this.dom.print;this._fnPrintShowNodes();if(""!==a.oScroll.sX||""!==a.oScroll.sY)e(this.s.dt.nTable).unbind("draw.DTTT_Print"),this._fnPrintScrollEnd();n.scrollTo(0,b.saveScroll);null!==c.message&&(g.body.removeChild(c.message),
c.message=null);e(g.body).removeClass("DTTT_Print");a._iDisplayStart=b.saveStart;a._iDisplayLength=b.saveLength;a.oApi._fnCalculateEnd(a);a.oApi._fnDraw(a);e(g).unbind("keydown.DTTT")},_fnPrintScrollStart:function(){var a=this.s.dt;a.nScrollHead.getElementsByTagName("div")[0].getElementsByTagName("table");var b=a.nTable.parentNode,c=a.nTable.getElementsByTagName("thead");0<c.length&&a.nTable.removeChild(c[0]);null!==a.nTFoot&&(c=a.nTable.getElementsByTagName("tfoot"),0<c.length&&a.nTable.removeChild(c[0]));
c=a.nTHead.cloneNode(!0);a.nTable.insertBefore(c,a.nTable.childNodes[0]);null!==a.nTFoot&&(c=a.nTFoot.cloneNode(!0),a.nTable.insertBefore(c,a.nTable.childNodes[1]));""!==a.oScroll.sX&&(a.nTable.style.width=e(a.nTable).outerWidth()+"px",b.style.width=e(a.nTable).outerWidth()+"px",b.style.overflow="visible");""!==a.oScroll.sY&&(b.style.height=e(a.nTable).outerHeight()+"px",b.style.overflow="visible")},_fnPrintScrollEnd:function(){var a=this.s.dt,b=a.nTable.parentNode;""!==a.oScroll.sX&&(b.style.width=
a.oApi._fnStringToCss(a.oScroll.sX),b.style.overflow="auto");""!==a.oScroll.sY&&(b.style.height=a.oApi._fnStringToCss(a.oScroll.sY),b.style.overflow="auto")},_fnPrintShowNodes:function(){for(var a=this.dom.print.hidden,b=0,c=a.length;b<c;b++)a[b].node.style.display=a[b].display;a.splice(0,a.length)},_fnPrintHideNodes:function(a){for(var b=this.dom.print.hidden,c=a.parentNode,d=c.childNodes,f=0,g=d.length;f<g;f++)if(d[f]!=a&&1==d[f].nodeType){var m=e(d[f]).css("display");"none"!=m&&(b.push({node:d[f],
display:m}),d[f].style.display="none")}"BODY"!=c.nodeName&&this._fnPrintHideNodes(c)}};TableTools._aInstances=[];TableTools._aListeners=[];TableTools.fnGetMasters=function(){for(var a=[],b=0,c=TableTools._aInstances.length;b<c;b++)TableTools._aInstances[b].s.master&&a.push(TableTools._aInstances[b]);return a};TableTools.fnGetInstance=function(a){"object"!=typeof a&&(a=g.getElementById(a));for(var b=0,c=TableTools._aInstances.length;b<c;b++)if(TableTools._aInstances[b].s.master&&TableTools._aInstances[b].dom.table==
a)return TableTools._aInstances[b];return null};TableTools._fnEventListen=function(a,b,c){TableTools._aListeners.push({that:a,type:b,fn:c})};TableTools._fnEventDispatch=function(a,b,c,d){for(var f=TableTools._aListeners,e=0,g=f.length;e<g;e++)a.dom.table==f[e].that.dom.table&&f[e].type==b&&f[e].fn(c,d)};TableTools.buttonBase={sAction:"text",sTag:"default",sLinerTag:"default",sButtonClass:"DTTT_button_text",sButtonText:"Button text",sTitle:"",sToolTip:"",sCharSet:"utf8",bBomInc:!1,sFileName:"*.csv",
sFieldBoundary:"",sFieldSeperator:"\t",sNewLine:"auto",mColumns:"all",bHeader:!0,bFooter:!0,bOpenRows:!1,bSelectedOnly:!1,fnMouseover:null,fnMouseout:null,fnClick:null,fnSelect:null,fnComplete:null,fnInit:null,fnCellRender:null};TableTools.BUTTONS={csv:e.extend({},TableTools.buttonBase,{sAction:"flash_save",sButtonClass:"DTTT_button_csv",sButtonText:"CSV",sFieldBoundary:'"',sFieldSeperator:",",fnClick:function(a,b,c){this.fnSetText(c,this.fnGetTableData(b))}}),xls:e.extend({},TableTools.buttonBase,
{sAction:"flash_save",sCharSet:"utf16le",bBomInc:!0,sButtonClass:"DTTT_button_xls",sButtonText:"Excel",fnClick:function(a,b,c){this.fnSetText(c,this.fnGetTableData(b))}}),copy:e.extend({},TableTools.buttonBase,{sAction:"flash_copy",sButtonClass:"DTTT_button_copy",sButtonText:"Copy",fnClick:function(a,b,c){this.fnSetText(c,this.fnGetTableData(b))},fnComplete:function(a,b,c,d){a=d.split("\n").length;a=null===this.s.dt.nTFoot?a-1:a-2;this.fnInfo("<h6>Table copied</h6><p>Copied "+a+" row"+(1==a?"":"s")+
" to the clipboard.</p>",1500)}}),pdf:e.extend({},TableTools.buttonBase,{sAction:"flash_pdf",sNewLine:"\n",sFileName:"*.pdf",sButtonClass:"DTTT_button_pdf",sButtonText:"PDF",sPdfOrientation:"portrait",sPdfSize:"A4",sPdfMessage:"",fnClick:function(a,b,c){this.fnSetText(c,"title:"+this.fnGetTitle(b)+"\nmessage:"+b.sPdfMessage+"\ncolWidth:"+this.fnCalcColRatios(b)+"\norientation:"+b.sPdfOrientation+"\nsize:"+b.sPdfSize+"\n--/TableToolsOpts--\n"+this.fnGetTableData(b))}}),print:e.extend({},TableTools.buttonBase,
{sInfo:"<h6>Print view</h6><p>Please use your browser's print function to print this table. Press escape when finished.",sMessage:null,bShowAll:!0,sToolTip:"View print view",sButtonClass:"DTTT_button_print",sButtonText:"Print",fnClick:function(a,b){this.fnPrint(!0,b)}}),text:e.extend({},TableTools.buttonBase),select:e.extend({},TableTools.buttonBase,{sButtonText:"Select button",fnSelect:function(a){0!==this.fnGetSelected().length?e(a).removeClass(this.classes.buttons.disabled):e(a).addClass(this.classes.buttons.disabled)},
fnInit:function(a){e(a).addClass(this.classes.buttons.disabled)}}),select_single:e.extend({},TableTools.buttonBase,{sButtonText:"Select button",fnSelect:function(a){1==this.fnGetSelected().length?e(a).removeClass(this.classes.buttons.disabled):e(a).addClass(this.classes.buttons.disabled)},fnInit:function(a){e(a).addClass(this.classes.buttons.disabled)}}),select_all:e.extend({},TableTools.buttonBase,{sButtonText:"Select all",fnClick:function(){this.fnSelectAll()},fnSelect:function(a){this.fnGetSelected().length==
this.s.dt.fnRecordsDisplay()?e(a).addClass(this.classes.buttons.disabled):e(a).removeClass(this.classes.buttons.disabled)}}),select_none:e.extend({},TableTools.buttonBase,{sButtonText:"Deselect all",fnClick:function(){this.fnSelectNone()},fnSelect:function(a){0!==this.fnGetSelected().length?e(a).removeClass(this.classes.buttons.disabled):e(a).addClass(this.classes.buttons.disabled)},fnInit:function(a){e(a).addClass(this.classes.buttons.disabled)}}),ajax:e.extend({},TableTools.buttonBase,{sAjaxUrl:"/xhr.php",
sButtonText:"Ajax button",fnClick:function(a,b){var c=this.fnGetTableData(b);e.ajax({url:b.sAjaxUrl,data:[{name:"tableData",value:c}],success:b.fnAjaxComplete,dataType:"json",type:"POST",cache:!1,error:function(){alert("Error detected when sending table data to server")}})},fnAjaxComplete:function(){alert("Ajax complete")}}),div:e.extend({},TableTools.buttonBase,{sAction:"div",sTag:"div",sButtonClass:"DTTT_nonbutton",sButtonText:"Text button"}),collection:e.extend({},TableTools.buttonBase,{sAction:"collection",
sButtonClass:"DTTT_button_collection",sButtonText:"Collection",fnClick:function(a,b){this._fnCollectionShow(a,b)}})};TableTools.classes={container:"DTTT_container",buttons:{normal:"DTTT_button",disabled:"DTTT_disabled"},collection:{container:"DTTT_collection",background:"DTTT_collection_background",buttons:{normal:"DTTT_button",disabled:"DTTT_disabled"}},select:{table:"DTTT_selectable",row:"DTTT_selected"},print:{body:"DTTT_Print",info:"DTTT_print_info",message:"DTTT_PrintMessage"}};TableTools.classes_themeroller=
{container:"DTTT_container ui-buttonset ui-buttonset-multi",buttons:{normal:"DTTT_button ui-button ui-state-default"},collection:{container:"DTTT_collection ui-buttonset ui-buttonset-multi"}};TableTools.DEFAULTS={sSwfPath:"media/swf/copy_csv_xls_pdf.swf",sRowSelect:"none",sSelectedClass:null,fnPreRowSelect:null,fnRowSelected:null,fnRowDeselected:null,aButtons:["copy","csv","xls","pdf","print"],oTags:{container:"div",button:"a",liner:"span",collection:{container:"div",button:"a",liner:"span"}}};TableTools.prototype.CLASS=
"TableTools";TableTools.VERSION="2.1.5";TableTools.prototype.VERSION=TableTools.VERSION;"function"==typeof e.fn.dataTable&&"function"==typeof e.fn.dataTableExt.fnVersionCheck&&e.fn.dataTableExt.fnVersionCheck("1.9.0")?e.fn.dataTableExt.aoFeatures.push({fnInit:function(a){a=new TableTools(a.oInstance,"undefined"!=typeof a.oInit.oTableTools?a.oInit.oTableTools:{});TableTools._aInstances.push(a);return a.dom.container},cFeature:"T",sFeature:"TableTools"}):alert("Warning: TableTools 2 requires DataTables 1.9.0 or newer - www.datatables.net/download");
e.fn.DataTable.TableTools=TableTools})(jQuery,window,document);


/**
* Table tools button plugin to create downloads from applicant data
* Copied form: http://datatables.net/extras/tabletools/plug-ins
**/
TableTools.BUTTONS.send_messages = {};
$.extend( true, TableTools.BUTTONS.send_messages, TableTools.buttonBase);
TableTools.BUTTONS.send_messages.fnClick =  function( nButton, oConfig ) {
  var tableTools = this;
  var dialog = $('<div>');
  var obj = new FormObject();
  var field = obj.newField({name: 'legend', value: 'Send Message'});

  var applicantIds = [];
  var selected = tableTools.fnGetSelectedData();
  var toList = [];
  for(var i = 0; i < selected.length; i++){
    applicantIds.push(selected[i].id);
    toList.push(selected[i].fullName);
  }
  var element = field.newElement('Plaintext', 'to');
  element.label = 'To';
  if(toList.length < 10){
    element.value = toList.join(', ');
  } else {
    element.value = toList.length + ' recipients';
  }
  var element = field.newElement('TextInput', 'subject');
  element.label = 'Subject';
  element.required = true;

  var body = field.newElement('Textarea', 'body');
  body.label = 'Body';
  body.required = true;

  var formObject = new Form().create(obj);
  var form = $('form',formObject);
  form.append($('<button type="submit" name="submit">').html('Send').button({
    icons: {
      primary: 'ui-icon-mail-closed'
    }
  }));

  form.append($('<input>').attr('name', 'applicantIds').attr('type', 'hidden').val(applicantIds));
  $('input', form).css('width', '250px');
  $('textarea', form).css('width', '250px').css('height', '180px');
  form.attr( 'action', oConfig.sUrl );
  form.on('submit',function(e){
    $('p.message', this).remove();
    var subject = $('input[name="subject"]', this).val();
    var body = $('textarea[name="body"]', this).val();
    var error = false;
    if(subject.length == 0){
      $('input[name="subject"]', this).parent().append($('<p>').addClass('message').html('this element is Required and you left it blank.'));
      error = true;
    }
    if(body.length == 0){
      $('textarea[name="body"]', this).parent().append($('<p>').addClass('message').html('this element is Required and you left it blank.'));
      error = true;
    }
    if(!error){
    var overlay = $('<div>').attr('id', 'downloadoverlay');
      overlay.dialog({
        height: 90,
        modal: true,
        autoOpen: true,
        open: function(event, ui){
          $(".ui-dialog-titlebar", ui.dialog).hide();
          var label = $('<div>').addClass('label').html('Sending Email...').css('float', 'left').css('margin','10px 5px');
          var progressbar = $('<div>').addClass('progress').append(label);
          overlay.append(progressbar);
          progressbar.progressbar({
            value: false
          });
          dialog.dialog("destroy").remove();
          $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: form.serialize(),
            success: function(){
              overlay.dialog("destroy").remove();
            }
          });

        }
      });
    }
    e.preventDefault();
    return false;
  });//end submit
  dialog.append(form);
  dialog.dialog({
    modal: true,
    autoOpen: true,
    width: 500
  });
};

/**
* Table tools button plugin to create downloads from applicant data
**/
TableTools.BUTTONS.download_applicants = {};
$.extend( true, TableTools.BUTTONS.download_applicants, TableTools.buttonBase);
TableTools.BUTTONS.download_applicants.fnClick =  function( nButton, oConfig ) {
  var tableTools = this;
  var dialog = $('<div>');
  var obj = new FormObject();
  var field = obj.newField({name: 'legend', value: 'Download Options'});

  var type = field.newElement('SelectList', 'type');
  type.label = 'Download Type';
  type.required = true;
  var downloadTypes = [
    {action: 'downloadXls', title: 'Excel'},
    {action: 'downloadXml', title: 'XML'},
    {action: 'downloadPdfArchive', title: 'PDF'},
    {action: 'downloadJson', title: 'JSON'},
  ];
  for(var i = 0; i < downloadTypes.length; i++){
    if(oConfig[downloadTypes[i].action]){
      type.addItem(downloadTypes[i].title, oConfig.basepath + '/' + downloadTypes[i].action);
    }
  }

  var template = field.newElement('SelectList', 'pdftemplate');
  template.label = 'PDF Type';
  template.required = true;
  var templates = [];
  templates.push({title: 'Portrait', id: 'portrait'});
  templates.push({title: 'Landscape', id: 'landscape'});
  templates = templates.concat(oConfig.templates);
  for(var i = 0; i < templates.length; i++){
    template.addItem(templates[i]["title"], templates[i]["id"]);
  }

  var formObject = new Form().create(obj);
  var form = $('form',formObject);
  form.append($('<button type="submit" name="submit">').html('Download').button({
    icons: {
      primary: 'ui-icon-mail-closed'
    }
  }));
  $('select[name=pdftemplate]', form).parent().parent().hide();
  $('select[name=type]', form).on('change', function(){
    if($('option:selected', this).text() == 'PDF'){
      $('select[name=pdftemplate]', form).parent().parent().show();
    } else {
      $('select[name=pdftemplate]', form).parent().parent().hide();
    }
  });
  form.on('submit',function(e){
    var overlay = $('<div>').attr('id', 'downloadoverlay');
    overlay.dialog({
      height: 90,
      modal: true,
      autoOpen: true,
      open: function(event, ui){
        $(".ui-dialog-titlebar", ui.dialog).hide();
        var label = $('<div>').addClass('label').html('Downloading...').css('float', 'left').css('margin','10px 5px');
        var progressbar = $('<div>').addClass('progress').append(label);
        overlay.append(progressbar);
        progressbar.progressbar({
          value: false
        });
        dialog.dialog("destroy").remove();
        var iFrameName = "iFrame" + (new Date().getTime());
        var iFrame = $("<iframe name='" + iFrameName + "' src='about:blank' />").insertAfter('body');
        iFrame.css("display", "none");

        var iframeForm = $('<form>');
        iframeForm.attr( 'method', 'post' );
        var template = $('select[name=pdftemplate]', form).val();
        var type = $('select[name=type]', form).val();
        iframeForm.attr('action', type);
        iframeForm.append($('<input>').attr('name', 'pdftemplate').attr('type', 'hidden').val(template));

        var applicantIds = [];
        var selected = tableTools.fnGetSelectedData();
        for(var i = 0; i < selected.length; i++){
          applicantIds.push(selected[i].id);
        }
        iframeForm.append($('<input>').attr('name', 'applicantIds').attr('type', 'hidden').val(applicantIds));
        iframeForm.bind('submit', function(){
          var cookieName = 'fileDownload';
          var check = function(){
            if($.cookie(cookieName) == 'complete'){
              $.cookie(cookieName, 'false', {expires: 1, path: '/' });
              $('#downloadoverlay').dialog('destroy').remove();
              iFrame.remove();
            } else {
              setTimeout(check, 500);
            }
          };
          setTimeout(check, 500);
          return true;
        });
        iFrame.append(iframeForm);
        iframeForm.submit();
      }
    });
    e.preventDefault();
    return false;
  });//end submit
  dialog.append(form);
  dialog.dialog({
    modal: true,
    autoOpen: true,
    width: 500
  });
};