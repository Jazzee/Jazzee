function Pages(){
  var self = this;
  this.canvas;
  this.baseUrl;
  this.pageStore;
  this.currentPageID;
  this.currentTabName;
  
  this.init = function(){
    self.baseUrl = document.location.href;
    self.canvas = $('#canvas');
    self.refreshPageList();
    self.fillNewPages();
    self.pageStore = new PageStore;
  }
  
  this.refreshPageList = function(){
    $.get(self.baseUrl + 'pageList', function(json){
      $('#application-pages ol').remove();
      var ol = $('<ol>');
      $(json.data.pages).each(function(i){
        ol.append(
          $('<li>').attr('id', 'application-page-'+ this.id).append($('<span>').text(this.title)
          .bind('click', function(e){
            self.selectPage($(this).parent().attr('id').substring(17));
          })).append($("<img src='resource/foundation/icons/delete.png'>").addClass('delete').bind('click', function(){
            $.get(self.baseUrl + 'delete/' + $(this).parent().attr('id').substring(17), function(json){
              $('#workspace').empty();
              self.refreshPageList();
            }); //end get
          }))
        );
      });
      $('#application-pages').append(ol); 
    });
  }
  
  this.fillNewPages = function(){
    $.get(self.baseUrl + 'newPageList', function(json){
      var ol = $('<ol>');
      $(json.data.pages).each(function(i){
        ol.append(
          $('<li>').attr('id', 'new-page-'+ this.id).text(this.name)
            .hover(function(){
              $(this).addClass('hover');
            }, function(){
              $(this).removeClass('hover');
            })
            .bind('click', function(e){
              $(this).effect("transfer",{to: "#application-pages"}, 300);
              $.post(self.baseUrl + 'addPage',{pageType: encodeURIComponent($(this).attr('id').substring(9))}, function(json){
                self.refreshPageList();
              });
            })
          .prepend($("<img src='resource/foundation/icons/add.png'>").addClass('add'))
        );
      });
      $('#new-pages').append(ol);
    });
  }
	
  this.selectPage = function(pageID){
    self.currentPageID = pageID;
    var tabs = self.pageStore.get(pageID);
    var tab;
    $('#tabs').empty().append($('<ul>'));
    for(var i = 0; i < tabs.length; i++){
      tab = tabs[i];
      $('#tabs ul:first').append(
        $('<li>').attr('id', 'tabs-'+tab.name).html(tab.title).bind('click', function(e){
          $('#tabs ul li').removeClass('active');
          $(this).addClass('active');
          var currentTab = self.pageStore.getTab(self.currentPageID, $(this).attr('id').substring(5));
          self.currentTabName = currentTab.name;
          switch(currentTab.type){
            case 'form':
              self.formWorkspace();
              break;
            case 'elements':
              self.elementsWorkspace();
              break;
            case 'preview':
              self.previewWorkspace();
              break;
            break;
          } //end switch tab type
        }) //end bind
      ); //end append
    }; //end for loop
    $('#tabs ul:first li:first').trigger('click');
  }
  
  this.formWorkspace = function(){
    var tab = self.pageStore.getTab(self.currentPageID, self.currentTabName);
    self.makeForm(tab.form);
  }
  
  this.previewWorkspace = function(){
    var tab = self.pageStore.getTab(self.currentPageID, self.currentTabName);
    $('#workspace').html(tab.html);
    $('#workspace form').unbind().bind('submit',function(e){
      e.preventDefault();
      var messages = new Messages;
      messages.create('error', 'Forms should not be submitted from preview');
    });
  }
  
  this.makeForm = function(obj){
    var form = new Form;
    $('#workspace').html(form.create(obj));
    $('#workspace form').unbind().bind('submit',function(e){
      e.preventDefault();
      $.post(e.target.action, $(this).serialize(),function(json){
        self.pageStore.refreshPage(self.currentPageID);
        self.refreshPageList();
        self.makeForm(json.data.form);
      });
    });
  }
  
  this.elementsWorkspace = function(){
    var tab = self.pageStore.getTab(self.currentPageID, self.currentTabName);
    $('#workspace').empty().addClass('elements').append(
      $('<div>').addClass('yui-ge')
        .append($('<div>').addClass('yui-u').addClass('first').attr('id', 'ws-left'))
        .append($('<div>').addClass('yui-u').attr('id', 'ws-right'))   
    );
    $('#ws-left').empty().append('<ol>');
    
    $(tab.elements).each(function(i){
      var li = $('<li>').addClass('element')
        .append($('<div>').addClass('element-form'))
        .append($('<div>').addClass('element-list'));
      self.elementForm(li, this.form);
      if(this.hasListItems){
        self.elementListItems(li, this);
      }
      $('#ws-left ol:first').append(li);
    }); //end each elements
    
    $.get(self.baseUrl + 'newElementsList', function(json){
      $('#ws-right').append($('<ol>'));
      $(json.data.elements).each(function(i){
        $('#ws-right ol:first').append(
            $('<li>').attr('id', 'new-element-'+ this.id).html(this.name)
              .hover(function(){
                $(this).addClass('hover');
              }, function(){
                $(this).removeClass('hover');
              })
              .bind('click', function(e){
                $(this).effect("transfer",{to: "#ws-left"}, 300);
                $.post(self.baseUrl + 'addElement/' +tab.pageID,{elementType: encodeURIComponent($(this).attr('id').substring(12))}, function(json){
                  //refresh all the pages because we don't really know where we are pageID may not be the real pageID
                  self.pageStore.refreshPage(self.currentPageID);
                  self.elementsWorkspace();
                });
              })
            .prepend($("<img src='resource/foundation/icons/add.png'>").addClass('add'))
        );
      }); //end each elements
    });
  }
  
  this.elementForm = function(li, obj){
    var div = $('.element-form', li).first();
    var form = new Form;
    div.html(form.create(obj));
    $('form', div).unbind().bind('submit',function(e){
      e.preventDefault();
      $.post(e.target.action, $(this).serialize(),function(json){
        self.elementForm(li, json.data.form);
        });
    });
  }
  
  this.elementListItems = function(li, element){
    var div = $('.element-list', li).first();
    var form = $('<form>').bind('submit',function(e){
      e.preventDefault();
      $.post(self.baseUrl + 'addListItem/' +element.id, $(this).serialize(),function(json){
        self.pageStore.refreshPage(self.currentPageID);
        self.elementsWorkspace();
      });
    });
    form.append($('<label>').html('Value: ')).append($('<input>').attr('type' ,'text').attr('name', 'value'));
    form.append($('<button>').attr('type', 'submit').html('Save'));
    div.append(form);
    
    var ol = $('<ol>');
    $(element.listItems).each(function(i){
      var li = $('<li>').attr('id', 'list-item-'+ this.id).text(this.value)
        .hover(function(){
          $(this).addClass('hover');
        }, function(){
          $(this).removeClass('hover');
        })
        .bind('click', function(e){
//            $(this).effect("transfer",{to: "#application-pages"}, 300);
//            $.post(self.baseUrl + 'addPage',{pageType: encodeURIComponent($(this).attr('id').substring(9))}, function(json){
//              self.refreshPages();
//            });
          console.log('clicked');
        });
      if(!this.active){
        li.addClass('strike');
        li.append($("<img src='resource/foundation/icons/add.png'>").addClass('add').bind('click', function(){
          $.get(self.baseUrl + 'activateListItem/' + $(this).parent().attr('id').substring(10), function(json){
            self.pageStore.refreshPage(self.currentPageID);
            self.elementsWorkspace();
          }); //end get
        }));
      } else {
        li.append($("<img src='resource/foundation/icons/delete.png'>").addClass('delete').bind('click', function(){
          $.get(self.baseUrl + 'deactivateListItem/' + $(this).parent().attr('id').substring(10), function(json){
            self.pageStore.refreshPage(self.currentPageID);
            self.elementsWorkspace();
          }); //end get
        }));
      }
      ol.append(li);
    });
    div.append(ol);
    return div;
  }
}

