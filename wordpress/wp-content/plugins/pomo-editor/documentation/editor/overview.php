<title><?php _e( 'Overview', 'pomo-editor' ); ?></title>

<p><?php _e( 'This screen is the translation editor, where you can make changes to the file and recompile it.', 'pomo-editor' ); ?></p>

<p><?php
/* Translators: %1$s = edit icon, %2$s = checkmark icon, %3$s = close icon, %4$s = trash icon */
printf( __( 'Click the %1$s icon to open an entry for editing. When you’re done with your changes to an entry, <strong>you must click the %2$s icon to save it</strong>, or otherwise the %3$s icon to discard your changes. To delete an entry, click the %4$s icon (this cannot be undone).', 'pomo-editor' ), '<i class="dashicons dashicons-edit"></i>', '<i class="dashicons dashicons-yes"></i>', '<i class="dashicons dashicons-no"></i>', '<i class="dashicons dashicons-trash"></i>' ); ?></p>

<p><?php
/* Translators: %1$s = plus icon */
printf( __( 'By default, editing the source text and context is disabled, since you would need to edit any PHP code referencing it to match. If you know what you’re doing though and need to edit these, click <strong>Enable Advanced Editing</strong>. This will also open up access to add new entries (using the %1$s button) as well as edit the <strong>Headers</strong> entries for the file.' ), '<i class="dashicons dashicons-plus"></i>' ); ?></p>

<p><?php _e( 'When you’re done editing translation entries, click the <strong>Save Translations</strong> button to update the .po file and regenerate the .mo file.', 'pomo-editor' ); ?> <em><?php _e( 'It is recommended you backup the original translation files to be safe, in the event an error occurs with updating/compiling.', 'pomo-editor' ); ?></em></p>

<p><?php _e( 'Some entries include a <em>context</em> qualifier, which allows multiple translations of the same basic text for different uses. The context for an entry cannot be changed.', 'pomo-editor' ); ?></p>

<p><?php _e( 'When editing a translation entry, you can also view the <em>developer comments</em> and <em>code references</em>, as well as leave/edit your own comments under the translation. Like the source text and context, you can edit the developer comment and code references in Advanced Editing Mode.', 'pomoe-editor' ); ?></p>
