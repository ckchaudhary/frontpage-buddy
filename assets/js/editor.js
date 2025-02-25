class FPBuddyWidgetsManager {
	_l = {};
	layout_manager = {};

	options = {
		'el_content' : '',
	};

	constructor( args ){
		this.options = jQuery.extend( {}, this.options, args )
		this.setup = this.setup.bind(this);
		this.setup();
	};

	setup() {
		const _class = this;
        
		if ( ! _class.getElements() ) {
			return false;
		}

		// Move the whole thing outside the default form
		let $form = this._l.parent.closest('form');
		if ( $form.length > 0 ) {
			$form.after( this._l.outer );
		}

		// Add css class if insufficient width
		if ( this._l.parent.width() < 500 ) {
			this._l.parent.addClass( 'layout-small' );
		}

		//bind 'enable custom front page' checkbox event
        jQuery('input[name="has_custom_frontpage"]').change(function () {
			_class.toggleFPStatus(jQuery(this));
		});

		// Preview to settings & vice-versa
		_class._l.parent.on( 'click', '.fp-widget .js-toggle-widget-state', function(e){
			e.preventDefault();
			let $widget = jQuery(this).closest('.fp-widget');
			
			if ( $widget.hasClass( 'state-preview' ) ) {
				_class.showWidgetOpts( $widget );
			} else {
				$widget.addClass( 'state-preview' ).removeClass( 'state-edit' );

				// Set parent to flex layout if all the widgets inside it are in preview sate.
				let $row = $widget.closest( '.row-contents' );
				if ( $row.find( '.fp-widget.state-edit' ).length === 0 && $row.find( '.all-widgets-list' ).length === 0 ) {
					$row.removeClass( 'dblock' );
				}
			}
		} );
		
		// layout manager
		_class.layout_manager = new FPBuddyLayoutManager( { 'parent' : _class._l.parent }, FRONTPAGE_BUDDY.fp_layout, _class );

		jQuery(document).on('fpbuddy_on_widget_edit', function (e, $widget) {
            if ( $widget.data('type') === 'richcontent' ) {
				//init visual editor
				$widget.find('textarea').trumbowyg({
					btns: FRONTPAGE_BUDDY.rich_content.editor_btns,
					minimalLinks: true,
				});
			}
            
			_class.bindWidgetOptsUpdate( $widget );
        });

		// Do things when a widget is updated.
		_class._l.parent.on( 'widget_updated', '.fp-widget', function(){
			const $widget = jQuery(this);

			// Update widget title.
			let widget_title = $widget.find('.field-heading input').val();
			if ( widget_title ) {
				widget_title = jQuery.trim( widget_title );
			}
			if ( widget_title ) {
				widget_title = widget_title.substring( 0, 100 );
			} else {
				if ( $widget.hasClass( 'widget-richcontent' ) ) {
					let html = $widget.find('.field .trumbowyg-box textarea').first().val();
					if ( html.length > 0 ) {
						widget_title = jQuery("<div>").html( html ).text().substring( 0, 100 );
					}
				} else if( $widget.hasClass( 'widget-instagramprofile' ) ) {
					let insta_id = $widget.find('.field [name="insta_id"]').first().val();
					if ( insta_id.length > 0 ) {
						insta_id = jQuery.trim( insta_id );
						widget_title = '@' + insta_id.replace( '@', '' ) + ' - instagram';
					}
				} else if( $widget.hasClass( 'widget-twitterprofile' ) ) {
					let x_id = $widget.find('.field [name="username"]').first().val();
					if ( x_id.length > 0 ) {
						x_id = jQuery.trim( x_id );
						widget_title = '@' + x_id.replace( '@', '' ) + ' - X';
					}
				} else {
					widget_title = $widget.attr('data-type');
				}
			}

			$widget.find( '.fp-widget-title > span:last' ).text( widget_title );
		} );
    };

	getElements() {
		this._l.outer = jQuery( this.options.el_outer );
		this._l.parent = jQuery( this.options.el_content );
		if ( this._l.parent.length > 0 ) {
			return true;
		} else {
			return false;
		}
	};

    toggleFPStatus ($checkbox) {
		let enabled = 'no';

        if ($checkbox.is( ':checked' )) {
            enabled = 'yes';
        }

        if ( 'yes' == enabled ) {
            jQuery('.show_if_fp_enabled').removeClass( 'fpbuddy_hidden' );
            jQuery('.hide_if_fp_enabled').addClass( 'fpbuddy_hidden' );
        } else {
            jQuery('.show_if_fp_enabled').addClass( 'fpbuddy_hidden' );
			jQuery('.hide_if_fp_enabled').removeClass( 'fpbuddy_hidden' );
        }

		fetch(
			FRONTPAGE_BUDDY.config.rest_url_base + '/status',
			{
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': FRONTPAGE_BUDDY.config.rest_nonce
				},
				body: JSON.stringify({
					'object_type' : FRONTPAGE_BUDDY.object_type,
					'object_id' : FRONTPAGE_BUDDY.object_id,
					'updated_status': enabled,
				}),
			}
		);
    };

	getWidgetsList() {
		let html = '<div class="all-widgets-list">';
		for ( let widget of FRONTPAGE_BUDDY.all_widgets ) {
			html += `
			<div class="widget-to-add widget-${widget.type}" data-type="${widget.type}">
				<div class="widget-choose">
					<a href="#">
						<i class="gg-add"></i>
						<span>${widget.name}</span>
					</a>
				</div>
			</div>
			`;
		}
		html += '</div>';

		return html;
	};

	initNewWidget ( widget_type, $el_container ) {
		let widget_id = Date.now() + '_' + Math.random();
		let widget_title = '';
		let widget_icon = '';
		let widget_description = '';

		for ( let i_widget of FRONTPAGE_BUDDY.all_widgets ) {
			if ( i_widget.type === widget_type ) {
				widget_title = i_widget.name;
				widget_icon = i_widget.icon;
				widget_description = i_widget.description;
				break;
			}
		}

		let html = `
			<div class='widget-content'>
				<div class="fp-widget state-preview widget-${widget_type}" data-id="${widget_id}" data-type="${widget_type}">
					<div class="fp-widget-header">
						<div class="fp-widget-title js-toggle-widget-state">
							${widget_icon}
							<span>${widget_title}</span>
						</div>
						<div class="remove_item remove_widget">
							<a href="#"><i class="gg-close-r"></i></a>
						</div>
					</div>
					<div class="widget-desc"><i class="gg-info"></i>${widget_description}</div>
					<div class="widget-settings"></div>
					<div class="loading_overlay"><span class="helper"></span><img src="${FRONTPAGE_BUDDY.config.img_spinner}" ></div>
				</div>
			</div>	
		`;

		$el_container.html( html );
		this.showWidgetOpts( $el_container.find('.fp-widget') );
	};

	getWidgetContents( widget_id ) {
		let is_valid = false;
		let widget_type = '';
		let widget_title = '';
		let widget_icon = '';
		let widget_description = '';

		for ( let i_widget of FRONTPAGE_BUDDY.added_widgets ) {
			if ( i_widget.id === widget_id ) {
				widget_type  = i_widget.type;
				widget_title = i_widget.title;
				break;
			}
		}

		if ( widget_type ) {
			for ( let i_widget of FRONTPAGE_BUDDY.all_widgets ) {
				if ( i_widget.type === widget_type ) {
					widget_title = widget_title.length > 0 ? widget_title : i_widget.name;
					widget_description = i_widget.description;
					widget_icon = i_widget.icon;
					is_valid = true;
					break;
				}
			}
		}

		if ( !is_valid ) {
			return `
				<div>${FRONTPAGE_BUDDY.lang.invalid}</div>
				<div class="remove_item remove_widget">
					<a href="#"></a>
				</div>
			`;
		}

		return `
			<div class="fp-widget state-preview widget-${widget_type}" data-id="${widget_id}" data-type="${widget_type}">
				<div class="fp-widget-header">
					<div class="fp-widget-title js-toggle-widget-state">
						${widget_icon}
						<span>${widget_title}</span>
					</div>
					<div class="remove_item remove_widget">
						<a href="#"><i class="gg-close-r"></i></a>
					</div>
				</div>
				
				<div class="widget-desc"><i class="gg-info"></i>${widget_description}</div>

				<div class="widget-settings"></div>

				<div class="loading_overlay"><span class="helper"></span><img src="${FRONTPAGE_BUDDY.config.img_spinner}" ></div>
			</div>
		`;
	};

	showWidgetOpts ( $widget ) {
		if ( $widget.find('.widget-settings form').length > 0 ) {
			$widget.closest('.row-contents').addClass( 'dblock' );
			$widget.removeClass( 'state-preview' ).addClass( 'state-edit' );
			return false;
		}

		$widget.addClass( 'loading' );

		let apiUrl = new URL(FRONTPAGE_BUDDY.config.rest_url_base + '/widget-opts', window.location.origin);
		let data = {
			'object_type' : FRONTPAGE_BUDDY.object_type,
			'object_id' : FRONTPAGE_BUDDY.object_id,
			'widget_type' : $widget.data('type'),
			'widget_id' : $widget.data('id'),
		};
		Object.keys(data).forEach(key => apiUrl.searchParams.append(key, data[key]));

		fetch(
			apiUrl.toString(),
			{
				method: 'GET',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': FRONTPAGE_BUDDY.config.rest_nonce
				},
			}
		)
		.then(response => {
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || 'Server error occurred');
                });
            }
            return response.json();
        })
		.then(responseJSON =>{
			if (!responseJSON || !responseJSON.success) {
                throw new Error(responseJSON.message || 'Invalid response format');
            }
			$widget.removeClass( 'loading' );
			$widget.closest('.row-contents').addClass( 'dblock' );
			$widget.find('.widget-settings').html( responseJSON.data.html );
			$widget.removeClass( 'state-preview' ).addClass( 'state-edit' );
			jQuery(document).trigger( 'fpbuddy_on_widget_edit', [ $widget ] );
		})
		.catch(error => {
			$widget.removeClass('loading');
			console.error('Error:', error);
			$widget.find('.widget-settings').html(`<div class="response alert-error">${error.message}</div>`);
		});
	};

	bindWidgetOptsUpdate ($widget) {
		const _class = this;
        let $form = $widget.find('form');

        var options = {
			headers: {
                'X-WP-Nonce': FRONTPAGE_BUDDY.config.rest_nonce
            },
            beforeSerialize: function () {

            },
            beforeSubmit: function () {
                $widget.find('.response').remove();
                $widget.addClass('loading');
            },
            success: function (response) {
				$widget.removeClass( 'loading' );
				if ( response.success ) {
					if ( response.message ) {
						$form.append( `<div class="response alert-success">${response.message}</div>` );
					}
					$widget.trigger( 'widget_updated' );
					_class._l.parent.trigger( 'content_updated' );
				} else {
					if ( response.message ) {
						$form.append( `<div class="response alert-error">${response.message}</div>` );
					}
				}
                
            }
        };
        $form.ajaxForm(options);
    };
};

