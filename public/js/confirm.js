/**
 * bcmConfirm — promise-based confirmation dialog for BuddyPress Contact Me.
 *
 * Replacement for window.confirm. Returns a Promise<boolean>.
 *
 *   bcmConfirm({
 *     title:     'Delete message?',
 *     message:   'This cannot be undone.',
 *     confirm:   'Delete',
 *     cancel:    'Cancel',
 *     dangerous: true,
 *   }).then(function(ok) { if (ok) { ... } });
 */
( function () {
	'use strict';

	var i18n = window.bcmConfirmI18n || { confirm: 'Confirm', cancel: 'Cancel' };

	window.bcmConfirm = function ( options ) {
		return new Promise( function ( resolve ) {
			var opts = options || {};

			var overlay = document.createElement( 'div' );
			overlay.className = 'bcm-confirm-overlay';

			var box = document.createElement( 'div' );
			box.className = 'bcm-confirm';
			box.setAttribute( 'role', 'alertdialog' );
			box.setAttribute( 'aria-modal', 'true' );
			box.setAttribute( 'aria-labelledby', 'bcm-confirm-title' );
			box.setAttribute( 'aria-describedby', 'bcm-confirm-message' );

			if ( opts.title ) {
				var title = document.createElement( 'h2' );
				title.className = 'bcm-confirm__title';
				title.id = 'bcm-confirm-title';
				title.textContent = opts.title;
				box.appendChild( title );
			}

			var msg = document.createElement( 'p' );
			msg.className = 'bcm-confirm__message';
			msg.id = 'bcm-confirm-message';
			msg.textContent = opts.message || '';
			box.appendChild( msg );

			var actions = document.createElement( 'div' );
			actions.className = 'bcm-confirm__actions';

			var cancelBtn = document.createElement( 'button' );
			cancelBtn.type = 'button';
			cancelBtn.className = 'bcm-confirm__btn bcm-confirm__btn--cancel';
			cancelBtn.textContent = opts.cancel || i18n.cancel;
			actions.appendChild( cancelBtn );

			var confirmBtn = document.createElement( 'button' );
			confirmBtn.type = 'button';
			confirmBtn.className = 'bcm-confirm__btn bcm-confirm__btn--confirm' + ( opts.dangerous ? ' bcm-confirm__btn--dangerous' : '' );
			confirmBtn.textContent = opts.confirm || i18n.confirm;
			actions.appendChild( confirmBtn );

			box.appendChild( actions );
			overlay.appendChild( box );
			document.body.appendChild( overlay );

			var previousFocus = document.activeElement;

			function close( result ) {
				document.removeEventListener( 'keydown', onKey );
				overlay.classList.remove( 'bcm-confirm-overlay--visible' );
				overlay.addEventListener( 'transitionend', function () {
					overlay.remove();
					if ( previousFocus && previousFocus.focus ) { previousFocus.focus(); }
				}, { once: true } );
				resolve( result );
			}

			function onKey( e ) {
				if ( e.key === 'Escape' ) { close( false ); }
				if ( e.key === 'Enter' && document.activeElement !== cancelBtn ) { close( true ); }
			}

			cancelBtn.addEventListener( 'click', function () { close( false ); } );
			confirmBtn.addEventListener( 'click', function () { close( true ); } );
			overlay.addEventListener( 'click', function ( e ) {
				if ( e.target === overlay ) { close( false ); }
			} );
			document.addEventListener( 'keydown', onKey );

			void overlay.offsetWidth;
			overlay.classList.add( 'bcm-confirm-overlay--visible' );
			window.setTimeout( function () { confirmBtn.focus(); }, 0 );
		} );
	};
}() );
