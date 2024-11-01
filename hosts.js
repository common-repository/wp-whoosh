function wpwhoosh_add_secret(secretval, updated) {
	var secret = '';
	var oldsecret = '';
	if ((secretval==null) || (secretval=='')) return false;
	var ts = Math.round((new Date()).getTime() / 1000).toString();
	var s = CryptoJS.MD5(secretval);
	if (s)  { 
		secret = CryptoJS.MD5(s.toString()+ts);
		if (updated > 0) oldsecret = CryptoJS.MD5(s.toString()+updated);
	}
	if (secret.toString().length > 0) {
        jQuery('#secret').val(''); //clear the secret as we do not want to send it in free text	
		jQuery('form#host_form').append(
			'<input type="hidden" name="time" value="'+ts+'"/><input type="hidden" name="hash" value="'+secret.toString()+'"/><input type="hidden" name="hash2" value="'+oldsecret.toString()+'"/>');
			return true;
	} else {
			return false;
	}
}

function wpwhoosh_check_secret(updated) {
	var sval = jQuery('#secret').val();
	return sval ? wpwhoosh_add_secret(sval, updated) : wpwhoosh_open_dialog(); 
}

function wpwhoosh_open_dialog() {
	jQuery( "#dialog-form" ).dialog( "open" );
	return false;
}

function wpwhoosh_define_dialog() {
    jQuery( "#dialog-form" ).dialog({
		autoOpen: false,
		height: 200,
		width: 350,
		resize: false,
		modal: true,
		buttons: { 
		"Ok": function() {
			var bValid = true;
			var yoursecret = jQuery('#yoursecret');
			bValid = bValid && wpwhoosh_check_length( yoursecret, "API Secret field", 4, 32 ); 
			bValid = bValid && wpwhoosh_check_regex( yoursecret, /^([0-9a-zA-Z!@£#$%&*\(\)_+<>?:;,. ])+$/, "API Secret PIN field only allows standard characters" );
			if ( bValid ) {
          		jQuery('#secret').val(yoursecret.val()); //move valid PIN to hidden field in main form
          		jQuery('#host_form').submit(); //submit form
          		jQuery( this ).dialog( "close" );
			}

        },
    	"Cancel" : function() { jQuery( this ).dialog( "close" ); } 
      },    
      close: function() { jQuery('#yoursecret').val('').removeClass('ui-state-error'); },
      dialogClass: 'wp-dialog'
	});
}
 
function wpwhoosh_update_tips( t ) {
      jQuery("#validateTips").text(t).addClass( "ui-state-highlight" );
      setTimeout(function() {
        jQuery("#validateTips").removeClass( "ui-state-highlight", 1500 );
      }, 500 );
}
 
function wpwhoosh_check_length( o, n, min, max ) {
	if ( o.val().length > max || o.val().length < min ) {
        o.addClass( "ui-state-error" );
        wpwhoosh_update_tips( "Length of " + n + " must be between " + min + " and " + max + "." );
        return false;
    } else {
        return true;
    }
}
 
function wpwhoosh_check_regex( o, regexp, n ) {
	if ( !( regexp.test( o.val() ) ) ) {
        o.addClass( "ui-state-error" );
        wpwhoosh_update_tips( n );
        return false;
	} else {
        return true;
	}
}
 
function wpwhoosh_is_save() {
	action = jQuery('input[name=action]').val(); //get action   
	return "save"===action;
}

function wpwhoosh_user_length() {
	return wpwhoosh_is_save() ? 8 : 32;
}

function wpwhoosh_host_validator() {
    jQuery('#save').unbind('click'); 
	
	if ( wpwhoosh_is_save()) {
    jQuery('#host_form').validate({
      ignore: ":submit",
      rules: {
        host_name: { required: true },
        cpanel_url: { required: true, url: true },
        cpanel_user: { required: true, alphanumeric: true, minlength : 2, maxlength: 8 },
        cpanel_password: { required: true },
		},
      messages: {
        host_name: { required: "Enter a short meaningful name for your host" },
        cpanel_url: { required: "Enter your full cPanel URL including the port number",
        		url : "cPanel should be of the format https://gator1234.hostgator.com:2083" },
        cpanel_user: { 
        	required: "Enter your cPanel user name",
        	alphanumeric: "The username should consist of just numbers and letters"
        	},
        cpanel_password: { required:  "Enter your cPanel password" },
      },
      submitHandler: function(form) {
      	updated = jQuery('input[name=updated]').val(); //get updated
      	action = jQuery('input[name=action]').val(); //get action
      	hassecret = jQuery('input[name=hassecret]').val(); //is secret required?
		if ((('save'==action) || 'update'==action) && (1==hassecret) && !wpwhoosh_check_secret(updated)) return false;
        jQuery('#save').attr("disabled", "disabled"); //stop extra clicks
        form.submit();
      },       
    });
    } else {		
    jQuery('#host_form').validate({
      ignore: ":submit",
      rules: {
        host_name: { required: true },
        cpanel_url: { required: true, url: true },
        cpanel_user: { required: true, minlength : 2, maxlength: 32 },
        cpanel_password: { required: true },
		},
      messages: {
        host_name: { required: "Enter a short meaningful name for your host" },
        cpanel_url: { required: "Enter your full cPanel URL including the port number"},
        cpanel_user: { required: "Enter your cPanel user name" },
        cpanel_password: { required:  "Enter your cPanel password" },
      },
      submitHandler: function(form) {
      	updated = jQuery('input[name=updated]').val(); //get updated
      	action = jQuery('input[name=action]').val(); //get action
      	hassecret = jQuery('input[name=hassecret]').val(); //is secret required?
		if ((('save'==action) || 'update'==action) && (1==hassecret) && !wpwhoosh_check_secret(updated)) return false;
        jQuery('#save').attr("disabled", "disabled"); //stop extra clicks
        form.submit();
      },       
    });
    }
}

jQuery(document).ready(function() {
	wpwhoosh_define_dialog();
	wpwhoosh_host_validator();
});