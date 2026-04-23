/**
 * bcmToast — stacking toast notifications for BuddyPress Contact Me.
 *
 * Never use window.alert / native notices. Paid-plugin UX requires this.
 *
 *   bcmToast( 'Message sent.', 'success' );
 *
 * Types: 'info' | 'success' | 'warning' | 'error'
 */
( function () {
	'use strict';

	window.bcmToast = function ( message, type, duration ) {
		type     = type     || 'info';
		duration = duration || 3500;

		var container = document.getElementById( 'bcm-toast-container' );
		if ( ! container ) {
			container = document.createElement( 'div' );
			container.id = 'bcm-toast-container';
			container.className = 'bcm-toast-container';
			document.body.appendChild( container );
		}

		var toast = document.createElement( 'div' );
		toast.className = 'bcm-toast bcm-toast--' + type;
		toast.setAttribute( 'role', 'status' );
		toast.setAttribute( 'aria-live', 'polite' );
		toast.textContent = message;

		container.appendChild( toast );

		void toast.offsetWidth;
		toast.classList.add( 'bcm-toast--visible' );

		window.setTimeout( function () {
			toast.classList.remove( 'bcm-toast--visible' );
			toast.addEventListener( 'transitionend', function () {
				toast.remove();
			}, { once: true } );
		}, duration );
	};
}() );
