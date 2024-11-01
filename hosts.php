<?php
class wpwhoosh_hosts {

    private static $slug = 'wpwhoosh_hosts';
    private static $parenthook = WPWHOOSH_PLUGIN_NAME;
    private static $screen_id;
    private static $list;    
    private static $keys;
    private static $tips = array(
			'host_id' => array('heading' => 'Host ID', 'tip' => 'Host ID.'),
			'host_name' => array('heading' => 'Host Name', 'tip' => 'Supply a short unique name for this hosting account - this is useful if you have several accounts with the same host: eg My HostGator Baby.'),
			'host_notes' => array('heading' => 'Host Notes', 'tip' => 'You have the option to supply some more option about the hosts here which will be visible. You might want to include the nameservers here for reference or maybe a username or password hint.'),
			'cpanel_provider' => array ('heading' => 'Host Provider', 'tip' => 'Choose your host. This will help us to choose default values for some fields.'),
			'cpanel_url' => array('heading' => 'cPanel URL', 'tip' => 'The full URL of your cPanel e.g https://gator123.hostgator.com:2083. '),
			'cpanel_user' => array('heading' => 'cPanel User', 'tip' => 'Your cPanel Username. This is normally 8 characters in length. Your cPanel username is always transferred and stored in an encrypted format for reasons of security'), 
			'cpanel_password' => array('heading' => 'cPanel Password', 'tip' => 'Your cPanel Password. Your password is always transferred and stored in an encrypted format for reasons of security.'), 
			'is_encrypted' => array('heading' => 'Is Encrypted', 'tip' => 'Indicates if cPanel username and password are encrypted by the API Secret.'),
			'host_ftp' => array('heading' => 'Use FTP', 'tip' => 'Only click this option if the plugin has issues with your uploading files on to your server and wish to try FTP as an alternative. Most servers work well without this option selected.'),
			'cpanel_updated' => array('heading' => 'Last Updated', 'tip' => 'The time details were last updated.'),
			'cpanel_verified_date' => array('heading' => 'Last Verified', 'tip' => 'Date login details were last verified.'),
			'cpanel_status' => array('heading' => 'Host Status', 'tip' => 'If a host is unverified it means either the cPanel URL, username or password is incorrect and hence it is not possible to deploy a site to that host.')
	);
	private static $tooltips;

    static function get_slug() {
		return self::$slug;
	}

    static function get_parenthook(){
		return self::$parenthook;
	}

    static function get_screen_id(){
		return self::$screen_id;
	}

    static function empty_host() {
		return array_fill_keys(self::$keys,'');
	}
	
 	static function get_url($id='', $noheader = false) {
		return WPWhooshUtils::admin_url(self::get_slug(), '', $id, $noheader);
	}

    static function get_list() {
		return self::$list;
	}

	static function set_list($list) {
		self::$list = $list;
	}	
	
	static function init() {
		add_action('admin_menu',array(WPWHOOSH_HOSTS, 'admin_menu'));
	}

	static function admin_menu() {
		self::$screen_id = add_submenu_page(self::get_parenthook(), __('List Hosts',WPWHOOSH_PLUGIN_NAME), 
			__('Hosts',WPWHOOSH_PLUGIN_NAME), 'read', self::get_slug(), array(WPWHOOSH_HOSTS,'controller'));
		add_action('load-'.self::get_screen_id(), array(WPWHOOSH_HOSTS, 'load_page'));
	}

	static function load_page() {
		self::$keys = array_keys(self::$tips);
		self::$tooltips = new wpwhoosh_tooltip(self::$tips);
		add_filter('screen_options_show_screen', array(WPWHOOSH_HOSTS,'enable_screen'),10,2);
		add_filter('screen_layout_columns', array(WPWHOOSH_HOSTS, 'screen_layout_columns'), 10, 2);	
		add_action ('admin_enqueue_scripts',array(WPWHOOSH_HOSTS, 'enqueue_styles'));
		add_action ('admin_enqueue_scripts',array(WPWHOOSH_HOSTS, 'enqueue_scripts'));		
		$id = array_key_exists('id', $_GET) ? $_GET['id'] : 0;
		$action =  array_key_exists('action',$_REQUEST) ? $_REQUEST['action'] : -1;		
		if (! empty($id) ||  ($action=='add')  || ($action=='save')) 
			self::load_page_edit($id) ;
		else 
	    	self::load_page_list();	
    }

