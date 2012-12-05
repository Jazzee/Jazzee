var PAGE_CONTAINER_TEMPLATE = "#PageTableTemplate";
var PAGE_ANSWER_TEMPLATE = "#SubTableRowTemplate";
var PAGE_ITEM_TEMPLATE = "#ColumnTableTemplate";
    
$.views.helpers({
  checkReal: function(value){
    return !(value == null || ((value+"").replace(/ /g, "").length == 0));
  }
});

$.views.converters({
  cssClass: function(value){
    return value.replace(/ /g,"_").replace(/[^a-zA-Z 0-9]+/g,'');
  },
  percent: function(value){
    return (value*100)+'%';
  }
});

Grid = new JS.Class({

  initialize: function(selector, dataUrl) {
    console.log("Creating grid...");
    // this can be a single table like '#ApplicantTable', a set of
    // tables like 'table.updatable' or all tables 'table' 
    // 
    this.tableSelector = selector;
    this.filterElem = $("#filter");

    // this.filterElem.keyup(this.doFilter.bind(this));

    if(dataUrl)
      $(document).ready(this.load.bind(this, dataUrl));
  },

  doFilter: function(){ //input, tableName){
    this.filterTable(this.filterElem, $(this.tableSelector));
  },

  filterTable: function(term, table) {

    this.dehighlight(table);
    var terms = term.val().toLowerCase().split(" ");
    var thisGrid = this;
    //for (var r = 1; r < table.rows.length; r++) {
    $(table).find("tr").each(function(key, val){
      var display = '';
      for (var i = 0; i < terms.length; i++) {
        if (val.innerHTML.replace(/<[^>]+>/g, "").toLowerCase()
          .indexOf(terms[i]) < 0) {
          display = 'none';
        } else {
          if (terms[i].length) thisGrid.highlight(terms[i], val);
        }
        val.style.display = display;
      }
    });
  },

  /*
	 * Transform back each
	 * <span>preText <span class="highlighted">term</span> postText</span>
	 * into its original
	 * preText term postText
	 */
  dehighlight: function(container) {
    console.log("dehighlighting: "+container);
    $(container).find(".highlighted").each(function(key, high){
      $(high).removeClass("highlighted");
    });
  },

  getTextNodesIn: function(el) {
    return $(el).find(":not(iframe)").andSelf().contents().filter(function() {
      return this.nodeType == 3;
    });
  },

  /*
	 * Create a
	 * <span>preText <span class="highlighted">term</span> postText</span>
	 * around each search term
	 */
  highlight: function(term, container) {

    this.getTextNodesIn($(container)).each(function(key, textNode){
      var data = textNode.data;
      var data_low = data.toLowerCase();
      if (data_low.indexOf(term) >= 0) {
        //term found!
        $(textNode.parentNode).addClass("highlighted");
      }			
    });
  },

  /**
	 * @param template   the id of the header template
	 * @param items      the column headers
	 * @replace replace  boolean; true replaces the current table data, false does not
	 */
  addHeader: function(template, items, replace) {

    if(replace){
    // not implemented yet
    }

    // use 'html' instead of 'append' if you want to replace the content
    $(this.tableSelector).append($(template).render(items));
  },

  addRow: function(rowTemplate, colDefs, key, row) {

    // render single template for whole row
    $(this.tableSelector+">tbody").append($(rowTemplate).render(row));
  },

  load: function(dataUrl){
    console.log("Loading grid..");
	    
    var doIt = this.populate.bind(this);
    $.getJSON(dataUrl, doIt);
  },

  /**
	 * The html template for the table header
	 */
  getHeaderTemplate: function(){
    return "#DataHeaderTemplate";
  },

  populate: function(jsonResponse) {

    // subclass can specify which data they need from the response
    var data = this.getData(jsonResponse);

    // we create a map of field name to data template
    this.colDefs = new JS.OrderedHash();
    // this is the default column template
    this.colDefs.setDefault(PAGE_ITEM_TEMPLATE);

    // this fills our map 
    this.getColumnDefinitions(data, this.colDefs);
    //	    console.log("COLDEFS:"+this.colDefs.keys()+", email: "+colDefs.get("Email"));
    this.addHeader(this.getHeaderTemplate(), {
      names: this.colDefs.keys()
      });

    var newRow = this.addRow.bind(this, "#RowTemplate", this.colDefs);
    $.each(data, newRow);

    $(this.tableSelector).tablesorter({
      selectorHeaders: '> thead > tr > th'
    });
    // not bound to this grid, so we don't have access to the other 
    // grid data.
    // var activate = this.highlightHover; 
    // does not transmit the event source reliably
    // var activate = this.highlightHover2.bind(this); // bound to this grid

    //	    console.log("new highlight hover");
    var thisGrid = this;
    var activate = function(e){
      if((e.type == 'mouseover')){// && (e.shiftKey)) {
        if(e.shiftKey) {
        //	$(this).parent().addClass("hover");
        }else{
          $(this).parent().addClass("shadow-hover");
        }
        $(thisGrid.tableSelector).find("colgroup").eq($(this).index()).addClass("hover");
      }else {
        //$(this).parent().removeClass("hover");
        $(this).parent().removeClass("shadow-hover");
        $(thisGrid.tableSelector).find("colgroup").eq($(this).index()).removeClass("hover");
		    
      }
		
    }
    // $("table").delegate('td','mouseover mouseleave', activate);  
    $(this.tableSelector).delegate('td','mouseover mouseleave', activate);  

    var focus = function(e){
      console.log("IN DEFAULT Grid click");
      if($(this).parent().hasClass("focus")){
        $(this).parent().removeClass("focus");
        $(this).parent().removeClass("hover");
      }else{
        $(this).parent().addClass("hover");

        if(!$(e.target).id){
          $(e.target).attr("id", $.uidGen("grid-"));
        }
        setTimeout( function(){
          console.log("scrolling to #"+$(e.target).id);
          //			    $(e.target).scrollTo();
          $.scrollTo("#"+$(e.target).id, 800);
        }, 3000);

        //   if(e.shiftKey)
        $(this).parent().addClass("focus");

      }
    }

    $(this.tableSelector).delegate('td','click', focus);  


    var helper = this.help.bind(this);
    $(this.tableSelector).delegate('td','click mouseover mouseleave', helper);  

	    
    console.log("created tablesorter");
  },

  help: function(e){
    var trigger = $(e.target);
    var isShift = e.shiftKey;

    var msg = "";
    if (e.type == 'mouseover') {

    }

	    

  },

  highlightHover: function(e) {
    //	    console.log("new highlight hover");
    if (e.type == 'mouseover') {
      $(this).parent().addClass("hover");
      $("colgroup").eq($(this).index()).addClass("hover");
    }
    else {
      $(this).parent().removeClass("hover");
      $("colgroup").eq($(this).index()).removeClass("hover");
    }
		
  },

  append: function(jsonResponse) {
    if(!this.colDefs){
      this.populate(jsonResponse);
    } else {
      var data = this.getData(jsonResponse);
      var newRow = this.addRow.bind(this, "#RowTemplate", this.colDefs);
      $.each(data, newRow);

      //	    console.log("inserting fragment: "+html+" into "+this.tableSelector);
      // append the "ajax'd" data to the table body 
      //$(this.tableSelector+" tbody").append("<tr><td>test!</td></tr>") //); 
      // let the plugin know that we made a update 
      $(this.tableSelector).trigger("update"); 
    // set sorting column and direction, this will sort on the first and third column 
    //    var sorting = [[2,1],[0,0]]; 
    // sort on the first column 
    //$(this.tableSelector).trigger("sorton",[sorting]); 
    }
  },

  addAppender: function(jqElem, url){
    var doUpdate = this.append.bind(this);
    jqElem.click(function() { 
      $.getJSON(url, doUpdate); 
      return false; 
    });
  }, 

  dump: function(data){

    for(y in data){
      console.log("["+y+"] => "+data[y]);
      for(z in data[y])
        console.log(" ==["+z+"]==> "+data[y][z]);
    }

  },

  /**
	 *  Extract data from the json response. This default method
	 *  just returns the response.
	 */
  getData: function(jsonResponse){
    return jsonResponse; //jsonResponse["applicants"];
  },

  getColumnDefinitions: function(data, hash) {

    // the default template
    var def = hash.getDefault();

    for(x in data)
      hash.store(data[x], def);

  }
});

