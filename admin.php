<?php
define('WPWHOOSH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPWHOOSH_ADMIN', WPWHOOSH_PLUGIN_NAME.'_admin');
define('WPWHOOSH_KEY', 'wpwhoosh_key');
define('WPWHOOSH_SECRET', 'wpwhoosh_secret');
define('WPWHOOSH_TEMPLATES', 'wpwhoosh_templates');
define('WPWHOOSH_CREDITS', 'wpwhoosh_credits');
define('WPWHOOSH_HOSTS', 'wpwhoosh_hosts');
define('WPWHOOSH_SITES', 'wpwhoosh_sites');

require_once (WPWHOOSH_PLUGIN_DIR.'functions.php');
require_once (WPWHOOSH_PLUGIN_DIR.'updater.php');
require_once (WPWHOOSH_PLUGIN_DIR.'list.php');
require_once (WPWHOOSH_PLUGIN_DIR.'key.php');
require_once (WPWHOOSH_PLUGIN_DIR.'secret.php');
require_once (WPWHOOSH_PLUGIN_DIR.'template-factory.php');
require_once (WPWHOOSH_PLUGIN_DIR.'templates.php');
require_once (WPWHOOSH_PLUGIN_DIR.'credits.php');
require_once (WPWHOOSH_PLUGIN_DIR.'hosts.php');
require_once (WPWHOOSH_PLUGIN_DIR.'sites.php');

$wpwhoosh_admin = new wpwhoosh_admin();

class wpwhoosh_admin {

    private $pagehook;

	function __construct() {
		add_action('init', array(WPWHOOSH_KEY,'init'));
		add_action('init', array(WPWHOOSH_SECRET,'init'));
		add_action('init', array(WPWHOOSH_HOSTS,'init'));
		add_action('init', array(WPWHOOSH_TEMPLATES,'init'));
		add_action('init', array(WPWHOOSH_CREDITS,'init'));
		add_action('init', array(WPWHOOSH_SITES,'init'));
		add_action('admin_menu', array(&$this,'admin_menu'));
		add_filter('plugin_action_links',array(&$this, 'plugin_action_links'), 10, 2 );	
	}

	function plugin_action_links( $links, $file ) {
		if ( is_array($links) && (WPWHOOSH_PLUGIN_PATH == $file )) {
			$settings_link = '<a href="' . admin_url( 'admin.php?page='.WPWHOOSH_PLUGIN_NAME) . '">Settings</a>';
			array_unshift( $links, $settings_link );
		}
		return $links;
	}

	function admin_menu() {
		$intro = sprintf('Intro (v%1$s)', WPWHOOSH_VERSION);				
		$this->pagehook = add_menu_page(WPWHOOSH_FRIENDLY_NAME, WPWHOOSH_FRIENDLY_NAME, 'read', 
			WPWHOOSH_PLUGIN_NAME, array(&$this,'controller'),WPWHOOSH_IMAGES_URL.'menu-icon.png' );
		add_submenu_page(WPWHOOSH_PLUGIN_NAME, WPWHOOSH_FRIENDLY_NAME, $intro, 'manage_options', WPWHOOSH_PLUGIN_NAME,array(&$this,'controller') );
		add_action('load-'.$this->pagehook, array(&$this, 'load_page'));
	}			

	function load_page() {
		add_action ('admin_enqueue_scripts',array(&$this, 'enqueue_styles'));
		add_filter('screen_layout_columns', array(&$this, 'screen_layout_columns'), 10, 2);
		$plugin = WPWHOOSH_FRIENDLY_NAME;	
		$current_screen = get_current_screen();		
		if (method_exists($current_screen,'add_help_tab')) {		
			$current_screen->add_help_tab( array( 'id' => 'wsh_links', 'title' => sprintf('%1$s Links',$plugin), 		
				'content' => $this->links_panel()));
			$current_screen->add_help_tab( array( 'id' => 'wsh_overview', 'title' => sprintf('What is %1$s?',$plugin), 		
				'content' => $this->help_panel()));		
		}
	}		
			
	function screen_layout_columns($columns, $screen) {
		if (!defined( 'WP_NETWORK_ADMIN' ) && !defined( 'WP_USER_ADMIN' )) {
			if ($screen == $this->pagehook) {
				$columns[$this->pagehook] = 1;
			}
		}
		return $columns;
	}

	function enqueue_styles() {
		wp_enqueue_style('wshadmin', WPWHOOSH_PLUGIN_URL.'admin.css', array(),WPWHOOSH_VERSION);	
 	}

