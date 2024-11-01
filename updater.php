<?php

class WPWhooshUpdater {
	private static $plugin=WPWHOOSH_PLUGIN_NAME;
	private static $local_version=WPWHOOSH_VERSION;
	private static $default_action='updates';	
	private static $default_timeout=10;	
	private static $transient_expiry=86400;	
	private static $initialized=false;
	private static $key='-';
	private static $upgrader;
	private static $version;
	private static $package;
	private static $expiry;
	private static $notice;
	private static $updates;
	private static $balance;
	private static $last_updated;
	private static $valid;
	private static $cipher;
	private static $block_cipher_mode;
	private static $secret_enabled;
	
	private static function init() {
    	if (!self::$initialized) self::update(); 
	}

	public static function update($cache = true, $args = false) {
		if (is_array($args)) {
			$action = array_key_exists('action',$args) ? $args['action'] : self::$default_action;
		} else {
			$action = empty($args) ? self::$default_action : $args;
			$args = array ('action' => $action);
		}
		return ($cache && self::$initialized && ($action==self::$default_action)) ? self::$updates : self::get_updates($args,$cache); 
	}

	public static function set_key($new_key, $md5) {
		self::$key = empty($new_key) ? '' : ($md5 ? md5($new_key) : $new_key);
    }

	public static function has_key($cache=true) {
		$key = self::get_key($cache);
    	return !empty($key) && (strlen($key) == 32); //has a key worth checking
	}

	public static function empty_key($cache=true) {
		$key = self::get_key($cache);
    	return empty($key);
	}

	public static function get_key($cache=true) {
    	if (!$cache || ('-'== self::$key)) self::$key = WPWhooshUtils::get_config('key');
    	return self::$key;
	}

	public static function save_key($new_key,$md5 = true) {
		$updated = false;
  		$old_key = self::get_key(false); //fetch old key from database
		if ($new_key != $old_key) {
		    self::$initialized = false;
			self::set_key($new_key,$md5);
			$updated = WPWhooshUtils::save_config('key',self::$key);
   			if ($updated) self::update(false); //get updates for new key
   		}
   		return $updated;
	}		

	public static function check_validity(){
    	if (self::get_key()) {
    		self::init(); 
    		return self::$valid;
    		}
    	else 
    		return false;
	}

  	public static function get_version(){
    	self::init(); 
    	return self::$version;
   	}
   
 	public static function get_package(){
    	self::init(); 
    	return self::$package;
   	}   

	public static function get_notice(){
    	self::init(); 
    	return self::$notice;
   	}

 	public static function get_expiry(){
    	self::init(); 
    	return self::$expiry;
   	}

	public static function get_last_updated(){
    	self::init(); 
    	return self::$last_updated;
   	}

 	public static function get_balance(){
    	self::init(); 
    	return self::$balance;
   	}

 	public static function get_secret_enabled(){
    	self::init(); 
    	return self::$secret_enabled;
   	}    	
   	
    private static function add_plugin_prefix($args) {
    	$s = is_array($args) ? serialize($args) : $args;	
		return self::$plugin.'_'.$s;
    }  	
	
	private static function get_updates($args,$cache) {
		$action = array_key_exists('action',$args) ? $args['action'] : self::$default_action;
	    $result = self::has_key($cache)  ? 
	    	self::parse_updates(self::fetch_remote_or_cache($args,$cache)) :
	    	self::set_defaults(self::empty_key($cache) ? '' : __('Invalid License Key',WPWHOOSH_PLUGIN_NAME) );
		if ($action==self::$default_action) self::$updates = $result;
		return $result;
	}

    private static function parse_updates($response) {
        	self::$initialized = true; 
 			if (is_array($response) && (count($response) >= 6)) {
    	        self::$valid = $response['valid_key']; 
    	        self::$version = $response['version']; 
    	        self::$package = $response['package'];  
    	        self::$notice = $response['notice']; 
    	        self::$expiry = $response['expiry']; 
    	        self::$balance = $response['balance']; 
    	        self::$secret_enabled = $response['pin_enabled']; 
    	        self::$last_updated = date('c');
    			return $response['updates'];
			} else {
				return self::set_defaults('Unable to contact the server. Please try again later.'); 
			}
    }

    private static function set_defaults($notice = '') {
 		self::$valid = false; 
    	self::$version = self::$local_version; 
    	self::$package = '';  
    	self::$notice = empty($notice) ? '' : ('<div class="message">'.__($notice).'</div>'); 
    	self::$expiry = ''; 
    	self::$balance = 0;
    	self::$updates = '';
    	return self::$updates;
    }
    
    private static function fetch_remote_or_cache($args,$cache=true){
		$transient = self::add_plugin_prefix($args);
		$values = $cache ? self::get_transient($transient) : false;
    	if ((false === $values)  || is_array($values) || empty($values)) {
     	    $raw_response = self::remote_call($args, $cache);
    	    $values = (is_array($raw_response) && array_key_exists('body',$raw_response)) ? $raw_response['body'] : false;
    	    self::set_transient($transient, $values); 
		}
		return false === $values ? false : self::decode($values);
	}

