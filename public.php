<?php
define ('WPWHOOSH_PUBLIC','wpwhoosh_public');
add_action ('init', array(WPWHOOSH_PUBLIC, 'init' ) );

class wpwhoosh_public {

	const REMOTE_KEY = 'wpwhoosh_remote';

	static function init() {
		remove_action( 'wp_head', 'wp_generator' ); //don't show WordPress version
		remove_action( 'wp_head', 'wlwmanifest_link' ); //don't show Manifest Link
		add_filter('login_errors', array(WPWHOOSH_PUBLIC, 'suppress_errors' ));	//don't divulge database login name	
		if (self::allow_remote()) {
			add_filter ('xmlrpc_methods', array(WPWHOOSH_PUBLIC, 'xmlrpc_methods' ) );
		}
	}

	static function xmlrpc_methods($methods) {
	    	$methods[WPWHOOSH_PLUGIN_NAME . '.remoteMethod'] = array(WPWHOOSH_PUBLIC, 'xmlrpc_handler') ;
	    	return $methods;	
	}

	static function xmlrpc_handler($params) {
    	$username = $params[0];
    	$password = $params[1];
    	$method   = $params[2];
    	$args     = $params[3];

    	global $wp_xmlrpc_server;
    	if ( !$user = $wp_xmlrpc_server->login($username, $password) ) {
            return $wp_xmlrpc_server->error;
    	}    

    	if (self::xmlrpc_is_permitted_method($method)) {
        	try {
        	    return call_user_func_array($method, array($args));
        	} catch (Exception $e) {
        	    return new IXR_Error( 401, __( 'Remote call failed for '.$method . ' failed.',WPWHOOSH_PUBLIC) );
        	}
    	} else {
			return new IXR_Error( 401, __( 'Remote call for ' . $method . ' is not available.',WPWHOOSH_PUBLIC ) );
    	}	
	}
	
	static function xmlrpc_is_permitted_method($method) {
		$permitted_functions = array(
			WPWHOOSH_PUBLIC.'::update_footer_credits', 
			WPWHOOSH_PUBLIC.'::update_user', 
			WPWHOOSH_PUBLIC.'::update_contact_recipient',
			WPWHOOSH_PUBLIC.'::update_header_image',
			WPWHOOSH_PUBLIC.'::update_widget_value',			
			WPWHOOSH_PUBLIC.'::update_widget_values',	
			WPWHOOSH_PUBLIC.'::update_options',
			WPWHOOSH_PUBLIC.'::update_option',
			WPWHOOSH_PUBLIC.'::update_suboption',
			WPWHOOSH_PUBLIC.'::update_suboption',
			WPWHOOSH_PUBLIC.'::replace_option',
			WPWHOOSH_PUBLIC.'::replace_text_widgets',		
			WPWHOOSH_PUBLIC.'::disable_jetpack',
			WPWHOOSH_PUBLIC.'::disable_remote',
			WPWHOOSH_PUBLIC.'::deactivate');
    	return is_callable($method) && in_array($method, $permitted_functions);
	}

	static function update_footer_credits($footer_credits) {
		$updater = class_exists('FooterCredits') ? array('FooterCredits','save'): false;
		if ($updater && is_callable($updater))
			return call_user_func($updater, $footer_credits);
		else 
			return false;
	}

	static function update_user($user) {
		if($userdata = get_user_by('login',$user['user_login'])) {
			$user['ID'] = $userdata->ID;
			return wp_update_user($user);
		} else {
			return false;
		}
	}		
	
	static function update_contact_recipient($args) {
		$ukey = 'recipient';
		if ($args 
		&& is_array($args) 
		&& array_key_exists('old_url',$args)
		&& array_key_exists('new_url',$args)
		&& array_key_exists($ukey,$args) && !empty($args[$ukey])
		&& ($form = get_page_by_path('contact-form-1',OBJECT,'wpcf7_contact_form')) && !is_null($form)
		&& ($mail_config = get_post_meta($form->ID,'_mail',true))) {
				$mail_config[$ukey] = $args[$ukey];
				$mail_config['body'] = str_replace($args['old_url'],$args['new_url'], $mail_config['body']);
				return update_post_meta($page->ID,'_mail',$mail_config);
		}
		return false;
	}
	
	static function update_header_image($args) {
		if ($args 
		&& is_array($args) 
		&& array_key_exists('old_url',$args)
		&& array_key_exists('new_url',$args)
		&& ($mods = get_theme_mods())
		&& (strpos(serialize($mods), $args['old_url']) !== FALSE)) {
				if ($header_image = get_theme_mod('header_image')) {
					$header_image = str_replace($args['old_url'],$args['new_url'], $header_image);
					set_theme_mod('header_image', $header_image);
				}
				if ($header_image_data = get_theme_mod('header_image_data')) {
					$header_image_data->url = str_replace($args['old_url'],$args['new_url'], $header_image_data->url);
					$header_image_data->thumbnail_url = str_replace($args['old_url'],$args['new_url'], $header_image_data->thumbnail_url);
					set_theme_mod('header_image_data', $header_image_data);
				}
		}
		return false;
	}	

