<?php
class wpwhoosh_key {

    private static $initialized = false;
    private static $slug = WPWHOOSH_KEY;
    private static $screen_id;
    private static $label;
    private static $pin;
	private static $tips = array(
			'key' => array('heading' => 'API Key', 'tip' => 'Copy your API key from the email and paste it into the box')
			);
	private static $tooltips;


    static function get_slug() {
		return self::$slug;
	}

    static function get_screen_id(){
		return self::$screen_id;
	}

 	static function get_url($action='',$noheader=false,$with_nonce=false) {
 		return WPWhooshUtils::admin_url(self::get_slug(), $action, '', $noheader, $with_nonce);       
	}

	static function init() {
	    if (self::$initialized) return true;
		self::$initialized = true;
		self::$label = WPWHOOSH_FRIENDLY_NAME.' '.__('API Key',WPWHOOSH_PLUGIN_NAME);
		self::$pin = WPWHOOSH_FRIENDLY_NAME.' '.__('PIN',WPWHOOSH_PLUGIN_NAME);
		self::$tooltips = new wpwhoosh_tooltip(self::$tips);
		add_action('admin_menu', array(WPWHOOSH_KEY, 'admin_menu'));
	}

	static function admin_menu() {
		self::$screen_id = add_submenu_page(WPWHOOSH_PLUGIN_NAME, self::$label, __('API Key',WPWHOOSH_PLUGIN_NAME), 'read', 
			self::get_slug(), array(WPWHOOSH_KEY,'controller'));
		add_action('load-'.self::get_screen_id(), array(WPWHOOSH_KEY, 'load_page'));
	}

	static function load_page() {
		add_action ('admin_enqueue_scripts',array(WPWHOOSH_KEY, 'enqueue_styles'));
		add_action ('admin_enqueue_scripts',array(WPWHOOSH_KEY, 'enqueue_scripts'));
		add_filter('screen_layout_columns', array(WPWHOOSH_KEY, 'screen_layout_columns'), 10, 2);				
		add_meta_box('api-key-panel', self::$label. __('(Required)',WPWHOOSH_PLUGIN_NAME), array(WPWHOOSH_KEY, 'key_panel'), self::get_screen_id(), 'normal', 'core');
		add_meta_box('request-panel', __('Request API Key',WPWHOOSH_PLUGIN_NAME), array(WPWHOOSH_KEY, 'request_panel'), self::get_screen_id(), 'side', 'core');
		$current_screen = get_current_screen();
		if (method_exists($current_screen,'add_help_tab')) {		
			$current_screen->add_help_tab( array( 'id' => 'wpwhoosh_key_help', 'title' => self::$label, 'content' => self::key_help_panel()));		
		}	
	}

	static function screen_layout_columns($columns, $screen) {
		if (!defined( 'WP_NETWORK_ADMIN' ) && !defined( 'WP_USER_ADMIN' )) {
			if ($screen == self::get_screen_id()) {
				$columns[self::get_screen_id()] = 2;
			}
		}
		return $columns;
	}

	public static function enqueue_styles() {
		wp_enqueue_style('tooltip', WPWHOOSH_PLUGIN_URL.'tooltip.css', array(),WPWHOOSH_VERSION);	
		wp_enqueue_style('key', WPWHOOSH_PLUGIN_URL.'key.css', array(),WPWHOOSH_VERSION);	
 	}

	public static function enqueue_scripts() {
		wp_enqueue_script('key', WPWHOOSH_PLUGIN_URL.'key.js', array('jquery'), WPWHOOSH_VERSION, true);
		wp_enqueue_script('crypto',WPWHOOSH_PLUGIN_URL.'md5.js',array(),'3.0.2',true);			
		wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');	
		add_action('admin_footer-'.self::get_screen_id(), array(WPWHOOSH_KEY, 'toggle_postboxes'));
	}

    static function toggle_postboxes() {
    	$hook = self::get_screen_id();
    	print <<< TOGGLE_POSTBOXES
<script type="text/javascript">
//<![CDATA[
		jQuery(document).ready( function($) {
			$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
			postboxes.add_postbox_toggles('{$hook}');
		});
//]]>
</script>
TOGGLE_POSTBOXES;
    }

	static function save() {
		$redir = WPWhooshUtils::next_url('save');
		$key = trim(stripslashes($_POST['key']));
		$message = self::$label.' '.__(WPWhooshUpdater::save_key($key) ? "saved" : "has not changed" ,WPWHOOSH_PLUGIN_NAME);
 		WPWhooshUtils::save_templates(WPWhooshUpdater::update(false)); //update with new entitlements as a licensed user
  		$redir = add_query_arg( array('message' => urlencode($message)), $redir );  
    	wp_redirect( $redir ); 
    	exit;
	}

