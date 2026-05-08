/**
 * WS Paste Cleaner — Gutenberg integration
 *
 * Listens to native paste events on the block editor root and, when Word
 * markup is detected, sends the clipboard HTML to the server for cleaning.
 * The cleaned HTML is then dispatched to TinyMCE's clipboard pipeline so
 * Gutenberg's own rich-text handling treats it as a normal paste.
 */
( function () {
	'use strict';

	if ( typeof window.wp === 'undefined' || ! window.wp.domReady ) {
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

	function cleanRemote( html, callback ) {
		var formData = new FormData();
		formData.append( 'action', 'ws_paste_cleaner_clean' );
		formData.append( 'nonce', settings.nonce );
		formData.append( 'html', html );
		formData.append( 'level', settings.level || 'moderate' );

		fetch( settings.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: formData
		} )
			.then( function ( r ) { return r.json(); } )
			.then( function ( json ) {
				if ( json && json.success && json.data && typeof json.data.html === 'string' ) {
					callback( json.data.html );
				}
			} )
			.catch( function () { /* silent fail; original paste already happened */ } );
	}

	function attach() {
		document.addEventListener( 'paste', function ( evt ) {
			if ( ! evt.clipboardData ) {
				return;
			}

			// Only hijack pastes inside the block editor surface.
			var inEditor = evt.target && (
				evt.target.closest( '.block-editor-writing-flow' ) ||
				evt.target.closest( '.editor-styles-wrapper' ) ||
				evt.target.closest( '[contenteditable="true"]' )
			);

			if ( ! inEditor ) {
				return;
			}

			var html = evt.clipboardData.getData( 'text/html' );

			if ( ! looksLikeWord( html ) ) {
				return;
			}

			evt.preventDefault();
			evt.stopPropagation();

			cleanRemote( html, function ( cleaned ) {
				// Re-dispatch a paste event with the cleaned HTML.
				var dt = new DataTransfer();
				dt.setData( 'text/html', cleaned );
				dt.setData( 'text/plain', cleaned.replace( /<[^>]+>/g, '' ) );

				var newEvt = new ClipboardEvent( 'paste', {
					clipboardData: dt,
					bubbles: true,
					cancelable: true
				} );

				if ( evt.target ) {
					evt.target.dispatchEvent( newEvt );
				}
			} );
		}, true );
	}

	wp.domReady( attach );
} )();
