/**
 * WS Paste Cleaner — admin test zone.
 *
 * Reads the textarea, sends it to the server via the
 * `ws_paste_cleaner_test` AJAX action, and renders the cleaned HTML
 * back into the readonly output textarea.
 */
( function ( $ ) {
	'use strict';

	$( function () {

		var $btn    = $( '#ws-test-clean-btn' );
		var $input  = $( '#ws-test-input' );
		var $output = $( '#ws-test-output' );

		if ( ! $btn.length || ! $input.length || ! $output.length ) {
			return;
		}

		$btn.on( 'click', function () {

			var html = $input.val() || '';

			if ( '' === html.trim() ) {
				$output.val( '' );
				return;
			}

			// Read the currently-selected level from the form, fall back to moderate.
			var level = $( 'input[name="ws_paste_cleaner_level"]:checked' ).val() || 'moderate';

			$btn.prop( 'disabled', true );

			$.post( wsPasteCleanerTest.ajaxUrl, {
				action: 'ws_paste_cleaner_test',
				nonce:  wsPasteCleanerTest.nonce,
				html:   html,
				level:  level
			} )
				.done( function ( res ) {
					if ( res && res.success && res.data && typeof res.data.html === 'string' ) {
						$output.val( res.data.html );
					} else {
						$output.val( '' );
					}
				} )
				.fail( function () {
					$output.val( '' );
				} )
				.always( function () {
					$btn.prop( 'disabled', false );
				} );

		} );

	} );

} )( jQuery );