var myTextExtraction = function(node)  
{  
  // extract data from markup and return it  
  //		return node.childNodes[0].childNodes[0].innerHTML; 
  var txt = node.innerText;
  //		console.log("["+txt+"]");
  return txt; 
} 

SubTableGrid = new JS.Class(Grid, {
  initialize: function(){
    // JS.Class apparently automatically passed the args:
    // http://jsclass.jcoglan.com/classes.html
    this.callSuper(); 

    this.activate();
  },

  activate: function(){

    $(this.tableSelector).tablesorter({
      debug: false,
      //selectorHeaders: '> thead > tr > th',
      selectorHeaders: '.sub-table-header-row > th',
      textExtraction: myTextExtraction
    });

    var thisGrid = this;
    var activateSub = function(e){

    }
    $(this.tableSelector).delegate('th,td','mouseover mouseleave', activateSub);  
    //	    $(this.tableSelector).delegate('td','mouseover mouseleave', activateSub);  
    var thisTable = $(this.tableSelector);
    var focus = function(e){
      console.log("SUBTABLE: click!"+e+", elem:"+e.target+", classes: "+e.target.className);
      // deselect any un-sorted-by colgroups
      try{
        $.each(thisTable.find("colgroup"), function(cidx, colgroup){
          // look up the column header based on the name of the
          // colgroup
          $.each(thisTable.find("th.header-"+colgroup.className),
            function(hidx, header){
              if($(header).hasClass("tablesorter-headerSortUp")
                || $(header).hasClass("tablesorter-headerSortDown")){

              }else{
                $(colgroup).removeClass("hover-subtable");
              }

            });
        });
      }catch(noreset){
        console.log("unable to reset colgroup backgrounds: "+noreset);
      }
      // update the colgroup to show the sorted columns
      if($(e.target.parentNode).hasClass("tablesorter-headerSortUp")
        || $(e.target.parentNode).hasClass("tablesorter-headerSortDown")

        ){// && (e.shiftKey)) {
        //$(this).parent().addClass("hover-subtable");
        thisTable.find("colgroup").eq($(this).index()).addClass("hover-subtable");
      }else {
        //			$(this).parent().removeClass("hover-subtable");
        thisTable.find("colgroup").eq($(this).index()).removeClass("hover-subtable");

      }


      e.stopPropagation();

    }
	    
    $(this.tableSelector).delegate('th','click', focus);  


  }
});

