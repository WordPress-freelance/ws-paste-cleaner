/**
 * WS Paste Cleaner — TinyMCE (Classic Editor) integration
 *
 * Hooks into the editor's paste_preprocess event. When Word markup is
 * detected, sends the HTML to the server for cleaning and replaces the
 * paste content with the cleaned version.
 *
 * The wp* globals are exposed by WordPress when the Classic Editor is
 * present, so we don't need any external dependency.
 */
( function () {
	'use strict';

	if ( typeof window.tinymce === 'undefined' ) {
		return;
	}

	var settings = window.wsPasteCleaner || {};

	function looksLikeWord( html ) {
		if ( ! html ) {
			return false;
		}
		return /class=["'][^"']*Mso/i.test( html )
			|| /<o:p\b/i.test( html )
			|| /<w:[a-z]+\b/i.test( html )
			|| /<v:[a-z]+\b/i.test( html )
			|| /xmlns:[ovw]=/i.test( html )
			|| /<!\-\-\s*\[if\s+gte\s+mso/i.test( html );
	}

	tinymce.create( 'tinymce.plugins.ws_paste_cleaner', {

		init: function ( ed ) {

			ed.on( 'PastePreProcess', function ( e ) {

				if ( ! looksLikeWord( e.content ) ) {
					return;
				}

				// Block default paste pipeline; we'll re-insert cleaned HTML.
				e.preventDefault();

				var formData = new FormData();
				formData.append( 'action', 'ws_paste_cleaner_clean' );
				formData.append( 'nonce', settings.nonce );
				formData.append( 'html', e.content );
				formData.append( 'level', settings.level || 'moderate' );

				fetch( settings.ajaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					body: formData
				} )
					.then( function ( r ) { return r.json(); } )
					.then( function ( json ) {
						if ( json && json.success && json.data && typeof json.data.html === 'string' ) {
							ed.execCommand( 'mceInsertContent', false, json.data.html );
						}
					} )
					.catch( function () { /* silent fail */ } );
			} );
		}

	} );

	tinymce.PluginManager.add( 'ws_paste_cleaner', tinymce.plugins.ws_paste_cleaner );

} )();
