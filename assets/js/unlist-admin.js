/**
 * Quick Edit: ticks the Unlist box when the row's Unlisted column says the
 * entry is unlisted (inc/unlist.php). Post lists only.
 */
( function ( $ ) {
	'use strict';

	if ( ! $ || typeof window.inlineEditPost === 'undefined' ) {
		return;
	}

	var original = window.inlineEditPost.edit;

	window.inlineEditPost.edit = function ( id ) {
		original.apply( this, arguments );

		var postId = parseInt( typeof id === 'object' ? this.getId( id ) : id, 10 );

		if ( ! postId ) {
			return;
		}

		var unlisted = String( $( '#post-' + postId ).find( '.murtadd-unlist-status' ).data( 'unlisted' ) ) === '1';

		$( '#edit-' + postId ).find( 'input[name="murtadd_unlisted"]' ).prop( 'checked', unlisted );
	};
}( window.jQuery ) );
