/* globals _, Backbone, pomoeditorL10n, confirm */
( function( $ ) {
	var POMOEditor = window.POMOEditor = {
		advanced: false	// Wether or not advanced editing is enabled
	};

	// XSS-Safe Sanitize Utility
	function htmlDecode( text ) {
		if ( typeof text === 'string' ) {
			var doc = new DOMParser().parseFromString( text, 'text/html' );
			return doc.documentElement.textContent || doc.body.innerHTML;
		}
		return text;
	}

	// =========================
	// ! Backbone Stuff
	// =========================

	var Framework = POMOEditor.Framework = {};

	var Record = Framework.Record = Backbone.Model.extend( {
		defaults: {
			name  : '',
			value : ''
		},

		parse: function( attrs ) {
			// Unescape attribute values
			for ( var a in attrs ) {
				if ( attrs[ a ] instanceof Array ) {
					attrs[ a ] = attrs[ a ].map( htmlDecode );
				} else if ( typeof attrs[ a ] === 'string' ) {
					attrs[ a ] = htmlDecode( attrs[ a ] );
				}
			}

			return attrs;
		},
	} );

	var Records = Framework.Records = Backbone.Collection.extend( {
		model: Record,

		reset: function( models, options ) {
			var _models, k;

			if ( ! ( models instanceof Array ) ) {
				_models = [];
				for ( k in models ) {
					_models.push( {
						name: k,
						value: models[ k ]
					} );
				}
				models = _models;
			}

			Backbone.Collection.prototype.reset.call( this, models, options );
		}
	} );

	var Translation = Framework.Translation = Record.extend( {
		defaults: {
			is_plural           : false,
			context             : '',
			singular            : '',
			plural              : '',
			translations        : [],
			translator_comments : '',
			extracted_comments  : '',
			references          : [],
			flags               : []
		},

		constructor: function( attrs, options ) {
			attrs = attrs || {}; // default to empty hash

			// Ensure translations/references/flags are arrays
			if ( ! ( attrs.translations instanceof Array ) ) {
				attrs.translations = [];
			}
			if ( ! ( attrs.references instanceof Array ) ) {
				attrs.references = [];
			}
			if ( ! ( attrs.flags instanceof Array ) ) {
				attrs.flags = [];
			}

			Backbone.Model.call( this, attrs, options );
		},

		initialize: function() {
			this.on( 'change:translations', function() {
				var translations = this.get( 'translations' );
				if ( ! this.get( 'is_plural' ) ) {
					this.attributes.translations = translations.splice( 0, 1 );
				}
			} );
		},

		key: function() {
			var key;

			if ( null === this.get( 'singular' ) || '' === this.get( 'singular' ) ) {
				key = this.cid;
			}

			if ( this.get( 'context' ) ) {
				key = this.get( 'context' ) + String.fromCharCode( 4 ) + this.get( 'singular' );
			} else {
				key = this.get( 'singular' );
			}

			key = key.replace( /[\r\n]+/, '\n' );

			return key;
		},

		mergeWith: function( entry ) {
			this.set( 'flags', this.get( 'flags' ).concat( entry.get( 'flags' ) ) );
			this.set( 'references', this.get( 'references' ).concat( entry.get( 'references' ) ) );

			if ( this.get( 'extracted_comments' ) !== entry.get( 'extracted_comments' ) ) {
				this.set( 'extracted_comments', this.get( 'extracted_comments' ) + entry.get( 'extracted_comments' ) );
			}
		}
	} );

	var Translations = Framework.Translations = Backbone.Collection.extend( {
		model: Translation
	} );

	var Project = Framework.Project = Backbone.Model.extend( {
		defaults: {
			file      : {},
			language  : {},
			pkginfo   : {},
			is_modded : false
		},

		initialize: function( attributes, options ) {
			this.Headers      = new Records();
			this.Translations = new Translations();

			if ( attributes.po_headers ) {
				this.Headers.reset( attributes.po_headers, { parse: options.parse } );
			}
			if ( attributes.po_entries ) {
				this.Translations.reset( attributes.po_entries, { parse: options.parse } );
			}

			var file = this.get( 'file' );
			var editpath = ( attributes.is_modded ? POMOEditor.MODDED_BASE_DIR : '' ) + file.dirname + '/' + file.basename;

			this.set( 'editpath', editpath );
		},
	} );

	var Projects = Framework.Projects = Backbone.Collection.extend( {
		model: Project
	} );

	// =========================
	// ! Views
	// =========================

	var ProjectsList = Framework.ProjectsList = Backbone.View.extend( {
		initialize : function( options ) {
			this.collection = options.collection || new Projects();
			this.children = [];

			options.collection.each( function( project ) {
				this.children.push( new ProjectItem( {
					model: project,
					template: options.itemTemplate
				} ) );
			}.bind( this ) );

			this.render();
		},

		render: function() {
			this.$el.find( 'tbody' ).empty();

			_( this.children ).each( function( view ) {
				this.$el.find( 'tbody' ).append( view.render().el );
			}.bind( this ) );
		}
	} );

	var ProjectItem = Framework.ProjectItem = Backbone.View.extend( {
		tagName: 'tr',

		initialize: function( options ) {
			if ( options.template ) {
				if ( options.template instanceof HTMLElement ) {
					options.template = options.template.innerHTML;
				}

				this.template = _.template( options.template );
			}
		},

		render: function( fresh ) {
			var template = this.template( this.model.attributes );
			this.$el.html( template );
			this.$el.toggleClass( 'pme-is-modded', this.model.get( 'is_modded' ) );
			return this;
		}
	} );

	// =========================
	// ! - Editor Rows
	// =========================

	var EditorRow = Framework.EditorRow = Backbone.View.extend( {
		tagName: 'tr',

		className: 'pme-row',

		events: {
			'click .pme-delete' : 'destroy',
			'change .pme-input' : 'save'
		},

		isBlank: function() {
			return '' === this.$el.find( 'input,textarea' ).val();
		},

		remove: function() {
			this.$el.remove();
		},

		initialize: function( options ) {

			this.model.view = this;

			if ( options.template ) {
				if ( options.template instanceof HTMLElement ) {
					options.template = options.template.innerHTML;
				}

				this.template = _.template( options.template );
			}

			this.listenTo( this.model, 'destroy', this.remove );

			this.render();

			this.$el.data( 'model', this.model );
		},

		render: function() {
			var template = this.template( this.model.attributes );
			this.$el.html( template );
			return this;
		},

		destroy: function() {
			if ( ! this.isBlank() && ! confirm( pomoeditorL10n.ConfirmDelete ) ) {
				return;
			}

			this.model.destroy();
		}
	} );

	var RecordRow = Framework.RecordRow = EditorRow.extend( {
		events: {
			'click .pme-delete'      : 'destroy',
			'keyup .pme-name-input'  : 'updateName',
			'keyup .pme-value-input' : 'updateValue'
		},

		updateName: function( e ) {
			if ( POMOEditor.advanced ) { // Only update name in advanced mode
				this.model.set( 'name', $( e.target ).val() );
			}
		},

		updateValue: function( e ) {
			if ( POMOEditor.advanced ) { // Only update value in advanced mode
				this.model.set( 'value', $( e.target ).val() );
			}
		}
	} );

	var TranslationRow = Framework.TranslationRow = EditorRow.extend( {
		isOpen: false,

		className: 'pme-translation',

		events: {
			'click .pme-delete' : 'destroy',
			'click .pme-edit'   : 'toggle',
			'click .pme-save'   : 'save',
			'click .pme-cancel' : 'close',
			'keyup .pme-input'  : 'checkChanges'
		},

		initialize: function() {
			this.$el.toggleClass( 'has-context', null !== this.model.get( 'context' ) );
			this.$el.toggleClass( 'has-plural', this.model.get( 'is_plural' ) );
			this.$el.toggleClass( 'has-extracted-comments', '' !== this.model.get( 'extracted_comments' ) );
			this.$el.toggleClass( 'has-translator-comments', '' !== this.model.get( 'translator_comments' ) );
			this.$el.toggleClass( 'has-references', this.model.get( 'references' ).length > 0 );

			this.listenTo( this.model, 'change:singular change:plural', this.renderSource );
			this.listenTo( this.model, 'change:translations', this.renderTranslation );
			this.listenTo( this.model, 'change:context', this.renderContext );
			this.listenTo( this.model, 'change:extracted_comments change:translator_comments change:references', this.renderComments );

			EditorRow.prototype.initialize.apply( this, arguments );
		},

		render: function() {
			EditorRow.prototype.render.apply( this );

			this.renderSource();
			this.renderTranslation();
			this.renderContext();
			this.renderComments();
		},

		renderSource: function() {
			var singular = this.model.get( 'singular' ),
				plural = this.model.get( 'plural' );

			this.$el.find( '.pme-source .pme-preview.pme-singular' ).text( singular || '' );
			this.$el.find( '.pme-source .pme-input.pme-singular' ).val( singular );

			this.$el.find( '.pme-source .pme-preview.pme-plural' ).text( plural || '' );
			this.$el.find( '.pme-source .pme-input.pme-plural' ).val( plural );
		},

		renderTranslation: function() {
			var translations = this.model.get( 'translations' ) || [];

			this.$el.find( '.pme-translated .pme-preview.pme-singular' ).text( translations[0] || '' );
			this.$el.find( '.pme-translated .pme-input.pme-singular' ).val( translations[0] );

			this.$el.find( '.pme-translated .pme-preview.pme-plural' ).text( translations[1] || '' );
			this.$el.find( '.pme-translated .pme-input.pme-plural' ).val( translations[1] );
		},

		renderContext: function() {
			var context = this.model.get( 'context' );

			this.$el.find( '.pme-context .pme-preview' ).text( context || '' );
			this.$el.find( '.pme-context .pme-input' ).val( context );
		},

		renderComments: function() {
			var extracted_comments = this.model.get( 'extracted_comments' ),
				translator_comments = this.model.get( 'translator_comments' ),
				references = this.model.get( 'references' ) || [];

			this.$el.find( '.pme-extracted-comments .pme-input' ).val( extracted_comments );
			this.$el.find( '.pme-translator-comments .pme-input' ).val( translator_comments );
			this.$el.find( '.pme-references .pme-input' ).val( references.join( '\n' ) );

			this.$el.find( '.pme-extracted-comments .pme-preview' ).text( extracted_comments || '' );
			this.$el.find( '.pme-references .pme-preview' ).text( references.join( '\n' ) );
		},

		checkChanges: function() {
			var context, singular, plural, translations, extracted_comments, translator_comments, references;

			context              = this.model.get( 'context' );
			singular             = this.model.get( 'singular' );
			plural               = this.model.get( 'plural' );
			translations         = this.model.get( 'translations' );
			extracted_comments   = this.model.get( 'extracted_comments' );
			translator_comments  = this.model.get( 'translator_comments' );
			references           = this.model.get( 'references' ).join( '\n' );

			if ( context  !== this.$el.find( '.pme-context .pme-input' ).val() ||
				 singular !== this.$el.find( '.pme-source .pme-input.pme-singular' ).val() ||
				 plural   !== this.$el.find( '.pme-source .pme-input.pme-singular' ).val() ||
				 translations[0] !== this.$el.find( '.pme-translated .pme-input.pme-singular' ).val() ||
				 translations[1] !== this.$el.find( '.pme-translated .pme-input.pme-singular' ).val() ||
				 extracted_comments  !== this.$el.find( '.pme-extracted-comments .pme-input' ).val() ||
				 translator_comments !== this.$el.find( '.pme-translator-comments .pme-input' ).val() ||
				 references          !== this.$el.find( '.pme-references .pme-input' ).val() )
			{
				this.$el.addClass( 'changed' );
			}
		},

		toggle: function() {
			return this.isOpen ? this.close() : this.open();
		},

		open: function() {
			this.$el.addClass( 'open' );
			this.isOpen = true;
			return this;
		},

		close: function( e, noconfirm ) {
			if ( this.$el.hasClass( 'changed' ) && true !== noconfirm ) {

				// Need to confirm closing without saving changes
				if ( confirm( pomoeditorL10n.ConfirmCancel ) ) {
					this.renderSource();
					this.renderTranslation();
					this.renderContext();
					this.renderComments();
					this.$el.removeClass( 'changed' );
				} else {
					return;
				}
			}

			this.$el.removeClass( 'open' );
			this.isOpen = false;
			return this;
		},

		save: function() {
			if ( POMOEditor.advanced ) { // Only save context/source if in advanced mode
				this.model.set( 'context', this.$el.find( '.pme-context .pme-input' ).val() );
				this.model.set( 'singular', this.$el.find( '.pme-source .pme-input.pme-singular' ).val() );
				this.model.set( 'plural', this.$el.find( '.pme-source .pme-input.pme-plural' ).val() );

				this.model.set( 'extracted_comments', this.$el.find( '.pme-extracted-comments .pme-input' ).val() );
				this.model.set( 'references', this.$el.find( '.pme-references .pme-input' ).val().split( '\n' ) );
			}

			this.model.set( 'translations', [
				this.$el.find( '.pme-translated .pme-input.pme-singular' ).val(),
				this.$el.find( '.pme-translated .pme-input.pme-plural' ).val()
			] );

			this.model.set( 'translator_comments', this.$el.find( '.pme-translator-comments .pme-input' ).val() );

			this.$el.removeClass( 'changed' );
			this.close();

			this.model.isSaved = true;
		}
	} );

	// =========================
	// ! - Editors
	// =========================

	var Editor = Framework.Editor = Backbone.View.extend( {
		events: {
			'click .pme-add': 'addEntry'
		},

		entryView: Backbone.View,

		initialize: function( options ) {
			this.rowTemplate = options.rowTemplate;

			// If the row template is an element, get the inner HTML
			if ( this.rowTemplate instanceof HTMLElement ) {
				this.rowTemplate = this.rowTemplate.innerHTML;
			}

			// Generate the rows for each entry
			this.collection.each( this.addEntry.bind( this ) );

			// Copy the header row to the footer
			this.$el.find( 'tfoot' ).html( this.$el.find( 'thead' ).html() );
		},

		addEntry: function( e ) {
			var entry, event, row, $tbody;

			// Abort if adding a new entry while not in advanced editing mode
			if ( ! ( e instanceof this.entryModel ) && ! POMOEditor.advanced ) {
				return;
			}

			// If e was an itself, assing it to entry, assume no event
			if ( e instanceof this.entryModel ) {
				entry = e;
				event = false;
			}

			// Otherwise, it's the event, and create a blank entry
			else {
				event = e;
				entry = new this.entryModel();
				this.collection.add( entry );
			}

			// Creat the row and add it
			row = new this.entryView( {
				model: entry,
				template: this.rowTemplate
			} );

			$tbody = this.$el.find( 'tbody' );
			if ( event && $( event.target ).parents( 'thead' ).length > 0 ) {
				$tbody.prepend( row.$el );
			} else {
				$tbody.append( row.$el );
			}

			return row;
		}
	} );

	var RecordsEditor = Framework.RecordsEditor = Editor.extend( {
		entryModel : Record,
		entryView  : RecordRow,

		addEntry: function( e ) {
			Editor.prototype.addEntry.apply( this, arguments );
		}
	} );

	var TranslationsEditor = Framework.TranslationsEditor = Editor.extend( {
		entryModel : Translation,
		entryView  : TranslationRow,

		addEntry: function( e ) {
			var row = Editor.prototype.addEntry.apply( this, arguments );

			// If newly generated, open for editing
			if ( row.isBlank() ) {
				row.open();
			}
		}
	} );
} )( jQuery );