	static function update_widget_value($args) { //updates a single value which can be an option or suboption
		$altered = false;
		if ($args && is_array($args) && array_key_exists('option_value',$args) 
			&& array_key_exists('widget_key',$args) && ($widget_key = $args['widget_key'])
			&& array_key_exists('option_key',$args) && ($option_key = $args['option_key'])) {
			$suboption_key = (array_key_exists('suboption_key',$args) && !empty($args['suboption_key'])) ? $args['suboption_key'] : false;
			$widgets = get_option($widget_key);			
			if ($widgets && is_array($widgets)) 
			  	foreach ($widgets as $key => $widget)
			  		if (is_array($widget) && array_key_exists($option_key,$widget))
			  			if (! is_array($widget[$option_key]) ) {
							$widgets[$key][$option_key] = $args['option_value'];
							$altered = true;			  			
						} elseif ($suboption_key && array_key_exists($suboption_key,$widget[$option_key]) ) {
							$widgets[$key][$option_key][$suboption_key] = $args['option_value'];
							$altered = true;
						}
		}	
		return $altered ? update_option($widget_key,$widgets) : false;
	}

	static function update_widget_values($args) { //updates multiple values
		$altered = false;
		if ($args && is_array($args) 
			&& array_key_exists('widget_key',$args) && ($widget_key = $args['widget_key'])
			&& array_key_exists('options',$args) && ($options = $args['options']) && is_array($options)
			&& ($widgets = get_option($widget_key)) && is_array($widgets)) 			
				foreach ($widgets as $key => $widget)
			  		if (is_array($widget))
			  			foreach ($options as $option )
							if (is_array($option)
							&& array_key_exists('option_value',$option) 
							&& array_key_exists('option_name',$option)
							&& ($option_name = $option['option_name'])
							&& array_key_exists($option_name,$widget)) {
								$widgets[$key][$option_name] = $option['option_value'];
								$altered = true;			  			
							}
		return $altered ? update_option($widget_key,$widgets) : false;
	}

	static function update_options($options) {
		foreach ($options as $option) self::update_option($option);
	}

	static function update_option($args) {
		if ($args 
		&& is_array($args) 
		&& array_key_exists('option_value',$args)
		&& array_key_exists('option_name',$args)
		&& ($option_name = $args['option_name'])) 
			update_option($option_name,$args['option_value']);
	}

	static function update_suboption($args) {
		if ($args 
		&& is_array($args) 
		&& array_key_exists('option_value',$args)
		&& array_key_exists('option_name',$args)
		&& array_key_exists('suboption',$args) 
		&& ($option_name = $args['option_name'])
		&& ($suboption = $args['suboption'])
		&& ($options = get_option($option_name))
		&& is_array($options)
		&& array_key_exists($suboption,$options)) {
			if (is_array($options[$suboption]) && is_array($args['option_value']))
				$options[$suboption] = wp_parse_args($args['option_value'],$options[$suboption]);
			else
				$options[$suboption] = $args['option_value'];
			update_option($option_name,$options);
		}
	}	
	
	static function replace_option($args) { //replace values (urls) deep in a option array
		$altered = false;
		if ($args 
		&& is_array($args) 
		&& array_key_exists('to',$args)
		&& array_key_exists('from',$args)
		&& array_key_exists('option_name',$args)
		&& ($option_name = $args['option_name'])
		&& ($from = $args['from'])
		&& ($to = $args['to']) 
		&& ($options = get_option($option_name))) {
			if (is_array($options)) {
				foreach ($options as $suboption_key => $suboption_value) 
					if (is_array($suboption_value)) {
						foreach ($suboption_value as $subsub_key => $subsub_value) { 					
							$options[$suboption_key][$subsub_key] = str_replace($from,$to,$subsub_value);
							if ($options[$suboption_key][$subsub_key] != $subsub_value) $altered = true;						
						}
					} else {				
						$options[$suboption_key] = str_replace($from,$to,$suboption_value);
						if ($options[$suboption_key] != $suboption_value) $altered = true;						
					}
			} else {				
				$options = preg_replace($from,$to,$options);
				$altered = true;			  			
			}
			if ($altered) update_option($option_name,$options);
		}
	}	

	static function replace_text_widgets($args) { //substitutes one of more regexs in all the text widgets
		$altered = false;
		$widget_key = 'widget_text';
		if ($args && is_array($args) 
		&& array_key_exists('from',$args) && ($from = $args['from'])
		&& array_key_exists('to',$args) && ($to = $args['to'])
		&& ($widgets = get_option($widget_key)) && is_array($widgets)) 			
			foreach ($widgets as $key => $widget) 
				if (is_array($widget) 
				&& array_key_exists('text',$widget) 	
				&& (strpos($widgets[$key]['text'], $from) !== false)) {	
					$widgets[$key]['text'] = str_replace($from,$to,$widgets[$key]['text']);
					$altered = true;
				}
		return $altered ? update_option($widget_key,$widgets) : false;
	}
	
	static function disable_jetpack() {
		$options = get_option('jetpack_options');
		if ($options && array_key_exists('user_token',$options)) unset ($options['user_token']); 
		if ($options && array_key_exists('blog_token',$options)) unset ($options['blog_token']); 
		if ($options && array_key_exists('time_diff',$options)) unset ($options['time_diff']); 
		update_option('jetpack_options',$options);
		if(get_option('jetpack_activated')) update_option('jetpack_activated',false);
	}	

	static function disable_remote() {
		return update_option(self::REMOTE_KEY, false); 
	}

	static function allow_remote() {
		return get_option(self::REMOTE_KEY); 
	}
	
	static function deactivate() {
        deactivate_plugins( WPWHOOSH_PLUGIN_PATH );
        return true;
    }    	

	static function suppress_errors($error_messages) {
		return '';
	}
	
}
?>