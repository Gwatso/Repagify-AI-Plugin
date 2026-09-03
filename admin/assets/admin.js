/**
 * Repagify admin behaviour.
 *
 * Loaded only on the plugin's own screens. Vanilla JS, no build step.
 */
( function () {
	'use strict';

	var settings = window.repagifyAdmin || {};
	var i18n = settings.i18n || {};

	/**
	 * Builds a notice element. Text is set with textContent so that anything
	 * coming back from the API cannot inject markup into the admin.
	 *
	 * @param {string} message Message to show.
	 * @param {string} type    'success' or 'error'.
	 * @return {HTMLElement} The notice.
	 */
	function buildNotice( message, type ) {
		var notice = document.createElement( 'div' );
		var paragraph = document.createElement( 'p' );

		notice.className = 'repagify-notice repagify-notice--' + type;
		paragraph.textContent = message;
		notice.appendChild( paragraph );

		return notice;
	}

	/**
	 * Appends a label/value row to a notice.
	 *
	 * @param {HTMLElement} list  List element to append to.
	 * @param {string}      label Row label.
	 * @param {string}      value Row value.
	 * @return {void}
	 */
	function appendAccountRow( list, label, value ) {
		var item = document.createElement( 'li' );
		var labelEl = document.createElement( 'span' );
		var valueEl = document.createElement( 'strong' );

		labelEl.className = 'repagify-account-label';
		labelEl.textContent = label;
		valueEl.textContent = value;

		item.appendChild( labelEl );
		item.appendChild( valueEl );
		list.appendChild( item );
	}

	/**
	 * Renders the account summary underneath a success notice.
	 *
	 * @param {HTMLElement} notice  Notice to extend.
	 * @param {Object}      account Account summary from the server.
	 * @return {void}
	 */
	function appendAccount( notice, account ) {
		if ( ! account ) {
			return;
		}

		var hasPlan = account.plan;
		var hasRemaining =
			account.conversions_remaining !== null &&
			account.conversions_remaining !== undefined;

		if ( ! hasPlan && ! hasRemaining ) {
			return;
		}

		var list = document.createElement( 'ul' );
		list.className = 'repagify-account';

		if ( hasPlan ) {
			appendAccountRow( list, i18n.planLabel || 'Plan', String( account.plan ) );
		}

		if ( hasRemaining ) {
			appendAccountRow(
				list,
				i18n.remaining || 'Conversions remaining',
				String( account.conversions_remaining )
			);
		}

		notice.appendChild( list );
	}

	/**
	 * Wires the Test connection button.
	 *
	 * @return {void}
	 */
	function initConnectionTest() {
		var button = document.getElementById( 'repagify-test-connection' );
		var result = document.getElementById( 'repagify-test-result' );
		var spinner = document.getElementById( 'repagify-test-spinner' );

		if ( ! button || ! result ) {
			return;
		}

		button.addEventListener( 'click', function () {
			var defaultLabel = button.getAttribute( 'data-default-label' ) || button.textContent;

			button.disabled = true;
			button.textContent = i18n.testing || 'Testing…';
			result.textContent = '';

			if ( spinner ) {
				spinner.classList.add( 'is-active' );
			}

			var body = new URLSearchParams();
			body.append( 'action', 'repagify_test_connection' );
			body.append( 'nonce', settings.nonce || '' );

			window
				.fetch( settings.ajaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
					},
					body: body.toString()
				} )
				.then( function ( response ) {
					return response.json();
				} )
				.then( function ( payload ) {
					var data = payload && payload.data ? payload.data : {};
					var message = data.message || i18n.genericError || 'Something went wrong.';

					if ( payload && payload.success ) {
						var notice = buildNotice( message, 'success' );
						appendAccount( notice, data.account );
						result.appendChild( notice );
						return;
					}

					result.appendChild( buildNotice( message, 'error' ) );
				} )
				.catch( function () {
					result.appendChild(
						buildNotice( i18n.genericError || 'Something went wrong.', 'error' )
					);
				} )
				.then( function () {
					button.disabled = false;
					button.textContent = defaultLabel;

					if ( spinner ) {
						spinner.classList.remove( 'is-active' );
					}
				} );
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', initConnectionTest );
	} else {
		initConnectionTest();
	}
}() );
