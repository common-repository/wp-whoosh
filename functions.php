<?php
class WPWhooshUtils {

	const OPTIONS_KEY = 'options';
	const HOSTS_KEY = 'hosts';
	const SITES_KEY = 'sites';
	const TEMPLATES_KEY = 'templates';

	protected static $lists  = array(
 		'site_permalinks' => array(
			'/%post_id%/%postname%/' => array('/%post_id%/%postname%/' ),
			'/%postname%/' => array('/%postname%/' ),
			'/%postname%-%post_id%/' => array('/%postname%-%post_id%/' ),
			'/%category%/%postname%/' => array('/%category%/%postname%/' ),
 			'/%year%/%monthnum%/%day%/%postname%/' => array('/%year%/%monthnum%/%day%/%postname%/' ),
 			'/%year%/%monthnum%/%postname%/' => array('/%year%/%monthnum%/%postname%/' ),
			'' => array('/?p=id')
		),		
 		'cpanel_provider' => array(
			'GATORBABY' => array('HostGator Baby' ),
			'GATORBUS' => array('HostGator Business' ),
			'GATORRES' => array('HostGator Reseller' ),
			'GATORVPS'  => array('HostGator VPS'),
			'GATORDED' => array('HostGator Dedicated'),
			'BLUEHPROF' => array('BlueHost Prof' ),
			'BLUEHVPS'  => array('BlueHost VPS' ),
			'LIQWBVPS'  => array('LiquidWeb VPS' ),
 			'LIQWBDED' => array('LiquidWeb Dedicated Server' ),
 			'LIQWBSMA' => array('LiquidWeb Smart Server' ),
			'OTHER' => array('Other Host with Regular CPANEL')
		)
	);
	protected static $defaults = array();
	protected static $options = array();
	protected static $hosts = array();
	protected static $sites = array();
	protected static $templates = array();
			
	static function is_multiuser() {
		return defined('WPWHOOSH_MULTIUSER') && WPWHOOSH_MULTIUSER;
	}
	
	static function get_list($id) {
    	if ($id && array_key_exists($id,self::$lists))
    	    return self::$lists[$id];
    	else
    	    return false;
	}	

	static function get_config($config) {
		$keyname = self::get_key_name($config);
		return self::is_multiuser() ? get_user_meta (get_current_user_id(),$keyname,true) : get_option($keyname);		
	}

	static function save_config($config, $new_value) {
		$keyname = self::get_key_name($config);
		return self::is_multiuser() ? update_user_meta (get_current_user_id(),$keyname,$new_value) : update_option($keyname,$new_value);
	}

	static function delete_config($config) {
		$keyname = self::get_key_name($config);
		return self::is_multiuser() ? delete_user_meta (get_current_user_id(),$keyname) : delete_option($keyname);
	}

	private static function get_key_name($suffix) {
		return WPWHOOSH_PLUGIN_NAME.'_'. $suffix;
	}

	static function get_options($cache = true) {
	   	if ($cache && (count(self::$options) > 0)) return self::$options;
		$keyname = self::get_key_name(self::OPTIONS_KEY);
		$options = get_option($keyname);		
   		self::$options = empty($options) ? self::$defaults : shortcode_atts( self::$defaults, $options);   
		return self::$options;
	}

	static function save_options($new_options) {
		$keyname = self::get_key_name(self::OPTIONS_KEY);
		$updated = update_option($keyname,$new_options);
		if ($updated) self::get_options(false);
		return $updated;
	}

	static function save_option($key, $new_value) {
		if (array_key_exists($key,self::$defaults)) {
			$options = self::get_options(false);
			$options[$key] = $new_value;
			return self::save_options($options);
		} else {
			return false;
		}
	}

	static function get_templates($cache = true) {
   		if ($cache && (count(self::$templates) > 0)) return self::$templates;
		$templates = self::get_config(self::TEMPLATES_KEY);		
		if (! empty($templates) && ! is_array($templates)) $templates = unserialize(strrev(base64_decode($templates)));
		self::$templates = empty($templates) ? array() : $templates;   
		return self::$templates;
	}

	static function save_templates($new_templates) {
		$keyname = self::TEMPLATES_KEY;
		if (count($new_templates) == 0) {
			$updated = self::delete_config($keyname);	
		} else {
			$new_templates = base64_encode(strrev(serialize($new_templates)));
			$updated = self::save_config($keyname,$new_templates);
		}
		if ($updated) self::get_templates(false);
		return $updated;
	}