ApplicantGrid = new JS.Class(Grid, {
	
  initialize: function(){
    // JS.Class apparently automatically passed the args:
    // http://jsclass.jcoglan.com/classes.html
    this.callSuper(); 
    console.log("finished call to super");
    this.filterElem.keyup(this.doFilter.bind(this));

  //	 var tip =   $(this.tableSelector).tipTip({defaultPosition: "top",
  //			maxWidth: "auto", edgeOffset: 10
  //			,			content: "hold shift to expand rows"
  //});


  },
	
  /**
	 *  Extract data from the json response
	 */
  getData: function(jsonResponse){
    return jsonResponse["applicants"];
  },

  getColumnDefinitions: function(data, hash) {

    // the default template
    var def = hash.getDefault();
    hash.store("Applicant", def);
    hash.store("Last Name", def);
    hash.store("First Name", def);
    hash.store("Email Address", def);
    hash.store("Last Update", '#DateTemplate');
    hash.store("Progress", def);
    hash.store("Last Login", def);
    hash.store("Account Created", def);
    
    // ISSUE: we loop through all the data in orde to find all columns.
    // this is obviously expensive and probably necessary
    $.each(data, function(key, row){

      $.each(row["pages"], function(key, page){
        if(!hash.hasKey(page.title)){
          hash.store(page.title, def);
        }
      });
      return false;
    });
  },

  addRow: function(rowTemplate, colDefs, key, row) {
    try{

      // break up row into multiple templates: row, then page, then element
      var string = $(rowTemplate).render(row);
      // console.log("ROW: "+string);
		
      var pages = "";
      var rowFrag = $(string);
      $.each(row["pages"], function(key, page){
        var pageFrag = $($(PAGE_CONTAINER_TEMPLATE).render(page));
        // var allAnswers = pageFrag.find("div.all-answers");
        var eList = pageFrag.find(".element-list");
        //console.log("found #"+eList.length+" element lists");
        if(page.title && colDefs.get(page.title)){
          PAGE_ITEM_TEMPLATE = colDefs.get(page.title);
        }
        var answerCount = 0;
        $.each(page["answers"], function(key2, answer){
          answerCount++;
          var answerFrag = null;
          try{
            answerFrag = $($(PAGE_ANSWER_TEMPLATE).render(page));
            //    console.log("answer: "+answerFrag.html());
            eList.append(answerFrag);
            answerFrag.addClass("answer-item");
          }catch(noa){
          // ignore
          }
          $.each(answer["elements"], function(key3, element){
            element.answerNumber = answerCount;
            var elem = $(PAGE_ITEM_TEMPLATE).render(element);

            if(answerFrag != null){
              answerFrag.append(elem);

            }else{
              //console.log("rendered element: "+elem);
              eList.append(elem);
            }
          });
				
        });

        pages += pageFrag[0].outerHTML; //pageFrag.html();
      });
		
      var found = rowFrag.find("td.pages-placeholder");
      //		console.log("found placeholder: "+found);
      var realColumns = $(pages);
      found.replaceWith(realColumns);
      $(this.tableSelector+">tbody").append(rowFrag);

      $.each(realColumns.find(".answer-table"), function(idx, subTable){

        // add class to the all-answers parent field so we can
        // style this table differently to the other grid tables
        $(subTable.parentNode).addClass("withSubTable");
        if(subTable.getAttribute("id") == null){
          $(subTable).attr("id", $.uidGen("subtable-"));
        //console.log(" ==[generated new]==> "+subTable.id);
        }
        new SubTableGrid("#"+subTable.id);
      });
		
    }catch(ex){
      console.log("Unable to add row: "+ex);
    }
  }

});
