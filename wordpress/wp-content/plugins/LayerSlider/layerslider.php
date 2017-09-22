<?php

/*
Plugin Name: LayerSlider WP
Plugin URI: https://codecanyon.net/item/layerslider-responsive-wordpress-slider-plugin-/1362246
Description: LayerSlider is the most advanced responsive WordPress slider plugin with the famous Parallax Effect and over 200 2D & 3D transitions.
Version: 6.3.0
Author: Kreatura Media
Author URI: https://layerslider.kreaturamedia.com
Text Domain: LayerSlider
*/


// Prevent direct file access.
if( ! defined('ABSPATH') ) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}


// Attempting to detect duplicate versions of LayerSlider to offer
// a more user-friendly error message explaining the situation.
if( defined('LS_PLUGIN_VERSION') || isset($GLOBALS['lsPluginPath']) ) {
	die('ERROR: It looks like you already have one instance of LayerSlider installed. WordPress cannot activate and handle two instanced at the same time, you need to remove the old version first.');
}


// Used to enable/disable the activation box in older versions.
// Nowadays, it displays a notification warning theme users about
// potential activation issues and purchase codes. A more convenient
// solution will replace this in future versions.
$GLOBALS['lsAutoUpdateBox'] = true;


// Basic configuration
define('LS_DB_TABLE', 'layerslider');
define('LS_DB_VERSION', '6.3.0');
define('LS_PLUGIN_VERSION', '6.3.0');


// Path info
// v6.2.0: LS_ROOT_URL is now set in the after_setup_theme action
// hook to provide a way for theme authors to override its value
define('LS_ROOT_FILE', __FILE__);
define('LS_ROOT_PATH', dirname(__FILE__));


// Other constants
define('LS_PLUGIN_SLUG', basename(dirname(__FILE__)));
define('LS_PLUGIN_BASE', plugin_basename(__FILE__));
define('LS_MARKETPLACE_ID', '1362246');
define('LS_TEXTDOMAIN', 'LayerSlider');
define('LS_REPO_BASE_URL', 'https://repository.kreaturamedia.com/v4/');


if( ! defined('NL')  ) { define('NL', "\r\n"); }
if( ! defined('TAB') ) { define('TAB', "\t");  }


// Shared
include LS_ROOT_PATH.'/wp/scripts.php';
include LS_ROOT_PATH.'/wp/menus.php';
include LS_ROOT_PATH.'/wp/hooks.php';
include LS_ROOT_PATH.'/wp/widgets.php';
include LS_ROOT_PATH.'/wp/shortcodes.php';
include LS_ROOT_PATH.'/wp/compatibility.php';
include LS_ROOT_PATH.'/includes/slider_utils.php';
include LS_ROOT_PATH.'/classes/class.ls.posts.php';
include LS_ROOT_PATH.'/classes/class.ls.sliders.php';
include LS_ROOT_PATH.'/classes/class.ls.sources.php';

// Back-end only
if( is_admin() ) {
	include LS_ROOT_PATH.'/wp/activation.php';
	include LS_ROOT_PATH.'/wp/tinymce.php';
	include LS_ROOT_PATH.'/wp/notices.php';
	include LS_ROOT_PATH.'/wp/actions.php';
	include LS_ROOT_PATH.'/classes/class.ls.revisions.php';

	LS_Revisions::init();
}

if( ! class_exists('KM_PluginUpdatesV3') ) {
	require_once LS_ROOT_PATH.'/classes/class.km.autoupdate.plugins.v3.php';
}

// Register [layerslider] shortcode
LS_Shortcode::registerShortcode();


// Add default skins.
// Reads all sub-directories (individual skins) from the given path.
LS_Sources::addSkins(LS_ROOT_PATH.'/static/layerslider/skins/');


// Setup auto updates. This class also has additional features for
// non-activated sites such as fetching update info.
$GLOBALS['LS_AutoUpdate'] = new KM_PluginUpdatesV3(array(
	'name' => 'LayerSlider WP',
	'repoUrl' => LS_REPO_BASE_URL,
	'root' => LS_ROOT_FILE,
	'version' => LS_PLUGIN_VERSION,
	'itemID' => LS_MARKETPLACE_ID,
	'codeKey' => 'layerslider-purchase-code',
	'authKey' => 'layerslider-authorized-site',
	'channelKey' => 'layerslider-release-channel'
));


// Offering a way for authors to override LayerSlider resources by
// triggering filter and action hooks after the theme has loaded.
add_action('after_setup_theme', function() {
	define('LS_ROOT_URL', apply_filters('layerslider_root_url', plugins_url('', __FILE__)));
	layerslider_loaded();
});


// Load locales
add_action('plugins_loaded', function() {
	load_plugin_textdomain('LayerSlider', false, LS_PLUGIN_SLUG . '/locales/' );
});
