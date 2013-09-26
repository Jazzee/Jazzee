/**
 * Javascript for the setup_pages controller
 */
document.write('<style type="text/css">#payments{display:none}</style>');
$(document).ready(function(){
  $('input.DateInput').each(function(i){
    var input = $(this);
    if(input.val().length < 1){
      jazzeePaymentsReportShowButton(input);
    } else {
      jazzeePaymentsReportDatePicker(input);
    }
  });
  var progressbar = $('<div>').attr('id', 'progress').append('Loading...').css('width', '250px');
  $('div.form').append(progressbar);
  progressbar.progressbar({
    value: false,
    create: function(event,ui){
      setTimeout(function(){
        var changeProgram = new ChangeProgram(document.location.href + '/../../changeprogram');
        changeProgram.init();
        $('#payments').dataTable({
        "sDom": '<"H"Tfrl>t<"F"ip>',
        "oTableTools": {
          "sSwfPath": "//cdnjs.cloudflare.com/ajax/libs/datatables-tabletools/2.1.4/swf/copy_csv_xls_pdf.swf"
        },
        "aaSorting": [[ 1, "desc" ]],
        "aoColumnDefs": [
            {
              "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                if(changeProgram.check($(nTd).attr('programId'))){
                    var a = $('<a>').attr('href', document.location.href + '/../../applicants/single/' + $(nTd).attr('applicantId')).html(sData).data('programId', $(nTd).attr('programId'));
                    a.bind('click', function(){
                      changeProgram.changeTo($(this).data('programId'));
                      return true;
                    });
                    $(nTd).html(a);
                }
              },
              "aTargets": [2]
            }
         ],
        "fnFooterCallback": function ( nRow, aaData, iStart, iEnd, aiDisplay ) {
            var totalAmount = 0;
            for ( var i=0 ; i<aaData.length ; i++ )
            {
                totalAmount += parseInt(aaData[i][6]);
            }
            /* Modify the footer row to match what we want */
            var nCells = nRow.getElementsByTagName('th');
            nCells[1].innerHTML = '$' + totalAmount;
        }
      });
      $('#progress').hide().remove();
      $('#payments').show();
    }, 50);
    }
  });
  
});

/**
 * Setup the data picker on an input element
 */
function jazzeePaymentsReportDatePicker(input){
  var button = $('<button>').html('Clear');
  button.button({
    icons: {
      primary: 'ui-icon-trash'
    }
  });
  button.bind('click', function(e){
    var input = $('input', $(this).parent());
    input.val('');
    input.AnyTime_noPicker();
    $(this).remove();
    jazzeePaymentsReportShowButton(input);
    return false;
  });
  input.after(button);
  input.AnyTime_noPicker().AnyTime_picker(
    {format: "%Y-%m-%dT%T%:",
          formatUtcOffset: "%: (%@)",
          hideInput: true,
          placement: "inline"}
  );
  
};

/**
 * Setup the data picker on an input element
 */
function jazzeePaymentsReportShowButton(input){
  var button = $('<button>').html('Pick Date');
  button.button({
    icons: {
      primary: 'ui-icon-plus'
    }
  });
  button.bind('click', function(e){
    input.show();
    jazzeePaymentsReportDatePicker(input);
    $(this).remove();
  });
  input.after(button);
  input.hide();
};