	private static function remote_call($args, $cache=true, $backup = false){
		if (!is_array($args)) $args = array();
 		$timeout = array_key_exists('timeout',$args) ? $args['timeout'] : self::$default_timeout;
		$action = array_key_exists('action',$args) ? $args['action'] : self::$default_action;
		$xtra = '&act='.$action;
		$secret = '';
 		if (('updates' != $action) && array_key_exists('hash',$_POST) &&  array_key_exists('time',$_POST)) {
 			$xtra .= '&t='.$_POST['time'];
 			if (self::can_encrypt())
 				$secret = $_POST['hash']; //use secret to encrypt entire message
 			else
 				$xtra .= '&s='.$_POST['hash']; //no encryption available - so just send secret for validation
 		}
 				
        $options = array('method' => 'POST', 'timeout' => $timeout);
        $options['headers'] = array(
            'Content-Type' => 'application/x-www-form-urlencoded; charset=' . get_option('blog_charset'),
            'User-Agent' => 'WordPress/' . get_bloginfo("version"),
            'Referer' => get_bloginfo("url")
        );
		if (array_key_exists('body',$args)) $options['body'] = self::encode($args['body'], $secret);
        $raw_response = wp_remote_request(self::get_upgrader($cache, $backup).$xtra, $options);
        if ( is_wp_error( $raw_response ) || (200 != $raw_response['response']['code']) || empty($raw_response)){
			return $backup ? false : self::remote_call($action, $cache, true);
        } else {
            return $raw_response;
        }
	}

	private static function get_upgrader($cache = true, $backup=false){
        global $wpdb;
        if (empty(self::$upgrader) || ($cache == false) || $backup)
            self::$upgrader = self::get_remote_updater($backup). sprintf("?of=%s&key=%s&v=%s&wp=%s&php=%s&mysql=%s",
                urlencode(self::$plugin), urlencode(self::get_key()), urlencode(self::$local_version), urlencode(get_bloginfo("version")),
                urlencode(phpversion()), urlencode($wpdb->db_version()));

        return self::$upgrader;
	}
		
	private static function get_remote_updater($backup = false) {
    	return $backup ? WPWHOOSH_UPDATER_URL2 : WPWHOOSH_UPDATER_URL;
	}

	public static function can_encrypt() {
		if (defined ('MCRYPT_TRIPLEDES')) {
			self::$cipher = MCRYPT_TRIPLEDES;
			self::$block_cipher_mode = 'ecb';
			return true;
		} else
			return false;
	}

    private static function decode($message,$secret='') {
        return @unserialize(empty($secret) ? @gzinflate(base64_decode($message)) : self::decrypt($message, $secret)) ;
    }

    private static function encode($message, $secret='') {
        return empty($secret) ? @base64_encode(@gzdeflate(@serialize($message))) : self::encrypt(@serialize($message), $secret) ;
    }

	public static function reencrypt($val, $oldkey, $newkey) {
		if (!empty($oldkey)) $val = self::decrypt($val, $oldkey); //decrypt if there was a key
		if (!empty($newkey)) $val = self::encrypt($val, $newkey); //encrypt if there is a key
		return $val;
	}

    public static function encrypt($input, $ky) {
	if ( empty($input) || !self::can_encrypt()) return $input;
        $key = substr($ky,-24);
        $size = mcrypt_get_block_size(self::$cipher, self::$block_cipher_mode);
        $input = self::pkcs5_pad($input, $size);
        $td = mcrypt_module_open(self::$cipher, '', self::$block_cipher_mode, '');
        $iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, $key, $iv);
        $data = mcrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $data = base64_encode($data); 
        return $data;
    }

    public static function decrypt($crypt, $ky) {
	if ( empty($crypt) || !self::can_encrypt()) return $crypt;
        $key = substr($ky,-24);
        $crypt = base64_decode($crypt);
        $td = mcrypt_module_open (self::$cipher, '', self::$block_cipher_mode, '');
        $ivsize = mcrypt_enc_get_iv_size($td);
        $iv = substr ($crypt,0,$ivsize); //get IV prepended to message
        $crypt = substr($crypt,$ivsize); //get message
        mcrypt_generic_init($td, $key, $iv);
        $decrypted_data = mdecrypt_generic ($td, $crypt);
        mcrypt_generic_deinit ($td);
        mcrypt_module_close ($td);
        $decrypted_data = self::pkcs5_unpad($decrypted_data); //remove padding
        $decrypted_data = rtrim($decrypted_data); //trim any spaces
        return $decrypted_data;
    }

    private static function pkcs5_pad($text, $blocksize) {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    private static function pkcs5_unpad($text) {
        $pad = ord($text{strlen($text)-1});
        if ($pad > strlen($text)) return false;
        return substr($text, 0, -1 * $pad);
    }

	private static function get_transient($transient) {
    	if ($value = get_transient($transient)) return $value;
    	/**transients may not be working so use homespun alternative ***/
    	$transient = '_'.$transient;
		$last_update = get_option($transient.'_date');
    	if ($last_update && (abs(time() - $last_update) < self::$transient_expiry) )
    		return get_option($transient);
    	else
    		return false;
	}

	private static function set_transient($transient, $value) {
    	if (set_transient($transient, $value, self::$transient_expiry) 
    	&& ( $value = get_transient($transient))) return true;
    	/**transients not working so use alternative ***/  
    	$transient = '_'.$transient;    	 
    	update_option( $transient, $value); 	
    	update_option( $transient.'_date', time()); 
    	return true;
	}

}
?>
