<?php
/**
 * POMOEditor Project Model
 *
 * @package POMOEditor
 * @subpackage Structures
 *
 * @since 1.0.0
 */

namespace POMOEditor;

/**
 * The Project Model
 *
 * A model representing a project file, it's
 * PO data, and it's package information.
 *
 * @package POMOEditor
 * @subpackage Structures
 *
 * @api
 *
 * @since 1.0.0
 */
final class Project {
	// =========================
	// ! Properties
	// =========================

	/**
	 * The file it was loaded from and should save to.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 *
	 * @var string
	 */
	protected $filename = '';

	/**
	 * The language tag of the project.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 *
	 * @var string
	 */
	protected $language = '';

	/**
	 * The metadata of the package it belongs to.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 *
	 * @var array
	 */
	protected $package = array(
		'name' => '',
		'slug' => '',
		'type' => '',
	);

	/**
	 * Wether or not this is a modded project file.
	 *
	 * @internal
	 *
	 * @since 1.2.0
	 *
	 * @access protected
	 *
	 * @var bool
	 */
	protected $is_modded = false;

	/**
	 * The PO interface.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 *
	 * @var \PO
	 */
	protected $po;

	/**
	 * The loaded status.
	 *
	 * @internal
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 *
	 * @var bool
	 */
	protected $loaded = false;

	// =========================
	// ! Property Access
	// =========================

	/**
	 * Get the file of the project, relative to the wp-content directory if applicable.
	 *
	 * @since 1.2.0 Added stripping for PME content directory prefix.
	 * @since 1.0.0
	 *
	 * @return string The file path.
	 */
	public function file() {
		if ( strpos( $this->filename, WP_CONTENT_DIR ) === false ) {
			return $this->filename;
		} else {
			if ( strpos( $this->filename, PME_CONTENT_DIR ) === 0 ) {
				$file = substr( $this->filename, strlen( PME_CONTENT_DIR . '/' ) );
			} else {
				$file = substr( $this->filename, strlen( WP_CONTENT_DIR . '/' ) );
			}
			return $file;
		}
	}

	/**
	 * Get a field of the package metadata.
	 *
	 * @since 1.0.0
	 *
	 * @param string $field The field to retrieve from the package metadata array.
	 *
	 * @return mixed The value of specified field.
	 */
	public function package( $field ) {
		return $this->package[ $field ];
	}

	/**
	 * Get the language of the project.
	 *
	 * @since 1.0.0
	 *
	 * @param bool|string $slug Optional Return just the slug or the language name?
	 *
	 * @return string The language name or slug.
	 */
	public function language( $slug = false ) {
		if ( $slug ) {
			return $this->language;
		} else {
			return Dictionary::identify_language( $this->language );
		}
	}

	/**
	 * Get the is_modded status.
	 *
	 * @since 1.2.0
	 *
	 * @return bool The value of $this->is_modded.
	 */
	public function is_modded() {
		return $this->is_modded;
	}

	// =========================
	// ! Methods
	// =========================

	/**
	 * Create a new project with an assigned file location.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file The path to the file this will import from and export to.
	 */
	public function __construct( $file = null ) {
		// Load necessary libraries
		require_once( ABSPATH . WPINC . '/pomo/po.php' );

		// Create the PO interface
		$this->po = new \PO();

		$this->filename = $file;

		$this->identify();
	}

	/**
	 * Utility for Project::identify() if it's a theme or plugin.
	 *
	 * Uses the already set package type to handle it.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type The type of package expected.
	 * @param string $slug The slug of the package.
	 */
	public function handle_package( $type, $slug ) {
		$this->package['slug'] = $slug;
		$this->package['type'] = $type;

		// Get/cache all themes/plugins available
		if ( ! $themes = wp_cache_get( 'pomo-editor', 'all themes' ) ) {
			$themes = wp_get_themes();
			wp_cache_set( 'pomo-editor', $themes, 'all themes' );
		}
		if ( ! $plugins = wp_cache_get( 'pomo-editor', 'all plugins' ) ) {
			$plugins = get_plugins();
			wp_cache_set( 'pomo-editor', $plugins, 'all plugins' );
		}

		switch ( $type ) {
			case 'theme':
				if ( isset ( $themes[ $slug ] ) ) {
					$this->package['name'] = $themes[ $slug ]->name;
					$this->package['data'] = $themes[ $slug ];
				}
				break;
			case 'plugin':
				foreach ( $plugins as $basename => $plugin ) {
					if ( dirname( $basename ) == $slug ) {
						$this->package['name'] = $plugin['Name'];
						$this->package['data'] = $plugin;
					}
				}
				break;
		}
	}

