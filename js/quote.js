function validateForm()
{
var x=document.forms["quote-form"]["from_location"].value;
var y=document.forms["quote-form"]["to_location"].value;

if (x==null || x=="" || x==y || y=="")
  {
  alert("Please select the options with different Pickup and Destination address.");
  return false;
  }
}

function validateFormHourly()
{
var x=document.forms["quote-form-hourly"]["from_location_hourly"].value;

if (x==null || x=="" )
  {
  alert("Please select Pickup address.");
  return false;
  }
}


jQuery(function() {
	jQuery( "#tabs" ).tabs();
	jQuery( "#datepicker1" ).datepicker({ showOn: "button", minDate: 0, maxDate: "+1M +10D"  });
	jQuery( "#datepicker2" ).datepicker({ showOn: "button", minDate: 0, maxDate: "+1M +10D"  });
	
	
	jQuery("#quote-form-submit").validate({
		rules: {
			name: "required",
			captcha: {
				required: true,
				minlength: 5
			},
			confirm_captcha: {
				required: true,
				minlength: 5,
				equalTo: "#captcha"
			},
			email: {
				required: true,
				email: true
			}
		},
		messages: {
			name: "Please enter your name",
			confirm_captcha: {
				required: "Please Enter Above Captcha",
				equalTo: "Please enter the same Captcha as shown above"
			},
			email: "Please enter a valid email address",
		}
	});
	
});