	static function enable_screen($show_screen,$screen) {
		if ($screen->id == self::get_screen_id())
			return true;
		else
			return $show_screen;
	}	
	
	static function screen_layout_columns($columns, $screen) {
		if (!defined( 'WP_NETWORK_ADMIN' ) && !defined( 'WP_USER_ADMIN' )) {
			if ($screen == self::get_screen_id()) {
				$columns[self::get_screen_id()] = 1;
			}
		}
		return $columns;
	}

	static function enqueue_styles() {
		wp_enqueue_style('tooltip', WPWHOOSH_PLUGIN_URL.'tooltip.css', array(),WPWHOOSH_VERSION);	
		wp_enqueue_style('hosts', WPWHOOSH_PLUGIN_URL.'hosts.css', array(),WPWHOOSH_VERSION);	
		wp_enqueue_style('wp-jquery-ui-dialog');
 	}

	static function enqueue_scripts() {
		wp_enqueue_script ('jquery-validate', WPWHOOSH_PLUGIN_URL.'jquery.validate.min.js', 
			array('jquery'), '1.11.0', true);
		wp_enqueue_script ('jquery-validate-additional', WPWHOOSH_PLUGIN_URL.'additional-methods.min.js',
			array('jquery','jquery-validate'), '1.11.0', true);
		wp_enqueue_script ('sites', WPWHOOSH_PLUGIN_URL.'hosts.js', 
			array('jquery','jquery-validate','jquery-ui-core','jquery-ui-dialog'), WPWHOOSH_VERSION, true);
		wp_enqueue_script('crypto', WPWHOOSH_PLUGIN_URL.'md5.js',
			array(),'3.0.2',true);				
		wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');
		add_action('admin_footer-'.self::get_screen_id(), array(WPWHOOSH_HOSTS, 'toggle_postboxes'));
	}	

	static function load_page_list() {
		require_once (WPWHOOSH_PLUGIN_DIR.'host-list-table.php');
		self::set_list(new wpwhoosh_host_list_table(self::get_url()));
		add_filter ('manage_' . self::get_screen_id() . '_columns', array(self::get_list(),'get_columns'));		
		$plugin = WPWHOOSH_FRIENDLY_NAME;			
		$current_screen = get_current_screen();
		if (method_exists($current_screen,'add_help_tab')) {		
			$current_screen->add_help_tab( array( 'id' => 'wpwhoosh_hosts_overview', 'title' => $plugin.' Hosts', 		
				'content' => self::help_list_panel()));
		}
	}

