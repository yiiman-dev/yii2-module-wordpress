<?php
/**
 * POMOEditor Manager Funtionality
 *
 * @package POMOEditor
 * @subpackage Handlers
 *
 * @since 1.0.0
 */

namespace POMOEditor;

/**
 * The Management System
 *
 * Hooks into the backend to add the interfaces for
 * managing the configuration of POMOEditor.
 *
 * @internal Used by the System.
 *
 * @since 1.0.0
 */
final class Manager extends Handler {
	// =========================
	// ! Hook Registration
	// =========================

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 */
	public static function register_hooks() {
		// Don't do anything if not in the backend
		if ( ! is_admin() ) {
			return;
		}

		// Settings & Pages
		self::add_action( 'admin_menu', 'add_menu_pages' );
		self::add_action( 'admin_init', 'process_request' );
		self::add_action( 'admin_head', 'display_help_tabs' );
		self::add_action( 'admin_notices', 'print_notices' );
	}

	// =========================
	// ! Settings Page Setup
	// =========================

	/**
	 * Register admin pages.
	 *
	 * @since 1.0.0
	 *
	 * @uses Manager::settings_page() for general options page output.
	 */
	public static function add_menu_pages() {
		// Main Interface page
		$interface_page_hook = add_management_page(
			__( 'PO/MO Editor', 'pomo-editor' ), // page title
			__( 'PO/MO Editor', 'pomo-editor' ), // menu title
			'manage_options', // capability
			'pomo-editor', // slug
			array( get_called_class(), 'admin_page' ) // callback
		);
	}

	/**
	 * Setup the help tabs based on what's being displayed for the page.
	 *
	 * @since 1.2.1 Added check for NULL screen.
	 * @since 1.0.0
	 *
	 * @uses Documenter::setup_help_tabs() to display the appropriate help tabs.
	 */
	public static function display_help_tabs() {
		$screen = get_current_screen();

		// Abort if no screen or not the admin page for this plugin
		if ( ! $screen ||  $screen->id != 'tools_page_pomo-editor' ) {
			return;
		}

		// If the file is specified, setup the interface help tabs
		if ( isset( $_GET['pofile'] ) ) {
			Documenter::setup_help_tabs( 'editor' );
		}
		// Otherwise, assume it's the index
		else {
			Documenter::setup_help_tabs( 'index' );
		}
	}

	// =========================
	// ! Settings Saving
	// =========================