	static function key_panel($post, $metabox) {		
		$home = WPWHOOSH_HOME_URL;	
		$friendly_name = WPWHOOSH_FRIENDLY_NAME;	
		$is_valid = false;
		$key_status_indicator ='';
		$notice ='';
		$key = WPWhooshUpdater::get_key(false);
		if (! empty($key)) {
   			$is_valid = WPWhooshUpdater::check_validity();
   			$flag = $is_valid ? 'tick' : 'cross';
   			$key_status_indicator = '<img src="' . WPWHOOSH_PLUGIN_URL .'/images/'.$flag.'.png" alt="a '.$flag.'" />';
 			$notice = WPWhooshUpdater::get_notice();
 		}
        $readonly = $is_valid ? '' : 'readonly="readonly" class="readonly"';
 		$this_url = self::get_url('save',true,true); 		        
		$closed_nonce = wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false, false); 
		$order_nonce = wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false, false);
		$tip1 = self::$tooltips->tip('key');		
		print <<< LICENCE_PANEL
<h4>How To Get A {$friendly_name} API Key</h4>
<p>To get an API key: </p>
<ol>
<li>Fill out the form wit the big fiery arrow on the right and we will send you an email ->>></li>
<li>If you receive an email containing a long link click it to confirm your email address</li>
<li>Next you will receive an email containing your API key</li>
<li>Copy and paste the API key into the box below and click on the <i>Save Changes</i></li>
</ol>
<form id="api_key_entry" method="post" action="{$this_url}"> 
<p>{$tip1}<input type="password" name="key" id="key"  style="width:320px" value="{$key}" />&nbsp;{$key_status_indicator}</p>
<p class="api-key-notice">{$notice}</p>
<p><input type="submit" class="button-primary" name="options_update" value="Save Changes" /></p>
<p class="submit"><input type="hidden" name="action" value="save" />{$closed_nonce}{$order_nonce}</p>
</form>
LICENCE_PANEL;
	}

	static function key_help_panel() {		
		$url = WPWHOOSH_HOME_URL . '/tutorials/how-to-get-your-wp-whoosh-api-key/' ;	
		$friendly_name = WPWHOOSH_FRIENDLY_NAME;	
		$label = self::$label;
		$panel = <<< KEY_HELP_PANEL
<h4>Get Your {$label}</h4>
<p>On this page you can get your API Key by completing the form on the right below by supplying your name and email address.
An email with your API key will be sent to you. The key is 32 characters in length. Then copy the key from the email and paste 
it into the API Key and then press the Save button. Either a green tick or a red cross will appear depending on whether
the key you supplied is valid or not.</p>
<p>For more detailed instructions check out the tutorial at <a target="_blank" href="{$url}">How To Get Your API Key</a></p>
KEY_HELP_PANEL;
		return $panel;
	}

    static private function fetch_message($action) {
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

	static function request_panel($post, $metabox) {
		$plugin = WPWHOOSH_PLUGIN_NAME;
		$home = WPWHOOSH_HOME_URL;
		$images = WPWHOOSH_IMAGES_URL;
		$domain = parse_url(site_url(),PHP_URL_HOST);
		print <<< REQUEST_PANEL
<img src="{$images}get-api-key.png" alt="API Key Request" />
<form id="api_key_signup" name="api_key_signup" method="post" action="{$home}/" onsubmit="return wpwhoosh_validate_form(this);">
<input type="hidden" name="form_storm" value="submit" />
<input type="hidden" name="destination" value="{$plugin}" />
<input type="hidden" name="domain" value="{$domain}" />
<label for="firstname">First Name</label><br/><input id="firstname" name="firstname" type="text" value="" /><br/>
<label for="email">Your Email</label><br/><input id="email" name="email" type="text" /><br/>
<label id="lsubject" for="subject">Subject<input id="subject" name="subject" type="text" /></label>
<input type="submit" value="" />
</form>
REQUEST_PANEL;
	}	

	static function controller() {
 		switch ($_REQUEST['action']) {
 		 case 'reset_pin' : self::reset_pin(); break;
 		 case 'change_pin': self::change_pin(); break;
 		 case 'save': self::save(); break;
 		}
		echo self::fetch_message(array('reset_pin','change_pin','save'));
		$plugin = WPWHOOSH_FRIENDLY_NAME;
?>
    <div id="poststuff" class="metabox-holder has-right-sidebar">
        <h2>Your <?php echo $plugin; ?> API Key</h2>
		<p>You need an API key for you to be able to connect with our server and complete a remote WordPress installation.</p>
     	<p>Click on the <i>Help</i> tab on the top right of the page for more about your API key.</p>
		<div id="side-info-column" class="inner-sidebar">
		<?php do_meta_boxes(self::get_screen_id(), 'side', null); ?>
        </div>
        <div id="post-body" class="has-sidebar">
            <div id="post-body-content" class="has-sidebar-content">
			<?php do_meta_boxes(self::get_screen_id(), 'normal', null); ?>
 			</div>
        </div>
        <br class="clear"/>
    </div>
<?php
	}  
}
?>