class FPBuddyLayoutManager {
	options = {};
	_l = {};
	initial_content = '';
	widgets_manager = {};
	max_cols = 2;

	constructor ( args, content, caller ) {
		this.options = jQuery.extend( {}, this.options, args )
		this.initial_content = content;
		this.widgets_manager = caller;
		this.setup = this.setup.bind(this);
		this.setup();
	}

	setup () {
		const _class = this;
		if ( Object.prototype.hasOwnProperty.call( _class.options, 'parent' ) ) {
			_class._l.parent = _class.options.parent;
		} 
		if ( ! Object.prototype.hasOwnProperty.call( _class._l, 'parent' ) ) {
			return false;
		}

		_class.initLayout( this.initial_content );

		_class._l.parent.sortable({
			'items' : ' > .row-content',
			'handle' : '.row-actions',
			'placeholder' : 'lrow sortable-placeholder',
			update: function( event, ui ) {
				_class._l.parent.trigger( 'content_updated' );
			}
		});

		// Add new row
		_class._l.parent.on( 'click', '.row-add-new a',  function(e){
			e.preventDefault();
			let html = '<div class="lrow row-content lcol-1">';

			html += '<div class="row-actions">'
			html += '<div class="fp-mover"><i class="gg-select"></i><span>' + FRONTPAGE_BUDDY.lang.drag_move + '</span></div>';
			html += '<div class="remove_item remove_row"><a href="#"><i class="gg-close-r"></i></a></div>';
			html += '</div>';

			html += '<div class="row-contents">';

			let col_count = 0;
			while( col_count < _class.max_cols ) {
				html += '<div class="lcol">';
				html += _class.getExpndWidgetOptionsButton();
				html += '</div>';
				col_count++;
			}

			html += '</div>';
			
			html += '</div><!-- .row -->';

			_class._l.parent.find('.row-add-new').before( html );
		} );

		// Delete row
		_class._l.parent.on( 'click', ' .remove_row a', function(e){
			e.preventDefault();

			let $row = jQuery(this).closest( '.lrow' );
			let proceed = true;
			// Check if at least one widget is added.
			let has_content = false;
			if ( $row.find('.fp-widget').length > 0 ) {
				has_content = true;
			}

			if ( has_content ) {
				proceed = confirm( FRONTPAGE_BUDDY.lang.confirm_delete_section );
			}

			if ( proceed ) {
				$row.remove();
				_class._l.parent.trigger( 'content_updated' );
			}
		} );

		// Delete columns
		_class._l.parent.on( 'click', ' .remove_widget a', function(e){
			e.preventDefault();
			let $col = jQuery(this).closest('.lcol');

			let proceed = true;
			let updated_data = false;
			if ( $col.find('.fp-widget').length ) {
				updated_data = true;
				proceed = confirm( FRONTPAGE_BUDDY.lang.confirm_delete_widget );
			}

			if ( proceed ) {
				// replace the widget with a 'add-new' widget
				let html = _class.getExpndWidgetOptionsButton();
				$col.html( html );

				let $row = $col.closest('.row-contents');
				// back to flex layout, if possible
				if ( $row.find( '.fp-widget.state-edit' ).length === 0 && $row.find( '.all-widgets-list' ).length === 0 ) {
					$row.removeClass( 'dblock' );
				}

				if ( updated_data ) {
					_class._l.parent.trigger( 'content_updated' );
				}
			}
		} );

		// Add column
		_class._l.parent.on( 'click', ' .splitter a', function(e){
			e.preventDefault();
			let $row = jQuery(this).closest('.lrow');
			if ( $row.hasClass( 'lcol-1' ) ) {
				// Add a new column
				let html = '<div class="lcol">';
				html += _class.getExpndWidgetOptionsButton();
				html += '</div>';
				$row.find('.lcol').after( html );
				$row.removeClass( 'lcol-1' ).addClass( 'lcol-2' );
			}
		});

		// Expand widgets list
		_class._l.parent.on( 'click', ' .expand-widgets-list a', function(e){
			e.preventDefault();
			let $col = jQuery(this).closest('.lcol');
			
			let html = '<div class="choose-widget-to-add">';
			html += _class.getCollapseWidgetOptionsButton();
			html += _class.widgets_manager.getWidgetsList();
			html += '</div>';
			$col.html( html );

			$col.closest('.row-contents').addClass( 'dblock' );
		});

		// Collapse widgets list
		_class._l.parent.on( 'click', ' .collapse-widgets-list a', function(e){
			e.preventDefault();
			let $col = jQuery(this).closest('.lcol');
			
			let html = _class.getExpndWidgetOptionsButton();
			$col.html( html );

			let $row = $col.closest('.row-contents');
			// back to flex layout, if possible
			if ( $row.find( '.fp-widget.state-edit' ).length === 0 && $row.find( '.all-widgets-list' ).length === 0 ) {
				$row.removeClass( 'dblock' );
			}
		});

		// Add widget
		_class._l.parent.on( 'click', ' .widget-choose a', function(e){
			e.preventDefault();
			let widget_type = jQuery(this).closest('.widget-to-add').attr('data-type');
			_class.widgets_manager.initNewWidget( widget_type, jQuery(this).closest('.lcol') );
		});

		// Save layout info when a widget is updated or removed.
		_class._l.parent.on( 'content_updated', function(){
			let new_layout = [];
			jQuery( _class._l.parent ).find( ".row-content" ).each( function(){
				let widgets = [];
				jQuery(this).find(".fp-widget").each( function(){
					widgets.push( jQuery(this).data('id'));
				});

				if ( widgets.length > 0 ) {
					new_layout.push( widgets );
				}
			});

			fetch(
				FRONTPAGE_BUDDY.config.rest_url_base + '/layout',
				{
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': FRONTPAGE_BUDDY.config.rest_nonce
					},
					body: JSON.stringify({
						'object_type' : FRONTPAGE_BUDDY.object_type,
						'object_id' : FRONTPAGE_BUDDY.object_id,
						'layout': new_layout,
					}),
				}
			);
		} );
	}

	/**
	 * @param {object} content
	 */
	initLayout ( layout ) {
		const _class = this;

		/*if ( content.length === 0 ) {
			content = '[[ "","test"],["full width"],[ "another test","test3"],["four",""]]';
		}*/

		let html = "";
		
		if ( layout.length > 0 && typeof layout === 'object' ) {
			for ( let row of layout ) {
				let c_class = ( row.length > 1 ) ? 'lcol-2' : 'lcol-1';
				html += `<div class="lrow row-content ${c_class}">`;

				html += '<div class="row-actions">'
				html += '<div class="fp-mover"><i class="gg-select"></i><span>' + FRONTPAGE_BUDDY.lang.drag_move + '</span></div>';
				html += '<div class="remove_item remove_row"><a href="#"><i class="gg-close-r"></i></a></div>';
				html += '</div><!-- .row-actions -->';

				html += '<div class="row-contents">';

				let col_count = 0;
				for ( let widget_id of row ) {
					html += '<div class="lcol">';
					
					if ( widget_id !== '' ) {
						html += '<div class="widget-content">';
						html += _class.widgets_manager.getWidgetContents( widget_id );
						html += '</div>';
					} else {
						html += _class.getExpndWidgetOptionsButton();
					}

					html += '</div>';
					col_count++;
				}

				if ( col_count < _class.max_cols ) {
					// Add 'add' buttons
					while( col_count < _class.max_cols ) {
						html += '<div class="lcol">';
						html += _class.getExpndWidgetOptionsButton();
						html += '</div>';
						col_count++;
					}
				}

				html += '</div><!-- .row-contents -->';
				html += '</div><!-- .row -->';
			}
		}
		html += '<div class="row-add-new"><a href="#"><span class="gg-add"></span><span>' + FRONTPAGE_BUDDY.lang.add_section + '</span></a></div>';

		_class._l.parent.html( html );
	};

	getExpndWidgetOptionsButton () {
		return `
			<div class="new-widget">
				<div class="expand-widgets-list">
					<a href="#">
						<i class="gg-add"></i>
					</a>
				</div>
			</div>
		`;
	};

	getCollapseWidgetOptionsButton () {
		return `
			<div class="collapse-widgets-list">
				<div class="fp-widget-title">${FRONTPAGE_BUDDY.lang.choose_widget}</div>
				<div class="remove_item remove_widget"><a href="#"><i class="gg-close-r"></i></a></div>
			</div>
		`;
	};
}

jQuery( ($) => {
	let fpbuddy_manager = new FPBuddyWidgetsManager({
		"el_outer" : ".fpbuddy_manage_widgets",
		"el_content" : "#fpbuddy_fp_layout_outer",
	});
});