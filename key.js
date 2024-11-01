function wpwhoosh_validate_form(frm){
    var firstname = frm.elements["firstname"];
	if ((firstname.value==null)||(firstname.value=="")){
		alert("Please enter your First Name")
		firstname.focus();
		return false;
	}
    var email = frm.elements["email"];
	if ((email.value==null)||(email.value==""))
		alert("Please enter your Email Address")
    else {
        if (wpwhoosh_validate_email(email.value))
           return true;
	    else
	  	   alert('Please provide a valid email address');
        }
	email.focus();
	return false;
 }

function wpwhoosh_validate_email(emailaddress) {
    var filter = /^([a-zA-Z0-9_.-])+@(([a-zA-Z0-9-])+.)+([a-zA-Z0-9]{2,4})+$/;
    return filter.test(emailaddress);
}

function wpwhoosh_validate_pin(frm) { 
	var pinval = '0000';
    var pin = frm.elements["pin"];
    if (pin.value.length > 0) { 
    	if (! /^[0-9]{4,8}$/.test(pin.value)) {
			alert("Please enter a PIN of between 4 and 8 digits in length")
			pin.focus();
			return false;
		}
		pinval = pin.value;
 	}

    var auth = frm.elements["authorization"];
	if (auth.value.length != 32) {
		alert("The authorization code is not the correct length. Please check you have copied it correctly.")
		auth.focus();
		return false;
	}
	var p = CryptoJS.MD5(pinval);
	if (p) {
		pin.value = p.toString(); 
 		return true;
 	} else {
 		return false;
 	}
}
