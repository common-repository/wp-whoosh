<?php
class wpwhoosh_secret {

    private static $slug = WPWHOOSH_SECRET;
    private static $screen_id;
    private static $label;
    private static $secret;
	private static $tips = array(
			'secret' => array('heading' => 'API Secret ', 'tip' => 'Supply a Secret. This can be just a number or a phrase. The minimum size is 4 characters; the maximum size is 32 characters. The longer it is, the greater the security. You will need to remember your API Secret as you will need it for every installation.'),
			'authorization' => array ('heading' => 'Authorization Code', 'tip' => 'Copy the authorization code from the email we sent you and paste it into the box. It should be 32 characters in length.'),
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
		self::$label = WPWHOOSH_FRIENDLY_NAME.' '.__('API Key',WPWHOOSH_PLUGIN_NAME);
		self::$secret = WPWHOOSH_FRIENDLY_NAME.' '.__('API Secret',WPWHOOSH_PLUGIN_NAME);
		self::$tooltips = new wpwhoosh_tooltip(self::$tips);
		add_action('admin_menu', array(WPWHOOSH_SECRET, 'admin_menu'));
	}

	static function admin_menu() {
		self::$screen_id = add_submenu_page(WPWHOOSH_PLUGIN_NAME, self::$secret, __('API Secret',WPWHOOSH_PLUGIN_NAME), 'read', 
			self::get_slug(), array(WPWHOOSH_SECRET,'controller'));
		add_action('load-'.self::get_screen_id(), array(WPWHOOSH_SECRET, 'load_page'));
	}

	static function load_page() {
		add_action ('admin_enqueue_scripts',array(WPWHOOSH_SECRET, 'enqueue_styles'));
		add_action ('admin_enqueue_scripts',array(WPWHOOSH_SECRET, 'enqueue_scripts'));		
		add_filter('screen_layout_columns', array(WPWHOOSH_SECRET, 'screen_layout_columns'), 10, 2);
		add_meta_box('secret-panel', self::$secret, array(WPWHOOSH_SECRET, 'secret_panel'), self::get_screen_id(), 'normal', 'core');
		$current_screen = get_current_screen();
		if (method_exists($current_screen,'add_help_tab')) {		
			$current_screen->add_help_tab( array( 'id' => 'wpwhoosh_secret_protect', 'title' => self::$secret, 'content' => self::secret_protect_help()));		
			$current_screen->add_help_tab( array( 'id' => 'wpwhoosh_secret_reason', 'title' => 'Why Have An API Secret?', 'content' => self::secret_reasons_help()));		
			$current_screen->add_help_tab( array( 'id' => 'wpwhoosh_secret_change', 'title' => 'How To Set Your API Secret', 'content' => self::secret_change_help()));		
		}	
	}

	public static function enqueue_styles() {
		wp_enqueue_style('tooltip', WPWHOOSH_PLUGIN_URL.'tooltip.css', array(),WPWHOOSH_VERSION);	
		wp_enqueue_style('secret', WPWHOOSH_PLUGIN_URL.'secret.css', array(),WPWHOOSH_VERSION);	
 	}

	public static function enqueue_scripts() {
		wp_enqueue_script('secret', WPWHOOSH_PLUGIN_URL.'secret.js', array('jquery'), WPWHOOSH_VERSION, true);
		wp_enqueue_script('crypto',WPWHOOSH_PLUGIN_URL.'md5.js',array(),'3.0.2',true);			
		wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');	
		add_action('admin_footer-'.self::get_screen_id(), array(WPWHOOSH_SECRET, 'toggle_postboxes'));
	}