	/**
	 * Check if a file is specified for loading.
	 *
	 * Also save changes to it if posted.
	 *
	 * @since 1.3.0 Added reversion handling.
	 * @since 1.2.0 Added custom save destination; under PME content directory.
	 * @since 1.1.0 Improved sprintf calls for localization purposes.
	 * @since 1.0.0
	 */
	public static function process_request() {
		// Skip if no file is specified
		if ( ! isset( $_REQUEST['pofile'] ) ) {
			return;
		}

		// If file was specified via $_POST, check for manage nonce action
		if ( isset( $_POST['pofile'] ) && ( ! isset( $_POST['_pomoeditor_nonce'] ) || ! wp_verify_nonce( $_POST['_pomoeditor_nonce'], 'pomoeditor-manage-' . md5( $_POST['pofile'] ) ) ) ) {
			wp_die( __( 'Cheatin&#8217; uh?' ), 403 );
		}

		// Create the source/destination paths
		$file = $_REQUEST['pofile'];
		$source = realpath( WP_CONTENT_DIR . '/' . $file );

		// Check that the source exists
		if ( strtolower( pathinfo( $source, PATHINFO_EXTENSION ) ) != 'po' ) {
			/* Translators: %s = full path to file */
			wp_die( sprintf( __( 'The requested file is not supported: %s', 'pomo-editor' ), $source ), 400 );
		}
		// Check the file is a .po file
		elseif ( ! file_exists( $source ) ) {
			/* Translators: %s = full path to file */
			wp_die( sprintf( __( 'The requested file cannot be found: %s', 'pomo-editor' ), $source ), 404 );
		}
		// Check the file is within permitted path
		elseif ( ! is_path_permitted( $source ) ) {
			/* Translators: %s = full path to file */
			wp_die( sprintf( __( 'The requested file is not within one of the permitted paths: %s', 'pomo-editor' ), $source ), 403 );
		}
		// Check the file is writable
		elseif ( ! is_writable( $source ) ) {
			/* Translators: %s = full path to file */
			wp_die( sprintf( __( 'The requested file is not writable: %s', 'pomo-editor' ), $source ), 403 );
		}
		else {
			// Load
			$project = new Project( $source );
			$project->load();

			// Check if the revert nonce is present, validate it and delete the file if it checks out
			if ( isset( $_GET['_pomoeditor_revert'] ) ) {
				if ( ! $project->is_modded() || ! wp_verify_nonce( $_GET['_pomoeditor_revert'], 'pomoeditor-revert-' . md5( $_GET['pofile'] ) ) ) {
					wp_die( __( 'Cheatin&#8217; uh?' ), 403 );
				}

				// Switch to the original for the redirect
				$file = $project->file();

				// Delete the modded file
				unlink( $source );

				$notice = 'file-reverted';
			}
			// Check if the file is being updated
			elseif ( isset( $_POST['podata'] ) ) {
				// Update
				$data = json_decode( stripslashes( $_POST['podata'] ), true );
				$project->update( $data, true );

				// Create destination from $source
				$destination = $source;
				// If the destination isn't already in the PME content directory, prepend it
				if ( strpos( $file, 'pomo-editor/' ) !== 0 ) {
					$destination = str_replace( WP_CONTENT_DIR, PME_CONTENT_DIR, $source );
					$file = 'pomo-editor/' . $file;
				}

				// Save
				$project->export( $destination );

				$notice = 'changes-saved';
			} else {
				return;
			}

			// Redirect
			wp_redirect( admin_url( "tools.php?page=pomo-editor&pofile={$file}&{$notice}=true" ) );
			exit;
		}
	}

	// =========================
	// ! Settings Page Output
	// =========================

	/**
	 * Output for generic settings page.
	 *
	 * @since 1.0.0
	 */
	public static function admin_page() {
?>
		<div class="wrap">
			<h2><?php echo get_admin_page_title(); ?></h2>

			<?php
			if ( isset( $_REQUEST['pofile'] ) ) {
				self::project_editor();
			} else {
				self::project_index();
			}
			?>
		</div>
		<?php
	}