	static function load_page_edit($host_id) {
		$host = self::fetch($host_id);
		$existing = array_key_exists('host_id',$host) && !empty($host['host_id']);		
		$callback_params = array ('host' => $host, 'existing' => $existing);
		add_meta_box('wpwhoosh-host-intro', __('Introduction',WPWHOOSH_PLUGIN_NAME), array(WPWHOOSH_HOSTS, 'intro_panel'), self::get_screen_id(), 'normal', 'core');
		add_meta_box('wpwhoosh-host-identity', __('Host Details',WPWHOOSH_PLUGIN_NAME), array(WPWHOOSH_HOSTS, 'id_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		$plugin = WPWHOOSH_FRIENDLY_NAME;
		$current_screen = get_current_screen();
		if (method_exists($current_screen,'add_help_tab')) {		
			$current_screen->add_help_tab( array( 'id' => 'hosts_edit', 
				'title' => sprintf('%1$s %2$s Host', $plugin, $existing ? 'Edit':'Add'), 
				'content' => $existing ? self::help_update_panel() : self::help_save_panel()));	
			$current_screen->add_help_tab( array( 'id' => 'hosts_support', 'title' => 'Troubleshooting', 'content' => self::help_trouble_panel()));		
		}
	}

	static function controller() {
		WPWhooshUtils::check_permissions();
    	$message='';
    
		if ( isset($_REQUEST['message']) ) {
			$message = urldecode($_REQUEST['message']);
			$_SERVER['REQUEST_URI'] = remove_query_arg(array('message'), $_SERVER['REQUEST_URI']);
		}

		$action =  array_key_exists("action",$_REQUEST) ? $_REQUEST['action'] : -1;
		if (array_key_exists('id', $_GET)  || ($action=='add') || ($action=='save'))
			self::single_action($action, $message);
		else 
			self::bulk_actions($action, $message);
	}

	static function bulk_actions($action, $message) {
		if (isset( $_REQUEST['cb'] ) ) {
			check_admin_referer( 'bulk-hosts' );
			$ids =  (array) $_REQUEST['cb'];
			switch ($action) {
				case 'delete':  { $message = self::delete_rows($ids); break; }
				default: {}
			}
		} 
		WPWhooshUtils::clear_nonce();
		self::list_rows($message);
	}

	static function single_action($action, $message) {
		$id = array_key_exists('id', $_GET)  ? $_GET['id'] : 0;
		if ($host = self::fetch($id)) 	
			switch ($action) {		
				case 'add':
				case 'edit' : {	self::edit($host,$message); break; }
				case 'save' : { self::save(); break; }
				case 'update': { self::update($host); break; }			
				case 'delete' : { self::delete($host); break; }
				case 'copy' : { self::copy($host,$_POST['new_name']); break; }
				default: { wp_die(__('Unknown/invalid action for host.'));  } 
			}
		else
			wp_die ('Host '.$id.' not found');
	}

	static function fetch($id = 0) {
		if  (empty($id)) 
			return self::empty_host();
		else 
			return WPWhooshUtils::get_host($id);
	}

	static function list_rows($message='') {
		$action_url = self::get_url();
		if (empty($message) && ! WPWhooshUpdater::check_validity()) $message = 'Validate your API key before adding a host';
		if (!empty($message)) $message = '<div id="message" class="updated"><p>'.$message.'</p></div>';
		print <<< HOSTS_LIST
<div class="wrap nosubsub">
<h2>Hosts <a href="{$action_url}&action=add" class="button add-new-h2">Add New</a></h2>
{$message}
<form id="posts-filter" action="{$action_url}" method="post" >
HOSTS_LIST;
		self::reset_vars();
	    self::get_list()->prepare_items();
	    self::get_list()->search_box( __( 'Search Hosts' ), 'host' ); 	    
		self::get_list()->display();
		echo('<div id="ajax-response"></div></form></div>');
	}

	static function delete_rows($ids) {
	    $deleted = 0;
		foreach ( $ids as $id ) { 
			if (($host=WPWhooshUtils::get_host($id)) && self::update_data( $host, true )) $deleted++; 
			}
		$message = sprintf(__('%d Hosts have been deleted.'), $deleted);	
	    return $message;
	}
	
	static function delete($host) {
    	$host_id = $host['host_id'];
		$redir = WPWhooshUtils::next_url('delete-host_' . $host_id); 
    	if (self::update_data( $host, true)) {
			$message = sprintf('Host %1$s was deleted successfully.',$host['host_name']); 
		} else {
			$message = sprintf('Host %1$s failed to be deleted.',$host['host_name']); 
			$redir = add_query_arg( array('id' => $host_id, 'action' => 'edit'), $redir );	//go back to edit page	
		}
		$redir = add_query_arg( array('message' => urlencode($message)), $redir ); //add the message 
    	wp_redirect( $redir ); 
    	exit;
	}

	static function save() {
	    $redir = WPWhooshUtils::next_url('add-host'); 
	    $host_name = WPWhooshUtils::clean_name($_POST['host_name']);
  		$host = self::sanitize_post();
		if (self::is_valid_host($host) ) {
			if (self::update_data($host)) {
				if (WPWhooshUpdater::check_validity())
    				if (self::login($host))
						$message = sprintf('Host %1$s was added and verified successfully.',$host_name);		
					else
						$message = sprintf('Host %1$s was added but login details could not be verified.',$host_name);
				else
					$message = sprintf('Host %1$s was added but cannot be verified until you register your API key.',$host_name);					
				$redir = add_query_arg( array('id' => $host['host_id'], 'action' => 'edit'), $redir );	//go to edit page			
			} else {
				$message = sprintf('Host %1$s failed to be added.',$host_name); 
				$redir = add_query_arg( array('action' => 'add'), $redir );	//go back to add page	
			}
		} else {
			$message = sprintf('Host information is incomplete. Hit the back key and enter the missing values.'); 
			$redir = add_query_arg(  array('action' => 'add'), $redir ); //go back to add page		}
		}
		$redir = add_query_arg( array('message' => urlencode($message)), $redir ); //add the message 
	    wp_redirect( $redir ); 
	    exit;
	}

	static function update($host) {
    	$host_id = $host['host_id'];
    	$redir = WPWhooshUtils::next_url('edit-host_' . $host_id); 
   		$host_name = WPWhooshUtils::clean_name($_POST['host_name']); //check for blank ID
		$host = self::sanitize_post($host);
  		if (self::is_valid_host($host) )
    		if (self::update_data($host)) 
    			if (WPWhooshUpdater::check_validity())
    				if (self::login($host))
						$message = sprintf('Host %1$s was updated and verified successfully.',$host_name);		
					else
						$message = sprintf('Host %1$s was updated but login details could not be verified.',$host_name);					
				else
					$message = sprintf('Host %1$s was updated but cannot be verified until you register your API key.',$host_name);					
			else 
				$message = sprintf('No changes were made to host %1$s.',$host_name);  
		else 
			$message = sprintf('All required fields must be supplied for %1$s. Go back and edit the host info.',$host_name); 
		$redir = add_query_arg( array('action' => 'edit', 'message' => urlencode($message)), $redir );  
    	wp_redirect( $redir ); 
    	exit;
	}

	static function generate_copy_host_name($original) {
		$suffix = 'copy';
		$num = 0;
		$root = $original.$suffix;
		if (strpos($original,$suffix) !== FALSE) 
			if (preg_match_all("|\d+|", $original, $m)) {
				$last = count($m) - 1;
				$num = $m[$last];
				$root = substr($original,0,strrpos($original,$num));
			}  				
		do {
			$num=+1;
			$new_host_name = $root.$num;
		} while (WPWhooshUtils::get_host_by_name($new_host_name));
		return $new_host_name;  //return an unique name
	}

	static function copy($host, $new_host_name='') {
		$host_id = $host['host_id'];
    	$redir = WPWhooshUtils::next_url('copy-host_' . $host_id); 
	    $new_host_name = WPWhooshUtils::clean_name($new_host_name); //clean_id
	    if (empty($new_host_name) || WPWhooshUtils::get_host_by_name($new_host_name)) 
	    	$new_host_name = self::generate_copy_host_name( $host['host_name']); //new name

		$new_host = array();
		$new_host['host_name'] = $new_host_name; //give it the new name		
		$new_host['host_ftp'] = $host['host_ftp'];
		$new_host['cpanel_url'] = $host['cpanel_url'];
		$new_host['cpanel_user'] = $host['cpanel_user'];
		$new_host['cpanel_password'] = $host['cpanel_password'];
		if ($host_id = self::update_data($new_host))   //update and return new host ID
    		if (self::login($host))
				$message = sprintf('Host %1$s was copied and verified successfully.',$new_host_name);		
			else
				$message = sprintf('Host %1$s was copied but login details could not be verified.',$new_host_name);			
		else 
			$message = sprintf(__('Failed to create new host %1$s.'),$new_host_name);		
		$redir = add_query_arg( 
			array('id' => urlencode($host_id),'action' => 'edit','message' => urlencode($message)), $redir );  
    	wp_redirect( $redir ); 
    	exit;
 	}
	
	static function login(&$host) {
		$args = array('action' => 'host_checks', 'timeout' => 30, 'body' => array( 'host' => $host));
		$status = WPWhooshUpdater::update(false, $args);
		if ($status['success']) {
		 	$host['cpanel_status'] = 'VERIFIED';
		 	$host['cpanel_verified_date'] = date('Y-m-d h:i');
			$verified = true;
		} else {
		 	$host['cpanel_status'] = 'INVALID';
		 	$host['cpanel_verified_date'] = '';
			$verified = false;
		}
		self::update_data($host);
		return $verified;
	}

	static function is_valid_host($host) {
		if (!is_array($host)) $host = WPWhooshUtils::get_host($host);
		return is_array($host)
			&& array_key_exists('cpanel_url',$host) && ! empty($host['cpanel_url'])
			&& array_key_exists('cpanel_user',$host) && ! empty($host['cpanel_user'])
			&& array_key_exists('cpanel_password',$host) && ! empty($host['cpanel_password'])
			&& array_key_exists('host_name',$host) && ! empty($host['host_name']) ;
	}

	static function update_data( &$new_host, $delete=false) { 
		$old_host = array();
		if (! array_key_exists('host_id',$new_host) || empty($new_host['host_id'])) $new_host['host_id'] = WPWhooshUtils::generate_host_id(); 

		$host_id =  $new_host['host_id'] ;
		$all_hosts = WPWhooshUtils::get_hosts (false) ; //fetch from database - not the cache
		if (array_key_exists($host_id,$all_hosts)) {
			$old_host = $all_hosts[$host_id];
			unset($all_hosts[$host_id]); //delete the old host from the array
		} else {
			$old_host = self::empty_host();
		}
	
		if (! $delete) {
			$revised_data = self::review_data($new_host,$old_host); //updated any derived fields / change of state
			$all_hosts[$host_id] = $revised_data ; //add the new or updated host
		}
		$new_host = $revised_data;
		if (WPWhooshUtils::save_hosts( $all_hosts))  //save to database
			return $host_id; //return current/deleted host id
		else 
			return false; //update false
 	}

    static function reencrypt (&$new_host, $old_host, $flds) {
 		if (is_array($flds) && is_array($_POST) && array_key_exists('time',$_POST) 
    	&& array_key_exists('hash',$_POST)  && array_key_exists('hash2',$_POST)) {
			foreach ($flds as $fld) if (array_key_exists($fld, $new_host)) 
				if (is_array($old_host) && array_key_exists($fld, $old_host) && ($old_host[$fld] == $new_host[$fld]) ) 
					$new_host[$fld] = WPWhooshUpdater::reencrypt($old_host[$fld], $_POST['hash2'], $_POST['hash']);
				else
					$new_host[$fld] = WPWhooshUpdater::encrypt($new_host[$fld], $_POST['hash']);
		 	$new_host['cpanel_updated'] = $_POST['time'];
			$new_host['is_encrypted'] = true; 
		}
    }

	static function review_data($new_host,$old_host) {			
		if (($old_host['cpanel_url'] != $new_host['cpanel_url'])
		|| ($old_host['cpanel_user'] != $new_host['cpanel_user'])
		|| ($old_host['cpanel_password'] != $new_host['cpanel_password'])) {
	    	$new_host['cpanel_status'] = 'UNVERIFIED';
    	    $new_host['cpanel_verified_date'] = '';	
    	    $new_host['is_encrypted'] = false;	
	    	if (WPWhooshUpdater::can_encrypt() && WPWhooshUpdater::get_secret_enabled())
	    		self::reencrypt($new_host, $old_host, array('cpanel_user', 'cpanel_password') );
		}
	    return array_merge(array_intersect_key($old_host, self::empty_host()),$new_host);
	}

	static function sanitize_post($old_host = array()) {
		$host = array();
		$host['host_id'] = WPWhooshUtils::clean_name($_POST['host_id']);
		$host['host_name'] = WPWhooshUtils::clean_name($_POST['host_name']);
		$host['host_notes'] = WPWhooshUtils::clean_text($_POST['host_notes']);
		$host['host_ftp'] = array_key_exists('host_ftp',$_POST);
		$host['cpanel_provider'] = WPWhooshUtils::clean_text($_POST['cpanel_provider']);
		$host['cpanel_url'] = WPWhooshUtils::clean_url($_POST['cpanel_url']);		

		if ( array_key_exists('cpanel_user',$old_host) && (md5($old_host['cpanel_user']) == $_POST['cpanel_user']))
			$host['cpanel_user'] = $old_host['cpanel_user'];
		else
			$host['cpanel_user'] = WPWhooshUtils::clean_text($_POST['cpanel_user']);		
			
		if ( array_key_exists('cpanel_password',$old_host) && (md5($old_host['cpanel_password']) == $_POST['cpanel_password']))
			$host['cpanel_password'] = $old_host['cpanel_password'];	
		else
			$host['cpanel_password'] = WPWhooshUtils::clean_text($_POST['cpanel_password']);

		if ( array_key_exists('cpanel_updated',$old_host))
			$host['cpanel_updated'] = $old_host['cpanel_updated'];		

		return $host;
	}

	static function reset_vars() {
		wp_reset_vars(array('action',  'submit',  'host_id', 'host_name','host_notes', 'host_ftp',
			'cpanel_url', 'cpanel_user', 'cpanel_password', 'cpanel_status', 'cpanel_verified_date', 'cpanel_provider'));
	}

    static function toggle_postboxes() {
    	$slug = self::get_screen_id();
    	print <<< POSTBOXES
<script type="text/javascript">
//<![CDATA[
	jQuery(document).ready( function($) {
	// close postboxes that should be closed
		$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
		// postboxes setup
		postboxes.add_postbox_toggles('{$slug}');
	});
//]]>
</script>
POSTBOXES;
    }

    static function fetch_message($action) {
		$actions = empty($action) ? array() : (is_array($action) ? $action : array($action));
		$show_message = '' ;
		if (isset($_REQUEST['message']) && ! empty($_REQUEST['message'])) { 
			if ((count($actions) == 0)
			|| (array_key_exists('lastaction',$_REQUEST)  && in_array($_REQUEST['lastaction'],$actions))) {
				$message = urldecode($_REQUEST['message']);
				$style = strpos($message,'success') !== FALSE ? ' success' : (strpos($message,'fail') !== FALSE ? ' failure' : '');
				$show_message = sprintf ('<div id="message" class="%2$s"><div class="updated">%1$s</div></div>',$message,$style); 
				$_SERVER['REQUEST_URI'] = remove_query_arg(array('message'), $_SERVER['REQUEST_URI']);
			}
		}
		return $show_message;
    }  

	static function edit($host,$message='') {
		$existing = array_key_exists('host_id',$host) && !empty($host['host_id']);
		$host_id = $existing?esc_attr($host['host_id']):'';	
		$hassecret = WPWhooshUpdater::can_encrypt() && WPWhooshUpdater::get_secret_enabled() ? '1' : '0'; 
		$updated = $existing?$host['cpanel_updated']:'0';
		$action = $existing?'update':'save';
		$submit_text = __(($existing?'Update':'Add').' Host');
		$nonce = $existing?('edit-host_' . $host_id):'add-host';
		echo('<div id="poststuff" class="metabox-holder">');
        echo ('<h2>'.($existing?'Edit':'Add').' Host</h2>');
		echo('<p>Click on the <i>Help</i> tab on the top right of the page for more about hosts.</p>  ');
		echo('<div id="side-info-column">');
		do_meta_boxes(self::get_screen_id(), 'side', null); 
        echo('</div>');
        echo('<div id="post-body"><div id="post-body-content" >');
		echo('<form name="host_form" id="host_form" method="post" action="'.self::get_url($host_id, 'noheader').'">');
		echo('<input type="hidden" id="host_id" name="host_id" value="'.$host_id.'" />');
		echo('<input type="hidden" id="hassecret" name="hassecret" value="'.$hassecret.'" />');
		echo('<input type="hidden" id="secret" name="secret" value="" />');
		echo('<input type="hidden" id="action" name="action" value="'.$action.'" />');
		echo('<input type="hidden" id="updated" name="updated" value="'.$updated.'" />');
		wp_nonce_field( $nonce,'_wpnonce',true);
		wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false );
		wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false );
		do_meta_boxes(self::get_screen_id(), 'normal', null);
		echo('<p class="submit"><input type="submit" class="button-primary" name="save" id="save" accesskey="s" value="'.$submit_text.'" /></p>');
		echo('</form>');
 		echo('</div></div><br class="clear"/></div>');
 		print <<< SECRET
<div id="dialog-form" title="Security Check: Enter your API Secret">
<p>Changes to host details require the entry of your API Secret.</p>
<form id="popsecret" onSubmit="return false;"><fieldset><label for="yoursecret">API Secret</label><input type="password" name="yoursecret" id="yoursecret" size="16" maxlength="32" value="" class="text ui-widget-content ui-corner-all" />
</fieldset></form>
<p id="validateTips">Reminder: your API Secret is between 4 and 32 characters in length.</p>
</div>
SECRET;
	}    
    	
