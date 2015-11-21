// jshint ;_;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Module: Fab/Vidi/Vidi/Clipboard
 */
define([
	'jquery',
	'TYPO3/CMS/Backend/Notification',
	'TYPO3/CMS/Backend/Modal'
], function($, Notification, Modal) {
	'use strict';

	var Clipboard = {

		/**
		 * Bind action "save to clipboard" menu item.
		 *
		 * @return void
		 */
		attachHandler: function() {

			/**
			 * Action when button "save to clipboard" is hit.
			 */
			$('.clipboard-save').click(function(e) {
				e.preventDefault();

				// Make Grid appearing busy.
				$('#content-list').css('opacity', 0.3);

				// Fill Matches
				$.ajax(
					{
						type: 'get',
						url: Vidi.Clipboard.getSaveUrl($(this).attr('href'))
					})
					.done(function(data) {

						var fadeOut = true;
						if (data >= 0) {
							Notification.success('', TYPO3.l10n.localize('clipboard.items_saved', {0: data}));
							if (data > 0) {
								$('.btn-clipboard-copy-or-move').show(); // display clipboard button.
							}
						} else {
							Notification.error(TYPO3.l10n.localize('general.error'), TYPO3.l10n.localize('clipboard.items_not_saved'));
						}

						// Make Grid appearing busy.
						$('#content-list').css('opacity', 1);
					});
			});

			/**
			 * Action when button "save to clipboard" is hit.
			 */
			$('.btn-clipboard-copy-or-move').click(function(e) {
				e.preventDefault();

				// Open form for copying or moving content from the clipboard.
				Vidi.Clipboard.showWindow($(this).attr('href'));
			});
		},

		/**
		 * Set possible additional parameters for Ajax.
		 *
		 * @param {string} url
		 * @return {string}
		 */
		setAjaxAdditionalParameters: function(url) {

			var additionalParametersList = $('#ajax-additional-parameters').val();
			if (additionalParametersList) {
				//var uri = new Uri(url);
				var additionalParameters = additionalParametersList.split('&');
				for (var i = 0; i < additionalParameters.length; i++) {
					var splitValues = additionalParameters[i].split('=');
					if (splitValues.length === 2) {
						var parameterName = splitValues[0];
						var parameterValue = splitValues[1];

						// Add parameters to the Uri object.
						url += '&' + Vidi.module.parameterPrefix + '[' + parameterName + ']=' + parameterValue;
					}
				}
			}
			return url;
		},

		/**
		 * @return array
		 */
		getButtons: function() {

			var buttons = [
				{
					text: TYPO3.l10n.localize('cancel'),
					btnClass: 'btn btn-default',
					trigger: function() {
						Modal.dismiss();
					}
				}, {
					text: TYPO3.l10n.localize('clipboard.copy_items'),
					btnClass: 'btn btn-default btn-clipboard-copy btn-clipboard',
					trigger: function() {

						// Avoid double click on this button.
						$('.btn-clipboard').addClass('disabled');

						var action = $('#form-clipboard-copy-or-move').attr('action');
						action += '&' + Vidi.module.parameterPrefix + '[action]=copyClipboard';
						$('#form-clipboard-copy-or-move')
							.attr('action', action)
							.submit();

						// Possibly hide clipboard button if it was told so.
						if ($('.btn-clipboard-flush').is(':checked')) {
							$('.btn-clipboard-copy-or-move').hide();
						}
					}
				}, {
					text: TYPO3.l10n.localize('clipboard.move_items'),
					btnClass: 'btn btn-primary btn-clipboard-move btn-clipboard',
					trigger: function() {

						// Avoid double click on this button.
						$('.btn-clipboard').addClass('disabled')

						var action = $('#form-clipboard-copy-or-move').attr('action');
						action += '&' + Vidi.module.parameterPrefix + '[action]=moveClipboard';
						$('#form-clipboard-copy-or-move')
							.attr('action', action)
							.submit();

						// Possibly hide clipboard button if it was told so.
						if ($('.btn-clipboard-flush').is(':checked')) {
							$('.btn-clipboard-copy-or-move').hide();
						}
					}
				}
			];

			return buttons;
		},

		/**
		 * Open form for copying or moving content from the clipboard.
		 *
		 * @return string
		 */
		showWindow: function(url) {

			Vidi.modal = Modal.loadUrl(
				'TODO123',
				TYPO3.Severity.notice,
				this.getButtons(),
				url,
				function() {

					// bind submit handler to form.
					$('#form-clipboard-copy-or-move').on('submit', function(e) {

						// Prevent native submit.
						e.preventDefault();

						// Register
						$(this).ajaxSubmit({

							/**
							 * On success call back
							 * @param response
							 */
							success: function(response) {

								// Hide the modal window
								Modal.dismiss();

								// Reload the grid.
								Vidi.grid.fnDraw();
							}
						})
					});
				}
			);

		},

		/**
		 * Get the mass edit URL.
		 *
		 * @param {string} url
		 * @return string
		 * @private
		 */
		getSaveUrl: function(url) {

			var uri = new Uri(url);

			if (Vidi.Grid.hasSelectedRows()) {
				// Case 1: mass editing for selected rows.

				// Add parameters to the Uri object.
				uri.addQueryParam(Vidi.module.parameterPrefix + '[matches][uid]', Vidi.Grid.getSelectedIdentifiers().join(','));

			} else {

				var storedParameters = Vidi.Grid.getStoredParameters();
				if (typeof storedParameters.search === 'object') {
					uri.addQueryParam('search[value]', storedParameters.search.value);
				}
			}

			// Fix a bug in URI object. URL should looks like index.php?xyz and not index.php/?xyz
			url = uri.toString().replace('.php/?', '.php?');
			url = Vidi.Clipboard.setAjaxAdditionalParameters(url);
			return url;
		}
	};

	// Expose in Vidi object for compatibility reason.
	Vidi.Clipboard = Clipboard;
	return Clipboard;
});
