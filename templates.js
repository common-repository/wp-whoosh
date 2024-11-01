jQuery(document).ready(function() {
	jQuery('span.buy').click(function() { 
		jQuery('#template').val(jQuery(this).attr('id'));
		jQuery('#action').val('buy');
		jQuery('#wpwhoosh_form').submit();
	});
});