	function help_panel() {
		$home = WPWHOOSH_HOME_URL;
		$images = WPWHOOSH_IMAGES_URL;
		$plugin = WPWHOOSH_FRIENDLY_NAME;
		$result = <<< HELP_PANEL
<h4>{$plugin} Overview</h4>
<p>{$plugin} DOES NOT AFFECT THIS SITE. It is used to build sites elsewhere</p>
<p>{$plugin} allows you to make a professional and secure installation of WordPress with good performance and SEO in under 60 seconds.</p>
<p>A {$plugin} installation uses a customized template. The template comprises a chosen WordPress theme, our selection of quality plugins, standard pages 
(contact, terms and conditions, privacy statement), and example posts, or real posts if your chose one of our niche specific templates. 
The installation also includes items such as .htaccess, robots.txt, sitemap.xml in order to improve the security and performance of the site.</p> 
<p>Using {$plugin} will not only give you peace of mind with a more secure installation of WordPress but will save you precious time: typically something 
between 15 minutes to 120 minutes in getting your WordPress site up and running.</p> 
HELP_PANEL;
		return $result;
	}

	function links_panel() {
		$home = WPWHOOSH_HOME_URL;
		$images = WPWHOOSH_IMAGES_URL;
		$plugin = WPWHOOSH_FRIENDLY_NAME;
		$result = <<< HELP_PANEL
<ul class="help-links">
<li><a target="_blank" href="{$home}" rel="external">{$plugin} Plugin Home Page</a></li>
<li><a target="_blank" href="{$home}/features/" rel="external">More {$plugin} Features</a></li>
<li><a target="_blank" href="{$home}/templates/" rel="external">More {$plugin} Templates</a></li>
<li><a target="_blank" href="{$home}/faq/" rel="external">Frequently Asked Questions About {$plugin}</a></li>
<li><a target="_blank" href="{$home}/tutorials/" rel="external">Getting Started With {$plugin}</a></li>
<li><a target="_blank" href="{$home}/help/" rel="external">{$plugin} Help</a></li>
</ul>
HELP_PANEL;
		return $result;
	}
			
	function controller() {
    	$templates_url = WPWHOOSH_TEMPLATES::get_url(); 
    	$key_url = WPWHOOSH_KEY::get_url(); 
    	$secret_url = WPWHOOSH_SECRET::get_url(); 
    	$hosts_url = WPWHOOSH_HOSTS::get_url(); 
    	$sites_url = WPWHOOSH_SITES::get_url(); 
    	$credits_url = WPWHOOSH_CREDITS::get_url(); 
    	$home_url = WPWHOOSH_HOME_URL;
    	$version = WPWHOOSH_VERSION;
    	$plugin = WPWHOOSH_FRIENDLY_NAME;
    	$alternatives = WPWHOOSH_HOME_URL . '/alternatives/';
    	$warning = __(WPWhooshUpdater::can_encrypt() ? '':
    		'<h2>Security Notice</h2><p><em>Your host does NOT have encryption software installed. (Your web server installation is missing the <a href="http://www.php.net/manual/en/mcrypt.installation.php">mcrypt</a> library) </em></p>
    		</p>In the unlikely event that this site is hacked, and the hacker can log in as you and see what you can see, then your host details may be compromised.</p> 
    		<p>Therefore, if you feel this site is not secure then we recommend you do NOT use this plugin on this site.</p>
    		<p>Please see <a href="'.$alternatives.'">alternative ways of making a Secure WordPress installation</a></p>',
    		WPWHOOSH_PLUGIN_NAME);
    	print <<< ADMIN_PANEL
<div class="wrap">
<h2>How Do I Start With WP Whoosh?</h2>
<p>Take the following steps: </p>
<ol>
<li>Start by getting your <a href="{$key_url}">API Key</a></li>
<li>Request a <a href="{$secret_url}">API Secret</a></li>
<li>Set up your <a href="{$hosts_url}">host</a></li>
<li>Go check out the available Whoosh <a href="{$templates_url}">templates</a></li>
<li>Purchase some <a href="{$credits_url}">credits</a></li>
<li>Whoosh a <a href="{$sites_url}">site</a></li>
</ol>
<hr>
<p>For help click the Help tab at the top right of the screen</p>
<h2>Do I Need An API Secret?</h2>
<p>Using an API Secret means you will have a more secure Whoosh experience. It is not compulsory, but we highly recommend it.</p>
<h2>Why Does Using An API Secret Make Me More Secure?</h2>
<p>If someone discovers your API key they won't be able to spend your Whoosh credits</p>
<p>If your email or your WordPress site is hacked, the hacker won't be able to access your cPanel details using Whoosh without your API Secret. 
And they won't have your API Secret as we never email it to you.</p>
</div>
ADMIN_PANEL;
	}
}
?>