	/**
	 * Output the Project Index interface.
	 *
	 * @since 1.3.0 Updated to use new editpath property for link; fixed listing of modded files.
	 * @since 1.0.0
	 *
	 * @global string $plugin_page The slug of the current admin page.
	 */
	protected static function project_index() {
		global $plugin_page;

		$projects = new Projects();
		$projects->scan();
		?>
		<div class="tablenav top">
			<div class="alignleft actions">
				<label for="filter_by_type" class="screen-reader-text"><?php _e( 'Filter by type', 'pomo-editor' ); ?></label>
				<select id="filter_by_type" class="pomoeditor-filter">
					<option value=""><?php _e( 'All types', 'pomo-editor' ); ?></option>
					<?php foreach ( $projects->types() as $type => $label ) : ?>
					<option value="<?php echo $type; ?>"><?php echo $label; ?></option>
					<?php endforeach; ?>
				</select>
				<label for="filter_by_package" class="screen-reader-text"><?php _e( 'Filter by package', 'pomo-editor' ); ?></label>
				<select id="filter_by_package" class="pomoeditor-filter">
					<option value=""><?php _e( 'All packages', 'pomo-editor' ); ?></option>
					<?php foreach ( $projects->packages() as $package => $label ) : ?>
					<option value="<?php echo $package; ?>"><?php echo $label; ?></option>
					<?php endforeach; ?>
				</select>
				<label for="filter_by_language" class="screen-reader-text"><?php _e( 'Filter by type', 'pomo-editor' ); ?></label>
				<select id="filter_by_language" class="pomoeditor-filter">
					<option value=""><?php _e( 'All languages', 'pomo-editor' ); ?></option>
					<?php foreach ( $projects->languages() as $language => $label ) : ?>
					<option value="<?php echo $language; ?>"><?php echo $label; ?></option>
					<?php endforeach; ?>
				</select>
				<label class="pomoeditor-toggle">
					<input type="checkbox" id="filter_modded_only" class="pomoeditor-filter" />
					<?php _e( 'Show Edited Files Only', 'pomo-editor' ); ?>
				</label>
			</div>
		</div>

		<table id="pomoeditor_projects" class="wp-list-table widefat fixed striped">
			<thead>
				<tr>
					<th class="manage-column column-pmeproject-file"><?php _e( 'File', 'pomo-editor' ); ?></th>
					<th class="manage-column column-pmeproject-title column-primary"><?php _e( 'Package', 'pomo-editor' ); ?></th>
					<th class="manage-column column-pmeproject-type"><?php _e( 'Type', 'pomo-editor' ); ?></th>
					<th class="manage-column column-pmeproject-language"><?php _e( 'Language', 'pomo-editor' ); ?></th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>

		<script type="text/template" id="pomoeditor_item_template">
			<td class="column-pmeproject-file"><a href="<?php echo admin_url( "tools.php?page={$plugin_page}&pofile=" ); ?><%= editpath %>" target="_blank">
				<%= file.dirname %>/<strong><%= file.basename %></strong>
			</a></td>
			<td class="column-pmeproject-title"><%= pkginfo.name %></td>
			<td class="column-pmeproject-type"><%= pkginfo.type %></td>
			<td class="column-pmeproject-language"><%= language.name %></td>
		</script>

		<script>
		POMOEditor.MODDED_BASE_DIR = '<?php echo basename( PME_CONTENT_DIR ); ?>/';

		POMOEditor.Projects = new POMOEditor.Framework.Projects(<?php echo escape_html( json_encode( $projects->dump() ) ); ?>, { parse: true } );

		POMOEditor.List = new POMOEditor.Framework.ProjectsList( {
			el: document.getElementById( 'pomoeditor_projects' ),

			collection: POMOEditor.Projects,

			itemTemplate: document.getElementById( 'pomoeditor_item_template' ),
		} );
		</script>
		<?php
	}

