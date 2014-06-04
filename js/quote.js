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