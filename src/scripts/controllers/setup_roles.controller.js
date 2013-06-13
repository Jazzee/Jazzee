/**
 * Javascript for the setup_roles controller
 */
$(document).ready(function(){
  var status = new Status($('#status'), $('#content'));
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
  
  var link = $('<a>').html(' (check all)').attr('href','#').bind('click', function(e){
    $(':checkbox', $(this).parent().parent().parent()).prop("checked", true);
    return false;
  });
  $('div.CheckboxList div.label label').append(link);
  
  var link = $('<a>').html('check everything').attr('href','#').bind('click', function(e){
    $(':checkbox').prop("checked", true);
    return false;
  });
  $('div.CheckboxList:first').before($('<div>').addClass('yui-gf').append($('<div>').addClass('yui-u').append(link)));
  var services = new Services;
  if(services.checkIsAllowed('setup_roles', 'getRoleDisplay')){
    var editDisplay = $('<button>').html('Edit Display');
    editDisplay.button();
    editDisplay.on('click', function(){
      var roleId = $(this).parent().attr('id').substr(4);
      var overlay = $('<div>').attr('id', 'loaddisplayoverlay');
        overlay.dialog({
          height: 90,
          modal: true,
          autoOpen: true,
          open: function(event, ui){
            $(".ui-dialog-titlebar", ui.dialog).hide();
            var basePath = services.getControllerPath('setup_roles');
            var application = services.getCurrentApplication();
            var label = $('<div>').addClass('label').html('Loading Display...').css('float', 'left').css('margin','10px 5px');
            var progressbar = $('<div>').addClass('progress').append(label);
            overlay.append(progressbar);
            progressbar.progressbar({
              value: false,
              create: function(e, ui){
                $.get(basePath + '/getRoleDisplay/'+roleId, function(json){
                  $('#loaddisplayoverlay').dialog("destroy").remove();
                  $('#role'+roleId+ ' span:first').html('(Limited Display)');
                  var display = new Display(json.data.result, application);
                  var div = $('<div>');
                  div.css("overflow-y", "auto");
                  div.dialog({
                    modal: true,
                    autoOpen: true,
                    position: 'center',
                    width: '90%',
                    height: 500,
                    close: function() {
                      div.dialog("destroy").remove();
                    },
                    buttons: [ 
                      {
                        text: "Save", click: function() { 
                          DisplayManager.save(basePath,display);
                          $(this).dialog("destroy").remove();
                      }},
                      {
                        text: "Remove Restrictions", click: function() { 
                          DisplayManager.remove(basePath,display);
                          $(this).dialog("destroy").remove();
                          $('#role'+roleId+ ' span:first').html('(Full Applicant Display)');
                      }}
                    ]
                  });
                  var displayManager = new DisplayManager(display, application);
                  displayManager.init(div);
                });
              }
            });
          }
        });
      return false;
    });
    $('#roleList li').append(editDisplay);
  }
});