	/**
	 * Output the Project Editor interface.
	 *
	 * @since 1.4.0 Added JSON escaping, dropped metadata editor.
	 * @since 1.3.0 Added revert button.
	 * @since 1.2.0 Added comments/reference display/editing,
	 *              Added Original link on PME edited versions.
	 * @since 1.1.0 Updated add buttons to be advanced-mode-only,
	 *              improved printf calls for localization purposes.
	 * @since 1.0.0
	 *
	 * @global string $plugin_page The slug of the current admin page.
	 */
	protected static function project_editor() {
		global $plugin_page;

		$file = $_GET['pofile'];
		// Load
		$path = realpath( WP_CONTENT_DIR . '/' . $file );
		$project = new Project( $path );
		$project->load();

		// Figure out the text direction for the translated text
		$direction = in_array( substr( $project->language( true ), 0, 2 ), Dictionary::$rtl_languages ) ? 'rtl' : 'ltr';
		?>
		<form method="post" action="tools.php?page=<?php echo $plugin_page; ?>" id="pomoeditor">
			<input type="hidden" name="pofile" value="<?php echo $file; ?>" />
			<?php wp_nonce_field( 'pomoeditor-manage-' . md5( $file ), '_pomoeditor_nonce' ); ?>

			<h2><?php
			/* Translators: %1$s = filename */
			printf( __( 'Editing: <code>%s</code>', 'pomo-editor' ), $file ); ?></h2>

			<?php if ( $project->is_modded() ) :
				$original = $project->file();
				$edit_link = admin_url( "tools.php?page=pomo-editor&pofile={$original}" );
				$revert_nonce = wp_create_nonce( 'pomoeditor-revert-' . md5( $file ) );
				$revert_link = admin_url( "tools.php?page=pomo-editor&pofile={$file}&_pomoeditor_revert={$revert_nonce}" );
				?>
				<p>
					<?php
					/* Translators: %1$s = filename, %2$s = URL */
					printf( __( 'Original: <a href="%2$s" target="_blank">%1$s</a>', 'pomo-editor' ), $original, $edit_link );
					?>
					(<a href="<?php echo $revert_link; ?>" id="pomoeditor_revert" class="hide-if-no-js"><?php _e( 'Revert', 'pomo-editor' ); ?></a>)
				</p>
			<?php endif; ?>

			<p>
				<?php
				/* Translators: %1$s = package name, %2$s = package type (system, theme, plugin) */
				printf( __( '<strong>Package:</strong> %1$s (%2$s)', 'pomo-editor' ), $project->package( 'name' ), $project->package( 'type' ) ); ?>
				<br />
				<?php
				/* Translators: %1$s = language name */
				printf( __( '<strong>Language:</strong> %1$s', 'pomo-editor' ), $project->language() ); ?>
			</p>

			<p>
				<button type="button" id="pomoeditor_advanced" class="button button-secondary"><?php _e( 'Enable Advanced Editing', 'pomo-editor' ); ?></button>
			</p>

			<div class="pomoeditor-advanced">
				<h3><?php _e( 'Headers', 'pomo-editor' ); ?></h3>

				<table id="pomoeditor_headers" class="fixed striped widefat">
					<thead>
						<tr>
							<th class="pme-edit-col">
								<button type="button" title="<?php _e( 'Add Translation Entry', 'pomo-editor' ); ?>" class="pme-button pme-add"><?php _e( 'Add Translation Entry', 'pomo-editor' ); ?></button>
							</th>
							<th class="pme-header-name"><?php _ex( 'Name', 'header name', 'pomo-editor' ); ?></th>
							<th class="pme-header-value"><?php _ex( 'Value', 'header value', 'pomo-editor' ); ?></th>
						</tr>
					</thead>
					<tfoot></tfoot>
					<tbody></tbody>
				</table>
			</div>

			<h3><?php _e( 'Translations', 'pomo-editor' ); ?></h3>

			<table id="pomoeditor_translations" class="fixed striped widefat pme-direction-<?php echo $direction; ?>">
				<thead>
					<tr>
						<th class="pme-edit-col">
							<button type="button" title="<?php _e( 'Add Translation Entry', 'pomo-editor' ); ?>" class="pme-button pme-add pomoeditor-advanced"><?php _e( 'Add Entry', 'pomo-editor' ); ?></button>
						</th>
						<th class="pme-source"><?php _e( 'Source Text', 'pomo-editor' ); ?></th>
						<th class="pme-translation"><?php _e( 'Translated Text', 'pomo-editor' ); ?></th>
						<th class="pme-context"><?php _e( 'Context', 'pomo-editor' ); ?></th>
					</tr>
				</thead>
				<tfoot></tfoot>
				<tbody></tbody>
			</table>

			<p class="submit">
				<button type="submit" id="submit" class="button button-primary"><?php _e( 'Save Translations', 'pomo-editor' ); ?></button>
			</p>

			<script type="text/template" id="pomoeditor_record_template">
				<th class="pme-edit-col">
					<button type="button" title="Delete Record" class="pme-button pme-delete"><?php _e( 'Delete', 'pomo-editor' ); ?></button>
				</th>
				<td class="pme-record-name">
					<input type="text" class="pme-input pme-name-input" value="<%- name %>" />
				</td>
				<td class="pme-record-value">
					<input type="text" class="pme-input pme-value-input" value="<%- value %>" />
				</td>
			</script>

			<script type="text/template" id="pomoeditor_translation_template">
				<td class="pme-edit-col">
					<button type="button" title="Edit Entry" class="pme-button pme-edit"><?php _e( 'Edit', 'pomo-editor' ); ?></button>
					<div class="pme-actions">
						<button type="button" title="Cancel (discard changes)" class="pme-button pme-cancel"><?php _e( 'Cancel', 'pomo-editor' ); ?></button>
						<button type="button" title="Save Changes" class="pme-button pme-save"><?php _e( 'Save', 'pomo-editor' ); ?></button>
						<button type="button" title="Delete Entry" class="pme-button pme-delete"><?php _e( 'Delete', 'pomo-editor' ); ?></button>
					</div>
				</td>
				<td class="pme-source">
					<div class="pme-previews">
						<div class="pme-preview pme-singular" title="<?php _e( 'Singular', 'pomo-editor' ); ?>"></div>
						<div class="pme-preview pme-plural" title="<?php _e( 'Plural', 'pomo-editor' ); ?>"></div>
					</div>
					<div class="pme-inputs">
						<textarea class="pme-input pme-singular" title="<?php _e( 'Singular', 'pomo-editor' ); ?>" rows="4" readonly></textarea>
						<textarea class="pme-input pme-plural" title="<?php _e( 'Plural', 'pomo-editor' ); ?>" rows="4" readonly></textarea>
					</div>
					<div class="pme-comments pme-extracted-comments">
						<div class="pme-preview pomoeditor-basic"></div>
						<textarea class="pme-input pomoeditor-advanced" title="<?php _e( 'Developer Comments', 'pomo-editor' ); ?>" rows="4" readonly></textarea>
					</div>
				</td>
				<td class="pme-translated">
					<div class="pme-previews">
						<div class="pme-preview pme-singular" title="<?php _e( 'Singular', 'pomo-editor' ); ?>"></div>
						<div class="pme-preview pme-plural" title="<?php _e( 'Plural', 'pomo-editor' ); ?>"></div>
					</div>
					<div class="pme-inputs">
						<textarea class="pme-input pme-singular" title="<?php _e( 'Singular', 'pomo-editor' ); ?>" rows="4"></textarea>
						<textarea class="pme-input pme-plural" title="<?php _e( 'Plural', 'pomo-editor' ); ?>" rows="4"></textarea>
					</div>
					<div class="pme-comments pme-translator-comments">
						<textarea class="pme-input" title="<?php _e( 'Translator Comments', 'pomo-editor' ); ?>" rows="4"></textarea>
					</div>
				</td>
				<td class="pme-context">
					<div class="pme-previews">
						<div class="pme-preview"></div>
					</div>
					<div class="pme-inputs">
						<textarea class="pme-input" rows="4" readonly></textarea>
					</div>
					<div class="pme-comments pme-references">
						<ul class="pme-preview pomoeditor-basic"></ul>
						<textarea class="pme-input pomoeditor-advanced" title="<?php _e( 'Code References', 'pomo-editor' ); ?>" rows="4" readonly></textarea>
					</div>
				</td>
			</script>

			<script>
			POMOEditor.Project = new POMOEditor.Framework.Project(<?php echo escape_html( json_encode( $project->dump() ) ); ?>, { parse: true } );

			POMOEditor.HeadersEditor = new POMOEditor.Framework.RecordsEditor( {
				el: document.getElementById( 'pomoeditor_headers' ),

				collection: POMOEditor.Project.Headers,

				rowTemplate: document.getElementById( 'pomoeditor_record_template' ),
			} );

			POMOEditor.TranslationsEditor = new POMOEditor.Framework.TranslationsEditor( {
				el: document.getElementById( 'pomoeditor_translations' ),

				collection: POMOEditor.Project.Translations,

				rowTemplate: document.getElementById( 'pomoeditor_translation_template' ),
			} );
			</script>
		</form>
		<?php
	}

	/**
	 * Print any necessary notices.
	 *
	 * @since 1.0.0
	 */
	public static function print_notices() {
		// Return if not on the editor page
		if ( get_current_screen()->id != 'tools_page_pomo-editor' || ! isset( $_GET['pofile'] ) ) {
			return;
		}

		// Print update notice if changes were saved
		if ( isset( $_GET['file-reverted'] ) && $_GET['file-reverted'] ) {
			?>
			<div class="updated notice is-dismissible">
				<p><strong><?php _e( 'Translations reverted to originals.', 'pomo-editor' ); ?></strong></p>
			</div>
			<?php
		}

		// Print update notice if changes were saved
		if ( isset( $_GET['changes-saved'] ) && $_GET['changes-saved'] ) {
			?>
			<div class="updated notice is-dismissible">
				<p><strong><?php _e( 'Translations saved and recompiled.', 'pomo-editor' ); ?></strong></p>
			</div>
			<?php
		}
	}
}
