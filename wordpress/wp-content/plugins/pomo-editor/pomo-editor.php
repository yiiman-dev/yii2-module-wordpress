<?php
/*
Plugin Name: PO/MO Editor
Plugin URI: https://github.com/dougwollison/pomo-editor
Description: Edit gettext po/mo files within WordPress.
Version: 1.4.2
Author: Doug Wollison
Author URI: http://dougw.me
Tags: pomo, po file, mo file, gettext, file editor
License: GPL2
Text Domain: pomo-editor
Domain Path: /languages
*/

// =========================
// ! Constants
// =========================

/**
 * Reference to the plugin file.
 *
 * @since 1.0.0
 *
 * @var string
 */
define( 'PME_PLUGIN_FILE', __FILE__ );

/**
 * Reference to the plugin directory.
 *
 * @since 1.0.0
 *
 * @var string
 */
define( 'PME_PLUGIN_DIR', dirname( PME_PLUGIN_FILE ) );

/**
 * Storage directory for all edited PO/MO files.
 *
 * @since 1.0.0
 *
 * @var string
 */
define( 'PME_CONTENT_DIR', WP_CONTENT_DIR  . '/pomo-editor' );

// =========================
// ! Includes
// =========================

require( PME_PLUGIN_DIR . '/includes/autoloader.php' );
require( PME_PLUGIN_DIR . '/includes/functions-pomoeditor.php' );

// =========================
// ! Setup
// =========================

POMOEditor\System::setup();
