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
  var grid = $('#grid-table').dataTable( {
    sScrollY: "500",
    sScrollX: "95%",
    sPaginationType: 'full_numbers',
    aaSorting: [[ 1, "asc" ]],
    fnDrawCallback: Grid.processDraw,
    aaData: data,
    aoColumns: columns,
    bJQueryUI: true
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
        columns.push(column);
        break;
      case 'page':
        columns.push({
          sTitle: this.title,
          mData: 'elements.element' + this.name,
          mRender: Grid.formatAnswers
        });
        break;
    }
  });
  return columns;
};

Grid.prototype.loadapps = function(applicantIds, grid){
  var self = this;
  if(applicantIds.length){
    var limitedIds = applicantIds.splice(0, 100);
    $.post(self.controllerPath + '/getApplicants',{applicantIds: limitedIds, display: self.display.getObj()
    }, function(json){
      var applicants = [];
      var length = json.data.result.applicants.length;
      while (length--) {
        var applicant = new ApplicantData(json.data.result.applicants.splice(length, 1)[0]);
        applicant.elements = {};
        $.each(self.display.getApplication().listApplicationPages(), function(){
          var applicationPage = this;
          $.each(self.display.getApplication().listPageElements(applicationPage.page.id), function(){
            applicant.elements['element' + this.id] = {
              data: applicant.getAnswersForPageElement(applicationPage.page.id, this.id),
              elementClass: self.display.getApplication().getElementClassById(this.id)
            };
          });
        });
        applicants.push(applicant);
      }
      grid.fnAddData(applicants);
      grid.fnAdjustColumnSizing();
      self.loadapps(applicantIds, grid);
    });
  } else {
    //after all of the data is loaded then fix the left column in place
    new FixedColumns(grid,  {iLeftColumns: 2});
  }
};

/**
 * Format date objects
 */
