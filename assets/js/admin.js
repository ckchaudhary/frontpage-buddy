jQuery( ($) => {
	$('.fpbuddy-color-picker').wpColorPicker();

	$('#btn_color_scheme_reset').click(function(e){
		const $this = $(this);
		e.preventDefault();
		if ( !confirm( $this.data('confirm') ) ) {
			return;
		}

		$('.fpbuddy-color-picker').each(function(){
			const default_val = $(this).data('default');
			$(this).val(default_val).wpColorPicker('color', default_val);
		});

		$this.closest('form').trigger('submit');
	});

	$('.rb_tabify_marker').each(function(){
		let $marker = $(this);
		let $form = $marker.closest('form');
		if ( $form.length > 0 ) {
			let screen_id = $marker.data('id');
			let style = $marker.data('style');
			new RBSettingsTabify( screen_id, $form, style );
		}
	});

	// .
	if ( $('.field-tinymce_tiny').length && typeof tinymce === 'object' ) {
		tinymce.init({
			selector: '.field-tinymce_tiny textarea', // Target your textarea
			branding: false, // Remove "Powered by TinyMCE" branding
			height: 100, // Set height of the editor
			menubar: false,
			toolbar: 'bold italic underline | bullist numlist | link', // Basic formatting options
        	plugins: 'lists link', // Enable necessary plugins
		});
	}

	if ( $('input[name$="show_encourage_prompt]"]').length ) {
		$('input[name$="show_encourage_prompt]"]').change(function(){
			let field_name = $(this).attr('name').replace( 'show_encourage_prompt', 'encourage_prompt_text' );
			const $target = $('[name="'+ field_name +'"]');
			if ( $target.length ) {
				$target.closest('.field').toggleClass('is-hidden');
			}
		});
	}
} );

class RBSettingsTabify {
	#id = '';
	#container = false;
	#style = 'nav';

	get id() { return this.#id; }

	constructor ( screen_id, $container, style ) {
		this.#id = screen_id;
		this.#container = $container;
		style = 'subnav' === style ? 'subnav' : 'nav';
		this.#style = style;
		this.init();
	};

	get_cookie_name () {
		return 'rb_s_tabify_' + this.#id;
	};

	get_current_tab () {
		return docCookies.getItem( this.get_cookie_name() );	
	};

	set_current_tab ( tab ) {
		// valid for 24 hours
		docCookies.setItem( this.get_cookie_name(), tab, 24 * 60 * 60, '/' );
	};

	init () {
		const _class = this;
		// Convert settings sections as tabs
		if ( _class.#container.find( '>h2' ).length > 1 ) { 
			// pick all section titles and convert those into subnav links
			let links = [];

			_class.#container.find( '>h2' ).each(function(){
				let $section_title = jQuery(this);
				let $table = $section_title.find( ' ~ table.form-table:first' );
				if ( ! $table.length ) {
					return 'continue';
				}

				let target_id = 'section_tab_' + links.length;
				// From this h2 to first table.form-table, wrap everything in a div, excluding h2 but including table.form-table
				let elms = [];
				$section_title.find( ' ~ *' ).each( function(){
					let $this = jQuery(this);
					elms.push( this );
					if ( $this.is('table.form-table') ) {
						return false;// break;
					}
				});

				links.push( [ $section_title.html(), target_id ] );
				$section_title.hide();
				
				jQuery( elms ).wrapAll( '<div class="psuedo_subnav_target" id="'+ target_id +'">' );
			});

			if ( links.length > 0 ) {
				let current_tab = _class.get_current_tab();
				let is_current_tab_valid = false;
				for ( let i = 0; i < links.length; i++ ) {
					if ( links[i][1] == current_tab ) {
						is_current_tab_valid = true;
						break;
					}
				}
				if ( !is_current_tab_valid ) {
					current_tab = links[0][1];
				}

				let nav_html = '';

				if ( 'subnav' === _class.#style ) {
					nav_html = '<ul class="subsubsub psuedo_subnav_links">';
					for ( let i = 0; i < links.length; i++ ) {
						nav_html += '<li>';

						nav_html += `<a class="${current_tab === links[i][1] ? 'current' : ''}" href="#${links[i][1]}" >${links[i][0]}</a>`;
						if ( current_tab !== links[i][1] ) {
							jQuery( '#'+links[i][1] ).hide();
						}

						if ( i < ( links.length -1 ) ) {
							nav_html += ' | ';
						}
						nav_html += '</li>';
					}
					nav_html += '</ul>';
				} else {
					nav_html = `<h2 id='nav-${_class.#id}' class='nav-tab-wrapper psuedo_subnav_links'>`;
					for ( let i = 0; i < links.length; i++ ) {
						nav_html += `<a class="nav-tab ${current_tab === links[i][1] ? 'nav-tab-active' : ''}" href="#${links[i][1]}" >${links[i][0]}</a>`;
						if ( current_tab !== links[i][1] ) {
							jQuery( '#'+links[i][1] ).hide();
						}
					}
					nav_html += "</h2>";
				}

				_class.#container.prepend( nav_html );
				
				jQuery('.psuedo_subnav_links a').click(function(e){
					e.preventDefault();
					jQuery('.psuedo_subnav_links a').removeClass('current').removeClass('nav-tab-active');
					jQuery(this).addClass('current').addClass('nav-tab-active');
					jQuery('.psuedo_subnav_target').hide();
					jQuery( jQuery(this).attr('href') ).show();

					_class.set_current_tab( jQuery(this).attr('href').replace( '#', '' ) );
				});
			}
		}
	}
}