/**
 * Cache the tabs for a page and update them when necessary
 */
function PageStore(){
  var self = this;
  this.baseUrl = document.location.href;
  this.pages = [];
  this.index = [];
  this.get = function(pageID){
    if(self.pages[pageID] === undefined){
      self.refreshPage(pageID);
    }
    return self.pages[pageID];
  }
  
  this.getTab = function(pageID, tabName){
    var tabs = self.get(pageID);
    return tabs[self.index[pageID][tabName]];
  }
  
  this.refreshPage = function(pageID){
    $.ajax({
      url: self.baseUrl + 'getTabs/' +pageID, 
      async: false, 
      success: function(json){
        self.pages[pageID] = [];
        self.index[pageID] = [];
        $(json.data.tabs).each(function(i){
          self.pages[pageID][i] = this;
          self.index[pageID][this.name] = i;
        });
      }
    });
  }
  
  this.refreshAll = function(pageID){
    self.pages = [];
    self.index = [];
  }
}
  
$(document).ready(function(){
  var messages = new Messages;
  $(document).ajaxError(function(e, xhr, settings, exception) {
    messages.create('error','There was an error with your request, please try again.');
  });
  
  $(document).ajaxComplete(function(e, xhr, settings) {
    if(xhr.getResponseHeader('Content-Type') == 'application/json'){
      eval("var json="+xhr.responseText);
      $(json.messages).each(function(i){
        messages.create(this.type, this.text);
      });
    }
  });

  var pages = new Pages;
  pages.init();
});