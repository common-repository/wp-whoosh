jQuery(document).ready(function() {
	jQuery('span.buy').click(function() { 
		jQuery('#credits').val(jQuery(this).attr('id'));
		jQuery('#action').val('buy');		
		jQuery('#wpwhoosh_form').submit();
	});
});