	static function get_template($id) {
    	$templates = self::get_templates();
    	if ($id && $templates && array_key_exists($id,$templates))
    	    return $templates[$id];
    	else
    	    return false;
	}

	static function get_hosts($cache = true) {
		if ($cache && (count(self::$hosts) > 0)) return self::$hosts;
		$hosts = self::get_config(self::HOSTS_KEY);		
		if (! empty($hosts) && ! is_array($hosts)) $hosts = stripslashes_deep(unserialize(strrev(base64_decode($hosts))));
		self::$hosts = empty($hosts) ? array() : $hosts; 
		foreach (self::$hosts as $host_id => $host)  self::$hosts[$host_id]['number_sites'] = count(self::get_sites_by_hosts($host_id));
		return self::$hosts;
	}

	static function save_hosts($new_hosts) {
		$keyname = self::HOSTS_KEY;
		if (count($new_hosts) == 0) {
			$updated = self::delete_config($keyname);	
		} else {	
			$new_hosts = base64_encode(strrev(serialize($new_hosts)));
			$updated = self::save_config($keyname, $new_hosts);
		}
		if ($updated) self::get_hosts(false);
		return $updated;
	}

	static function hosts_exist() {
		$hosts = self::get_hosts();
		return count($hosts) > 0;
	}

	static function get_host($id) {
    	$hosts = self::get_hosts();
    	if ($id && $hosts && array_key_exists($id,$hosts))
    	    return $hosts[$id];
    	else
    	    return false;
	}

	static function get_host_by_name($name) {
    	$hosts = self::get_hosts();
    	foreach ($hosts as $key => $host) if (strtolower($name) == strtolower($host['host_name'])) return $host;
    	return false;
	}

	static function generate_host_id() {
	    $max_id = 0;
	    $hosts = self::get_hosts();
	    foreach ($hosts as $key => $host) {
	    	$int_id = (int)substr($key,1);
	    	if ($int_id > $max_id) $max_id = $int_id;
		}
	    return 'h'.($max_id+1);
	}

	static function get_sites($cache = true) {
   		if ($cache && (count(self::$sites) > 0)) return self::$sites;
		$sites = self::get_config(self::SITES_KEY);		
		if (! empty($sites) && ! is_array($sites)) $sites = stripslashes_deep(unserialize(strrev(base64_decode($sites))));
		self::$sites = empty($sites) ? array() : $sites;   
		return self::$sites;
	}

	static function save_sites($new_sites) {
		$keyname = self::SITES_KEY;
		if (count($new_sites) == 0) {
			$updated = self::delete_config($keyname);	
		} else {
			$new_sites = base64_encode(strrev(serialize($new_sites)));
			$updated = self::save_config($keyname,$new_sites);
		}
		if ($updated) self::get_sites(false);
		return $updated;
	}

	static function get_site($id) {
    	$sites = self::get_sites();
    	if ($id && $sites && array_key_exists($id,$sites))
    	    return $sites[$id];
    	else
    	    return false;
	}

	static function get_site_by_name($name) {
    	$sites = self::get_sites();
    	foreach ($sites as $key => $site) if (strtolower($name) == strtolower($site['site_name'])) return $site;
    	return false;
	}

	static function get_sites_by_hosts($host_id) {
		$sites_on_host = array();
	    $sites = self::get_sites();
	    foreach ($sites as $key => $site) if (strtolower($host_id) == strtolower($site['host'])) $sites_on_host[$key] = $site;
	    return $sites_on_host;
	}

	static function unattached_host($host_id) {
		$sites_on_host = self::get_sites_by_hosts($host_id);
		return count($sites_on_host) == 0 ;
	}

	static function generate_site_id() {
    	$max_id = 0;
    	$sites = self::get_sites();
		if ($sites && is_array($sites) && (count($sites) > 0)) 
    		foreach ($sites as $key => $site) {
    			$int_id = (int)substr($key,1);
    			if ($int_id > $max_id) $max_id = $int_id;
			}
    	return 's'.($max_id+1);
	}

	static function get_option($option_name) {
    	$options = self::get_options();
    	if ($option_name && $options && array_key_exists($option_name,$options))
    	    return $options[$option_name];
    	else
    	    return false;
	}

	static function check_number($id) {
		$pattern = '/[0-9]+/';
		if (preg_match($pattern, $id, $matches) && ($matches[0]==trim($id))) 
			return $matches[0];
		else
			return false;
	}