	static function id_panel($post,$metabox){
		$host = $metabox['args']['host'];
		$existing = $metabox['args']['existing'];
		$host_id= $existing?esc_attr($host['host_id']):'';
		$host_name= $existing?esc_attr($host['host_name']):'';
		$tip1 = self::$tooltips->tip('host_name');
		$host_notes= $existing?esc_attr($host['host_notes']):'';
		$tip2 = self::$tooltips->tip('host_notes');
		$cpanel_provider = new wpwhoosh_provider_list();
		$cpanel_provider_options = $cpanel_provider->get_dropdown( $host['cpanel_provider']);
		$tip3 = self::$tooltips->tip('cpanel_provider');
		$cpanel_url= $existing?$host['cpanel_url']:'';
		$tip4 = self::$tooltips->tip('cpanel_url');
		$cpanel_user= $existing?md5($host['cpanel_user']):'';
		$tip5 = self::$tooltips->tip('cpanel_user');
		$cpanel_password= $existing?md5($host['cpanel_password']):'';
		$tip6 = self::$tooltips->tip('cpanel_password');
		$host_ftp= ($existing && $host['host_ftp']) ? 'checked="checked"':'';
		$tip7 = self::$tooltips->tip('host_ftp');
		$cpanel_status= $existing?ucwords(strtolower(esc_attr($host['cpanel_status']))):'';
		$cpanel_verified_date= $existing?esc_attr($host['cpanel_verified_date']):'';
		$tip8 = self::$tooltips->tip('cpanel_status');
		$message = self::fetch_message(array('save','update'));
		print <<< ID_PANEL
<label>{$tip1}</label><input type="text" id="host_name" name="host_name" size="50" value="{$host_name}" /><br/> 
<label>{$tip2}</label><textarea id="host_notes" name="host_notes" cols="60" rows="2">{$host_notes}</textarea><br/> 
<label>{$tip3}</label>{$cpanel_provider_options}<br/>
<label>{$tip4}</label><input type="text" id="cpanel_url" name="cpanel_url" size="40" value="{$cpanel_url}"  /><br/> 
<label>{$tip5}</label><input type="password" id="cpanel_user" name="cpanel_user" size="8" value="{$cpanel_user}" /><br/> 
<label>{$tip6}</label><input type="password" id="cpanel_password" name="cpanel_password" size="40" value="{$cpanel_password}" /><br/>
<label>{$tip7}</label><input type="checkbox" id="host_ftp" name="host_ftp" {$host_ftp} value="1" /><br/>
<label>{$tip8}</label>{$cpanel_status} {$cpanel_verified_date} {$message}<br/>
ID_PANEL;
	}