Grid.formatDate = function(data, type, full){
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
 * Format page element grid data
 */
Grid.formatAnswers = function(data, type, full){
  return data.elementClass.gridData(data.data, type, full);
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
    div.html('applicant data');
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

/*
 * File:        FixedColumns.nightly.min.js
 * Version:     2.5.0.dev
 * Author:      Allan Jardine (www.sprymedia.co.uk)
 * Info:        www.datatables.net
 * 
 * Copyright 2008-2012 Allan Jardine, all rights reserved.
 *
 * This source file is free software, under either the GPL v2 license or a
 * BSD style license, available at:
 *   http://datatables.net/license_gpl2
 *   http://datatables.net/license_bsd
 * 
 * This source file is distributed in the hope that it will be useful, but 
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY 
 * or FITNESS FOR A PARTICULAR PURPOSE. See the license files for details.
 */
/*
     GPL v2 or BSD 3 point style
*/
var FixedColumns;
(function(d,t){FixedColumns=function(a,b){var c=this;if(!this instanceof FixedColumns)alert("FixedColumns warning: FixedColumns must be initialised with the 'new' keyword.");else{if(typeof b=="undefined")b={};this.s={dt:a.fnSettings(),iTableColumns:a.fnSettings().aoColumns.length,aiOuterWidths:[],aiInnerWidths:[]};this.dom={scroller:null,header:null,body:null,footer:null,grid:{wrapper:null,dt:null,left:{wrapper:null,head:null,body:null,foot:null},right:{wrapper:null,head:null,body:null,foot:null}},
clone:{left:{header:null,body:null,footer:null},right:{header:null,body:null,footer:null}}};this.s.dt.oFixedColumns=this;this.s.dt._bInitComplete?this._fnConstruct(b):this.s.dt.oApi._fnCallbackReg(this.s.dt,"aoInitComplete",function(){c._fnConstruct(b)},"FixedColumns")}};FixedColumns.prototype={fnUpdate:function(){this._fnDraw(true)},fnRedrawLayout:function(){this._fnColCalc();this._fnGridLayout();this.fnUpdate()},fnRecalculateHeight:function(a){delete a._DTTC_iHeight;a.style.height="auto"},fnSetRowHeight:function(a,
b){a.style.height=b+"px"},_fnConstruct:function(a){var b=this;if(typeof this.s.dt.oInstance.fnVersionCheck!="function"||this.s.dt.oInstance.fnVersionCheck("1.8.0")!==true)alert("FixedColumns "+FixedColumns.VERSION+" required DataTables 1.8.0 or later. Please upgrade your DataTables installation");else if(this.s.dt.oScroll.sX==="")this.s.dt.oInstance.oApi._fnLog(this.s.dt,1,"FixedColumns is not needed (no x-scrolling in DataTables enabled), so no action will be taken. Use 'FixedHeader' for column fixing when scrolling is not enabled");
else{this.s=d.extend(true,this.s,FixedColumns.defaults,a);this.dom.grid.dt=d(this.s.dt.nTable).parents("div.dataTables_scroll")[0];this.dom.scroller=d("div.dataTables_scrollBody",this.dom.grid.dt)[0];this._fnColCalc();this._fnGridSetup();d(this.dom.scroller).scroll(function(){if(b.s.iLeftColumns>0)b.dom.grid.left.liner.scrollTop=b.dom.scroller.scrollTop;if(b.s.iRightColumns>0)b.dom.grid.right.liner.scrollTop=b.dom.scroller.scrollTop});if(b.s.iLeftColumns>0){d(b.dom.grid.left.liner).scroll(function(){b.dom.scroller.scrollTop=
b.dom.grid.left.liner.scrollTop;if(b.s.iRightColumns>0)b.dom.grid.right.liner.scrollTop=b.dom.grid.left.liner.scrollTop});d(b.dom.grid.left.liner).bind("mousewheel",function(f){b.dom.scroller.scrollLeft-=f.originalEvent.wheelDeltaX/3})}if(b.s.iRightColumns>0){d(b.dom.grid.right.liner).scroll(function(){b.dom.scroller.scrollTop=b.dom.grid.right.liner.scrollTop;if(b.s.iLeftColumns>0)b.dom.grid.left.liner.scrollTop=b.dom.grid.right.liner.scrollTop});d(b.dom.grid.right.liner).bind("mousewheel",function(f){b.dom.scroller.scrollLeft-=
f.originalEvent.wheelDeltaX/3})}d(t).resize(function(){b._fnGridLayout.call(b)});var c=true;this.s.dt.aoDrawCallback=[{fn:function(){b._fnDraw.call(b,c);b._fnGridLayout(b);c=false},sName:"FixedColumns"}].concat(this.s.dt.aoDrawCallback);this._fnGridLayout();this.s.dt.oInstance.fnDraw(false)}},_fnColCalc:function(){var a=this,b=d(this.dom.grid.dt).width(),c=0,f=0;this.s.aiInnerWidths=[];d("tbody>tr:eq(0)>td, tbody>tr:eq(0)>th",this.s.dt.nTable).each(function(g){a.s.aiInnerWidths.push(d(this).width());
var e=d(this).outerWidth();a.s.aiOuterWidths.push(e);if(g<a.s.iLeftColumns)c+=e;if(a.s.iTableColumns-a.s.iRightColumns<=g)f+=e});this.s.iLeftWidth=this.s.sLeftWidth=="fixed"?c:c/b*100;this.s.iRightWidth=this.s.sRightWidth=="fixed"?f:f/b*100},_fnGridSetup:function(){var a=this._fnDTOverflow(),b;this.dom.body=this.s.dt.nTable;this.dom.header=this.s.dt.nTHead.parentNode;this.dom.header.parentNode.parentNode.style.position="relative";var c=d('<div class="DTFC_ScrollWrapper" style="position:relative; clear:both;"><div class="DTFC_LeftWrapper" style="position:absolute; top:0; left:0;"><div class="DTFC_LeftHeadWrapper" style="position:relative; top:0; left:0; overflow:hidden;"></div><div class="DTFC_LeftBodyWrapper" style="position:relative; top:0; left:0; overflow:hidden;"><div class="DTFC_LeftBodyLiner" style="position:relative; top:0; left:0; overflow-y:scroll;"></div></div><div class="DTFC_LeftFootWrapper" style="position:relative; top:0; left:0; overflow:hidden;"></div></div><div class="DTFC_RightWrapper" style="position:absolute; top:0; left:0;"><div class="DTFC_RightHeadWrapper" style="position:relative; top:0; left:0;"><div class="DTFC_RightHeadBlocker DTFC_Blocker" style="position:absolute; top:0; bottom:0;"></div></div><div class="DTFC_RightBodyWrapper" style="position:relative; top:0; left:0; overflow:hidden;"><div class="DTFC_RightBodyLiner" style="position:relative; top:0; left:0; overflow-y:scroll;"></div></div><div class="DTFC_RightFootWrapper" style="position:relative; top:0; left:0;"><div class="DTFC_RightFootBlocker DTFC_Blocker" style="position:absolute; top:0; bottom:0;"></div></div></div></div>')[0],
f=c.childNodes[0],g=c.childNodes[1];this.dom.grid.dt.parentNode.insertBefore(c,this.dom.grid.dt);c.appendChild(this.dom.grid.dt);this.dom.grid.wrapper=c;if(this.s.iLeftColumns>0){this.dom.grid.left.wrapper=f;this.dom.grid.left.head=f.childNodes[0];this.dom.grid.left.body=f.childNodes[1];this.dom.grid.left.liner=d("div.DTFC_LeftBodyLiner",c)[0];c.appendChild(f)}if(this.s.iRightColumns>0){this.dom.grid.right.wrapper=g;this.dom.grid.right.head=g.childNodes[0];this.dom.grid.right.body=g.childNodes[1];
this.dom.grid.right.liner=d("div.DTFC_RightBodyLiner",c)[0];b=d("div.DTFC_RightHeadBlocker",c)[0];b.style.width=a.bar+"px";b.style.right=-a.bar+"px";this.dom.grid.right.headBlock=b;b=d("div.DTFC_RightFootBlocker",c)[0];b.style.width=a.bar+"px";b.style.right=-a.bar+"px";this.dom.grid.right.footBlock=b;c.appendChild(g)}if(this.s.dt.nTFoot){this.dom.footer=this.s.dt.nTFoot.parentNode;if(this.s.iLeftColumns>0)this.dom.grid.left.foot=f.childNodes[2];if(this.s.iRightColumns>0)this.dom.grid.right.foot=g.childNodes[2]}},
_fnGridLayout:function(){var a=this.dom.grid,b=d(a.wrapper).width(),c=d(this.s.dt.nTable.parentNode).height(),f=d(this.s.dt.nTable.parentNode.parentNode).height(),g,e,h=this._fnDTOverflow();g=this.s.sLeftWidth=="fixed"?this.s.iLeftWidth:this.s.iLeftWidth/100*b;e=this.s.sRightWidth=="fixed"?this.s.iRightWidth:this.s.iRightWidth/100*b;if(h.x)c-=h.bar;a.wrapper.style.height=f+"px";if(this.s.iLeftColumns>0){a.left.wrapper.style.width=g+"px";a.left.wrapper.style.height=f+"px";a.left.body.style.height=
c+"px";if(a.left.foot)a.left.foot.style.top=(h.x?h.bar:0)+"px";a.left.liner.style.width=g+h.bar+"px";a.left.liner.style.height=c+"px"}if(this.s.iRightColumns>0){b=b-e;if(h.y)b-=h.bar;a.right.wrapper.style.width=e+"px";a.right.wrapper.style.left=b+"px";a.right.wrapper.style.height=f+"px";a.right.body.style.height=c+"px";if(a.right.foot)a.right.foot.style.top=(h.x?h.bar:0)+"px";a.right.liner.style.width=e+h.bar+"px";a.right.liner.style.height=c+"px";a.right.headBlock.style.display=h.x?"block":"none";
a.right.footBlock.style.display=h.x?"block":"none"}},_fnDTOverflow:function(){var a=this.s.dt.nTable,b=a.parentNode,c={x:false,y:false,bar:this.s.dt.oScroll.iBarWidth};if(a.offsetWidth>b.offsetWidth)c.x=true;if(a.offsetHeight>b.offsetHeight)c.y=true;return c},_fnDraw:function(a){this._fnCloneLeft(a);this._fnCloneRight(a);this.s.fnDrawCallback!==null&&this.s.fnDrawCallback.call(this,this.dom.clone.left,this.dom.clone.right);d(this).trigger("draw",{leftClone:this.dom.clone.left,rightClone:this.dom.clone.right})},
_fnCloneRight:function(a){if(!(this.s.iRightColumns<=0)){var b,c=[];for(b=this.s.iTableColumns-this.s.iRightColumns;b<this.s.iTableColumns;b++)c.push(b);this._fnClone(this.dom.clone.right,this.dom.grid.right,c,a)}},_fnCloneLeft:function(a){if(!(this.s.iLeftColumns<=0)){var b,c=[];for(b=0;b<this.s.iLeftColumns;b++)c.push(b);this._fnClone(this.dom.clone.left,this.dom.grid.left,c,a)}},_fnCopyLayout:function(a,b){for(var c=[],f=[],g=[],e=0,h=a.length;e<h;e++){var j=[];j.nTr=d(a[e].nTr).clone(true)[0];
for(var i=0,m=this.s.iTableColumns;i<m;i++)if(d.inArray(i,b)!==-1){var l=d.inArray(a[e][i].cell,g);if(l===-1){l=d(a[e][i].cell).clone(true)[0];f.push(l);g.push(a[e][i].cell);j.push({cell:l,unique:a[e][i].unique})}else j.push({cell:f[l],unique:a[e][i].unique})}c.push(j)}return c},_fnClone:function(a,b,c,f){var g=this,e,h,j,i,m,l,n,k,p;if(f){a.header!==null&&a.header.parentNode.removeChild(a.header);a.header=d(this.dom.header).clone(true)[0];a.header.className+=" DTFC_Cloned";a.header.style.width="100%";
b.head.appendChild(a.header);k=this._fnCopyLayout(this.s.dt.aoHeader,c);i=d(">thead",a.header);i.empty();e=0;for(h=k.length;e<h;e++)i[0].appendChild(k[e].nTr);this.s.dt.oApi._fnDrawHead(this.s.dt,k,true)}else{k=this._fnCopyLayout(this.s.dt.aoHeader,c);p=[];this.s.dt.oApi._fnDetectHeader(p,d(">thead",a.header)[0]);e=0;for(h=k.length;e<h;e++){j=0;for(i=k[e].length;j<i;j++){p[e][j].cell.className=k[e][j].cell.className;d("span.DataTables_sort_icon",p[e][j].cell).each(function(){this.className=d("span.DataTables_sort_icon",
k[e][j].cell)[0].className})}}}this._fnEqualiseHeights("thead",this.dom.header,a.header);this.s.sHeightMatch=="auto"&&d(">tbody>tr",g.dom.body).css("height","auto");if(a.body!==null){a.body.parentNode.removeChild(a.body);a.body=null}a.body=d(this.dom.body).clone(true)[0];a.body.className+=" DTFC_Cloned";a.body.style.paddingBottom=this.s.dt.oScroll.iBarWidth+"px";a.body.style.marginBottom=this.s.dt.oScroll.iBarWidth*2+"px";a.body.getAttribute("id")!==null&&a.body.removeAttribute("id");d(">thead>tr",
a.body).empty();d(">tfoot",a.body).remove();var q=d("tbody",a.body)[0];d(q).empty();if(this.s.dt.aiDisplay.length>0){h=d(">thead>tr",a.body)[0];for(n=0;n<c.length;n++){m=c[n];l=d(this.s.dt.aoColumns[m].nTh).clone(true)[0];l.innerHTML="";i=l.style;i.paddingTop="0";i.paddingBottom="0";i.borderTopWidth="0";i.borderBottomWidth="0";i.height=0;i.width=g.s.aiInnerWidths[m]+"px";h.appendChild(l)}d(">tbody>tr",g.dom.body).each(function(o){var r=this.cloneNode(false);o=g.s.dt.oFeatures.bServerSide===false?
g.s.dt.aiDisplay[g.s.dt._iDisplayStart+o]:o;for(n=0;n<c.length;n++){var s=g.s.dt.oApi._fnGetTdNodes(g.s.dt,o);m=c[n];if(s.length>0){l=d(s[m]).clone(true)[0];r.appendChild(l)}}q.appendChild(r)})}else d(">tbody>tr",g.dom.body).each(function(){l=this.cloneNode(true);l.className+=" DTFC_NoData";d("td",l).html("");q.appendChild(l)});a.body.style.width="100%";a.body.style.margin="0";a.body.style.padding="0";f&&typeof this.s.dt.oScroller!="undefined"&&b.liner.appendChild(this.s.dt.oScroller.dom.force.cloneNode(true));
b.liner.appendChild(a.body);this._fnEqualiseHeights("tbody",g.dom.body,a.body);if(this.s.dt.nTFoot!==null){if(f){a.footer!==null&&a.footer.parentNode.removeChild(a.footer);a.footer=d(this.dom.footer).clone(true)[0];a.footer.className+=" DTFC_Cloned";a.footer.style.width="100%";b.foot.appendChild(a.footer);k=this._fnCopyLayout(this.s.dt.aoFooter,c);b=d(">tfoot",a.footer);b.empty();e=0;for(h=k.length;e<h;e++)b[0].appendChild(k[e].nTr);this.s.dt.oApi._fnDrawHead(this.s.dt,k,true)}else{k=this._fnCopyLayout(this.s.dt.aoFooter,
c);b=[];this.s.dt.oApi._fnDetectHeader(b,d(">tfoot",a.footer)[0]);e=0;for(h=k.length;e<h;e++){j=0;for(i=k[e].length;j<i;j++)b[e][j].cell.className=k[e][j].cell.className}}this._fnEqualiseHeights("tfoot",this.dom.footer,a.footer)}b=this.s.dt.oApi._fnGetUniqueThs(this.s.dt,d(">thead",a.header)[0]);d(b).each(function(o){m=c[o];this.style.width=g.s.aiInnerWidths[m]+"px"});if(g.s.dt.nTFoot!==null){b=this.s.dt.oApi._fnGetUniqueThs(this.s.dt,d(">tfoot",a.footer)[0]);d(b).each(function(o){m=c[o];this.style.width=
g.s.aiInnerWidths[m]+"px"})}},_fnGetTrNodes:function(a){for(var b=[],c=0,f=a.childNodes.length;c<f;c++)a.childNodes[c].nodeName.toUpperCase()=="TR"&&b.push(a.childNodes[c]);return b},_fnEqualiseHeights:function(a,b,c){if(!(this.s.sHeightMatch=="none"&&a!=="thead"&&a!=="tfoot")){var f,g,e=b.getElementsByTagName(a)[0];c=c.getElementsByTagName(a)[0];a=d(">"+a+">tr:eq(0)",b).children(":first");a.outerHeight();a.height();e=this._fnGetTrNodes(e);b=this._fnGetTrNodes(c);c=0;for(a=b.length;c<a;c++){f=e[c].offsetHeight;
g=b[c].offsetHeight;f=g>f?g:f;if(this.s.sHeightMatch=="semiauto")e[c]._DTTC_iHeight=f;b[c].style.height=f+"px";e[c].style.height=f+"px"}}}};FixedColumns.defaults={iLeftColumns:1,iRightColumns:0,fnDrawCallback:null,sLeftWidth:"fixed",iLeftWidth:null,sRightWidth:"fixed",iRightWidth:null,sHeightMatch:"semiauto"};FixedColumns.prototype.CLASS="FixedColumns";FixedColumns.VERSION="2.5.0.dev"})(jQuery,window,document);