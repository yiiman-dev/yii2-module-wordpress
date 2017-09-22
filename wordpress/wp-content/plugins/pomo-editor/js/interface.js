/* globals _, POMOEditor, pomoeditorL10n, alert, confirm */
jQuery( function( $ ) {
	var $filters = {
		type:   $( '#filter_by_type' ),
		slug:   $( '#filter_by_package' ),
		lang:   $( '#filter_by_language' ),
		modded: $( '#filter_modded_only' )
	};

	$( '#pomoeditor_translations' ).on( 'click', '.pme-source .pme-inputs .pme-input[readonly]', function() {
		alert( pomoeditorL10n.SourceEditingNotice );
	} );
	$( '#pomoeditor_translations' ).on( 'click', '.pme-context .pme-inputs .pme-input[readonly]', function() {
		alert( pomoeditorL10n.ContextEditingNotice );
	} );
	$( '#pomoeditor_translations' ).on( 'click', '.pme-comments .pme-input[readonly]', function() {
		alert( pomoeditorL10n.CommentEditingNotice );
	} );

	$( '#pomoeditor_advanced' ).click( function() {
		if ( POMOEditor.advanced ) {
			return;
		}

		if ( ! confirm( pomoeditorL10n.ConfirmAdvancedEditing ) ) {
			return;
		}

		POMOEditor.advanced = true;
		$( this ).addClass( 'active' ).text( pomoeditorL10n.AdvancedEditingEnabled );
		$( 'body' ).addClass( 'pomoeditor-advanced-mode' );

		// Turn off read-only on all fields and strip from templates
		$( '.pme-input' ).attr( 'readonly', false );
		POMOEditor.HeadersEditor.rowTemplate      = POMOEditor.HeadersEditor.rowTemplate.replace( /readonly/g, '' );
		POMOEditor.TranslationsEditor.rowTemplate = POMOEditor.TranslationsEditor.rowTemplate.replace( /readonly/g, '' );
	} );

	$( '#pomoeditor_revert' ).click( function( e ) {
		if ( ! confirm( pomoeditorL10n.RevertWarning ) ) {
			e.preventDefault();
		}
	} );

	$( '.pomoeditor-filter' ).change( function() {
		var filter, visible;

		filter = {
			type:   $filters.type.val(),
			slug:   $filters.slug.val(),
			lang:   $filters.lang.val(),
			modded: $filters.modded.is( ':checked' )
		};

		visible = {
			type:   [],
			slug:   [],
			lang:   [],
			modded: []
		};

		_( POMOEditor.List.children ).each( function( view ) {
			var type   = view.model.get( 'pkginfo' ).type,
				slug   = view.model.get( 'pkginfo' ).slug,
				lang   = view.model.get( 'language' ).code,
				modded = view.model.get( 'is_modded' );

			view.$el.show();

			if ( filter.type && type !== filter.type ) {
				view.$el.hide();
				return;
			}

			if ( filter.slug && slug !== filter.slug ) {
				view.$el.hide();
				return;
			}

			if ( filter.lang && lang !== filter.lang ) {
				view.$el.hide();
				return;
			}

			if ( filter.modded !== modded ) {
				view.$el.hide();
				return;
			}

			visible.type.push( type );
			visible.slug.push( slug );
			visible.lang.push( lang );
		} );

		visible.type = _( visible.type ).uniq();
		visible.slug = _( visible.slug ).uniq();
		visible.lang = _( visible.lang ).uniq();

		_( $filters ).each( function( $filter, key ) {
			$filter.find( 'option' ).show();
			if ( ! filter[ key ] ) {
				$filter.find( 'option[value!=""]' ).each( function() {
					if ( _( visible[ key ] ).indexOf( $( this ).attr( 'value' ) ) === -1 ) {
						$( this ).hide();
					}
				} );
			}
		} );
	} );

	$( '#pomoeditor' ).submit( function( e ) {
		var Project, $storage, data;

		if ( $( '.pme-translation.changed' ).length > 0 ) {
			if ( ! confirm( pomoeditorL10n.ConfirmSave ) ) {
				e.preventDefault();
				return;
			}
		}

		$( '#submit' ).text( pomoeditorL10n.SavingTranslations );

		POMOEditor.Project.Translations.each( function( translation ) {
			translation.view.close( null, true );
		} );

		Project = POMOEditor.Project;
		$storage = $( '<textarea name="podata"></textarea>' ).hide().appendTo( this );

		data = {
			entries: []
		};

		Project.Translations.each( function( entry ) {
			data.entries.push( entry.attributes );
		} );

		// If in advanced editing mode, include the headers/metadata
		if ( POMOEditor.advanced ) {
			data.headers = {};
			data.metadata = {};

			Project.Headers.each( function( entry ) {
				data.headers[ entry.get( 'name' ) ] = entry.get( 'value' );
			} );

			Project.Metadata.each( function( entry ) {
				data.metadata[ entry.get( 'name' ) ] = entry.get( 'value' );
			} );
		}

		$storage.val( JSON.stringify( data ) );
	} );
} );
