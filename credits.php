<?php
class wpwhoosh_credits {

    private static $slug = 'wpwhoosh_credits';
    private static $parenthook  = WPWHOOSH_PLUGIN_NAME;
    private static $screen_id;

    static function get_slug() {
		return self::$slug;
	}

    static function get_parenthook(){
		return self::$parenthook;
	}

    static function get_screen_id(){
		return self::$screen_id;
	}

 	static function get_url($noheader=false) {
		return WPWhooshUtils::admin_url(self::get_slug(),'','',$noheader);
	}

	static function init() {
		add_action('admin_menu',  array(WPWHOOSH_CREDITS, 'admin_menu'));
	}

	static function admin_menu() {
		self::$screen_id = add_submenu_page(self::get_parenthook(), __('Credits'), __('Credits'), 'read', 
			self::get_slug(), array(WPWHOOSH_CREDITS,'controller'));
		add_action('load-'.self::get_screen_id(), array(WPWHOOSH_CREDITS, 'load_page'));		
	}

	static function load_page() {
		add_action ('admin_enqueue_scripts',array(WPWHOOSH_CREDITS, 'enqueue_styles'));
		add_action ('admin_enqueue_scripts',array(WPWHOOSH_CREDITS, 'enqueue_scripts'));
		$balance = self::get_balance();
		$groups = self::get_credit_groups();
		if (!is_array($groups)) $groups = array();
		$callback_params = array ('balance' => $balance, 'groups' => count($groups));			
		add_meta_box('credit-intro', __('Account Balance',WPWHOOSH_PLUGIN_NAME), array(WPWHOOSH_CREDITS, 'intro_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);		
		foreach ($groups as $key => $group) {
			$callback_params = array ('group' => $group);		
			$id = sprintf('%1$s-%2$s-credits',WPWHOOSH_PLUGIN_NAME,$key);
			$title = $group['name'];			
			$callback_params = array ('group' => $group);
			add_meta_box($id, $title, array(WPWHOOSH_CREDITS, 'group_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		}
		$current_screen = get_current_screen();
		if (method_exists($current_screen,'add_help_tab')) {		
			$current_screen->add_help_tab( array( 'id' => 'wsh_credits', 
				'title' => WPWHOOSH_FRIENDLY_NAME . ' Credits', 'content' => self::help_panel()));		
		}
	}
	
	static function enqueue_styles() {
		wp_enqueue_style('tooltip', WPWHOOSH_PLUGIN_URL.'tooltip.css', array(),WPWHOOSH_VERSION);	
		wp_enqueue_style('credits', WPWHOOSH_PLUGIN_URL.'credits.css', array(),WPWHOOSH_VERSION);	
 	}

	static function enqueue_scripts() {
		wp_enqueue_script ('credits', WPWHOOSH_PLUGIN_URL.'credits.js', array('jquery'), WPWHOOSH_VERSION, true);
		wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');
		add_action('admin_footer-'.self::get_screen_id(), array(WPWHOOSH_CREDITS, 'toggle_postboxes'));
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

	static function get_balance($cache=true) {
		return WPWhooshUpdater::update($cache, array('action' => 'balance', 'timeout' => 30));
	}

	static function get_credit_groups($cache=true) {
		return WPWhooshUpdater::update($cache, array('action' => 'credit_groups', 'timeout' => 30));
	}

    static function gallery($group) {
		$buyer = empty($email) ? '' : sprintf('/?Contact0Email=%1$s', $email);
	    $list = '';
		if (is_array($group) && array_key_exists('options',$group) && is_array($group['options']))
	    	foreach ($group['options'] as $key => $option) 
	    		$list .= sprintf('<li><span class="credits">%1$s %2$s</span><br/><span class="price">%3$s</span><br/><span id="%4$s" class="buy">Add To Cart</span><br/><span class="rate">Rate: %5$s</span></li>',
			$option['credits'],  $option['credits']>1 ?'Credits':'Credit', $option['price'], $key, $option['rate']);
	    if (!empty($list)) $list = sprintf('<ul class="credit-options">%1$s</ul>',$list);
	    return $list;
	}

	static function group_panel($post, $metabox) {
		$balance = $metabox['args']['balance'];
		$group = $metabox['args']['group'];
		echo ($group['description']);
		echo(self::gallery($group));
	}	

	static function intro_panel($post, $metabox) {
		$balance = $metabox['args']['balance'];
		$num_groups = $metabox['args']['groups'];
		if ($num_groups == 0) {
			$p1 = '<p>You need an API key to check your balance.</p>';			
			$p2 = sprintf ('<p><b>Please <a href="%1$s">fetch and validate your API key</a> before trying to purchase credits</b></p>',
				WPWHOOSH_KEY::get_url());
		} else {
			$url= $_SERVER['REQUEST_URI'];
        	$refresh = array_key_exists('refresh',$_GET);
        	if ($refresh)
				WPWhooshUtils::save_templates(WPWhooshUpdater::update(false)); 
			else 
				$url .= "&refresh=true";
			$updated = 	date('M d, Y H:i',strtotime(WPWhooshUpdater::get_last_updated()));
			$expiry = 	date('M d, Y',strtotime($balance['expiry']));
			$p1 = sprintf('%1$s <b>%2$d %3$s</b>',__('Your balance is'), $balance['balance'],__('credits'));
			if (($balance['balance'] > 0) && !empty($balance['expiry']))	
				$p1 .= sprintf(__(' as of %1$s with the credits expiring from %2$s'),  $updated, $expiry);
			$p2 = sprintf('<div><a rel="nofollow" class="check" href="%1$s">Check My Balance</a></div>',$url);					
		}  
		print <<< INTRO_PANEL
<p>{$p1}</p>
<p>{$p2}</p>
INTRO_PANEL;
	}	

	static function help_panel() {
		$home = WPWHOOSH_HOME_URL;
		$images = WPWHOOSH_IMAGES_URL;
		$plugin = WPWHOOSH_FRIENDLY_NAME;
		$result = <<< HELP_PANEL
<p>On this page you can check your balance and when your credits expire, and buy either Pay As You Go credits that last a 
year, or a monthly subscription with credits that last a month.</p>
<ul class="help-links">
<li><a target="_blank" href="{$home}/credits-explained/" >Credits Explained</a></li>
<li><a target="_blank" href="{$home}/tutorials/how-to-buy-credits/">How To Buy Credits</a></li>
<li><a target="_blank" href="{$home}/credits-terms/">Our Terms Of Use for Credits</a></li>
</ul>
HELP_PANEL;
	return $result;
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

	static function buy($credits) {
	  	$redir = WPWhooshUtils::next_url( WPWHOOSH_CREDITS);  
		$link =  WPWhooshUpdater::update(false, array('action' => 'buy_credits', 'timeout' => 30, 'body' => array( 'code' => $credits)));
		if ($link['success']) {
    		$redir = $link['url']; 
		} else {
			$message = '<div id="message">Credits not available for purchase at this time. Many Apologies.</div>';
			$redir = add_query_arg( array('message' => urlencode($message)), $redir ); //add the message 
		}
    	wp_redirect($redir); 
		exit;		
	}

	static function controller() {
		if (array_key_exists('action',$_REQUEST) && !empty($_REQUEST['action'])) self::buy($_REQUEST['credits']);    	
 		$this_url = self::get_url(true);  	
		$plugin = WPWHOOSH_FRIENDLY_NAME;
		echo self::fetch_message('buy');	
?>
    <div id="poststuff" class="metabox-holder">
        <h2><?php echo $plugin; ?> Credits</h2>
     	<p>Click on the <i>Help</i> tab on the top right of the page for more about credits.</p>        
        <div id="post-body">
            <div id="post-body-content">
			<form id="wpwhoosh_form" method="post" action="<?php echo $this_url; ?>">
			<input type="hidden" id="credits" name="credits" value="" />
			<input type="hidden" id="action" name="action" value="" />			
			<?php do_meta_boxes(self::get_screen_id(), 'normal', null); ?>
			<fieldset>
			<?php wp_nonce_field(WPWHOOSH_CREDITS); ?>
			<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
			<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
			</fieldset>			
			</form>
 			</div>
        </div>
        <br class="clear"/>
    </div>
<?php
	}  
}
?>