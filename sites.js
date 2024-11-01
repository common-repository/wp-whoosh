function wpwhoosh_set_validator() {
    jQuery('#site_form').validate({
      rules: {
        site_name: { required: true },
        site_url: { required: true, url: true },
        template: { required: true },
        host: { required: true },
        site_db_name: { nowhitespace: true, maxlength: 32 },
        site_db_user: { nowhitespace: true, maxlength: 7},
        site_db_password: { nowhitespace: true, maxlength: 32 },
        site_admin_email: { required: true, email : true },
        site_admin_name: { required: true, maxlength: 32 },
        site_copyright_start: {min: 1900, max: 2020 },
        site_twitter_name : { nowhitespace: true, alphanumeric: true, maxlength: 15 },
        site_facebook_url : { url: true, maxlength: 100 },
        site_googleplus_url : { url: true, maxlength: 100 },
        site_linkedin_url : { url: true, maxlength: 100 },
        site_pinterest_url : { url: true, maxlength: 100 },
        site_stumbleupon_url : { url: true, maxlength: 100 },
        site_flickr_id : { nowhitespace: true, maxlength: 15 },
        site_ga_code : { nowhitespace: true, maxlength: 25 }
      },
      messages: {
        site_name: "Enter a short name for your site",
        site_url: { 
        	required: "Enter the URL where you want to install the site",
        	url: "The URL should be of the form http://www.yoursite.com"
        	},        
        template: "Choose a template for your site",
        host: "Select a host",
        site_db_name: { 
        	nowhitespace: "The database name cannot contain spaces", 
        	maxlength: "The database name is limited to 32 characters" 
        },
        site_db_user: { 
        	nowhitespace: "The database username cannot contain spaces", 
        	maxlength: "The database name is limited to 7 characters" 
        },
        site_db_name: { 
        	nowhitespace: "The database password cannot contain spaces", 
        	maxlength: "The database password is limited to 32 characters" 
        },
        site_admin_email: {
       		required: "Enter your preferred WordPress administrator email address",
       		email: "The email address must be in the format of yourname@domain.com"
     	},
        site_admin_name: {
       		required: "Enter the public name that will appear on any post authored by your admin account",
        	maxlength: "The longest allowed name is 32 characters long." 
     	},     	
        site_twitter_name : { 
        	nowhitespace: "Enter a single twitter name",
        	alphanumeric: "A twitter name can only consist of letters, numbers and underscores.", 
        	maxlength: "The longest allowed twitter name is 15 characters long." 
        },
        site_facebook_url: { 
        	nowhitespace: "The URL should not contain spaces",
        	url: "The Facebook URL should typically be of the form https://www.facebook.com/yourpage or http://yourname.fbfollow.me"
        }, 
        site_googleplus_url: { 
        	nowhitespace: "The URL should not contain spaces",
        	url: "The Google+ Profile URL should typically be of the form https://plus.google.com/123456789012345678901/"
        }, 
        site_linkedin_url: { 
        	nowhitespace: "The URL should not contain spaces",
        	url: "The Linked In URL should typically be of the form http://www.linked.com/in/yourname or https://www.linked.com/pub/yourname"
        },
        site_pinterest_url: { 
        	nowhitespace: "The URL should not contain spaces",        
        	url: "The Pinterest URL should typically be of the form http:/pinterest.com/yourname/"
        },
        site_stumbleupon_url: { 
        	nowhitespace: "The URL should not contain spaces",        
        	url: "The StumbleUpon URL should typically be of the form http://www.stumbleupon.com/stumbler/yourname/"
        },                          
        site_flickr_id : { 
        	nowhitespace: "Enter a single Flickr ID of the form 12345678@N00", 
        	maxlength: "The longest allowed Flickr ID is 15 characters long." 
        },      
        site_ga_code : { 
        	nowhitespace: "Enter a single Google Analytics code of the form UA-1234567-89", 
        	maxlength: "The longest allowed Google Analytics code is 25 characters long." 
        }           
      },
      submitHandler: function(form) {       
        jQuery('input.wpwhoosh_action').attr("disabled", "disabled"); //stop extra clicks
      	action = jQuery('input[name=action]').val(); //get action
      	hassecret = jQuery('input[name=hassecret]').val(); //is secret required?
		if ((('check'==action) || ('install'==action) || ('delete'==action)) && (1==hassecret) && !wpwhoosh_check_secret()) return false;
		if (('check'==action) || ('install'==action) || ('delete'==action)) jQuery('#awaiting'+action).show(); //show waiting icon if required
        form.submit();
      },       
    });
}

function wpwhoosh_check_secret() {
	var sval = jQuery('#secret').val();
	return sval ? wpwhoosh_add_secret(sval) : wpwhoosh_open_dialog(); 
}