	static function admin_url($slug, $action='',  $id='', $noheader = false, $with_nonce = false, $item ='') {
		return admin_url('admin.php?page='.$slug.
			(empty($id) ? '' : ('&amp;id='.$id)).
			(empty($action) ? '' : ('&amp;action='.$action)).
			($noheader ? '&amp;noheader=true' : '').
			($with_nonce ? ('&amp;_wpnonce='.wp_create_nonce( self::nonce_name($action,$item,$id ))) : '') );
	}

	static function nonce_name( $action, $item, $id ='') {
		return $action . (empty($item) ? '' : ('-'.$item)) . (empty($id) ? '' : ('_' . $id)) ;
	}

	static function clear_nonce() {
		if ( ! empty( $_GET['_wp_http_referer'] ) ) {
		    wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'noheader' ), stripslashes( $_SERVER['REQUEST_URI'] ) ) );
		 	exit;
		}
	}

	static function check_permissions() {
		if ( !current_user_can( 'read' ) )
			wp_die( __( 'You do not have permission to update '.WPWHOOSH_FRIENDLY_NAME.' options' ) );
	}

	static function clear_request_params($url) {
		return remove_query_arg( array( '_wp_http_referer', '_wpnonce', 'noheader', 'action', 'lastaction' ), stripslashes($url)) ;
	}

	static function clear_post_params() {
		$nondata =  array( '_wp_http_referer', '_wpnonce', 'noheader', 'action', 'lastaction', 'save', 'closedpostboxesnonce', 'meta-box-order-nonce' );
		foreach ($nondata as $key) if (array_key_exists($key,$_POST)) unset($_POST[$key]);
	}

	static function next_url($referer, $nonce_name='_wpnonce') {
		check_admin_referer($referer, $nonce_name);
		$last_action = array_key_exists('action',$_REQUEST) ? $_REQUEST['action'] : '';
		$next_url = self::clear_request_params (wp_get_referer()) ; 
		self::clear_post_params();
		return $last_action ? add_query_arg('lastaction',$last_action, $next_url) : $next_url;
	}

	static function clean_id ($id) {
   		return preg_replace('/^[0-9]_[a-z]/', '',strtolower($id)); //only allow lower case, numbers and underscores 
	}
	
	static function clean_name ($name) {
		return filter_var($name, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);		
	}

	static function clean_email ($name) {
		return filter_var(trim($name), FILTER_SANITIZE_EMAIL);		
	}

	static function clean_url ($name) {
		return filter_var(trim($name), FILTER_SANITIZE_URL);		
	}
	
	static function clean_text($text) {
		if ($clean = filter_var(stripslashes(trim($text)), FILTER_SANITIZE_STRING))
			return str_replace("'","&quot;", $clean);
		else
			return '';
	}

	static function clean_domain($url,$strip_host = false) {
        $u = self::strip_protocol(strtolower($url));
        $u = strpos($u,'/')===false ? $u : substr($u,0,strpos($u,'/'));
        $u = strpos($u,':')===false ? $u : substr($u,0,strpos($u,':'));
        return $strip_host ? str_replace(array('ftp.','www.'),'',$u) : $u;
   }

	static function clean_path($url) {
        $u = self::strip_protocol($url);
        return strpos($u,'/')===false ? '' : self::trailingslashit(substr($u,strpos($u,'/')));
   }

	static function strip_protocol($url) {
        if (empty($url)) return $url;
        $protocols= array('http://','https://','ssl://','ftps://','ftp://');
        return str_replace($protocols,'',$url);
	}

   static function trailingslashit($path) {
        return empty($path) ? '' : ($path . (substr($path,-1) == '/' ? '' : '/'));
   }
   
   static function get_errors($array) {
   		$errors = '';
   		if (is_array($array) && array_key_exists('errors',$array) && is_array($array['errors']))
   			foreach ( $array['errors'] as $error) $errors .= '<br/>'.$error;
   		return $errors;
   }

}

class wpwhoosh_tooltip {
	private $labels = array();
	private $tabindex = 100;
	function __construct($labels) {
		$this->labels = $labels;
		$this->tabindex = 100;
	}
	
	function tip($label) {
		$this->tabindex += 1;
		$heading = array_key_exists($label,$this->labels) ? __($this->labels[$label]['heading'],WPWHOOSH_PLUGIN_NAME) : ''; 
		$tip = array_key_exists($label,$this->labels) ? __($this->labels[$label]['tip'],WPWHOOSH_PLUGIN_NAME) : ''; 
		return $heading ? sprintf('<a href="#" class="wshtooltip" tabindex="%3$s">%1$s<span>%2$s</span></a>',
			$heading, $tip, $this->tabindex) : '';
	}
}
?>