	static function intro_panel($post){
		print <<< INTRO_PANEL
		<p>Your cPanel information is required so we can deploy sites to this host.</p> 
		<p>If you have set a API Secret and your host supports encryption then for security reasons you will be 
		prompted to enter the API sScret just before saving changes to your host details.</p>
		<p>A tooltip appears when you place your cursor over the labels below to give you more information about what to enter in each field.</p>
INTRO_PANEL;
	}
	
	static function copy_panel($post,$metabox) {
		$host = $metabox['args']['host'];
		$existing = $metabox['args']['existing'];
		$id = $host['host_id'];
		$nonce = wp_nonce_field('copy-host_'.$id,'_wpnonce',true,false);
		$action_url = self::get_url($id,'noheader');
		print <<< COPY_PANEL
<form method="POST" name="copyhost" action="{$action_url}">
{$nonce}
<input type="hidden" name="action" value="copy" />
<h4>New Host Name</h4> <input type="text" id="new_name" name="new_name" value="" />
<p>Specify the name of the new host which will become a copy of the host displayed on this page.</p>
<input type="submit" name="copy-host" value="Copy Host" />
</form>
COPY_PANEL;
	}

	static function help_list_panel() {
		$plugin = WPWHOOSH_FRIENDLY_NAME;
	    $url = WPWHOOSH_HOME_URL. '/tutorials/how-to-delete-host-details/';
		$result = <<< HELP_SAVE_PANEL
<p>On this page you can list hosts. The listing shows the name of the host, whether the login details were correct last time it
tried to connect, and the number of sites that have been whooshed onto the host.</p>
<p>Click on a host to edit its details. If a host has no sites then you can delete it. </p>
<ul>
<li><a target="_blank" href="{$url}">How To Delete A Host</a></li>
</ul>	
HELP_SAVE_PANEL;
		return $result;
	}

