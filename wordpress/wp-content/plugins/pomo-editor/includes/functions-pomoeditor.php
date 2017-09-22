<?php
/**
 * POMOEditor Internal Functions
 *
 * @package POMOEditor
 * @subpackage Utilities
 *
 * @internal
 *
 * @since 1.0.0
 */

namespace POMOEditor;

/**
 * Check if the current directory matches one of the provided paths.
 *
 * @since 1.0.0
 *
 * @param string $dir  The directory to match.
 * @param string $list The list of directories to match against (colon-separated).
 *
 * @return bool Wether or not the directory is within one of the ones listed.
 */
function match_path( $dir, $list ) {
	// Split the list into individual directories
	$list = explode( ':', $list );

	// Loop through, see if $dir is within any of them
	foreach ( $list as $path ) {
		// If not an absolute path, prefix with WP_CONTENT_DIR
		if ( strpos( $path, '/' ) !== 0 ) {
			$path = WP_CONTENT_DIR . '/' . $path;
		}

		// Test if $dir starts with the path
		if ( strpos( $dir, rtrim( $path, '/' ) ) === 0 ) {
			return true;
		}
	}

	return false;
}

/**
 * Check if a path is permitted by the whitelist/blacklist.
 *
 * @since 1.3.0 Updated to check against the storage directory.
 * @since 1.0.0
 *
 * @param string $path The path to check.
 *
 * @return bool Wether or not the path is permitted by the whitelist/blacklist.
 */
function is_path_permitted( $path ) {
	// If it's in the storage directory, automatically return true
	if ( match_path( $path, PME_CONTENT_DIR ) ) {
		return true;
	}

	// Check if POMOEDITOR_SCAN_WHITELIST is defined; make sure it's in it
	if ( defined( 'POMOEDITOR_SCAN_WHITELIST' ) && ! match_path( $path, POMOEDITOR_SCAN_WHITELIST ) ) {
		return false;
	}

	// Check if POMOEDITOR_SCAN_BLACKLIST is defined; make sure it isn't in it
	if ( defined( 'POMOEDITOR_SCAN_BLACKLIST' ) && match_path( $path, POMOEDITOR_SCAN_BLACKLIST ) ) {
		return false;
	}

	return true;
}

/**
 * Bare minimum escaping for <script> purposes.
 *
 * Encode &, < and >
 *
 * @since 1.4.0
 *
 * @param string $text The text to escape.
 *
 * @return string The escaped text.
 */
function escape_html( $html ) {
	$text = str_replace( array(
		'&',
		'<',
		'>',
	), array(
		'&amp;',
		'&lt;',
		'&gt;',
	), $html );

	return $text;
}
