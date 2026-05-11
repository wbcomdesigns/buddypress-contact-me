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

	/* ============================================================
	 * REST helpers — every apiFetch call goes through bcmApi()
	 *
	 * Two problems this layer solves:
	 *
	 *   1. wp.apiFetch sits on top of fetch(), which has no default
	 *      network timeout. A hung server leaves the UI in a permanent
	 *      loading state because the .catch handler never fires. We
	 *      attach an AbortSignal with a ceiling so .catch always runs
	 *      and setSubmitting(false) / button re-enable always happens.
	 *
	 *   2. apiFetch may be unavailable (wp-api-fetch not enqueued, or
	 *      the IIFE ran before wp loaded). bcmApi() returns a rejected
	 *      Promise in that case so callers don't need to null-check
	 *      apiFetch before every call.
	 *
	 * Both problems were universal across Wbcom plugins; centralising
	 * here means future apiFetch sites in this plugin can't regress.
	 * ============================================================ */

	var DEFAULT_TIMEOUT_MS = 15000;

	function bcmTimeoutSignal( ms ) {
		if ( typeof AbortSignal !== 'undefined' && AbortSignal.timeout ) {
			return AbortSignal.timeout( ms );
		}
		if ( typeof AbortController === 'undefined' ) {
			return undefined;
		}
		var controller = new AbortController();
		window.setTimeout( function () { controller.abort(); }, ms );
		return controller.signal;
	}

	/**
	 * Make a REST call against the plugin's bcm/v1 namespace.
	 *
	 * @param {Object} opts
	 * @param {string} opts.url       Full REST URL (built from cfg.restUrl).
	 * @param {string} opts.method    HTTP method.
	 * @param {Object} [opts.data]    Body payload (POST/PUT only).
	 * @param {number} [opts.timeout] Override the 15s default ceiling (ms).
	 * @return {Promise} apiFetch promise, or a rejected promise if apiFetch
	 *                   is unavailable. Always resolves/rejects within the
	 *                   timeout window.
	 */
	function bcmApi( opts ) {
		if ( ! apiFetch ) {
			return Promise.reject( new Error( 'apiFetch unavailable' ) );
		}
		var request = {
			url:    opts.url,
			method: opts.method || 'GET',
			signal: bcmTimeoutSignal( opts.timeout || DEFAULT_TIMEOUT_MS ),
		};
		if ( opts.data ) {
			request.data = opts.data;
		}
		return apiFetch( request );
	}

	/**
	 * Build a route URL on the plugin's REST namespace.
	 *
	 * cfg.restUrl is localized as rest_url('bcm/v1/messages'). Other
	 * routes are derived from it so we only need one localized base.
	 *
	 * @param {string} suffix  Path relative to /bcm/v1, leading slash.
	 * @return {string} Full URL.
	 */
	function bcmRoute( suffix ) {
		// cfg.restUrl ends in /messages; strip and re-append the suffix.
		var base = ( cfg.restUrl || '' ).replace( /\/messages\/?$/, '' );
		return base + suffix;
	}

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
			confirmDeleteMessage( id, function () {
				toast( cfg.i18n.sent, 'success' );
				window.setTimeout( function () { window.location.href = back; }, 600 );
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
			bcmApi( {
				url:     bcmRoute( '/preferences/intro-dismiss' ),
				method:  'POST',
				timeout: 5000, // Best-effort; the panel is already removed from DOM. Shorter ceiling than the 15s default.
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

			bcmApi( {
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
				// AbortError from bcmApi's timeout signal also lands here, so
				// re-enabling the submit button on .catch ensures the UI
				// recovers from a hung server (the original hang-risk bug).
				setSubmitting( submitBtn, false );
				if ( err && err.errors && err.errors.length ) {
					err.errors.forEach( function ( e ) { toast( e.message, 'error' ); } );
				} else {
					// Reuses deleteError as the generic "something went wrong" copy;
					// kept for backward i18n compatibility (no new translation strings).
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
		confirmDeleteMessage( id, function ( response ) {
			removeRow( inbox, id );
			toast( ( response && response.message ) || cfg.i18n.sent, 'success' );
		} );
	}

	/**
	 * Confirm + DELETE a contact message.
	 *
	 * Single source of truth for the delete flow shared by the single-message
	 * page (initMessagePage) and the inbox row delete (confirmAndDelete). The
	 * confirm modal, REST call, success/error toasts and timeout handling all
	 * live here; callers only supply the success-side UI update.
	 *
	 * @param {string|number} id        Message ID.
	 * @param {Function}      onDeleted Called on successful DELETE with the
	 *                                  REST response object.
	 */
	function confirmDeleteMessage( id, onDeleted ) {
		var confirmPromise = window.bcmConfirm
			? window.bcmConfirm( {
				title:     cfg.i18n.deleteLabel,
				message:   cfg.i18n.confirmDelete,
				confirm:   cfg.i18n.deleteLabel,
				cancel:    cfg.i18n.cancel,
				dangerous: true,
			} )
			: Promise.resolve( true );

		confirmPromise.then( function ( ok ) {
			if ( ! ok ) { return; }
			bcmApi( {
				url:    cfg.restUrl + '/' + encodeURIComponent( id ),
				method: 'DELETE',
			} )
				.then( function ( response ) {
					if ( typeof onDeleted === 'function' ) {
						onDeleted( response );
					}
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