	static function screen_layout_columns($columns, $screen) {
		if (!defined( 'WP_NETWORK_ADMIN' ) && !defined( 'WP_USER_ADMIN' )) {
			if ($screen == self::get_screen_id()) {
				$columns[self::get_screen_id()] = 2;
			}
		}
		return $columns;
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

	private static function change_secret() {
    	$redir = WPWhooshUtils::next_url('change_secret'); //check nonce 
		$args = array('action' => 'secret_change', 'timeout' => 30, 
			'body' => array('pin' => $_POST['secret'], 'auth' => $_POST['authorization']));
		$status = WPWhooshUpdater::update(false, $args);
		if ($status['success']) {
			$message = __('Your secret has been changed',WPWHOOSH_PLUGIN_NAME);
		} else {
			$message = __('API Secret changes are not available at this time:',WPWHOOSH_PLUGIN_NAME). $status['error'];
		}
		$redir = add_query_arg( array('message' => urlencode($message)), $redir );  
    	wp_redirect( $redir ); 
    	exit;
	}

	private static function reset_secret() {
    	$redir = WPWhooshUtils::next_url('reset_secret'); 
		$args = array('action' => 'secret_reset', 'timeout' => 30);
		$status = WPWhooshUpdater::update(false, $args);
		if ($status['success']) {
				$message = WPWhooshUpdater::get_notice(). '<br/>'. __('Copy the authorization code from the email and paste it below',WPWHOOSH_PLUGIN_NAME);
		} else {
			$message = __('Secret authorization requests are not being accepted at this time as another request is outstanding:',WPWHOOSH_PLUGIN_NAME). WPWhooshUtils::get_errors($status);
		}
		$redir = add_query_arg( array('message' => urlencode($message)), $redir );  
    	wp_redirect( $redir ); 
    	exit;
	}

	static function secret_panel($post, $metabox) {		
		$home = WPWHOOSH_HOME_URL;	
		$secret = self::$secret;	
		$is_valid = false;
		$notice ='';
 		$reset_url = self::get_url('reset_secret',true,true);       
 		$change_url = self::get_url('change_secret',true,true);    
 		$secret_action = WPWhooshUpdater::get_secret_enabled() ? 'Change' : 'Create';  
		$tip1 = self::$tooltips->tip('authorization');
		$tip2 = self::$tooltips->tip('secret');
		print <<< SECRET_PANEL
<h4>How To Set Your {$secret}</h4>
<form id="secret_entry" method="post" onsubmit="return wpwhoosh_validate_secret(this);" action="{$change_url}"> 
<p><ol>
<li>Firstly click the red button and we'll email you an authorization code:<br/>
<a class="reset" href="{$reset_url}">Get Authorization Code</a></li>
<li>Now go and check your email and copy the authorization code and paste into the box below.<br/>
<label>{$tip1}</label><input type="text" name="authorization" id="authorization" value="Paste the authorization code here" /></li>
<li>Finally think of a phrase or a number between 4 and 32 characters long. <br/>This is your <i>API Secret</i>, known only to you and 
you alone. Enter it into the box below. <br/>For really strong security we recommend you use a phrase of at least 16 characters.<br/> 
For example, "Mary has a $5 lamb!" is an API Secret would take around 364 quintillion years to crack on a PC according to <a target="_blank" href="https://howsecureismypassword.net/">How Secure Is My Password?</a><br/>
<label>{$tip2}</label><input type="password" name="secret" id="secret" maxlength="32" /></li>
</p>
<p><input type="hidden" name="action" value="change_secret"/><input type="submit" class="button-primary" value="Save Your Secret" /></p>
</ol></form>
SECRET_PANEL;
	}

	static function secret_protect_help() {		
		$url = WPWHOOSH_HOME_URL . '/tutorials/how-to-change-your-whoosh-secret/';	
		$friendly_name = WPWHOOSH_FRIENDLY_NAME;	
		$label = self::$label;
		$secret = self::$secret;
		$panel = <<< HELP_PANEL
<h4>Protect Your {$label}</h4>
<p>With {$friendly_name} a Secret is optional but recommended. Your {$secret} can be between 4 and 32 characters long. 
Every time you spend credits you  will be prompted to enter your Secret. The {$secret} is encrypted in the browser and 
used to encrypt the access information that is required to install the new site.</p> 
<p>To maintain security, your {$secret} is never displayed or sent as free text. In fact it is not recoverable at all. In case of loss you need to set a new API Secret.</p>
<p>We formerly referred to the {$secret} as a PIN (personal identification number) as it is handled like like the PIN of 
"Chip and Pin" banking which is common in much of Europe but not as prevalent in the USA.
HELP_PANEL;
		return $panel;
	}

	static function secret_reasons_help() {		
		$url = WPWHOOSH_HOME_URL . '/tutorials/how-to-change-your-whoosh-api-secret/';	
		$friendly_name = WPWHOOSH_FRIENDLY_NAME;	
		$label = self::$label;
		$secret = self::$secret;
		$panel = <<< HELP_PANEL
<h4>Reasons To Use An API Secret</h4>
<p>If you have set an API Secret you will be asked to enter it every time you want to spend some credits. 
Setting a API Secret ensures that 
<ol>
<li>even if someone finds out your API key, they cannot spend your credits without knowing your API Secret; and</li>
<li>even if a hacker or some other unauthorized person obtains admin access to your WordPress site, your host details cannot be accessed without knowing your API Secret</li>
</ol></p>
<p>For security reasons, if you lose or change your API Secret, then you will need to reenter your host details.</p>
HELP_PANEL;
		return $panel;
	}

	static function secret_change_help() {		
		$url = WPWHOOSH_HOME_URL . '/tutorials/how-to-change-your-whoosh-api-secret/';	
		$friendly_name = WPWHOOSH_FRIENDLY_NAME;	
		$label = self::$label;
		$secret = self::$secret;
		$panel = <<< HELP_PANEL
<h4>How To Set Your API Secret</h4>
<p>If you lose or forget your API Secret, or simply want to change it, you can do so by following a two-step procedure. Firstly you request
a change of secret below and the system will email your primary email address an authorization code which lasts for 15 minutes.</p>
<p>Secondly, enter the authorization code and enter the new secret. If the authorization code is valid the new secret will become active.</p>
<p>If you set an empty new secret, then the secret challenge will be disabled.</p>
<p>For more detailed instructions check out the tutorial at <a target="_blank" href="{$url}">How To Change Your {$secret}</a></p>
HELP_PANEL;
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

	static function controller() {
 		switch ($_REQUEST['action']) {
 		 case 'reset_secret' : self::reset_secret(); break;
 		 case 'change_secret': self::change_secret(); break;
 		}
		echo self::fetch_message(array('reset_secret','change_secret','save'));
		$plugin = WPWHOOSH_FRIENDLY_NAME;
?>
    <div id="poststuff" class="metabox-holder">
     	<h2>Your <?php echo self::$secret; ?></h2>    
     	<p>Click on the <i>Help</i> tab on the top right of the page for more about security.</p>
        <div id="post-body">
            <div id="post-body-content">
			<?php do_meta_boxes(self::get_screen_id(), 'normal', null); ?>
 			</div>
        </div>
        <br class="clear"/>
    </div>
<?php
	}  

}
?>