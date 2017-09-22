<?php
/**
 * POMOEditor System
 *
 * @package POMOEditor
 * @subpackage Handlers
 *
 * @since 1.0.0
 */

namespace POMOEditor;

/**
 * The Main System
 *
 * Sets up all the Handler classes.
 *
 * @api
 *
 * @since 1.0.0
 */
final class System extends Handler {
	// =========================
	// ! Master Setup Method
	// =========================

	/**
	 * Register hooks and esure existence of content folder.
	 *
	 * @since 1.2.0 Added internal hooks setup, PME content directory adding.
	 * @since 1.0.0
	 *
	 * @uses Backend::register_hooks() to setup backend functionality.
	 * @uses Manager::register_hooks() to setup admin screens.
	 * @uses Documenter::register_hooks() to setup admin documentation.
	 */
	public static function setup() {
		// Register the hooks of the subsystems
		Backend::register_hooks();
		Manager::register_hooks();
		Documenter::register_hooks();

		// Ensure the content directory exists
		if ( ! file_exists( PME_CONTENT_DIR ) && is_readable( dirname( PME_CONTENT_DIR ) ) ) {
			wp_mkdir_p( PME_CONTENT_DIR );
		}

		// Register global hooks
		self::register_hooks();
	}

	// =========================
	// ! Setup Utilities
	// =========================

	/**
	 * Register hooks.
	 *
	 * @since 1.2.0
	 */
	public static function register_hooks() {
		self::add_filter( 'load_textdomain_mofile', 'rewrite_textdomain_mofile', 10 );
	}

	// =========================
	// ! MO File Rewriting
	// =========================

	/**
	 * Rewrite the provided MO file with the PME edited version if found and newer.
	 *
	 * @since 1.2.0
	 *
	 * @param string $mofile The path to the MO file.
	 *
	 * @return string The rewritten file path.
	 */
	public static function rewrite_textdomain_mofile( $mofile ) {
		// Determine the location in the PME content directory
		if ( strpos( $mofile, WP_CONTENT_DIR ) === 0 && is_readable( $mofile ) ) {
			$pme_mofile = str_replace( WP_CONTENT_DIR, PME_CONTENT_DIR, $mofile );
			if ( is_readable( $pme_mofile ) && filemtime( $pme_mofile ) > filemtime( $mofile ) ) {
				return $pme_mofile;
			}
		}

		return $mofile;
	}
}