/*\
|*|
|*|  :: cookies.js ::
|*|
|*|  A complete cookies reader/writer framework with full unicode support.
|*|
|*|  Revision #3 - July 13th, 2017
|*|
|*|  https://developer.mozilla.org/en-US/docs/Web/API/document.cookie
|*|  https://developer.mozilla.org/User:fusionchess
|*|  https://github.com/madmurphy/cookies.js
|*|
|*|  This framework is released under the GNU Public License, version 3 or later.
|*|  http://www.gnu.org/licenses/gpl-3.0-standalone.html
|*|
|*|  Syntaxes:
|*|
|*|  * docCookies.setItem(name, value[, end[, path[, domain[, secure]]]])
|*|  * docCookies.getItem(name)
|*|  * docCookies.removeItem(name[, path[, domain]])
|*|  * docCookies.hasItem(name)
|*|  * docCookies.keys()
|*|
\*/
var docCookies = docCookies || {
	getItem: function (sKey) {
	if (!sKey) { return null; }
	return decodeURIComponent(document.cookie.replace(new RegExp("(?:(?:^|.*;)\\s*" + encodeURIComponent(sKey).replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=\\s*([^;]*).*$)|^.*$"), "$1")) || null;
	},
	setItem: function (sKey, sValue, vEnd, sPath, sDomain, bSecure) {
	if (!sKey || /^(?:expires|max\-age|path|domain|secure)$/i.test(sKey)) { return false; }
	var sExpires = "";
	if (vEnd) {
		switch (vEnd.constructor) {
		case Number:
			sExpires = vEnd === Infinity ? "; expires=Fri, 31 Dec 9999 23:59:59 GMT" : "; max-age=" + vEnd;
			/*
			Note: Despite officially defined in RFC 6265, the use of `max-age` is not compatible with any
			version of Internet Explorer, Edge and some mobile browsers. Therefore passing a number to
			the end parameter might not work as expected. A possible solution might be to convert the the
			relative time to an absolute time. For instance, replacing the previous line with:
			*/
			/*
			sExpires = vEnd === Infinity ? "; expires=Fri, 31 Dec 9999 23:59:59 GMT" : "; expires=" + (new Date(vEnd * 1e3 + Date.now())).toUTCString();
			*/
			break;
		case String:
			sExpires = "; expires=" + vEnd;
			break;
		case Date:
			sExpires = "; expires=" + vEnd.toUTCString();
			break;
		}
	}
	document.cookie = encodeURIComponent(sKey) + "=" + encodeURIComponent(sValue) + sExpires + (sDomain ? "; domain=" + sDomain : "") + (sPath ? "; path=" + sPath : "") + (bSecure ? "; secure" : "");
	return true;
	},
	removeItem: function (sKey, sPath, sDomain) {
	if (!this.hasItem(sKey)) { return false; }
	document.cookie = encodeURIComponent(sKey) + "=; expires=Thu, 01 Jan 1970 00:00:00 GMT" + (sDomain ? "; domain=" + sDomain : "") + (sPath ? "; path=" + sPath : "");
	return true;
	},
	hasItem: function (sKey) {
	if (!sKey || /^(?:expires|max\-age|path|domain|secure)$/i.test(sKey)) { return false; }
	return (new RegExp("(?:^|;\\s*)" + encodeURIComponent(sKey).replace(/[\-\.\+\*]/g, "\\$&") + "\\s*\\=")).test(document.cookie);
	},
	keys: function () {
	var aKeys = document.cookie.replace(/((?:^|\s*;)[^\=]+)(?=;|$)|^\s*|\s*(?:\=[^;]*)?(?:\1|$)/g, "").split(/\s*(?:\=[^;]*)?;\s*/);
	for (var nLen = aKeys.length, nIdx = 0; nIdx < nLen; nIdx++) { aKeys[nIdx] = decodeURIComponent(aKeys[nIdx]); }
	return aKeys;
	}
};