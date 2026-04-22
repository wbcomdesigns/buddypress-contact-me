/**
 * BuddyPress Sticky Post: Admin JS (toast + confirm helpers).
 *
 * Skill Part 6 rules 10 + 12: no browser alert()/confirm() anywhere.
 * Expose window.bcmToast() and window.bcmConfirm() so both this
 * file and future Pro extensions can use the same feedback surface.
 *
 * @since 2.3.7
 */
( function ( $ ) {
	'use strict';

	var i18n = ( window.bcmAdmin && window.bcmAdmin.i18n ) || {};

	/* ─── Toast ─────────────────────────────────────────────── */

	function getToastHost() {
		var host = document.querySelector( '.bcm-toast-host' );
		if ( ! host ) {
			host = document.createElement( 'div' );
			host.className = 'bcm-toast-host';
			document.body.appendChild( host );
		}
		return host;
	}

	function toast( message, tone ) {
		tone = tone || 'info';
		var host = getToastHost();
		var el   = document.createElement( 'div' );
		el.className    = 'bcm-toast bcm-toast--' + tone;
		el.setAttribute( 'role', 'status' );
		el.textContent  = String( message );
		host.appendChild( el );

		// Trigger transition.
		requestAnimationFrame( function () {
			el.classList.add( 'bcm-toast--visible' );
		} );

		window.setTimeout( function () {
			el.classList.remove( 'bcm-toast--visible' );
			window.setTimeout( function () {
				if ( el.parentNode ) {
					el.parentNode.removeChild( el );
				}
			}, 250 );
		}, 3600 );
	}

	window.bcmToast = toast;

	/* ─── Confirm modal (returns a Promise) ──────────────────── */

	function confirmModal( opts ) {
		opts = opts || {};
		return new Promise( function ( resolve ) {
			var backdrop = document.createElement( 'div' );
			backdrop.className = 'bcm-confirm-backdrop';

			var card = document.createElement( 'div' );
			card.className = 'bcm-confirm';
			card.setAttribute( 'role', 'dialog' );
			card.setAttribute( 'aria-modal', 'true' );

			var title = document.createElement( 'h2' );
			title.className = 'bcm-confirm__title';
			title.textContent = opts.title || '';
			if ( opts.title ) { card.appendChild( title ); }

			var desc = document.createElement( 'p' );
			desc.className = 'bcm-confirm__desc';
			desc.textContent = opts.message || i18n.confirmDanger || '';
			if ( opts.message || i18n.confirmDanger ) { card.appendChild( desc ); }

			var actions = document.createElement( 'div' );
			actions.className = 'bcm-confirm__actions';

			var cancelBtn = document.createElement( 'button' );
			cancelBtn.type = 'button';
			cancelBtn.className = 'bcm-btn bcm-btn-secondary';
			cancelBtn.textContent = opts.cancelLabel || i18n.confirmCancel || 'Cancel';

			var confirmBtn = document.createElement( 'button' );
			confirmBtn.type = 'button';
			confirmBtn.className = 'bcm-btn ' + ( 'danger' === opts.tone ? 'bcm-btn-danger': 'bcm-btn-primary' );
			confirmBtn.textContent = opts.confirmLabel || i18n.confirmContinue || 'Continue';

			actions.appendChild( cancelBtn );
			actions.appendChild( confirmBtn );
			card.appendChild( actions );
			backdrop.appendChild( card );
			document.body.appendChild( backdrop );

			function cleanup( result ) {
				document.removeEventListener( 'keydown', onKey );
				if ( backdrop.parentNode ) {
					backdrop.parentNode.removeChild( backdrop );
				}
				resolve( result );
			}

			function onKey( e ) {
				if ( 'Escape' === e.key ) { cleanup( false ); }
				if ( 'Enter' === e.key ) { cleanup( true ); }
			}

			cancelBtn.addEventListener( 'click', function () { cleanup( false ); } );
			confirmBtn.addEventListener( 'click', function () { cleanup( true ); } );
			backdrop.addEventListener( 'click', function ( e ) {
				if ( e.target === backdrop ) { cleanup( false ); }
			} );
			document.addEventListener( 'keydown', onKey );
			confirmBtn.focus();
		} );
	}

	window.bcmConfirm = confirmModal;

	/* ─── Role grid: select all / clear all + live count ─────── */

	function refreshRoleGridCount( $grid ) {
		var $all  = $grid.find( 'input[type="checkbox"]' );
		var total = $all.length;
		var picked = $all.filter( ':checked' ).length;
		var $count = $grid.find( '.bcm-role-grid-count' );
		if ( ! $count.length || ! $count.data( 'bcm-tpl' ) ) {
			// Stash the original template on first call so we can format future updates.
			$count.data( 'bcm-tpl', $count.text() );
		}
		var tpl = $count.data( 'bcm-tpl' ) || '%1$d of %2$d roles selected';
		$count.text( tpl.replace( '%1$d', picked ).replace( '%2$d', total ) );
	}

	$( function () {
		$( '[data-bcm-role-grid]' ).each( function () {
			var $grid = $( this );
			$grid.find( '.bcm-role-grid-count' ).data( 'bcm-tpl', $grid.find( '.bcm-role-grid-count' ).text() );

			$grid.on( 'change', 'input[type="checkbox"]', function () {
				refreshRoleGridCount( $grid );
			} );

			$grid.on( 'click', '[data-bcm-role-action]', function () {
				var action = $( this ).data( 'bcm-role-action' );
				var $boxes = $grid.find( 'input[type="checkbox"]' );
				if ( 'select-all' === action ) {
					$boxes.prop( 'checked', true );
				} else if ( 'clear-all' === action ) {
					$boxes.prop( 'checked', false );
				}
				refreshRoleGridCount( $grid );
			} );
		} );
	} );

	/* ─── Color picker ──────────────────────────────────────── */

	$( function () {
		if ( $.fn.wpColorPicker ) {
			$( '.bcm-color-picker' ).wpColorPicker();
		}

		// Intercept destructive links that opt in via data-bcm-confirm.
		$( document ).on( 'click', '[data-bcm-confirm]', function ( e ) {
			var $el = $( this );
			if ( $el.data( 'bcm-confirm-ok' ) ) { return; }
			e.preventDefault();
			var message = $el.data( 'bcm-confirm' ) || i18n.confirmDanger;
			var tone    = $el.data( 'bcm-confirm-tone' ) || 'danger';
			confirmModal( { message: message, tone: tone } ).then( function ( ok ) {
				if ( ! ok ) { return; }
				$el.data( 'bcm-confirm-ok', true );
				if ( $el.is( 'a' ) ) {
					window.location.href = $el.attr( 'href' );
				} else if ( $el.is( 'button' ) || $el.is( 'input' ) ) {
					var form = $el.closest( 'form' ).get( 0 );
					if ( form ) { form.submit(); }
				}
			} );
		} );
	} );

} )( jQuery );
