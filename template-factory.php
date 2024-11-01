<?php
class WPWhooshTemplateFactory {

	private static $templates = array();
  	private static $groups = array();
	
	private static $defaults = array(	);

	public static function get_template_ids() {
    	return array_keys(self::get_templates());
	}

	public static function get_template_groups($cache=true) {
		if ((false == $cache) || count(self::$groups) == 0) self::refresh_groups($cache);
		if (! is_array(self::$groups)) self::$groups = array();
		return self::$groups;
 	}

	public static function get_templates_in_set($myset,$cache=true) {
		if (($groups = self::get_template_groups($cache)) && array_key_exists($myset, $groups)) 
			return $groups[$myset]['templates'];
 		else
 			return array();
 	}

	public function get_template($template_id) {
    	$templates = self::get_templates();
    	if ($template_id && is_array($templates) && array_key_exists($template_id,$templates)) {
        	return $templates[$template_id];
    	} else { 
        	return self::get_default_template();
		}
	}
	
	public function get_template_attribute($template_id, $attribute) {
    	if ($template = self::get_template($template_id)) 
   	 		return array_key_exists($attribute,$template) ? $template[$attribute] : false;
    	else
        	return false;
	}	

 	private static function get_default_template() {
 		return self::$defaults['twentyeleven'];
    }
    
	private static function get_templates ($cache = true) {
   		if (!$cache || (count(self::$templates) == 0)) self::refresh_templates($cache);
   		return self::$templates;
   	}

	private static function refresh_templates ($cache=true) {
		$templates = self::$defaults;
   		$more_templates = WPWhooshUpdater::update($cache);
   		if (is_array($more_templates) && (count($more_templates) > 0)) $templates = array_merge($templates,$more_templates);
   		self::$templates = $templates; //update static value
	}

	private static function refresh_groups ($cache=true) {
   		self::$groups = WPWhooshUpdater::update($cache,'template_groups');
	}

}
?>