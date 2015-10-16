"use strict";

/** @namespace Vidi */

/**
 * Object for handling Data Table
 *
 * @type {Object}
 */
Vidi.Clipboard = {

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
						Vidi.FlashMessage.add(TYPO3.l10n.localize('clipboard.items_saved', {0: data}));
						if (data > 0) {
							$('.btn-clipboard-copy-or-move').show(); // display clipboard button.
						}
					} else {
						fadeOut = false;
						Vidi.FlashMessage.add(TYPO3.l10n.localize('clipboard.items_not_saved'), 'error');
					}

					Vidi.FlashMessage.showAll(fadeOut);

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
			Vidi.Clipboard
				.loadContent($(this).attr('href'))
				.showWindow();
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
					url += '&' + Vidi.module.parameterPrefix + '[' + parameterName +']=' + parameterValue;
				}
			}
		}
		return url;
	},

	/**
	 * Load content by ajax.
	 *
	 * @param {string} url
	 * @return Vidi.Clipboard
	 */
	loadContent: function (url) {

		//// Load content by ajax for the modal window.
		$.ajax(
			{
				type: 'get',
				url: url
			})
			.done(function (data) {
				$('.modal-body').html(data);

				// bind submit handler to form.
				$('#form-clipboard-copy-or-move').on('submit', function (e) {

					// Prevent native submit.
					e.preventDefault();

					// Register
					$(this).ajaxSubmit({

						/**
						 * On success call back
						 * @param response
						 */
						success: function (response) {

							// Hide the modal window
							bootbox.hideAll();

							// Reload the grid.
							Vidi.grid.fnDraw();
						}
					})
				});

			})
			.fail(function (data) {
				alert('Something went wrong! Check out console log for more detail');
				console.log(data);
			});

		return Vidi.Clipboard;
	},

	/**
	 * Open form for copying or moving content from the clipboard.
	 *
	 * @param {string} facetLabel
	 * @return string
	 */
	showWindow: function(facetLabel) {

		var template = '<div style="text-align: center">' +
			'<img src="' + Vidi.module.publicPath + 'Resources/Public/Images/loading.gif" width="" height="" alt="" />' +
			'</div>';

		// Configure buttons and callback
		var box = bootbox.dialog(
			template,
			[
				{
					class: 'btn-primary',
					label: TYPO3.l10n.localize('cancel')
				},
				{
					'label': TYPO3.l10n.localize('clipboard.copy_items'),
					'class': 'btn-clipboard-copy btn-clipboard',
					'callback': function(e) {

						// Avoid double click on this button.
						$('.btn-clipboard').addClass('disabled')

						var action = $('#form-clipboard-copy-or-move').attr('action');
						action += '&' + Vidi.module.parameterPrefix + '[action]=copyClipboard';
						$('#form-clipboard-copy-or-move')
							.attr('action', action)
							.submit();

						// Possibly hide clipboard button if it was told so.
						if ($('.btn-clipboard-flush').is(':checked')) {
							$('.btn-clipboard-copy-or-move').hide();
						}

						// Loading message visible.
						$('.modal-body').html(template);

						// Prevent modal closing ; modal window will be closed after submitting.
						return false;
					}
				},
				{
					'label': TYPO3.l10n.localize('clipboard.move_items'),
					'class': 'btn-clipboard-move btn-clipboard',
					'callback': function() {

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

						// Loading message visible.
						$('.modal-body').html(template);

						// Prevent modal closing ; modal window will be closed after submitting.
						return false;
					}
				}
			], {
				onEscape: function() {
					// Empty but required function to have escape keystroke hiding the modal window.
				}
			});

		// Make the bootbox window a little bit bigger.
		$(box).css('width', '600px');
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

			var parametersToKeep = ['sSearch'];

			// Keep only certain parameters which make sense to transmit.
			for (var index in Vidi.Grid.getStoredParameters()) {
				var parameter = Vidi.Grid.getStoredParameters()[index];

				// Keep only certain parameters which make sense to transmit.
				if ($.inArray(parameter.name, parametersToKeep) > -1) {
					uri.addQueryParam(parameter.name, parameter.value);
				}
			}
		}

		// Fix a bug in URI object. URL should looks like mod.php?xyz and not mod.php/?xyz
		url = uri.toString().replace('.php/?', '.php?');
		url = Vidi.Clipboard.setAjaxAdditionalParameters(url);
		return url;
	}

};