function wpwhoosh_add_secret(secretval) {
	var secret = '';
	if ((secretval==null) || (secretval=='')) return false;
	var ts = Math.round((new Date()).getTime() / 1000).toString();
	var s = CryptoJS.MD5(secretval);
	if (s) secret = CryptoJS.MD5(s.toString()+ts);

	if (secret.toString().length > 0) {
    	jQuery('#secret').val(''); //clear the secret as we do not want to send it in free text	
		jQuery('form#site_form').append('<input type="hidden" name="time" value="'+ts+'"/><input type="hidden" name="hash" value="'+secret.toString()+'"/>');
		return true;
	} else {
		return false;
	}
}

function wpwhoosh_open_dialog() {
	jQuery('#dialog-form').dialog('open');
	return false;
}

function wpwhoosh_define_dialog() {
    jQuery( "#dialog-form" ).dialog({
      autoOpen: false,
      height: 250,
      width: 350,
      resize: false,
      modal: true,
	  buttons: { 
	 "Ok": function() {
          var bValid = true;
          var yoursecret = jQuery('#yoursecret');
          bValid = bValid && wpwhoosh_check_length( yoursecret, 'API Secret', 4, 32 ); 
          bValid = bValid && wpwhoosh_check_regex( yoursecret, /^([0-9a-zA-Z!@£#$%&*()_+<>?:;,. ])+$/, "API Secret field only allows standard characters" );
          if ( bValid ) {
          		jQuery('#secret').val(yoursecret.val()); //move valid Secret to hidden field in main form
          		jQuery('#site_form').submit(); //submit form
          		jQuery( this ).dialog( "close" );
			}
        },
    	"Cancel" : function() { jQuery( this ).dialog( "close" ); } 
      },    
      close: function() { jQuery('#yoursecret').val('').removeClass('ui-state-error'); },
      beforeClose: function(event,ui) {  if(event.keyCode == 13) {
      	event.preventDefault();event.stopImmediatePropagation(); return false; } },
      dialogClass: 'wp-dialog'
	});
}
 
function wpwhoosh_site_confirm_delete($name) {
	return confirm("You are about to delete the site  "+$name+"\n Press `Cancel` to stop, `OK` to delete." );
}

function wpwhoosh_set_local_date_fields() {
	jQuery('.wpwhoosh-date').each(function(i) {
		if (jQuery(this).text().length > 0) {
			var d=new Date(jQuery(this).text());
			jQuery(this).text(d.toLocaleString());
		}
	});
}

function wpwhoosh_set_metabox_tabs() {
	jQuery('.site-metabox-tabs li.tab a').each(function(i) {
		var thisTab = jQuery(this).parent().attr('class').replace(/tab /, '');
		if ( thisTab == 'tab1')
			jQuery(this).addClass('active');
		else
			jQuery(this).parent().parent().parent().find('div.' + thisTab).hide();

 		if (! jQuery(this).parent().parent('ul').hasClass('disabled'))
			jQuery(this).click(function(){
				jQuery(this).parent().parent().parent().children('div').hide();
				jQuery(this).parent().parent('ul').find('li a.active').removeClass('active');
				jQuery(this).parent().parent().parent().find('div.'+thisTab).show();
				jQuery(this).parent().parent().parent().find('li.'+thisTab+' a').addClass('active');
			});
		jQuery('.site-heading').hide();
		jQuery('.site-metabox-tabs').show();
	});
}

function wpwhoosh_set_password_show() {
    jQuery('#site_form input[type=password]').each(function(i) {
    	jQuery(this).addClass('password').after('<input style="display:none;" disabled="disabled" class="password" type="text" size="10" value="'+jQuery(this).val()+'" autocomplete="off" /><span style="cursor:pointer"><sup>Show</sup></span>');
		jQuery(this).next().next().click( function() {
			jQuery(this).prevAll('.password').toggle();
			jQuery(this).find('sup').text(jQuery(this).text() =='Show' ? 'Hide' :'Show');
		});
		jQuery(this).change( function() {
			jQuery(this).next().val(jQuery(this).val());
		});		
	});
}

function wpwhoosh_set_button_actions() {
	jQuery('#delete,#install, #check, #save, #save2, #update, #update2').each( function() {
		if (jQuery(this).hasClass('whoosh')) {
			jQuery(this).click( function() {
				jQuery('div#message').remove(); //clear any message
				action = jQuery(this).attr('id'); 
				jQuery('input[name=action]').val(action); //change action field
			});
		}
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

jQuery(document).ready(function() {
	if (jQuery('input[name=site_id]')) { 
		wpwhoosh_set_validator();
		wpwhoosh_define_dialog();
		wpwhoosh_set_local_date_fields();
		wpwhoosh_set_metabox_tabs();
		wpwhoosh_set_password_show();
		wpwhoosh_set_button_actions();
	}
});