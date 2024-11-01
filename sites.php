<?php
class wpwhoosh_sites {
    private static $slug = 'wpwhoosh_sites';
    private static $parenthook = WPWHOOSH_PLUGIN_NAME;
    private static $screen_id;
    private static $list;
    private static $redacted;       
    private static $multiuser = false;
    private static $options = array('admin', 'header', 'legal', 'seo', 'social', 'database', 'analytics'); 
    private static $keys = array('site_id', 'site_url', 'template', 'host', 
				'site_admin_email', 'site_admin_name', 'site_admin_user', 'site_admin_password', 'site_permalinks', 'site_public',
				'site_status', 'site_ready',  'site_ready_date', 'site_deployed_date', 'site_log', 'site_snapshot');
	private static $tips = array(
			'header' => array ('heading' => 'Header', 'tip' => 'These values the appear in the header of the site are optional. They will be defaulted if you do not specify them.'),
			'legal' => array('heading' => 'Legal', 'tip' => 'These values that appear in the legal notices on the site. They will be defaulted if you do not specify them.'),
			'admin' => array('heading' => 'Admin', 'tip' => 'The WordPress Administrator settings are optional. They will be defaulted if you do not specify them.'), 
			'database' => array('heading' => 'Database', 'tip' => 'The WordPress database settings are optional and we stronly recommend you leave these blank for best security. They will be defaulted if you do not specify them. <em>N.B.</em>If you do decide to specify them, do not supply the account prefix to the user or database name e.g specify <i>ws451</i> not <i>account_ws451</i>.'), 
			'seo' => array('heading' => 'SEO', 'tip' => 'The SEO settings are optional. They will be defaulted if you do not specify them.'), 
			'social' => array('heading' => 'Social Media', 'tip' => 'We can add some Social Media Widgets to your sidebar or footer: such as recent tweets, a Facebook likebox and your most recent Flickr photos. The specific location of the widget depends on your choice of template however you can always drag them around later.'), 
			'analytics' => array('heading' => 'Analytics', 'tip' => 'Enter a value below if you want to include Google Analytics Tracking.'), 
			'site_name' => array('heading' => 'Site Name', 'tip' => 'Enter a short meaningful name for the site. e.g <i>My Site</i>.'), 
			'site_url' => array('heading' => 'Site URL', 'tip' => 'Enter the full URL where you want the installation to take place.'), 
			'template' => array('heading' => 'Template Selection', 'tip' => 'Choose your preferred template.<br/>You can click on the <i>Templates</i> link in the menu to see what each looks like.'), 
			'host' => array('heading' => 'Host Selection', 'tip' => 'Choose the host where you want to install the site. If you want to enter a new host then click on the Hosts menu on the left hand side.'),
			'site_title' => array('heading' => 'Site Title', 'tip' => 'The Site Title that will appear in the header of the installed website.'), 
			'site_description' => array('heading' => 'Site Tagline' , 'tip' => 'The Tagline that will appear in the header of the website either beneath or alongside the site title according to the template you have chosen.'),
			'site_legal_owner' => array('heading' => 'Legal Owner', 'tip' => 'The name which will appear in the copyright notice in the footer and the sample privacy statement and terms and conditions pages.'), 
			'site_legal_telephone' => array('heading' => 'Telephone', 'tip' => 'Enter a telephone number here if you want it to appear in the footer of the installed site.'), 
			'site_legal_email' => array('heading' => 'Owner\'s Email', 'tip' => 'Enter the email address here if you want it to appear in the privacy statement.'), 
			'site_legal_address' => array('heading' => 'Owner\'s Address' , 'tip' => 'The postal address of the legal entity that owns the site. This can appear in both the footer and in the privacy statement'),
			'site_legal_jurisdiction' => array('heading' => 'Jurisidiction' , 'tip' => 'The Courts that have jurisdiction over any legal disputes regarding this site. For example: <i>the state and federal courts in Santa Clara County, California</i>, or <i>the Law Courts of England and Wales</i>'),
			'site_legal_country' => array('heading' => 'Country' , 'tip' => 'The country who laws will apply to any legal dispute regarding use of this site. For example, <i>the United States</i>, or <i>England</i>'),
			'site_legal_updated' => array('heading' => 'Last Updated' , 'tip' => 'This will be defaulted as the day you deploy the site unless you choose to supply it now. For example, Oct 23rd, 2012'),
			'site_copyright_start' => array('heading' => 'Copyright Start' , 'tip' => 'The start year of the business appears in the copyright statement in the footer and an on the Terms and Conditions page.'),
			'site_permalinks' => array('heading' => 'Permalink Structure', 'tip' => 'Recommended permalink structures are post_id/postname, post_name and category/postname. The best solution for your site depends of the keywords in your site titles and categories and the stability of those titles and categories. If in doubt stick with the default.'), 
			'site_public' => array('heading' => 'Search Visibility', 'tip' => 'Click the checkbox if you want the site to be visible to search engines. Normally you will want to add some content before doing this.'),
			'site_db_host' => array('heading' => 'Database Host', 'tip' => 'Leave this blank or set to localhost. Only specify a value if your host runs MYSQL databases using a different host to the one that runs the web site.'),
			'site_db_name' => array('heading' => 'Database Name', 'tip' => 'Enter the name of the database. e.g wrdp3, or just leave blank and one will be generated.  Do not include the account prefix <i>account_</i>.'),
			'site_db_user' => array('heading' => 'Username', 'tip' => 'Enter the user ID that owns the DB e.g wrdp3, or just leave blank and one will be generated. Do not include the account prefix <i>account_</i>.'),
			'site_db_password' => array('heading' => 'Password', 'tip' => 'Enter the password for username alongside or just leave blank and a secure one will be generated.'),
			'site_admin_email' => array('heading' => 'Email', 'tip' => 'Enter the email address of the WordPress administrator of the site.'),
			'site_admin_name' => array('heading' => 'User Display Name', 'tip' => 'Enter the name that will appear as your Author Name on the site. This should be different from your username.'),
			'site_admin_user' => array('heading' => 'Username', 'tip' => 'Enter your preferred username of the WordPress administrator of the site. Choose something that is difficult to guess but memorable to you. The username can be kept completely private making your site more secure as a hacker would need to guess both your username and your password. This will be defaulted if not supplied but it is better if you choose something. For example, if your name is John Doe, you might choose jd9403 as the username.'),
			'site_admin_password' => array('heading' => 'Password', 'tip' => 'Enter your preferred password for the WordPress administrator of the site. This will be defaulted if not supplied.'),
			'site_twitter_name' => array('heading' => 'Twitter Name', 'tip' => 'To show your recent tweets, enter your twitter name without the @ at the beginning.'),
			'site_facebook_url' => array('heading' => 'Facebook URL', 'tip' => 'To show a Facebook likebox for your site, enter the URL of the Facebook page you want to visitors of the site to "like".'),
			'site_googleplus_url' => array('heading' => 'Google+ URL', 'tip' => 'Enter the URL of your Google+ Profile and Whoosh will substitute your URL if your chosen template includes a GooglePlus widget.'),
			'site_linkedin_url' => array('heading' => 'LinkedIn URL', 'tip' => 'Enter the URL of your LinkedIn Profile and Whoosh will substitute your URL if your chosen template includes a LinkedIn widget.'),
			'site_pinterest_url' => array('heading' => 'Pinterest URL', 'tip' => 'Enter the URL of the your Pinterest page and Whoosh will substitute your URL if your chosen template includes a Pinterest widget.'),
			'site_stumbleupon_url' => array('heading' => 'StumbleUpon URL', 'tip' => 'Enter the URL of the your StumbleUpon page and Whoosh will substitute your URL if your chosen template includes a StumbleUpon widget.'),
			'site_flickr_id' => array('heading' => 'Flickr ID', 'tip' => 'To show a gallery of your recent photos in a widget, enter the Flickr ID of your account from which you want to supply photos for the site. This will be of the form "12341234@N00".'),
			'site_ga_code' => array('heading' => 'Analytics Code', 'tip' => 'A Google Analytocs code is typically of the form UA-123456789.'),
			'check' => array('heading' => 'Check Access', 'tip' => 'The system will check your host detail, whether the site can be made available on the host and access to databases and file systems. The checks normally will take around 15 seconds.'),
			'install' => array('heading' => 'Pay and Install', 'tip' => 'The installation of your site will typically take 60-90 seconds'),
			'delete' => array('heading' => 'Delete Site', 'tip' => 'The deletion of your site will take 10-30 seconds'),
			'price' => array('heading' => 'Price', 'tip' => 'The charge for installing this template'),
			'balance' => array('heading' => 'Balance', 'tip' => 'Your balance last time we checked it and when the first credit will expire'),
			'save' => array('heading' => '&nbsp;', 'tip' => 'Click to save changes'),
			'copy' => array('heading' => 'New Site Name', 'tip' => 'Enter a unique name for the new site. You will have the opportunity to change the parameters for the new site later.'),
			'install_panel' => array('heading' => 'Install', 'tip' => 'This is the main tab where you make the site installation.'),
			'errors_panel' => array('heading' => 'Errors', 'tip' => 'This tab shows any errors that occurred during the last operation.'),
			'log_panel' => array('heading' => 'Log', 'tip' => 'This tab maintains a history of the actions taking place during this installation.'),
			'view_panel' => array('heading' => 'View', 'tip' => 'This tab displays the site when it has been deployed.'),
			'delete_panel' => array('heading' => 'Delete', 'tip' => 'On this tab you can delete the site that you deployed earlier.'),
			'draft' => array('heading' => 'Install', 'tip' => 'The buttons below remain grayed out until your fill in the site, template and host details above. Once you have done this the Check button will turn yellow to show it is enabled.'),
			'ready' => array('heading' => 'Install', 'tip' => 'On clicking the Check button below we run a series of checks such as making sure we can access your host and that there is no existing site at that URL that is in danger of being overwritten, and that you have enough credits in your account for the installation. <br/> If all the pre-flight checks pass then the installation button will become enabled.'),
			'verified' => array('heading' => 'Install', 'tip' => 'On clicking the Install button then your balance of credits will be reduced and the site installed, typically in 60 - 90 seconds.'),
			'live' => array('heading' => 'Install', 'tip' => 'Site has been installed. For more info about the installation see the Log And View tabs.'),
			'archive' => array('heading' => 'Install', 'tip' => 'Site details have been archived. For more info see the Log And View tabs.'),
			);
			