	static function help_save_panel() {
		$plugin = WPWHOOSH_FRIENDLY_NAME;
	    $url = WPWHOOSH_HOME_URL. '/tutorials/how-to-add-and-verify-host-details/';
		$result = <<< HELP_SAVE_PANEL
<p>On this page you can add a host. Enter a meaningful name for the host as once entered and saved the cPanel URL, user and
password are hidden for reasons of security.</p>
<p>When you click the <i>Add Host</i> button the plugin will try and log in to your host and will indicate whether or not this is 
successful.</p>
<ul>
<li><a target="_blank" href="{$url}">How To Add And Verify A Host</a></li>
</ul>	
HELP_SAVE_PANEL;
		return $result;
	}

	static function help_update_panel() {
		$plugin = WPWHOOSH_FRIENDLY_NAME;
	    $url = WPWHOOSH_HOME_URL. '/tutorials/how-to-edit-host-details/';
		$result = <<< HELP_UPDATE_PANEL
<p>On this page you can edit a host. Editing the cPanel URL, user and password is performed "blind" for reasons of security. To avoid
typos, cut and paste this information from the original email you received from the host when you first created the hosting account.</p>
<p>When you click the <i>Update Host</i> button the plugin will try and log in to your host and will indicate whether or not this is 
successful.</p>
<ul>
<li><a target="_blank" href="{$url}">How To Edit A Host</a></li>
</ul>	
HELP_UPDATE_PANEL;
		return $result;
	}

	static function help_trouble_panel() {
		$plugin = WPWHOOSH_FRIENDLY_NAME;
	    $url = WPWHOOSH_HOME_URL. '/tutorials/how-to-add-and-verify-host-details/';
		$result = <<< SUPPORT_PANEL
<h3>Common Problems</h3>
<p>The most common problem is typing in the password incorrectly. Please use cut and paste for sake of reliability.</p>		
SUPPORT_PANEL;
		return $result;
	}

}

class wpwhoosh_provider_list extends wpwhoosh_list {
 	function __construct($list='cpanel_provider') { 
 		parent::__construct($list, WPWhooshUtils::get_list($list));
	}
}

?>
