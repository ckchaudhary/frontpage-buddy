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

		// Force block layout if insufficient width
		if ( this._l.parent.width() < 500 ) {
			this._l.parent.addClass( 'force-dblock' );
		}

		//bind 'enable custom front page' checkbox event
        jQuery('input[name="has_custom_frontpage"]').change(function () {
			_class.toggleFPStatus(jQuery(this));
		});

		// Preview to settings
		_class._l.parent.on( 'click', '.fp-widget.state-preview .widget-image,.fp-widget.state-preview .fp-widget-title', function(){
			_class.showWidgetOpts( jQuery(this).closest('.fp-widget') );
		} );

		// Settings to preview
		_class._l.parent.on( 'click', '.close-widget-settings', function(e){
			e.preventDefault();
			let $widget = jQuery(this).closest('.fp-widget');
			$widget.addClass( 'state-preview' ).removeClass( 'state-edit' );

			// Set parent to flex layout if all the widgets inside it are in preview sate.
			let $row = $widget.closest( '.row-contents' );
			if ( $row.find( '.fp-widget.state-edit' ).length === 0 ) {
				$row.removeClass( 'dblock' );
			}
		});
		
		// layout manager
		_class.layout_manager = new FPBuddyLayoutManager( { 'parent' : _class._l.parent }, FRONTPAGE_BUDDY.fp_layout, _class );

		jQuery(document).on('fpbuddy_on_widget_edit', function (e, $widget) {
            if ( $widget.data('type') === 'richcontent' ) {
				//init visual editor
				$widget.find('textarea').trumbowyg({
					btns: [
						['undo', 'redo'], // Only supported in Blink browsers
						['formatting'],
						['strong', 'em', 'del'],
						['link'],
						['insertImage'],
						['unorderedList', 'orderedList'],
						['horizontalRule'],
						['removeformat'],
						['fullscreen']
					]
				});
			}
            
			_class.bindWidgetOptsUpdate( $widget );
        });

		// Do things when a widget is updated.
		_class._l.parent.on( 'widget_updated', '.fp-widget', function(){
			const $widget = jQuery(this);
			if ( $widget.hasClass( 'widget-richcontent' ) ) {
				let html = $widget.find('.field .trumbowyg-box textarea').first().val();
				if ( html.length > 0 ) {
					let text = jQuery("<div>").html( html ).text().substring( 0, 100 );
					$widget.find( '.fp-widget-title' ).text( text );
				}
			} else if( $widget.hasClass( 'widget-instagramprofileembed' ) ) {
				let insta_id = $widget.find('.field [name="insta_id"]').first().val();
				if ( insta_id.length > 0 ) {
					insta_id = jQuery.trim( insta_id );
					insta_id = '@' + insta_id.replace( '@', '' ) + ' - instagram';
					$widget.find( '.fp-widget-title' ).text( insta_id );
				}
			} else if( $widget.hasClass( 'widget-twitterprofile' ) ) {
				let insta_id = $widget.find('.field [name="username"]').first().val();
				if ( insta_id.length > 0 ) {
					insta_id = jQuery.trim( insta_id );
					insta_id = '@' + insta_id.replace( '@', '' ) + ' - X';
					$widget.find( '.fp-widget-title' ).text( insta_id );
				}
			}
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

        let data = {
            'action': FRONTPAGE_BUDDY.config.req.change_status.action,
            '_wpnonce': FRONTPAGE_BUDDY.config.req.change_status.nonce,
			'object_type' : FRONTPAGE_BUDDY.object_type,
			'object_id' : FRONTPAGE_BUDDY.object_id,
			'updated_status': enabled,
        };

        jQuery.ajax({
            type: 'POST',
            url: FRONTPAGE_BUDDY.config.ajaxurl,
            data: data,
        });
    };

	getWidgetsList() {
		let html = '<div class="all-widgets-list">';
		for ( let widget of FRONTPAGE_BUDDY.all_widgets ) {
			html += `
			<div class="widget-to-add widget-${widget.type}" data-type="${widget.type}">
				<div class="widget-header">
					<div class="widget-choose">
						<a href="#"></a>
					</div>	
					<div class="fp-widget-title">
						${widget.name}
					</div>
				</div>
				<div class="widget-desc">
					${widget.description}
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
		let widget_image = '';
		let widget_description = '';

		for ( let i_widget of FRONTPAGE_BUDDY.all_widgets ) {
			if ( i_widget.type === widget_type ) {
				widget_title = i_widget.name;
				widget_image = i_widget.icon;
				widget_description = i_widget.description;
				break;
			}
		}

		let html = `
			<div class='widget-content'>
				<div class="fp-widget state-preview widget-${widget_type}" data-id="${widget_id}" data-type="${widget_type}">
					<div class="fp-widget-title js-show-settings">${widget_title}</div>
					<div class="widget-image js-show-settings">
						<img src='${widget_image}'>
					</div>
					
					<div class="widget-desc">${widget_description}</div>

					<div class="widget-settings"></div>
					<div class="loading_overlay"><span class="helper"></span><img src="${FRONTPAGE_BUDDY.config.img_spinner}" ></div>
				</div>
			</div>

			<div class="remove_item remove_widget">
				<a href="#"></a>
			</div>
		`;

		$el_container.html( html );
		this.showWidgetOpts( $el_container.find('.fp-widget') );
	};

	getWidgetContents( widget_id ) {
		let is_valid = false;
		let widget_type = '';
		let widget_title = '';
		let widget_image = '';
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
					widget_image = i_widget.icon;
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
				<div class="fp-widget-title js-show-settings">${widget_title}</div>
				<div class="widget-image js-show-settings">
					<img src='${widget_image}'>
				</div>
				
				<div class="widget-desc">${widget_description}</div>

				<div class="widget-settings"></div>

				<div class="loading_overlay"><span class="helper"></span><img src="${FRONTPAGE_BUDDY.config.img_spinner}" ></div>
			</div>
			<div class="remove_item remove_widget">
				<a href="#"></a>
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
		let data = {
            'action': FRONTPAGE_BUDDY.config.req.widget_opts_get.action,
            '_wpnonce': FRONTPAGE_BUDDY.config.req.widget_opts_get.nonce,
			'object_type' : FRONTPAGE_BUDDY.object_type,
			'object_id' : FRONTPAGE_BUDDY.object_id,
			'widget_type' : $widget.data('type'),
			'widget_id' : $widget.data('id'),
        };

		jQuery.ajax({
            type: 'GET',
            url: FRONTPAGE_BUDDY.config.ajaxurl,
            data: data,
        }).done(function( res ){
			$widget.removeClass( 'loading' );
			if ( res.success ) {
				$widget.closest('.row-contents').addClass( 'dblock' );
				$widget.find('.widget-settings').html( res.data.html );
				$widget.removeClass( 'state-preview' ).addClass( 'state-edit' );
				jQuery(document).trigger( 'fpbuddy_on_widget_edit', [ $widget ] );
			}
		});
	};

	bindWidgetOptsUpdate ($widget) {
		const _class = this;
        let $form = $widget.find('form');

        var options = {
            beforeSerialize: function () {

            },
            beforeSubmit: function () {
                $widget.find('.response').remove();
                $widget.addClass('loading');
            },
            success: function (response) {
				$widget.removeClass( 'loading' );
				if ( response.success ) {
					if ( response.data.message ) {
						$form.append( `<div class="response alert-success">${response.data.message}</div>` );
					}
					$widget.trigger( 'widget_updated' );
					_class._l.parent.trigger( 'content_updated' );
				} else {
					if ( response.data.message ) {
						$form.append( `<div class="response alert-error">${response.data.message}</div>` );
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
			html += '<div class="splitter"><a href="#"></a></div>';
			html += "<div class='remove_item remove_row'><a href='#'></a></div>"
			html += '</div>';

			html += '<div class="row-contents">';
			html += '<div class="lcol">';
			html += _class.getExpndWidgetOptionsButton();
			html += '</div>';
			html += '</div>';
			
			html += '</div><!-- .row -->';

			_class._l.parent.find('.row-add-new').before( html );
		} );

		// Delete row
		_class._l.parent.on( 'click', ' .remove_row a', function(e){
			e.preventDefault();
			jQuery(this).closest( '.lrow' ).remove();
			_class._l.parent.trigger( 'content_updated' );
		} );

		// Delete columns
		_class._l.parent.on( 'click', ' .remove_widget a', function(e){
			e.preventDefault();

			let $row = jQuery(this).closest('.lrow');
			let $col = jQuery(this).closest('.lcol');
			if ( $row.hasClass( 'lcol-1' ) ) {
				// replace the widget with a 'add-new' widget
				let html = _class.getExpndWidgetOptionsButton();
				$col.html( html );
			} else {
				$col.remove();
				$row.removeClass('lcol-2').addClass('lcol-1');
			}

			_class._l.parent.trigger( 'content_updated' );
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
			
			let html = '<div>';
			html += _class.getCollapseWidgetOptionsButton();
			html += _class.widgets_manager.getWidgetsList();
			html += '</div>';
			$col.html( html );
		});

		// Collapse widgets list
		_class._l.parent.on( 'click', ' .collapse-widgets-list a', function(e){
			e.preventDefault();
			let $col = jQuery(this).closest('.lcol');
			
			let html = _class.getExpndWidgetOptionsButton();
			$col.html( html );
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

			let data = {
				'action': FRONTPAGE_BUDDY.config.req.update_layout.action,
				'_wpnonce': FRONTPAGE_BUDDY.config.req.update_layout.nonce,
				'object_type' : FRONTPAGE_BUDDY.object_type,
				'object_id' : FRONTPAGE_BUDDY.object_id,
				'layout': new_layout,
			};
	
			jQuery.ajax({
				type: 'POST',
				url: FRONTPAGE_BUDDY.config.ajaxurl,
				data: data,
			});
		} );

		// Update widget preview titles when widget 
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
				html += '<div class="splitter">';
				html += '<a href="#"></a>';
				html += '</div>';
				html += '<div class="remove_item remove_row"><a href="#"></a></div>';
				html += '</div><!-- .row-actions -->';

				html += '<div class="row-contents">';

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
				}

				html += '</div><!-- .row-contents -->';
				html += '</div><!-- .row -->';
			}
		}
		html += '<div class="row-add-new"><a href="#"><span></span></a></div>';

		_class._l.parent.html( html );
	};

	getExpndWidgetOptionsButton () {
		return `
			<div class="new-widget">
				<div class="expand-widgets-list">
					<a href="#">
						<span></span>
					</a>
				</div>
				<div class="remove_item remove_widget"><a href="#"></a></div>
			</div>
		`;
	};

	getCollapseWidgetOptionsButton () {
		return `
			<div class="new-widget">
				<div class="collapse-widgets-list">
					<a href="#">
						<span></span>
					</a>
				</div>
				<div class="remove_item remove_widget"><a href="#"></a></div>
			</div>
		`;
	};
}