	private static $tooltips;


    private static function is_normal_mode() {
		return !self::$multiuser;
	}
	
    private static function get_slug() {
		return self::$slug;
	}

    private static function get_parenthook(){
		return self::$parenthook;
	}

    private static function get_screen_id(){
		return self::$screen_id;
	}

    private static function get_keys(){
		return self::$keys;
	}

 	public static function get_url($id='', $noheader = false) {
		return admin_url('admin.php?page='.self::get_slug().(empty($id) ? '' : ('&amp;id='.$id)).(empty($noheader) ? '' : '&amp;noheader=true'));
	}

 	private static function get_nonce_url($action, $id='', $noheader = false) {
		return WPWhooshUtils::admin_url(self::get_slug(), $action, $id, $noheader,true,'site'); 
	}

    private static function get_list() {
		return self::$list;
	}

	private static function set_list($list) {
		self::$list = $list;
	}	
	
	public static function init() {
		if (WPWhooshUtils::hosts_exist()) add_action('admin_menu',array(WPWHOOSH_SITES, 'admin_menu'));
	}

	public static function admin_menu() {
		self::$screen_id = add_submenu_page(self::get_parenthook(), __('List Sites'), __('Sites'), 'read', 
			self::get_slug(), array(WPWHOOSH_SITES,'controller'));
		add_action('load-'.self::get_screen_id(), array(WPWHOOSH_SITES, 'load_page'));
	}

	public static function load_page() {
		self::$tooltips = new wpwhoosh_tooltip(self::$tips);
		self::$redacted = __('Redacted', WPWHOOSH_PLUGIN_NAME);
		add_filter('screen_options_show_screen', array(WPWHOOSH_SITES,'enable_screen'),10,2);
		add_filter('screen_layout_columns', array(WPWHOOSH_SITES, 'screen_layout_columns'), 10, 2);	
		add_action ('admin_enqueue_scripts',array(WPWHOOSH_SITES, 'enqueue_styles'));
		add_action ('admin_enqueue_scripts',array(WPWHOOSH_SITES, 'enqueue_scripts'));		
		$id = array_key_exists('id', $_GET) ? $_GET['id'] : 0;
		if (! empty($id) ||  ( array_key_exists('action',$_REQUEST) && ($_REQUEST['action'] == 'add'))) 
			self::load_page_edit($id) ;
		else 
	    	self::load_page_list();	
    }

	public static function enable_screen($show_screen,$screen) {
		if ($screen->id == self::get_screen_id())
			return true;
		else
			return $show_screen;
	}	
	
	public static function screen_layout_columns($columns, $screen) {
		if (!defined( 'WP_NETWORK_ADMIN' ) && !defined( 'WP_USER_ADMIN' )) {
			if ($screen == self::get_screen_id()) {
				$columns[self::get_screen_id()] = 2;
			}
		}
		return $columns;
	}

	public static function enqueue_styles() {
		wp_enqueue_style('wsh-tooltip', WPWHOOSH_PLUGIN_URL.'tooltip.css', array(),WPWHOOSH_VERSION);	
		wp_enqueue_style('wsh-sites', WPWHOOSH_PLUGIN_URL.'sites.css', array(),WPWHOOSH_VERSION);	
		wp_enqueue_style('wp-jquery-ui-dialog');
 	}

	public static function enqueue_scripts() {
		wp_enqueue_script ('jquery-validate', WPWHOOSH_PLUGIN_URL.'jquery.validate.min.js', 
			array('jquery'), '1.11', true);
		wp_enqueue_script ('jquery-validate-additional', WPWHOOSH_PLUGIN_URL.'additional-methods.js',
			array('jquery','jquery-validate'), '1.11', true);
		wp_enqueue_script ('sites', WPWHOOSH_PLUGIN_URL.'sites.js', 
			array('jquery','jquery-validate','jquery-ui-core','jquery-ui-dialog'), WPWHOOSH_VERSION, true);
		wp_enqueue_script('crypto', WPWHOOSH_PLUGIN_URL.'md5.js',
			array(),'3.0.2',true);		
		wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');
		add_action('admin_footer-'.self::get_screen_id(), array(WPWHOOSH_SITES, 'write_footer_script'));
	}

	private static function load_page_list() {
		$plugin= WPWHOOSH_FRIENDLY_NAME;	
		require_once (WPWHOOSH_PLUGIN_DIR.'site-list-table.php');
		self::set_list(new wpwhoosh_site_list_table(self::get_slug()));
		add_filter ('manage_' . self::get_screen_id() . '_columns', array(self::get_list(),'get_columns'));			
		$current_screen = get_current_screen();
		if (method_exists($current_screen,'add_help_tab')) {		
			$current_screen->add_help_tab( array( 
				'id' => 'site_overview', 'title' => $plugin. ' List Actions', 'content' => self::help_list_panel()));		
		}
	}

