/**
 * BuddyPress Contact Me — public behaviour.
 *
 * Vanilla JS, uses wp.apiFetch for REST calls, bcmToast for feedback and
 * bcmConfirm for confirmation. No window.confirm / window.alert anywhere.
 */
( function () {
	'use strict';

	var cfg = window.bcmContactMe || {};
	var apiFetch = window.wp && window.wp.apiFetch ? window.wp.apiFetch : null;
	if ( apiFetch && cfg.restNonce ) {
		apiFetch.use( apiFetch.createNonceMiddleware( cfg.restNonce ) );
	}

	var toast = function ( m, t ) { if ( window.bcmToast ) { window.bcmToast( m, t ); } };

	document.addEventListener( 'DOMContentLoaded', function () {
		initContactForm();
		initInbox();
		initMessagePage();
		initCopyLink();
		initIntroDismiss();
		injectSubnavUnreadCount();
		showArrivalToast();
	} );

	function injectSubnavUnreadCount() {
		var count = parseInt( cfg.unreadCount, 10 );
		if ( ! count || count <= 0 ) { return; }
		var slug = cfg.inboxSlug || 'inbox';
		var link = document.getElementById( slug );
		if ( ! link || link.querySelector( '.bcm-subnav-count' ) ) { return; }
		var pill = document.createElement( 'span' );
		pill.className = 'count bcm-subnav-count';
		pill.textContent = count;
		link.appendChild( document.createTextNode( ' ' ) );
		link.appendChild( pill );
	}

	function initMessagePage() {
		var btn = document.querySelector( '[data-bcm-delete-page]' );
		if ( ! btn || ! apiFetch ) { return; }
		btn.addEventListener( 'click', function () {
			var id   = btn.getAttribute( 'data-bcm-delete-page' );
			var back = btn.getAttribute( 'data-bcm-back' );
			( window.bcmConfirm ? window.bcmConfirm( {
				title:     cfg.i18n.deleteLabel,
				message:   cfg.i18n.confirmDelete,
				confirm:   cfg.i18n.deleteLabel,
				cancel:    cfg.i18n.cancel,
				dangerous: true,
			} ) : Promise.resolve( true ) ).then( function ( ok ) {
				if ( ! ok ) { return; }
				apiFetch( { url: cfg.restUrl + '/' + encodeURIComponent( id ), method: 'DELETE' } )
					.then( function () {
						toast( cfg.i18n.sent, 'success' );
						window.setTimeout( function () { window.location.href = back; }, 600 );
					} )
					.catch( function () {
						toast( cfg.i18n.deleteError, 'error' );
					} );
			} );
		} );
	}

	function initIntroDismiss() {
		var btn = document.querySelector( '[data-bcm-info-dismiss]' );
		if ( ! btn || ! apiFetch ) { return; }
		btn.addEventListener( 'click', function () {
			var panel = btn.closest( '[data-bcm-info]' );
			if ( panel ) {
				panel.style.opacity = '0';
				window.setTimeout( function () { panel.remove(); }, 200 );
			}
			apiFetch( {
				url:    cfg.restUrl.replace( '/messages', '/preferences/intro-dismiss' ),
				method: 'POST',
			} ).catch( function () { /* silent — dismissal is best-effort */ } );
		} );
	}

	function showArrivalToast() {
		if ( window.location.search.indexOf( 'bcm=sent' ) !== -1 ) {
			toast( cfg.i18n.sent, 'success' );
		}
	}

	/* ---------- Contact form ---------- */

	function initContactForm() {
		var form = document.querySelector( '[data-bcm-form]' );
		if ( ! form ) { return; }

		var submitBtn = form.querySelector( '[data-bcm-submit]' );
		var counter   = form.querySelector( '[data-bcm-counter]' );
		var textarea  = form.querySelector( 'textarea[name="bp_contact_me_msg"]' );

		if ( textarea && counter ) {
			var updateCounter = function () {
				counter.textContent = textarea.value.length + ' / ' + textarea.maxLength;
			};
			textarea.addEventListener( 'input', updateCounter );
			updateCounter();
		}

		form.querySelectorAll( 'input[required], textarea[required]' ).forEach( function ( el ) {
			el.addEventListener( 'blur', function () { validateField( el ); } );
			el.addEventListener( 'input', function () { clearFieldError( el ); } );
		} );

		form.addEventListener( 'submit', function ( e ) {
			if ( ! apiFetch || ! cfg.restUrl ) {
				return; // Classic POST fallback.
			}
			e.preventDefault();

			var fields = form.querySelectorAll( 'input[required], textarea[required]' );
			var ok     = true;
			fields.forEach( function ( el ) {
				if ( ! validateField( el ) ) { ok = false; }
			} );
			if ( ! ok ) { return; }

			var fd   = new FormData( form );
			var data = {};
			fd.forEach( function ( v, k ) { data[ k ] = v; } );

			setSubmitting( submitBtn, true );

			apiFetch( {
				url:    cfg.restUrl,
				method: 'POST',
				data:   data,
			} ).then( function ( response ) {
				setSubmitting( submitBtn, false );
				form.reset();
				if ( counter && textarea ) {
					counter.textContent = '0 / ' + textarea.maxLength;
				}
				toast( ( response && response.message ) || cfg.i18n.sent, 'success' );
			} ).catch( function ( err ) {
				setSubmitting( submitBtn, false );
				if ( err && err.errors && err.errors.length ) {
					err.errors.forEach( function ( e ) { toast( e.message, 'error' ); } );
				} else {
					toast( ( err && err.message ) || cfg.i18n.deleteError, 'error' );
				}
			} );
		} );
	}

	function validateField( el ) {
		var describedBy = el.getAttribute( 'aria-describedby' );
		var errEl = describedBy ? document.getElementById( describedBy.split( ' ' ).pop() ) : null;
		var value = ( el.value || '' ).trim();
		var msg   = '';

		if ( el.required && ! value ) {
			msg = cfg.i18n.fieldRequired;
		} else if ( el.type === 'email' && value && ! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( value ) ) {
			msg = cfg.i18n.emailInvalid;
		} else if ( el.minLength && value.length < el.minLength ) {
			msg = cfg.i18n.tooShort.replace( '%d', el.minLength );
		} else if ( el.maxLength > 0 && value.length > el.maxLength ) {
			msg = cfg.i18n.tooLong.replace( '%d', el.maxLength );
		}

		if ( msg ) {
			el.classList.add( 'bcm-field__input--error' );
			if ( errEl ) { errEl.textContent = msg; }
			return false;
		}
		clearFieldError( el );
		return true;
	}

	function clearFieldError( el ) {
		el.classList.remove( 'bcm-field__input--error' );
		var describedBy = el.getAttribute( 'aria-describedby' );
		if ( ! describedBy ) { return; }
		var errEl = document.getElementById( describedBy.split( ' ' ).pop() );
		if ( errEl ) { errEl.textContent = ''; }
	}

	function setSubmitting( btn, sending ) {
		if ( ! btn ) { return; }
		btn.disabled = sending;
		btn.classList.toggle( 'is-sending', sending );
		var label = btn.querySelector( '.bcm-submit__label' );
		if ( label ) {
			if ( sending ) {
				if ( ! label.dataset.defaultLabel ) {
					label.dataset.defaultLabel = label.textContent;
				}
				label.textContent = cfg.i18n.sending;
			} else if ( label.dataset.defaultLabel ) {
				label.textContent = label.dataset.defaultLabel;
			}
		}
	}

	/* ---------- Inbox ---------- */

	function initInbox() {
		var inbox = document.querySelector( '[data-bcm-inbox]' );
		if ( ! inbox ) { return; }

		inbox.addEventListener( 'click', function ( e ) {
			var delBtn = e.target.closest( '[data-bcm-delete]' );
			if ( delBtn ) {
				e.preventDefault();
				confirmAndDelete( delBtn.getAttribute( 'data-bcm-delete' ), inbox );
			}
		} );
	}

	function confirmAndDelete( id, inbox ) {
		( window.bcmConfirm ? window.bcmConfirm( {
			title:     cfg.i18n.deleteLabel,
			message:   cfg.i18n.confirmDelete,
			confirm:   cfg.i18n.deleteLabel,
			cancel:    cfg.i18n.cancel,
			dangerous: true,
		} ) : Promise.resolve( true ) )
			.then( function ( ok ) {
				if ( ! ok ) { return; }
				apiFetch( { url: cfg.restUrl + '/' + encodeURIComponent( id ), method: 'DELETE' } )
					.then( function ( r ) {
						removeRow( inbox, id );
						toast( ( r && r.message ) || cfg.i18n.sent, 'success' );
					} )
					.catch( function () {
						toast( cfg.i18n.deleteError, 'error' );
					} );
			} );
	}

	function removeRow( inbox, id ) {
		var row = inbox.querySelector( '[data-bcm-row="' + id + '"]' );
		if ( ! row ) { return; }
		row.style.opacity = '0';
		window.setTimeout( function () {
			row.remove();
			var remaining = inbox.querySelectorAll( '[data-bcm-row]' ).length;
			if ( remaining === 0 ) {
				window.location.reload();
				return;
			}
			var title = inbox.querySelector( '.bcm-inbox__title' );
			if ( title ) {
				title.textContent = remaining === 1 ? '1 message' : remaining + ' messages';
			}
		}, 200 );
	}

	/* ---------- Copy contact link ---------- */

	function initCopyLink() {
		document.querySelectorAll( '.bcm-copy-link' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var url = btn.getAttribute( 'data-url' );
				if ( ! url ) { return; }

				var done = function () {
					toast( cfg.i18n.sent ? 'Link copied.' : 'Copied.', 'success' );
				};

				if ( navigator.clipboard && navigator.clipboard.writeText ) {
					navigator.clipboard.writeText( url ).then( done ).catch( fallback );
				} else {
					fallback();
				}

				function fallback() {
					var ta = document.createElement( 'textarea' );
					ta.value = url;
					ta.setAttribute( 'readonly', '' );
					ta.style.position = 'absolute';
					ta.style.left     = '-9999px';
					document.body.appendChild( ta );
					ta.select();
					try { document.execCommand( 'copy' ); done(); } catch ( e ) { toast( 'Copy not supported in this browser.', 'warning' ); }
					document.body.removeChild( ta );
				}
			} );
		} );
	}
} )();
