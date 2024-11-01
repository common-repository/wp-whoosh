<?php
/*
 * Plugin Name: WP Whoosh
 * Plugin URI: http://www.wpwhoosh.com
 * Description: Builds new WordPress sites with many useful features, good security, performance and SEO in under 60 seconds saving you precious time.
 * Version: 1.6
 * Author: Russell Jamieson
 * Author URI: http://www.diywebmastery.com
 * License: GPLv2+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
define('WPWHOOSH_VERSION','1.6');
if (!defined('WPWHOOSH_FRIENDLY_NAME')) define('WPWHOOSH_FRIENDLY_NAME', 'WP Whoosh') ;
if (!defined('WPWHOOSH_PLUGIN_NAME')) define('WPWHOOSH_PLUGIN_NAME', 'wpwhoosh') ;
if (!defined('WPWHOOSH_PLUGIN_PATH')) define('WPWHOOSH_PLUGIN_PATH', basename(dirname(__FILE__)).'/'.basename(__FILE__)) ;
if (!defined('WPWHOOSH_PLUGIN_URL')) define('WPWHOOSH_PLUGIN_URL', plugins_url( '',__FILE__).'/');
if (!defined('WPWHOOSH_IMAGES_URL')) define('WPWHOOSH_IMAGES_URL',WPWHOOSH_PLUGIN_URL.'images/');
if (!defined('WPWHOOSH_HOME_URL')) define('WPWHOOSH_HOME_URL','http://www.'.WPWHOOSH_PLUGIN_NAME.'.com');
if (!defined('WPWHOOSH_TEMPLATES_URL')) define('WPWHOOSH_TEMPLATES_URL',WPWHOOSH_HOME_URL.'/templates/');
if (!defined('WPWHOOSH_GALLERY_URL')) define('WPWHOOSH_GALLERY_URL','http://images.'.WPWHOOSH_PLUGIN_NAME.'.com/templates/');
if (!defined('WPWHOOSH_UPDATER_URL')) define('WPWHOOSH_UPDATER_URL','https://www.'.WPWHOOSH_PLUGIN_NAME.'.com/updates/');
if (!defined('WPWHOOSH_UPDATER_URL2')) define('WPWHOOSH_UPDATER_URL2','http://www.'.WPWHOOSH_PLUGIN_NAME.'.com/updates/');
require_once(dirname(__FILE__) . '/'.(is_admin()?'admin':'public').'.php');
?>