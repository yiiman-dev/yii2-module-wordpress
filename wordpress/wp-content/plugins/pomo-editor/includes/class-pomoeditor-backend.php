<?php
/**
 * POMOEditor Backend Functionality
 *
 * @package POMOEditor
 * @subpackage Handlers
 *
 * @since 1.0.0
 */

namespace POMOEditor;

/**
 * The Backend Functionality
 *
 * Hooks into various backend systems to load
 * custom assets and add the editor interface.
 *
 * @internal Used by the System.
 *
 * @since 1.0.0
 */
final class Backend extends Handler {
	// =========================
	// ! Hook Registration
	// =========================

	/**
	 * Register hooks.
	 *
	 * @since 1.2.0 Added MO file update notice.
	 * @since 1.0.0
	 */
	public static function register_hooks() {
		// Don't do anything if not in the backend
		if ( ! is_admin() ) {
			return;
		}

		// Setup stuff
		self::add_action( 'plugins_loaded', 'load_textdomain', 10, 0 );

		// Plugin information
		self::add_action( 'in_plugin_update_message-' . plugin_basename( PME_PLUGIN_FILE ), 'update_notice' );

		// Script/Style Enqueues
		self::add_action( 'admin_enqueue_scripts', 'enqueue_assets' );

		// Notices
		self::add_action( 'admin_notices', 'maybe_print_mofile_update_notice' );
	}

	// =========================
	// ! Setup Stuff
	// =========================

	/**
	 * Load the text domain.
	 *
	 * @since 1.0.0
	 */
	public static function load_textdomain() {
		// Load the textdomain
		load_plugin_textdomain( 'pomo-editor', false, dirname( PME_PLUGIN_FILE ) . '/languages' );
	}

	// =========================
	// ! Plugin Information
	// =========================

	/**
	 * In case of update, check for notice about the update.
	 *
	 * @since 1.0.0
	 *
	 * @param array $plugin The information about the plugin and the update.
	 */
	public static function update_notice( $plugin ) {
		// Get the version number that the update is for
		$version = $plugin['new_version'];

		// Check if there's a notice about the update
		$transient = "pomoeditor-update-notice-{$version}";
		$notice = get_transient( $transient );
		if ( $notice === false ) {
			// Hasn't been saved, fetch it from the SVN repo
			$notice = file_get_contents( "http://plugins.svn.wordpress.org/pomo-editor/assets/notice-{$version}.txt" ) ?: '';

			// Save the notice
			set_transient( $transient, $notice, YEAR_IN_SECONDS );
		}

		// Print out the notice if there is one
		if ( $notice ) {
			echo apply_filters( 'the_content', $notice );
		}
	}

	// =========================
	// ! Script/Style Enqueues
	// =========================

	/**
	 * Enqueue necessary styles and scripts.
	 *
	 * @since 1.3.0 Added revert warning text.
	 * @since 1.0.0
	 */
	public static function enqueue_assets(){
		// Only bother if we're viewing the editor screen
		if ( get_current_screen()->id != 'tools_page_pomo-editor' ) {
			return;
		}

		// Interface styling
		wp_enqueue_style( 'pomoeditor-interface', plugins_url( 'css/interface.css', PME_PLUGIN_FILE ), '1.3.0', 'screen' );

		// Interface javascript
		wp_enqueue_script( 'pomoeditor-framework-js', plugins_url( 'js/framework.js', PME_PLUGIN_FILE ), array( 'backbone' ), '1.3.0' );
		wp_enqueue_script( 'pomoeditor-interface-js', plugins_url( 'js/interface.js', PME_PLUGIN_FILE ), array( 'pomoeditor-framework-js' ), '1.3.0' );

		// Localize the javascript
		wp_localize_script( 'pomoeditor-interface-js', 'pomoeditorL10n', array(
			'SourceEditingNotice' => __( 'You should not edit the source text; errors may occur with displaying the translated text if you do.', 'pomo-editor' ),
			'ContextEditingNotice' => __( 'You should not edit the context; errors may occur with displaying the translated text if you do.', 'pomo-editor' ),
			'CommentEditingNotice' => __( 'You should not edit the comments; these were dictated by the developer.', 'pomo-editor' ),
			'ConfirmAdvancedEditing' => __( 'Are you sure you want enable advanced editing? You may break some of your translations if you change the source text or context values.', 'pomo-editor' ),
			'ConfirmCancel' => __( 'Are you sure you want to discard your changes?', 'pomo-editor' ),
			'ConfirmDelete' => __( 'Are you sure you want to delete this entry? It cannot be undone.', 'pomo-editor' ),
			'ConfirmSave' => __( 'You have uncommitted translation changes, do you want to discard them before saving?', 'pomo-editor' ),
			'AdvancedEditingEnabled' => __( 'Advanced Editing Enabled', 'pomo-editor' ),
			'SavingTranslations' => __( 'Saving Translations...', 'pomo-editor' ),
			'RevertWarning' => __( 'Are you sure you want to revert? The modified version will be deleted and cannot be recovered.', 'pomo-editor' ),
		) );
	}

	// =========================
	// ! Notices
	// =========================

	/**
	 * Helper function; recursively scan the PME content directory for MO files.
	 *
	 * @since 1.2.0
	 *
	 * @param string $dir   Optional The directory to scan.
	 * @param array  $files Optional The list of files to append to.
	 *
	 * @return array The found files.
	 */
	protected static function scan_pme_content_dir( $dir = PME_CONTENT_DIR, $files = array() ) {
		foreach ( scandir( $dir ) as $file ) {
			if ( substr( $file, 0, 1 ) == '.' ) {
				continue;
			}

			$path = "$dir/$file";
			// If it's a directory (but not a link) scan it, append the results
			if ( is_dir( $path ) && ! is_link( $path ) ) {
				$files += self::scan_pme_content_dir( $path, $files );
			} else
			// If it's a file with the .mo extension, append it to the list
			if ( is_file( $path ) && substr( $file, -3 ) === '.mo' ) {
				$files[] = $path;
			}
		}

		return $files;
	}

	/**
	 * Print notice if any original project files have been updated.
	 *
	 * Checks all files in the PME content directory to see if their
	 * orignals have a newer modification time.
	 *
	 * @since 1.4.1 Added file_exists check for original .po files.
	 * @since 1.2.0
	 */
	public static function maybe_print_mofile_update_notice() {
		// Get all the files found
		$pme_mofiles = self::scan_pme_content_dir();

		$files_to_edit = array();
		// Loop through them and check their originals for updates
		foreach ( $pme_mofiles as $file ) {
			$original = str_replace( PME_CONTENT_DIR, WP_CONTENT_DIR, $file );
			if ( file_exists( $original ) && filemtime( $original ) > filemtime( $file ) ) {
				$file = substr( $file, strlen( WP_CONTENT_DIR . '/' ) );
				$file = substr( $file, 0, -3 ) . '.po';
				$files_to_edit[] = $file;
			}
		}

		if ( $files_to_edit ) {
			?>
			<div class="error notice is-dismissible">
				<p><strong><?php _e( 'One or more translation files have had their originals updated. Please update your edited versions of them.', 'pomo-editor' ); ?></strong></p>
				<ul>
					<?php foreach ( $files_to_edit as $file ) : ?>
						<li><a href="<?php echo admin_url( "tools.php?page=pomo-editor&pofile={$file}&changes-saved=true" ); ?>" target="_blank"><?php echo $file; ?></a></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php
		}
	}
}
