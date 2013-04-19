// Extend the String prototype to include a splice method.
// This will use an Array-based splitting / joining approach
// internally.
String.prototype.splice = function(
				   index,
				   howManyToDelete,
				   stringToInsert /* [, ... N-1, N] */
				   ){
 
    // Create a character array out of the current string
    // by splitting it. In the context of this prototype
    // method, THIS refers to the current string value
    // being spliced.
    var characterArray = this.split( "" );
 
    // Now, let's splice the given strings (stringToInsert)
    // into this character array. It won't matter that we
    // are mix-n-matching character data and string data as
    // it will utlimately be joined back into one value.
    //
    // NOTE: Because splice() mutates the actual array (and
    // returns the removed values), we need to apply it to
    // an existing array to which we have an existing
    // reference.
    Array.prototype.splice.apply(characterArray,arguments );
 
    // To return the new string, join the character array
    // back into a single string value.
    return( characterArray.join( "" ) );
 
};

/**
 * Javascript for the apply_page controller
 * Everything in hear needs to be value added so JS isn't necessary for applicants
 */
$(document).ready(function(){
	//  var services = new Services;

  //Add the datepicker to the DateInput element
  $('div.form input.DateInput').datepicker({
		showOn: "button",
		buttonImage: "resource/foundation/media/icons/calendar_edit.png",
		buttonImageOnly: true
	});

  try{
      var availableTags = [];
      var searchSchools = function(searchInput, response){
      var elem = $('#'+searchInput[0].id);
      var term = elem.val();
      var path = elem.closest("form")[0].action;
      //      path = path.splice(path.lastIndexOf( "/" ), 0,"/lookupschool" );
      if(path.indexOf("/edit/") > -1){
	  // cut from edit to the end
	  var rem = path.length - path.indexOf("/edit/")
	  path = path.splice(path.lastIndexOf( "/edit/" ), rem,"/lookupschool?searchTerm="+term );
      }else{
	  path += "/lookupschool?searchTerm="+term;
      }

      // "lookupschool";  'relative' but for some reason omits the page id even though it is in the form action
      // '/apply/page/lookupschool' - absolute, reaches actionIndex ok but when i add the extra route for 'lookupschool' the apply page constructor blows up

      //services.getControllerPath('apply_grid'); // services seems to be an admin only thing
      
      $.ajax({url: path  ,
            // ISSUE: jquery is not giving me a json object!
	    dataType: 'json',
	    complete: function(json){

	    // so, manually convert it
	    json = eval(json.responseText);
	    availableTags.length = 0; // clear the array
	    for(x in json){
		availableTags.push({label: 
			json[x].fullName+", "+json[x].city+", "+json[x].state , value: json[x].fullName});
	    }
	    
	    if(availableTags.length == 0){
		if(term.startsWith("other:")){
		    term = $.trim(term.substring("other:".length));
		}
		availableTags.push({label: "No results found for '"+term+"'. Click here if you sure this is the school name.",
			    value: "other: "+term});
	    }

	    response(availableTags);
	      }});
      
  };

      $('.field.school-chooser').append('<div class="ball stop"></div><div class="ball1 stop"></div>');
      $('input.school-chooser').after('<input type="checkbox" name="user-specified-school" /> My School is not on the list');
      $('.field.school-chooser input[name="user-specified-school"]').prop("disabled", "disabled");

      $('input.school-chooser').autocomplete({
	      source: function(request, response){
		  searchSchools($('input.school-chooser'), response);
	      },
		  
	      create: function(event, ui){
	      },
         	  
	      select: function( event, ui ) {

		  if(ui.item && (ui.item["value"].startsWith('other:'))){
                      $('.field.school-chooser input[name="user-specified-school"]').prop("checked", true);

		     var dialog = $('<div id="newSchoolDialog"><p>Address: <input type="text" name="newSchoolAddress1" /></p><p>City: <input type="text" name="newSchoolCity" /></p><p>State: <input type="text" name="newSchoolState" /></p><p>Zip/Postal Code: <input type="text" name="newSchoolZip" /></p><p>Country: <input type="text" name="newSchoolCountry" /></p><p class="newSchoolInfo"></p></div>');

		      dialog.dialog({
			      autoOpen: true,
			      modal: true,
			      title: "Add New School",
			      beforeClose: function(event, ui){

				  var address = $('input[name="newSchoolAddress1"]').valueOf().val();
				  var city = $('input[name="newSchoolCity"]').valueOf().val();
				  var state = $('input[name="newSchoolState"]').valueOf().val();
				  var zip = $('input[name="newSchoolZip"]').valueOf().val();
				  var country = $('input[name="newSchoolCountry"]').valueOf().val();

				  if(address.trim().length == 0){
				      $('p.newSchoolInfo').html("<h3 style='color: red;'>You must enter an Address</h3>");
				      throw new Error("You must enter an address");
				  }

				  if(city.trim().length == 0){
				      $('p.newSchoolInfo').html("<h3 style='color: red;'>You must enter a City</h3>");
				      throw new Error("You must enter a city");
				  }


				  if(state.trim().length == 0){
				      $('p.newSchoolInfo').html("<h3 style='color: red;'>You must enter a State</h3>");
				      throw new Error("You must enter a state");
				  }


				  if(zip.trim().length == 0){
				      $('p.newSchoolInfo').html("<h3 style='color: red;'>You must enter a Zip</h3>");
				      throw new Error("You must enter a zip or postal code");
				  }

				  if(country.trim().length == 0){
				      $('p.newSchoolInfo').html("<h3 style='color: red;'>You must enter a Country</h3>");
				      throw new Error("You must enter a country");
				  }


				  var name = $('input.school-chooser').val();
				  $('input.school-chooser').val(name+"; "+address+"; "+city+"; "+state+"; "+zip+"; "+country)
			      },
				  open: function(event, ui){

			      },
			      dialogClass: "no-close",
				  buttons: [
					    {
						text: "OK",
						    click: function() {
						    $( this ).dialog( "close" );
						}
					    }
]
				  });

		  }
	      },

		  change: function( event, ui ) {
		  console.log("change!"+(ui.item ? ui.item.label : null));

		  if(!ui.item){
		      $('input.school-chooser').val("");
		  }
	      },

              search: function( event, ui ) {
		  $(' .ball1').removeClass('stop'); 
	      },
	
              response: function( event, ui ) {
	          $('.ball1').addClass('stop'); 
	      },
	
	      open: function( event, ui ) {
	      },

              close: function( event, ui ) {
		  console.log("close!"+(event.target ? event.target.label : null));
	      }
	  });
  }catch(ex){
      console.log("Unable to create autocomplete: "+ex);
  }
      

  $('div.field p.instructions').each(function(i){
    var p = $(this);
    p.hide();
    var label = $('label', $(this).siblings('div.element').first()).first();
    var img = $('<img>').attr('src', 'resource/foundation/media/icons/information.png').attr('title', p.html());
    img.click(function(e){
      p.toggle('slide',{direction: 'up'});
      $(this).hide();
    });
    label.append(img);
  });
  
  $('#answers p.controls a.delete').click(function(i){
    if(!confirm('Are you sure you want to delete?  This cannot be undone')) return false;
  });
});