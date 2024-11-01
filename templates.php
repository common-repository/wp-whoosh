<?php
class wpwhoosh_templates {

    private static $slug = 'wpwhoosh_templates';
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
		add_action('admin_menu',  array(WPWHOOSH_TEMPLATES, 'admin_menu'));
	}

	static function admin_menu() {
		self::$screen_id = add_submenu_page(self::get_parenthook(), __('Templates'), __('Templates'), 'read', 
			self::get_slug(), array(WPWHOOSH_TEMPLATES,'controller'));
		add_action('load-'.self::get_screen_id(), array(WPWHOOSH_TEMPLATES, 'load_page'));
	}

	static function load_page() {
		add_action ('admin_enqueue_scripts',array(WPWHOOSH_TEMPLATES, 'enqueue_styles'));
		add_action ('admin_enqueue_scripts',array(WPWHOOSH_TEMPLATES, 'enqueue_scripts'));	
		add_filter('screen_layout_columns', array(WPWHOOSH_TEMPLATES, 'screen_layout_columns'), 10, 2);
		$groups = WPWhooshTemplateFactory::get_template_groups();
		if (!is_array($groups)) $groups = array();
		$callback_params = array ('groups' => count($groups));			
		add_meta_box('template-intro', __('Introduction',WPWHOOSH_PLUGIN_NAME), array(WPWHOOSH_TEMPLATES, 'intro_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);		
		foreach ($groups as $key => $group) {
			$callback_params = array ('group' => $group);		
			$id = sprintf('%1$s-%2$s-templates',WPWHOOSH_PLUGIN_NAME,$key);
			$title = sprintf('%1$s '.__('Templates',WPWHOOSH_PLUGIN_NAME),$group['name']);			
			$callback_params = array ('group' => $group);
			add_meta_box($id, $title, array(WPWHOOSH_TEMPLATES, 'group_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		}
		$current_screen = get_current_screen();		
		if (method_exists($current_screen,'add_help_tab')) {		
			$current_screen->add_help_tab( array( 'id' => 'templates_overview', 'title' => 'Overview', 		
				'content' => self::help_panel()));		
			$current_screen->add_help_tab( array( 'id' => 'templates_links', 'title' => 'Links', 		
				'content' => self::links_panel()));				}
	}

	static function enqueue_styles() {
		wp_enqueue_style('tooltip', WPWHOOSH_PLUGIN_URL.'tooltip.css', array(),WPWHOOSH_VERSION);	
		wp_enqueue_style('templates', WPWHOOSH_PLUGIN_URL.'templates.css', array(),WPWHOOSH_VERSION);	
 	}

	static function enqueue_scripts() {
		wp_enqueue_script ('templates', WPWHOOSH_PLUGIN_URL.'templates.js', array('jquery'), WPWHOOSH_VERSION, true);
		wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');
		add_action('admin_footer-'.self::get_screen_id(), array(WPWHOOSH_TEMPLATES, 'toggle_postboxes'));
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

	static function screen_layout_columns($columns, $screen) {
		if (!defined( 'WP_NETWORK_ADMIN' ) && !defined( 'WP_USER_ADMIN' )) {
			if ($screen == self::get_screen_id()) {
				$columns[self::get_screen_id()] = 1;
			}
		}
		return $columns;
	}

    static function screenshot($template_id,$template, $group_id) {
		$images = WPWHOOSH_GALLERY_URL;
		$template_home = $template['url'];
		$thumbnail = $images.$template_id.'.png';
		$title = ucwords($template['name']);
		$availability = sprintf('<span id="%1$s" class="%2$s">%3$s</span>',
			 $template_id, strpos(strtolower($template['availability']),'buy') !== FALSE ? 'buy' :'',$template['availability']);
   		return sprintf('<a target="_blank"  title="Click for more about %1$s" href="%2$s"><img src="%3$s" title="%1$s" alt="Screenshot of %1$s" width="300" height="225" /><br/><b>%1$s</b></a><br/>%4$s',
   				$title, $template_home, $thumbnail, $availability);
   }

    static function gallery($group) {
	    $list = '';
		if (is_array($group) && array_key_exists('templates',$group) && is_array($group['templates']))
	    	foreach ($group['templates'] as $key => $template) $list .= '<li>'.self::screenshot($key,$template,$group['id']).'</li>';
	    if (!empty($list)) $list = sprintf('<ul class="template-thumbnails">%1$s</ul>',$list);
	    return $list;
	}

	static function group_panel($post, $metabox) {
		$group = $metabox['args']['group'];
		$class =  empty($group['access']) ? 'installed' : 'request'; 	
		printf('<div class="entitlement %1$s">%2$s</div>',$class, $group['access']);
		echo ($group['description']);
		printf('<p>%1$s</p>',__('Click the image for more information about that template.',WPWHOOSH_PLUGIN_NAME));
		echo(self::gallery($group));
	}	

	static function intro_panel($post, $metabox) {
		$num_groups = $metabox['args']['groups'];
		if ($num_groups == 0) 
			printf ('<p><b>Please <a href="%1$s">fetch and validate and API key</a> to obtain access to the templates</b></p>',
				WPWHOOSH_KEY::get_url());
		$url= $_SERVER['REQUEST_URI'];
        $refresh = array_key_exists('refresh',$_GET);
        if ($refresh) {
        	$cache = false;
			WPWhooshUtils::save_templates(WPWhooshUpdater::update($cache)); //update cache with latest entitlements as a licensed user
			}
		else {
			$cache = true;
			$url .= "&refresh=true";
			}
		$plugin = WPWHOOSH_FRIENDLY_NAME;		   
		print <<< INTRO_PANEL
<p>New templates will appear below automatically within 24 hours of being released.</p>
<p>If you have just purchased a template, click to the button below to refresh your templates.</p>
<div><a rel="nofollow" class="refresh" href="{$url}">Refresh</a></div>
INTRO_PANEL;
	}	

	static function links_panel() {
		$home = WPWHOOSH_HOME_URL;
		$images = WPWHOOSH_IMAGES_URL;
		$plugin = WPWHOOSH_FRIENDLY_NAME;
		$result = <<< HELP_PANEL
<ul class="help-links">
<li><a rel="external" href="{$home}">{$plugin} Plugin Home Page</a></li>
<li><a rel="external" href="{$home}/templates/" rel="external">More {$plugin} Templates</a></li>
<li><a rel="external" href="{$home}/faq/" rel="external">Frequently Asked Questions About {$plugin}</a></li>
<li><a rel="external" href="{$home}/tutorials/" rel="external">Getting Started With {$plugin}</a></li>
<li><a rel="external" href="{$home}/help/" rel="external">{$plugin} Help</a></li>
</ul>
HELP_PANEL;
		return $result;
	}	
	
	static function help_panel() {
		$result = <<< HELP_PANEL
<p>Here you can see what templates are available. You can have these templates up and running in few minutes.</p>
<p>Click the image to find out more about the template. From there you can click through a demo site where you can explore.</p>
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

	private static function buy($template) {
	  	$redir = WPWhooshUtils::next_url( WPWHOOSH_TEMPLATES);  
		$link =  WPWhooshUpdater::update(false, array('action' => 'buy_template', 'timeout' => 30, 'body' => array( 'code' => $template)));
		if ($link['success']) {
    		$redir = $link['url']; 
		} else {
			$message = '<div id="message">Template not available for purchase</div>';
			$redir = add_query_arg( array('message' => urlencode($message)), $redir ); //add the message 
		}
    	wp_redirect($redir); 
		exit;		
	}

	static function controller() {
		if (array_key_exists('action',$_REQUEST) && ('buy'==$_REQUEST['action'])) self::buy($_REQUEST['template']);    	
		$plugin = WPWHOOSH_FRIENDLY_NAME;	
    	$templates_url = WPWHOOSH_TEMPLATES_URL; 
 		$this_url = self::get_url(true); 		
		echo self::fetch_message('buy');	
?>
    <div id="poststuff" class="metabox-holder">
        <h2><?php echo $plugin; ?> Templates</h2>
     	<p>Click on the <i>Help</i> tab on the top right of the page for more about templates.</p>          
        <div id="post-body">
            <div id="post-body-content">
			<form id="wpwhoosh_form" method="post" action="<?php echo $this_url; ?>">
			<input type="hidden" id="template" name="template" value="" />
			<input type="hidden" id="action" name="action" value="" />
			<?php do_meta_boxes(self::get_screen_id(), 'normal', null); ?>
			<fieldset>
			<?php wp_nonce_field(WPWHOOSH_TEMPLATES); ?>
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