	/**
	 * Attempts to identify the package the project belongs to.
	 *
	 * Will examine it's location on the server and determine if
	 * it is a theme, plugin, or core package.
	 *
	 * @since 1.2.0 Added is_modded check.
	 * @since 1.0.0
	 */
	public function identify() {
		$slug = $language = null;

		// Extract the package name and language tag from the filename
		if ( preg_match( '/(.*?)-?([a-zA-Z_]+)\.po$/', basename( $this->filename ), $matches ) ) {
			list(, $slug, $language ) = $matches;
		}

		$this->package['slug'] = $slug;
		$this->language = $language;

		// Stop if not under the wp-content directory
		if ( strpos( $this->filename, WP_CONTENT_DIR ) === false ) {
			$this->package['name'] = $slug;
			$this->package['type'] = 'unknown';
			return;
		}

		// Check if modded (under the PME content directory)
		$this->is_modded = strpos( $this->filename, PME_CONTENT_DIR ) === 0;

		// Remove the preceeding path of the content directory, split into path parts
		$path = $this->file();
		$path_parts = explode( '/', $path );

		// Handle based on parent directory
		switch ( $path_parts[0] ) {
			case 'languages':
				// Go by filename or subdir
				switch ( $path_parts[1] ) {
					case 'themes':
						$this->handle_package( 'theme', $slug );
						break;
					case 'plugins':
						$this->handle_package( 'plugin', $slug );
						break;
					default:
						$this->package['type'] = 'system';
						switch ( $slug ) {
							case 'admin':
								$this->package['name'] = __( 'WordPress Admin', 'pomo-editor' );
								break;
							case 'admin-network':
								$this->package['name'] = __( 'WordPress Network Admin', 'pomo-editor' );
								break;
							case 'continents-cities':
								$this->package['name'] = __( 'Continent & City Names', 'pomo-editor' );
								break;
							case '':
							case 'core':
								$this->package['name'] = __( 'WordPress Core', 'pomo-editor' );
								$this->package['slug'] = 'core';
								break;
						}
				}
				break;
			case 'themes':
				$this->handle_package( 'theme', $path_parts[1] );
				break;
			case 'plugins':
				$this->handle_package( 'plugin', $path_parts[1] );
				break;
		}

		// Fallback value for name
		if ( ! $this->package['name'] ) {
			$this->package['name'] = $slug;
		}
	}

	/**
	 * Load data from the file into the PO interface.
	 *
	 * @since 1.0.0
	 *
	 * @uses Project::$filename
	 * @uses Project::$loaded
	 *
	 * @param bool $reload Force reload of the file?
	 */
	public function load( $reload = false ) {
		if ( ! $reload && $this->loaded ) {
			// Already loaded, no reload requested, abort
			return;
		}

		if ( ! file_exists( $this->filename ) ) {
			throw new Exception( "File not found ({$this->filename})" );
		}

		$this->po->import_from_file( $this->filename );

		$this->loaded = true;
	}

	/**
	 * Update with the provided data.
	 *
	 * @since 1.4.0 Dropped metadata updating.
	 * @since 1.0.0
	 *
	 * @param array $data    The data to update with.
	 * @param bool  $replace Optional Replace all headers/entries with those provided?
	 */
	public function update( $data, $replace = false ) {
		// Update headers if present
		if ( isset( $data['headers'] ) ) {
			if ( $replace ) {
				// empty all headers
				$this->po->headers = array();
			}

			$this->po->set_headers( $data['headers'] );
		}
		// Update entries if present
		if ( isset( $data['entries'] ) ) {
			if ( $replace ) {
				// empty all entries
				$this->po->entries = array();
			}

			foreach ( $data['entries'] as $entry ) {
				$this->po->add_entry( $entry );
			}
		}
	}

	/**
	 * Save the PO file and compile corresponding MO file.
	 *
	 * @since 1.2.0 Removed .bak creation, added destination directory generating.
	 * @since 1.0.0
	 *
	 * @uses \PO::export_to_file() to save the updated PO file.
	 * @uses \MO::export_to_file() to compile the MO file.
	 *
	 * @param string $file Optional The file path/name to use.
	 */
	public function export( $file = null ) {
		// Override file property with provided filename
		if ( $file ) {
			$this->filename = $file;
		}

		// Fail if no filename is available
		if ( ! $this->filename ) {
			throw new Exception( 'No path specified to save to.' );
		}

		// Load necessary libraries
		require_once( ABSPATH . WPINC . '/pomo/mo.php' );
		$mo = new \MO();

		// Create the .po and .mo filenames appropriately
		if ( substr( $this->filename, -3 ) == '.po' ) {
			// .po extension exists...
			$po_file = $this->filename;
			// ...replace with .mo
			$mo_file = substr( $this->filename, 0, -3 ) . '.mo';
		} else {
			// No extension, add each
			$po_file = $this->filename . '.po';
			$mo_file = $this->filename . '.mo';
		}

		// Copy all properties from the PO interface to the MO one
		foreach ( get_object_vars( $this->po ) as $key => $val ) {
			$mo->$key = $val;
		}

		// Ensure the parent directory exists
		wp_mkdir_p( dirname( $po_file ) );

		// Export the PO file
		$this->po->export_to_file( $po_file );

		// Compile the MO file
		$mo->export_to_file( $mo_file );
	}

	/**
	 * Dump the PO interface properties as an associative array.
	 *
	 * The entries are exported as a numeric array.
	 *
	 * @since 1.4.0 Dropped metadata dumping.
	 * @since 1.2.0 Added is_modded property.
	 * @since 1.0.0
	 *
	 * @return array The project data.
	 */
	public function dump() {
		$data = array(
			'file'      => pathinfo( $this->file() ),
			'language'  => array(
				'code'  => $this->language,
				'name'  => $this->language(),
			),
			'pkginfo'   => $this->package,
			'is_modded' => $this->is_modded,
		);

		// Add PO info if loaded
		if ( $this->loaded ) {
			$data['po_headers'] = $this->po->headers;
			$data['po_entries'] = array_values( $this->po->entries );
		}

		return $data;
	}
}
