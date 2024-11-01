function wpwhoosh_validate_secret(frm) { 
	var secretval = '0000';
    var secret = frm.elements["secret"];
    if (secret.value.length > 0) { 
    	if (! /^[0-9a-zA-Z!@£#$%&*()_+<>?:;,. ]{4,32}$/.test(secret.value)) {
			alert("Please enter an API Secret of between 4 and 32 characters in length. We recommend a length of at least 16 characters for strong security")
			secret.focus();
			return false;
		}
		secretval = secret.value;
 	}

    var auth = frm.elements["authorization"];
	if (auth.value.length != 32) {
		alert("The authorization code is not the correct length. Please check you have copied it correctly.")
		auth.focus();
		return false;
	}
	var s = CryptoJS.MD5(secretval);
	if (s) {
		secret.value = s.toString(); 
 		return true;
 	} else {
 		return false;
 	}
}