	private static function load_page_edit($site_id) {
		$plugin= WPWHOOSH_FRIENDLY_NAME;
		$site = self::fetch($site_id);
		$existing = array_key_exists('site_id',$site) && !empty($site['site_id']);		
	    $disabled = (('LIVE'==$site['site_status']) || ('ARCHIVE'==$site['site_status'])) ? 'disabled="disabled"' : ''; 
		$callback_params = array ('site' => $site, 'existing' => $existing, 'disabled' => $disabled);
		add_meta_box('site-basics', __('1. Site Essentials *',WPWHOOSH_PLUGIN_NAME), array(WPWHOOSH_SITES, 'required_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box('site-options', __('2. Site Options',WPWHOOSH_PLUGIN_NAME), array(WPWHOOSH_SITES, 'optional_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box('site-actions', __('3. Site Installation',WPWHOOSH_PLUGIN_NAME), array(WPWHOOSH_SITES, 'action_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		$current_screen = get_current_screen();
		if (method_exists($current_screen,'add_help_tab')) {		
			$current_screen->add_help_tab( array( 
				'id' => 'site_overview', 'title' => $plugin. ' Site', 'content' => self::help_edit_panel()));		
			$current_screen->add_help_tab( array( 
				'id' => 'site_copy', 'title' => $plugin.' Utilities', 'content' => self::copy_panel($site_id)));	
		}
	}

	public static function controller() {
		WPWhooshUtils::check_permissions();
		$action =  array_key_exists('action',$_REQUEST) ? $_REQUEST['action'] : -1;
		if (array_key_exists('id', $_GET)  || ($action=='add') || ($action=='save'))
			self::single_action($action);
		else 
			self::bulk_actions($action);
	}

	private static function bulk_actions($action) {
		$message= array_key_exists('message',$_REQUEST) ? $_REQUEST['message'] : '';
		if (isset( $_REQUEST['cb'] ) ) {
			check_admin_referer( 'bulk-sites' );
			$ids =  (array) $_REQUEST['cb'];
			switch ($action) {
				case 'remove':  { $message = self::remove_rows($ids); break; }
				default: {}
			}
		} 
		WPWhooshUtils::clear_nonce();
		self::list_rows($message);
	}

	private static function single_action($action) {
		$id = array_key_exists('id', $_GET)  ? $_GET['id'] : 0;
		if ($site = self::fetch($id)) 	
			switch ($action) {		
				case 'add':
				case 'edit' : {	self::edit($site); break; }
				case 'save2' :
				case 'save' : { self::save(); break; }
				case 'update2': 		
				case 'update': { self::update($site); break; }			
				case 'archive': { self::archive($site); break; }	
				case 'remove': { self::remove($site); break; }
				case 'delete' : { self::delete($site); break; }
				case 'check' : { self::check($site); break; }
				case 'pay' : { self::pay($site); break; }
				case 'install' : { self::install($site); break; }								
				case 'copy' : { self::copy($site,$_POST['new_name']); break; }
				default: { 	wp_die(__('Unknown/invalid action for site.',WPWHOOSH_PLUGIN_NAME));  } 
			}
		else
			wp_die ('Site'.$id.' not found');
	}
	
	private static function fetch($site_id = 0) {
		if  (!empty($site_id) && ($site = WPWhooshUtils::get_site($site_id))) 
			return $site;
		else  
			return array_merge(array_fill_keys(self::get_keys(),''),
			 array( 'site_public' => false,  'site_permalinks' => '/%post_id%/%postname%', 
			 	'site_ready' => false, 'site_status' => 'DRAFT', 'site_log' => array() ));
	}

	private static function list_rows($message='') {
		$action_url = self::get_url();
		if (!empty($message)) $message = '<div class="updated"><p>'.$message.'<p></div>';
		print <<< SITES_LIST
<div class="wrap nosubsub">
<h2>Sites <a href="{$action_url}&action=add" class="button add-new-h2">Add New</a></h2>
{$message}
<form id="site-form" action="{$action_url}" method="post" >
SITES_LIST;
	    self::get_list()->prepare_items();
		self::get_list()->display();
		echo('<div id="ajax-response"></div></form></div>');
	}

	private static function remove_rows($ids) {
	    $deleted = 0;
		foreach ($ids as $id) if (($site=WPWhooshUtils::get_site($id)) && self::update_data( $site, true )) $deleted++; 
		$message = sprintf(__('Information about the %d selected site%2$s has been removed.',WPWHOOSH_PLUGIN_NAME), $deleted,$deleted>1 ? 's' : '');	
	    return $message;
	}
	
	private static function remove($site) {
		$site_name = WPWhooshUtils::clean_text($site['site_name']);	
  		$redir = WPWhooshUtils::next_url( 'remove-site_' . $site['site_id']); 	
		if (self::update_data($site, true )) 
			$message = sprintf(__('Information about %1$s has been removed.',WPWHOOSH_PLUGIN_NAME), $site_name);	
		else
			$message = sprintf(__('Failed to remove %1$s.',WPWHOOSH_PLUGIN_NAME), $site_name);
		$redir = add_query_arg( array('message' => urlencode($message)), $redir ); //add the message
    	wp_redirect( $redir ); 
    	exit;				
	}
		
	private static function delete($site) {
    	$site_name = $site['site_name'];
		$redir = WPWhooshUtils::next_url('delete-site_' . $site['site_id'], 'delete_wpnonce'); 
		$delete =  WPWhooshUpdater::update(false, array('action' => 'site_delete', 'timeout' => 60, 
			'body' => array( 'site' => $site, 'host' => WPWhooshUtils::get_host($site['host']))));
		if ($delete['success']) {
			$message = __('Site was deleted successfully.',WPWHOOSH_PLUGIN_NAME); 
			self::update_data( $site, true);
			$redir = remove_query_arg( array('id'), $redir); //go to list
		} else {
			$message = __('Site could not be deleted from your host.',WPWHOOSH_PLUGIN_NAME); 
			$redir = add_query_arg( array('id' => $site['site_id'], 'action' => 'edit'), $redir );	//go back to edit page	
			}
		$redir = add_query_arg( array('message' => urlencode($message)), $redir ); //add the message
    	wp_redirect( $redir ); 
    	exit;
	}

	private static function save() {
  		$redir = WPWhooshUtils::next_url('add-site'); 
		$site_name = WPWhooshUtils::clean_text($_POST['site_name']);
		$site = self::sanitize_post();
		if ( ! self::is_valid_site($site) ) {
			$message = __('Site information is incomplete. Hit the BACK key and enter the missing values.',WPWHOOSH_PLUGIN_NAME); 
			$redir = add_query_arg(  array('action' => 'add'), $redir ); //go back to add page
		} elseif ($site_id = self::update_data( $site)) {
			$message = __('Site was added successfully.',WPWHOOSH_PLUGIN_NAME); 
			$redir = add_query_arg( array('id' => $site_id, 'action' => 'edit'), $redir );	//go to edit page			
			WPWhooshUpdater::update(false); //rebuild cache to get latest balance
		} else {
			$message = __('Site failed to be added.',WPWHOOSH_PLUGIN_NAME); 
			$redir = add_query_arg( array('action' => 'add'), $redir );	//go back to edit page	
		}
		$redir = add_query_arg( array('message' => urlencode($message)), $redir ); //add the message 
	    wp_redirect( $redir ); 
	    exit;
	}

	private static function update($site) {
    	$site_name = WPWhooshUtils::clean_text($_POST['site_name']);
  		$redir = WPWhooshUtils::next_url( 'edit-site_' . $site['site_id']); 	
  		$site = self::sanitize_post();
  		if (self::is_valid_site($site) )
  			if (self::update_data( $site)) 
				$message = __('Site was updated successfully.',WPWHOOSH_PLUGIN_NAME);		
			else 
				$message = __('No changes were made to the site details.',WPWHOOSH_PLUGIN_NAME); 
		else 
			$message = __('All required fields must be supplied. Go back and edit the site info',WPWHOOSH_PLUGIN_NAME); 
		$redir = add_query_arg( array('action' => 'edit', 'message' => urlencode($message)), $redir );  
    	wp_redirect( $redir ); 
    	exit;
	}

	private static function archive($site) {
    	$site_name = WPWhooshUtils::clean_text($site['site_name']);
  		$redir = WPWhooshUtils::next_url( 'archive-site_' . $site['site_id']); 	
  		$site['site_status'] = 'ARCHIVE';
  		if (self::update_data( $site)) {
			$redir = remove_query_arg( array('id'), $redir);
			$message = __('Site details were archived successfully.',WPWHOOSH_PLUGIN_NAME);	
		} else 
			$message = __('The site details were not archived.',WPWHOOSH_PLUGIN_NAME); 
		$redir = add_query_arg( array('message' => urlencode($message)), $redir );  
    	wp_redirect( $redir ); 
    	exit;
	}

	private static function check_deployment_log(&$site) {
		if ( ! array_key_exists('site_log',$site) 
		|| empty($site['site_log'])) 
			$site['site_log'] = array();
		else
			if (!is_array($site['site_log'])) 
				$site['site_log'] = explode("\n",$site['site_log']);
	}
	
	private static function generate_copy_site_name($original) {
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
			$new_name = $root.$num;
		} while (WPWhooshUtils::get_site_by_name($new_name));
		return $new_name;  //return an unique name
	}

	private static function copy($site, $new_name='') {
		$site_id = $site['site_id'];
    	$redir = WPWhooshUtils::next_url('copy-site_' . $site_id); 
	    $new_name = WPWhooshUtils::clean_name($new_name); //clean_id
	    if (empty($new_name) || WPWhooshUtils::get_site_by_name($new_name)) 
	    	$new_name = self::generate_copy_site_name( $site['site_name']); //supply a valid name	    		
		$new_site = $site; //copy all fields
		$new_site['site_id'] = 0; //clear site ID				
		$new_site['site_name'] = $new_name; //give it the new name		
		$new_site['site_log'] = array(); //clear the log
		$new_site['site_deploy_start_date'] = ''; 
		$new_site['site_deploy_finish_date'] = ''; 
		$new_site['site_deployed_date'] = ''; 
		$new_site['site_status'] = 'DRAFT';
		$new_site['site_ready'] = false;
		$new_site['site_ready_date'] = ''; 
		$new_site['site_snapshot'] = '';  
		$new_site['site_db_host'] = ''; 
		$new_site['site_db_name'] = ''; 
		$new_site['site_db_user'] = ''; 		
		$new_site['site_db_password'] = ''; 
		if ($site_id = self::update_data($new_site))   //update and return new site ID
			$message = __('Site was copied successfully. You are now editing the copy',WPWHOOSH_PLUGIN_NAME);		
		else 
			$message = __('Failed to create copy.',WPWHOOSH_PLUGIN_NAME);		
		$redir = add_query_arg( 
			array('id' => urlencode($site_id),'action' => 'edit','message' => urlencode($message)), $redir );  
    	wp_redirect( $redir ); 
    	exit;
 	}
 
	private static function check($site) {
	    $site_url = $site['site_url'];	 
    	$site_id = $site['site_id'];
  		$redir = WPWhooshUtils::next_url( 'edit-site_' . $site_id);  
  		$site = self::sanitize_post();
  		if (self::is_valid_site($site) && self::update_data( $site) && self::verify( $site)) {
			$message = __('All checks passed successfully.',WPWHOOSH_PLUGIN_NAME); 
		} else {
			$message = __('Checks failed. See Errors tab for details.',WPWHOOSH_PLUGIN_NAME); 
		}
		self::update_data($site); //update fields generated during checking
		WPWhooshUpdater::update(false); //rebuild cache to get latest balance
		$redir = add_query_arg( array('action' => 'edit','message' => urlencode($message)), $redir ); //add the message 
    	wp_redirect( $redir ); 
    	exit;
	}
	
	private static function verify(&$site) {
		$host = WPWhooshUtils::get_host($site['host']);
		$checks = WPWhooshUpdater::update(false, array('action' => 'site_checks', 'timeout' => 90, 'body' => array( 'site' => $site, 'host' => $host)));
		if ($checks['success']) {
		 	$site = $checks['site']; //set some defaults during checking process
		 	$site['site_status'] = 'VERIFIED';
		 	$site['site_verified_date'] = date('Y-m-d h:i');
		 	$site['site_errors'] = array();
		 	return true;
		} else {
		 	$site['site_status'] = 'INVALID';
		 	$site['site_verified_date'] = '';
		 	$site['site_errors'] = $checks['errors'];
		 	$site['site_log'] = array_merge(is_array($checks['log'])?$checks['log']:array(), (array)$site['site_log']); 	
			return false;
		}
	}

	private static function pay($site) {	 
    	$site_id = $site['site_id'];
  		$redir = WPWhooshUtils::next_url( 'edit-site_' . $site_id);   				
    	if (self::debit( $site)) {
			$message = __('Your credit balance has been updated to pay for this installation at %1$s',WPWHOOSH_PLUGIN_NAME); 
		} else {
			$message = __('Your credit balance could not be updated',WPWHOOSH_PLUGIN_NAME); 
		}
		self::update_data( $site); //update status
		$redir = add_query_arg( array('action' => 'edit','message' => urlencode($message)), $redir ); //add the message 
    	wp_redirect( $redir ); 
    	exit;
	}
	
	private static function debit(&$site) {
		$debit =  WPWhooshUpdater::update(false, array('action' => 'site_debit', 
			'body' => array( 'site' => $site, 'host' => WPWhooshUtils::get_host($site['host']))));
		if ($debit['success']) {
		 	$site['site_status'] = 'PAID';
		 	$site['site_paid_date'] = date('Y-m-d h:i');
		 	$site['site_reference'] = $debit['reference'];
			return true;
		} else {
		 	$site['site_status'] = 'UNPAID';
		 	$site['site_paid_date'] = '';
			return false;
		}
	}

	private static function install($site) {	 
    	$site_id = $site['site_id'];
		$redir = WPWhooshUtils::next_url( 'install-site_' . $site_id, 'install_wpnonce'); 
    	if (self::pay_and_deploy( $site)) {
			$message = __('Site was installed successfully.',WPWHOOSH_PLUGIN_NAME); 
		} else {
			$message = __('Site failed to be installed. See Errors tab for details.',WPWHOOSH_PLUGIN_NAME); 
		}
		self::update_data( $site); //update status and date
		WPWhooshUpdater::update(false); //rebuild cache to get latest balance
		$redir = add_query_arg( array('action' => 'edit', 'message' => urlencode($message)), $redir );	//go back to edit page	
    	wp_redirect( $redir ); 
    	exit;
	}

	private static function pay_and_deploy(&$site) {
		$host = WPWhooshUtils::get_host($site['host']);
		//pay first to make sure payment is only taken once if install fails
		return self::take_payment($site, $host) && self::deploy($site, $host);  
    }
	
	private static function take_payment(&$site, $host) {
		$paid = false;
		$args = array('action' => 'site_debit', 'timeout' => 30, 'body' => array( 'site' => $site, 'host' => $host));
		$account = WPWhooshUpdater::update(false, $args);
		if ($account['success']) {
		 	$site['site_reference'] = $account['site_reference'] ;
			$paid = true;
		} else {
		 	$site['site_status'] = 'FAILED';
		 	$site['site_errors'] = $account['errors'];
		 	$site['site_log'] = array_merge(is_array($account['log'])?$account['log']:array(), (array)$site['site_log']); 	
			$paid = false;
		}
		self::update_data($site);
		return $paid;
	}

	private static function send_login_email($site) {
		$headers = 'Content-type: text/html';
		$to = $site['site_admin_email'];
		$subj = sprintf('WordPress installation on %1$s', $site['site_url']);
		$content = sprintf('<p>Your login details are:</p><p>URL: %1$s</p><p>User: %2$s</p><p>Password: %3$s</p>',
			$site['site_url'].'/wp-admin/', $site['site_admin_user'], $site['site_admin_password']);
		try {
			wp_mail( $site['site_admin_email'],$subj, $content, $headers);
		} catch (Exception $e) {
			error_log(sprintf ('Failed to send to send WordPress login email for %1$s to %2$s: %3$s ', 
					$subj, $to, $content));
		}
	}

	private static function deploy(&$site, $host) {
		$args = array('action' => 'site_deploy', 'timeout' => 300, 'body' => array( 'site' => $site, 'host' => $host));
		self::check_deployment_log($site);
		$site['site_deploy_finish_date'] = '';
		$site['site_deploy_start_date'] = date('Y-m-d H:i:s e');
		$install = WPWhooshUpdater::update(false, $args);
		$site['site_deploy_finish_date'] = date('Y-m-d H:i:s e');
		$site['site_log'] = array_merge(
		 	(array)(__('**** Started (your server time) ',WPWHOOSH_PLUGIN_NAME).$site['site_deploy_start_date'].'****'),is_array($install['log'])?$install['log']:array(), 
		 	(array)(__('**** Finished (your server time) ',WPWHOOSH_PLUGIN_NAME).$site['site_deploy_finish_date'].'****'),(array)$site['site_log']); 	
		if (array_key_exists('site_reference',$install)) $site['site_reference'] = $install['site_reference']; 
		if (array_key_exists('creds',$install)) $site = array_merge($site, $install['creds']);
		if ($install['success']) {
		 	$site['site_errors'] = array();
			self::send_login_email($site); //notify administrator of installation
		 	self::make_redactions($site); //remove user names, logins and passwords - just leave email address 
		 	$site['site_status'] = 'LIVE';
		 	$site['site_deployed_date'] = date('Y-m-d h:i');
			$site['site_snapshot'] = $install['snapshot'];
			return true;
		} else {
		 	$site['site_status'] = 'FAILED';
		 	$site['site_errors'] = $install['errors'];
		 	$site['site_deployed_date'] = '';
			$site['site_snapshot'] = '';
			return false;
		}
	}
	
	private static function make_redactions(&$site) {
		$redactions = array('site_db_prefix', 'site_db_host','site_db_name', 'site_db_user', 'site_db_password', 'site_admin_user', 'site_admin_password');
		foreach ($redactions as $fld) $site[$fld] = self::$redacted;
	}

	private static function update_data( $new_site, $delete=false) { 
		$old_site = array();
		//generate a site ID if it is a brand new site
		if (!array_key_exists('site_id',$new_site) || empty($new_site['site_id'])) $new_site['site_id'] = WPWhooshUtils::generate_site_id();    

		$site_id = $new_site['site_id'];
		$all_sites = WPWhooshUtils::get_sites (false) ; //fetch from database - not the cache
		if (WPWhooshUtils::get_site($site_id)) {
			$old_site = $all_sites[$site_id];
			unset($all_sites[$site_id]); //delete the old site
		}
	
		if (! $delete) {
			$revised_data = self::review_data($new_site,$old_site);
			$all_sites[$site_id] = $revised_data ; //add the new or updated site
		}
		if (WPWhooshUtils::save_sites( $all_sites))  //save to database
			return $site_id; //return current/deleted site id
		else 
			return false; //update false
 	}

    private static function is_site_ready($site) {
		return $site['site_name'] && $site['site_url'] && $site['template'] && $site['host']
			&& ($host = WPWhooshUtils::get_host($site['host'])) && ('VERIFIED'==$host['cpanel_status']);
    }

	private static function review_data($new_data,$old_data) {
		if (!array_key_exists('site_ready',$old_data)) {  //new site
			$old_data['site_status'] = 'DRAFT';	
			$old_data['site_ready_date'] = '';
			$old_data['site_ready'] = false;
		}
		
		if (($old_data['site_url'] != $new_data['site_url']) 
		|| ($old_data['template'] != $new_data['template'])
		|| ($old_data['host'] != $new_data['host'])) { //need to re-verify as basics have changed
    	    $new_data['site_host'] = WPWhooshUtils::clean_domain($new_data['site_url']);
			$new_data['site_domain'] = WPWhooshUtils::clean_domain($new_data['site_url'],true);
    	    $new_data['site_folder'] = WPWhooshUtils::clean_path($new_data['site_url']);
			$new_data['site_recommended_plugins'] = WPWhooshTemplateFactory::get_template_attribute($new_data['template'],'plugins');	
			$old_data['site_status'] == 'DRAFT';
			$old_data['site_ready_date'] = '';
			$old_data['site_ready'] = false;	
		}	

		$new_data['site_ready'] = self::is_site_ready($new_data);

		if (($old_data['site_status'] == 'DRAFT') 
		&& ($old_data['site_ready']==false) 
		&& ($new_data['site_ready']==true)) {
			$new_data['site_ready_date'] = date('c');		
			$new_data['site_status'] = 'READY';
			$new_data['site_recommended_plugins'] = WPWhooshTemplateFactory::get_template_attribute($new_data['template'],'plugins');	
		}
		elseif (($old_data['site_ready']==true) 
		&& ($new_data['site_ready']==false)) {
			$new_data['site_ready_date'] = '';
			$new_data['site_status'] = 'DRAFT';			
		}
		self::check_deployment_log($new_data); //make sure log is an array
		$new_data['site_updated'] = date('j M Y h:i:s');		
		return array_merge($old_data,$new_data);
	}

	private static function is_valid_site($site) {
		return array_key_exists('site_name',$site) && ! empty($site['site_name'])
			&& 	array_key_exists('site_url',$site) && ! empty($site['site_url'])
			&& 	array_key_exists('template',$site) && ! empty($site['template'])
			&& 	array_key_exists('host',$site) && ! empty($site['host']) ;
	}

	private static function sanitize_post() {
		foreach ($_POST as $key => $val) $_POST[$key] = WPWhooshUtils::clean_text($val);
		$_POST['site_name'] = WPWhooshUtils::clean_name($_POST['site_name']);
		$_POST['site_url'] = WPWhooshUtils::clean_url($_POST['site_url']);
		$_POST['site_admin_email'] = WPWhooshUtils::clean_email($_POST['site_admin_email']);		
		$_POST['site_admin_user'] = WPWhooshUtils::clean_id($_POST['site_admin_user']);
		$_POST['site_admin_name'] = WPWhooshUtils::clean_name($_POST['site_admin_name']);
		return $_POST;
	}
	
    public static function write_footer_script() {
    	$slug = self::get_screen_id();
    	print <<< POSTBOXES
<script type="text/javascript">
//<![CDATA[
	jQuery(document).ready( function($) {
		$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
		postboxes.add_postbox_toggles('{$slug}');
	});
//]]>
</script>
POSTBOXES;
    }
    
    private static function add_toggle() {
    		print <<< TOGGLE_PANEL
<script type="text/javascript">
//<![CDATA[
		jQuery(document).ready( function($) { //when the toggle is clicked then hidden class is added or removed
			$('.toggler').click( function() { $('.togglee').toggleClass('hidden'); });
		});
//]]>
</script>    
TOGGLE_PANEL;
	}

	private static function edit($site) {
		$existing = array_key_exists('site_id',$site) && !empty($site['site_id']);
		$site_id = $existing?$site['site_id']:'';	
		$submit_text = __(($existing?'Update':'Add').' Site',WPWHOOSH_PLUGIN_NAME);
		$action = sprintf('action="%1$s"',self::get_url($site_id, 'noheader'));
		if (('LIVE'==$site['site_status']) || (('ARCHIVE'==$site['site_status']))) {
			$title = __('Review Site Installation',WPWHOOSH_PLUGIN_NAME);
			$secret_message=__('Enter your API Secret to confirm deletion of this site.',WPWHOOSH_PLUGIN_NAME);
		} else {
        	$title = __($existing?ucwords('Installing '.$site['site_name']):'Add Site',WPWHOOSH_PLUGIN_NAME);
			$secret_message=__('Enter your API Secret to connect to your host.',WPWHOOSH_PLUGIN_NAME);
		}
        echo ('<h2>'.$title.'</h2>');
		echo('<form name="site_form" id="site_form" method="post" '.$action.'>');
		echo('<input type="hidden" name="site_id" value="'.$site_id.'" />');
		echo('<input type="hidden" id="hassecret" name="hassecret" value="'.(WPWhooshUpdater::get_secret_enabled()?'1':'0').'" />');
		echo('<input type="hidden" id="secret" name="secret" value="" />');
		echo('<input type="hidden" name="action" value="'.($existing?'update':'save').'" />');
		wp_nonce_field( $existing?('edit-site_' . $site_id):'add-site','_wpnonce',true);
		if (('PAID'==$site['site_status']) || ('FAILED'==$site['site_status'])  || ('VERIFIED'==$site['site_status'])) 
			wp_nonce_field('install-site_' . $site_id, 'install_wpnonce',true);
		if (('LIVE'==$site['site_status']) || ('ARCHIVE'==$site['site_status'])) 
			wp_nonce_field('delete-site_' . $site_id, 'delete_wpnonce',true);
		wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false );
		wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false );
		echo('<div id="poststuff">');
		echo('<div class="error" style="display:none;"><span></span>.<br clear="all"/></div>');       
		echo('<div id="post-body" class="metabox-holder"><div id="post-body-content">');
		do_meta_boxes(self::get_screen_id(), 'normal', null);
 		echo('</div></div><br class="clear"/></div>');
		echo('</form>');
 		print <<< SECRET
<div id="dialog-form" title="Security Check: Enter your API Secret">
<p>{$secret_message}</p>
<form id="popsecret" onSubmit="return false;" ><fieldset><label for="yoursecret">API Secret</label><input type="password" name="yoursecret" id="yoursecret" size="16" maxlength="32" value="" class="text ui-widget-content ui-corner-all" />
</fieldset></form>
<p id="validateTips">Reminder: your API Secret is between 4 and 32 characters in length.</p>
</div>
SECRET;
	}    

    private static function fetch_message($action) {
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

	public static function intro_panel($post){
		print <<< INTRO_PANEL
<p>Firstly fill in the site basics: the name of the site, the URL, what template you want and where you are going to host it.</p>
<p>Next, and this is entirely optional, is to tweak the settings such as site title, tagline, database and WordPress usernames and passwords, social media widgets, analytics.</p>
<p>Finally, run the pre-flight checks to prove everything is accessible and finally to push the button for your professional WordPress installation in less than a minute.</p>		
INTRO_PANEL;
	}
	
    public static function required_panel($post,$metabox) {
		$site = $metabox['args']['site'];
		$existing = $metabox['args']['existing'];
		$disabled = $metabox['args']['disabled'];
		print self::id_panel($site,$existing,$disabled);
    }

    public static function optional_panel($post,$metabox) {
		$site = $metabox['args']['site'];
		$existing = $metabox['args']['existing'];		
		$disabled = $metabox['args']['disabled'];
		$action = $existing?'update2':'save2';
		$message = self::fetch_message(array('save2','update2'));
		$save_button = sprintf('<label>%1$s</label><input type="submit" class="whoosh" name="%2$s" id="%2$s" value="save" />%3$s<br/>', 
			self::$tooltips->tip('save'), $action, $message);
		$template_panels = explode(',',WPWhooshTemplateFactory::get_template_attribute($site['template'],'options'));
		$tab = 0;
		$tabs = '';
		$content = '';
		foreach (self::$options as $panel) if (in_array($panel,$template_panels)) {
			$func = array(__CLASS__,$panel.'_panel');
			$tab += 1;
			$tabs .= sprintf('<li class="tab tab%1$d">%2$s</li>', $tab,  self::$tooltips->tip($panel));			
			$content .= sprintf('<div class="tab%1$d"><div class="tab-content">%2$s</div></div>', $tab ,
				is_callable($func) ? call_user_func($func,$site,$disabled) : '');
		}
		if ($tab == 0) $disabled = ' disabled="disabled"';
		printf('<ul class="site-metabox-tabs">%1$s</ul>%2$s%3$s', $tabs, $content, empty($disabled) ? $save_button : '');
    }

    public static function action_panel($post,$metabox) {
		$site = $metabox['args']['site']; 		 
	    $site_status=$site['site_status'];
		$disabled = $metabox['args']['disabled'];	 
	    if (empty($disabled)) $disabled = 'DRAFT'==$site_status ? 'disabled="disabled"' : '';
	    $class_disabled = 'DRAFT'==$site_status ? 'disabled' : '';
		$install_panel = self::install_panel($site,$disabled);
		$errors_panel = self::errors_panel($site,$disabled);
		$errors_class = strpos($errors_panel,'site_errors') !== FALSE ? ' has_error' : '';
		$log_panel = self::log_panel($site,$disabled);
		$view_panel = self::view_panel($site,$disabled);
		$delete_panel = self::delete_panel($site,$disabled);
		$tip1 = self::$tooltips->tip(strtolower($site_status));		
		if (empty($tip1)) $tip1 = self::$tooltips->tip('install_panel');
		$tip2 = self::$tooltips->tip('errors_panel');
		$tip3 = self::$tooltips->tip('log_panel');
		$tip4 = self::$tooltips->tip('view_panel');
		$tip5 = self::$tooltips->tip('delete_panel');
		$hidden = 'LIVE'==$site_status ? '' : ' hidden';
		print <<< ACTION_PANEL
<ul class="site-metabox-tabs {$class_disabled}">
<li class="tab tab1">{$tip1}</li>
<li class="tab tab2">{$tip2}</li>
<li class="tab tab3">{$tip3}</li>
<li class="tab tab4">{$tip4}</li>
<li class="tab tab5{$hidden}">{$tip5}</li>
</ul>
<div class="tab1"><div class="tab-content">{$install_panel}</div></div>
<div class="tab2"><div class="tab-content">{$errors_panel}</div></div>
<div class="tab3"><div class="tab-content">{$log_panel}</div></div>
<div class="tab4"><div class="tab-content">{$view_panel}</div></div>
<div class="tab5{$hidden}"><div class="tab-content">{$delete_panel}</div></div>
ACTION_PANEL;
    }

	private static function id_panel($site,$existing,$disabled){	
		$tip1 = self::$tooltips->tip('site_name');
		$tip2 = self::$tooltips->tip('site_url');
		$tip3 = self::$tooltips->tip('template');	
		$tip4 = self::$tooltips->tip('host');	
	    $site_name= $existing?$site['site_name']:'';
	    $site_url= $existing?$site['site_url']:'';
	    $template_list = new wpwhoosh_template_list();
		$templates_dropdown = $template_list->get_dropdown($existing?$site['template']:'',$disabled); 	   
	    $host_list = new wpwhoosh_host_list();	
		$hosts = $host_list->get_dropdown($existing?$site['host'] : '',$disabled); 	    	 
		$action = $existing?'update':'save';
		$message = self::fetch_message(array('save','update'));
		$save_button = empty($disabled) ?  
			sprintf('<label>%1$s</label><input type="submit" class="whoosh" name="%2$s" id="%2$s"  value="save" />%3$s<br/>', 
				self::$tooltips->tip('save'), $action, $message) : '';
		$result = <<< ID_PANEL
<label>{$tip1}</label><input type="text" name="site_name" size="30"  class="required" minlength="1" value="{$site_name}" {$disabled}/><br/>
<label>{$tip2}</label><input type="text" name="site_url" size="50" class="required defaultInvalid url" value="{$site_url}" {$disabled} /><br/>
<label>{$tip3}</label>{$templates_dropdown}<br/>
<label>{$tip4}</label>{$hosts}<br/>
{$save_button}
ID_PANEL;
		return $result;
	}

	private static function header_panel($site, $disabled =''){
		$tip1 = self::$tooltips->tip('site_title');
		$tip2 = self::$tooltips->tip('site_description');		
		$site_title = stripslashes($site['site_title']);	
		$site_description = stripslashes($site['site_description']);	
		$result =  <<< HEADER_PANEL
<label>{$tip1}</label><input type="text" name="site_title" size="30" value="{$site_title}" {$disabled}/><br/>
<label>{$tip2}</label><input type="text" name="site_description" size="50" value="{$site_description}" {$disabled}/><br/>
HEADER_PANEL;
		return $result;
	}

	private static function legal_panel($site, $disabled =''){
		$tip1 = self::$tooltips->tip('site_legal_owner');
		$tip2 = self::$tooltips->tip('site_legal_telephone');
		$tip3 = self::$tooltips->tip('site_legal_email');		
		$tip4 = self::$tooltips->tip('site_legal_address');
		$tip5 = self::$tooltips->tip('site_legal_jurisdiction');
		$tip6 = self::$tooltips->tip('site_legal_country');
		$tip7 = self::$tooltips->tip('site_legal_updated');
		$tip8 = self::$tooltips->tip('site_copyright_start');		
		$result =  <<< FOOTER_PANEL
<label>{$tip1}</label><input type="text" name="site_legal_owner" size="20" value="{$site['site_legal_owner']}" {$disabled}/><br/>
<label>{$tip2}</label><input type="text" name="site_legal_telephone" size="20" value="{$site['site_legal_telephone']}" {$disabled}/><br/>
<label>{$tip3}</label><input type="text" name="site_legal_email" size="50" value="{$site['site_legal_email']}" {$disabled}/><br/>
<label>{$tip4}</label><input type="text" name="site_legal_address" size="50" value="{$site['site_legal_address']}" {$disabled}/><br/>
<label>{$tip5}</label><input type="text" name="site_legal_jurisdiction" size="50" value="{$site['site_legal_jurisdiction']}" {$disabled}/><br/>
<label>{$tip6}</label><input type="text" name="site_legal_country" size="12" value="{$site['site_legal_country']}" {$disabled}/><br/>
<label>{$tip7}</label><input type="text" name="site_legal_updated" size="12" value="{$site['site_legal_updated']}" {$disabled}/><br/>
<label>{$tip8}</label><input type="text" name="site_copyright_start" size="5" value="{$site['site_copyright_start']}" {$disabled}/><br/>
FOOTER_PANEL;
		return $result;
	}

	private static function seo_panel($site, $disabled =''){
		$tip1 = self::$tooltips->tip('site_permalinks');	
		$tip2 = self::$tooltips->tip('site_public');
		$checked = $site['site_public'] ? ' checked="checked"' : '';
		$site_permalinks = new wpwhoosh_permalinks_list();
		$site_permalinks_options = $site_permalinks->get_dropdown( $site['site_permalinks'],$disabled);		
		$result =  <<< SEO_PANEL
<label>{$tip1}</label>{$site_permalinks_options}<br/> 
<label>{$tip2}</label><input type="checkbox" name="site_public" {$checked} value="1" {$disabled}/><br/>
SEO_PANEL;
		return $result;
	}

	private static function database_panel($site, $disabled =''){
		$tip1 = self::$tooltips->tip('site_db_host');
		$tip2 = self::$tooltips->tip('site_db_name');
		$tip3 = self::$tooltips->tip('site_db_user');
		$tip4 = self::$tooltips->tip('site_db_password');
		$result = <<< DATABASE_PANEL
<label>{$tip1}</label><input type="text" name="site_db_host" size="20" value="{$site['site_db_host']}" {$disabled}/><br/>
<label>{$tip2}</label><input type="text" name="site_db_name" size="15" value="{$site['site_db_name']}" {$disabled}/><br/>
<label>{$tip3}</label><input type="text" name="site_db_user" size="15" value="{$site['site_db_user']}" {$disabled}/><br/>
<label>{$tip4}</label><input class="password" type="password" name="site_db_password" size="10" value="{$site['site_db_password']}" {$disabled}/><br/>
DATABASE_PANEL;
		return $result;
	}

	private static function admin_panel($site, $disabled =''){
		$tip1 = self::$tooltips->tip('site_admin_email');
		$tip2 = self::$tooltips->tip('site_admin_name');
		$tip3 = self::$tooltips->tip('site_admin_user');
		$tip4 = self::$tooltips->tip('site_admin_password');
		$current_user = wp_get_current_user();
		if (empty($site['site_admin_email'])) $site['site_admin_email'] = $current_user->user_email;
		if (empty($site['site_admin_name'])) $site['site_admin_name'] = $current_user->display_name;
		$result =  <<< ADMIN_PANEL
<label>{$tip1}</label><input type="text" name="site_admin_email" size="25" value="{$site['site_admin_email']}" class="required email" {$disabled}/><br/>
<label>{$tip2}</label><input type="text" name="site_admin_name" size="25" value="{$site['site_admin_name']}" class="required" {$disabled}/><br/>
<label>{$tip3}</label><input type="text" name="site_admin_user" size="25" value="{$site['site_admin_user']}" {$disabled}/><br/>
<label>{$tip4}</label><input type="password" class="password" name="site_admin_password" size="10" value="{$site['site_admin_password']}" {$disabled}/><br/>
ADMIN_PANEL;
		return $result;
	}

	private static function social_panel($site, $disabled =''){
		$tip1 = self::$tooltips->tip('site_twitter_name');
		$tip2 = self::$tooltips->tip('site_facebook_url');
		$tip3 = self::$tooltips->tip('site_googleplus_url');
		$tip4 = self::$tooltips->tip('site_linkedin_url');
		$tip5 = self::$tooltips->tip('site_pinterest_url');
		$tip6 = self::$tooltips->tip('site_stumbleupon_url');
		$tip7 = self::$tooltips->tip('site_flickr_id');
		$result =  <<< SM_PANEL
<label>{$tip1}</label><input type="text" name="site_twitter_name" size="15" value="{$site['site_twitter_name']}" {$disabled}/><br/>
<label>{$tip2}</label><input type="text" name="site_facebook_url" size="60" value="{$site['site_facebook_url']}" {$disabled}/><br/>
<label>{$tip3}</label><input type="text" name="site_googleplus_url" size="60" value="{$site['site_googleplus_url']}" {$disabled}/><br/>
<label>{$tip4}</label><input type="text" name="site_linkedin_url" size="60" value="{$site['site_linkedin_url']}" {$disabled}/><br/>
<label>{$tip5}</label><input type="text" name="site_pinterest_url" size="60" value="{$site['site_pinterest_url']}" {$disabled}/><br/>
<label>{$tip6}</label><input type="text" name="site_stumbleupon_url" size="60" value="{$site['site_stumbleupon_url']}" {$disabled}/><br/>
<label>{$tip7}</label><input type="text" name="site_flickr_id" size="12" value="{$site['site_flickr_id']}" {$disabled}/><br/>
SM_PANEL;
		return $result;
	}

	private static function analytics_panel($site, $disabled =''){
		$tip = self::$tooltips->tip('site_ga_code');
		$result =  <<< ANALYTICS_PANEL
<label>{$tip}</label><input type="text" name="site_ga_code" size="12" value="{$site['site_ga_code']}" {$disabled}/><br/>
ANALYTICS_PANEL;
		return $result;
	}

	private static function errors_panel($site, $disabled ='') {
		$errors_log='';
		$log = $site['site_errors'];	
		if (is_array($log)) {
			foreach ($log as $line) $errors_log .= $line ."\n";	
		} else
				$errors_log = $log;
		$result = <<< ERROR_PANEL
<textarea id="site_errors" name="site_errors" cols="90" rows="3" readonly="readonly" {$disabled}>{$errors_log}</textarea>
ERROR_PANEL;
		return empty($errors_log) ? '<p>No errors</p>' : $result;
}

	private static function log_panel($site, $disabled ='') {
	    $site_status=$site['site_status'];
		$log='';
		$site_log = $site['site_log'];	
		if (is_array($site_log)) {
			foreach ($site_log as $line) $log .= $line ."\n";	
		} else
				$log = $site_log;
		$result = <<< LOG_PANEL
<textarea id="site_log" name="site_log" cols="90" rows="20" readonly="readonly" $disabled>{$log}</textarea>
LOG_PANEL;
		return (('DRAFT'==$site_status) || empty($log)) ?  sprintf('<p>%1$s</p>',__('There are no log messages right now.',WPWHOOSH_PLUGIN_NAME)) : $result;
}

	private static function view_panel($site, $disabled =''){
		$site_status= $site['site_status'];
		$url = $site['site_url'];
		$result = <<< VIEW_PANEL
<p>If you have visited the site before your browser may have cached the page.</p>
<p>To be sure you are seeing the latest page then <a rel="external" href="{$url}">open the site</a> in a new tab and click refresh a few times.</p>
<iframe width="960" height="960" scrolling="auto" src="{$url}"></iframe>
VIEW_PANEL;
		return empty($disabled) ? 'This tab is currently empty.' : $result;
	}
	
	private static function install_panel($site, $disabled ='') {
		$site_status = $site['site_status'];
		$site_url = $site['site_url'];
		$check_disabled = $disabled;
		$install_disabled = $disabled;
		if (empty($check_disabled) && ('READY'!=$site_status) && ('INVALID'!=$site_status)) 
			$check_disabled = 'disabled="disabled"';
		if (empty($install_disabled) && (('READY'==$site_status) || ('INVALID'==$site_status) || ('DRAFT'==$site_status)))
			 $install_disabled = 'disabled="disabled"';		
		$check_class = $check_disabled ? 'dimmed':'whoosh';
		$install_class = $install_disabled ? 'dimmed':'whoosh';		
		$price_info = sprintf('<span>1 %1$s</span>', __('credit',WPWHOOSH_PLUGIN_NAME));
		$balance = WPWhooshUpdater::get_balance();
		$bal_info = (is_array($balance) && array_key_exists('balance',$balance)) ?
			 sprintf('<span>%1$d credits %2$s</span>', 
				$balance['balance'], 
				$balance['balance'] > 0 ? (' expiring from '.date('D, d M Y', strtotime($balance['expiry']))):'') :
				__('Your balance is not currently available',WPWHOOSH_PLUGIN_NAME) ;
		$check_message = self::fetch_message('check');				
		$install_message = self::fetch_message('install'). ('LIVE'==$site_status? sprintf(' <a target="_blank" rel="external" href="%1$s">Click to view the site</a>',$site_url):'');		
		$awaiting = '<img id="awaiting%1$s" class="working" width="20" height="20" src="'.WPWHOOSH_IMAGES_URL.'waiting-32.gif" alt="%2$s"/>';		
		$check_title = __('Click to check the domain is located on your chosen host ');
		$awaiting_check = sprintf($awaiting, 'check', 'Checking...');
		$install_title = __('Click to start the installation.');
		$awaiting_install = sprintf($awaiting, 'install', 'Installing ...');
		$tip1 = self::$tooltips->tip('check');
		$tip2 = self::$tooltips->tip('install');
		$tip3 = self::$tooltips->tip('price');
		$tip4 = self::$tooltips->tip('balance');
		$buttons = <<< BUTTON_PANEL
<label>{$tip1}</label><input type="submit" id="check" name="check" value="check" title="{$check_title}"  {$check_disabled}  class="{$check_class}" alt="Run Pre-Flights Checks"  /> {$awaiting_check} {$check_message}<br/>
<label>{$tip2}</label><input type="submit" id="install" name="install" value="use credits &amp; install" title="{$install_title}"  {$install_disabled}   class="{$install_class}" /> {$awaiting_install}&nbsp;&nbsp;{$install_message}<br/>
<label>{$tip3}</label>{$price_info}<br/>
<label>{$tip4}</label>{$bal_info}<br/>
BUTTON_PANEL;
		return $buttons;
	}

	private static function delete_panel($site, $disabled ='') {
		$site_status = $site['site_status'];
		$site_url = $site['site_url'];
		$disabled = (empty($disabled) && (('LIVE'==$site_status) || ('ARCHIVE'==$site_status))) ? 'disabled="disabled"' : '';		
		$delete_class = $disabled ? 'dimmed':'whoosh';		
		$awaiting = '<img id="awaiting%1$s" class="working" width="20" height="20" src="'.WPWHOOSH_IMAGES_URL.'waiting-32.gif" alt="%2$s"/>';		
		$awaiting_delete = sprintf($awaiting, 'delete', 'Deleting ...');
		$delete_title = __('Click to delete the site from the world wide web.');
		$tip1 = self::$tooltips->tip('delete');
		$panel = <<< DELETE_PANEL
<p>Here you can delete a site that you Whooshed earlier providing the site configuration has not changed in the interim.</p>
<label>{$tip1}</label><input type="submit" id="delete" name="delete" value="Delete" title="{$delete_title}"  {$disabled}  class="{$delete_class}" /> {$awaiting_delete}<br/>
DELETE_PANEL;
		return empty($disabled) ? $panel : '';

	}

	private static function copy_panel($id) {
		$nonce = wp_nonce_field('copy-site_'.$id,'_wpnonce',true,false);
		$action_url = self::get_url($id,'noheader');
		$tip1 = self::$tooltips->tip('copy');		
		$result = <<< COPY_PANEL
<h3>Copy Site Information</h3>
<p>Specify the name of the new site whose details will be copied from the information displayed on this page.</p>
<form method="POST" name="copysite" action="{$action_url}">
{$nonce}
<input type="hidden" name="action" value="copy" />
<label>{$tip1}</label><input type="text" id="new_name" name="new_name" value="" />
<input type="submit" name="copy-site" value="Copy Site" class="button-primary"/>
</form>
COPY_PANEL;
		return $result;
	}

	public static function help_edit_panel() {
		$plugin= WPWHOOSH_FRIENDLY_NAME;
		$tuts = WPWHOOSH_HOME_URL . '/tutorials/';
		$result = <<< HELP_PANEL
<h4>Before You Install</h4>
<p>Before you install a site you need to have make sure the domain for the site is pointed at your host. Click for more on 
<a target="_blank" href="{$tuts}how-to-set-up-dns-for-your-new-domain/">How To Set Up DNS for your new domain</a></p>
<h4>How To Install</h4>
<p>On this page you can install a site. Clicks the link below for detailed instructions</p>
<p><ul>
<li><a target="_blank" href="{$tuts}how-to-add-a-site/">How To Prepare A Site For Installation</a></li>
<li><a target="_blank" href="{$tuts}how-to-check-a-site/">How To Check A Site Can Be Installed</a></li>
<li><a target="_blank" href="{$tuts}how-to-install-a-site/">How To Install The Site</a></li>
</ul></p>
HELP_PANEL;
		return $result;
	}

	public static function help_list_panel() {
		$plugin= WPWHOOSH_FRIENDLY_NAME;
		$tuts = WPWHOOSH_HOME_URL . '/tutorials/';
		$result = <<< LIST_PANEL
<h3>{$plugin} List Sites</h3>
<p>On this page you can list sites and possibly remove, archive or delete a site from list according to its status. 
Click the link below for detailed instructions</p>
<p><ul>
<li><a target="_blank" href="{$tuts}how-to-delete-a-site/">How To Delete A Whooshed Site</a></li>
<li><a target="_blank" href="{$tuts}how-to-archive-a-site/">How To Archive The Site Information</a></li>
<li><a target="_blank" href="{$tuts}how-to-remove-a-site/">How To Remove The Site Information From The List</a></li>
</ul></p>
LIST_PANEL;
		return $result;
	}

}

class wpwhoosh_host_list extends wpwhoosh_list {
 	function __construct($list='host') { 
 		parent::__construct($list, 
 			WPWhooshUtils::get_hosts(),false,'host_name,cpanel_status');
	}
}

class wpwhoosh_template_list extends wpwhoosh_list {
 	function __construct($list='template') { 
 		parent::__construct($list, WPWhooshUtils::get_templates(),false,'name');
	}
}

class wpwhoosh_permalinks_list extends wpwhoosh_list {
 	function __construct($list='site_permalinks') { 
 		parent::__construct($list, WPWhooshUtils::get_list($list));
	}
}

?>