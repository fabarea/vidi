"use strict";

/** @namespace Vidi */
/**
 * Object for handling "remove" actions.
 *
 * @type {Object} Vidi.Remove
 */
Vidi.Remove = {

	/**
	 * Bind delete buttons in list view.
	 *
	 * @return void
	 */
	attachHandlerInGrid: function() {
		$('.btn-delete')
			.click(function() {
				Vidi.Remove.scope = this;
			})
			// Click-over is a jQuery Plugin extending pop-over plugin from Twitter Bootstrap
			.clickover({
				esc_close: true,
				width: 200,
				placement: 'left',
				title: Vidi.translate('are-you-sure'),
				content: "<div class='btn-toolbar'>" +
					"<button data-dismiss='clickover' class='btn'>Cancel</button>" +
					"<button class='btn btn-danger btn-delete-row'>Delete</button>" +
					"</div>",
				onShown: function() {

					// Element corresponds to the click-over box. Keep it accessible in the closure.
					var dialogBox = this;

					// bind click on "btn-delete-row"
					$('.btn-delete-row').bind('click', function(e) {
						var row, message, url;

						$(this).addClass('disabled').text(Vidi.translate('deleting'));

						url = $(Vidi.Remove.scope).attr('href');
						row = $(Vidi.Remove.scope).closest("tr").get(0);

						// Send Ajax request to delete media
						$.get(url,
							function(response) {

								// Hide click-over box.
								dialogBox.hide();

								Vidi.Response.processResponse(response, 'delete');
							}
						);
					});
				}
			})
			// Reset default title which was stripped by clickover plugin.
			.attr('title', Vidi.translate('delete'));
	},

	/**
	 * Remove selected rows.
	 *
	 * @param {string} baseUrl
	 * @return void
	 */
	removeSelectedRows: function(baseUrl) {
		var selectedIdentifiers, message, url;

		// Get selected rows.
		selectedIdentifiers = Vidi.Grid.getSelectedIdentifiers();

		url = baseUrl + '&' + Vidi.module.parameterPrefix + '[matches][uid]=' + selectedIdentifiers.join(',');

		var numberOfSelectedRows = Vidi.Grid.getNumberOfSelectedRows();
		if (numberOfSelectedRows > 0) {
			message = Vidi.format('confirm-mass-delete-plural', numberOfSelectedRows);
		} else {
			message = Vidi.format('confirm-mass-delete-singular', numberOfSelectedRows);
		}

		// Trigger mass-delete action against selected rows.
		Vidi.Remove.massRemove(message, url);
	},

	/**
	 * Remove the current selection.
	 *
	 * @return void
	 */
	removeSelection: function() {
		var message;

		var uri = new Uri(Vidi.Grid.getStoredUrl());

		// Add parameters to the Uri being built.
		var actionParameterName = Vidi.module.parameterPrefix + '[action]';
		for (var index in Vidi.Grid.getStoredParameters()) {
			var parameter = Vidi.Grid.getStoredParameters()[index];

			if (parameter.name === actionParameterName) {
				parameter.value = 'delete';
			} else if (parameter.name === 'iDisplayLength' || parameter.name === 'iDisplayStart') {
				parameter.value = 0;
			}
			uri.addQueryParam(parameter.name, parameter.value);
		}

		var numberOfSelectedRows = Vidi.Grid.getStoredTransaction().fnRecordsTotal();
		if (numberOfSelectedRows > 0) {
			message = Vidi.format('confirm-mass-delete-plural', numberOfSelectedRows);
		} else {
			message = Vidi.format('confirm-mass-delete-singular', numberOfSelectedRows);
		}

		// Trigger mass-delete action against current selection.
		Vidi.Remove.massRemove(message, uri.toString());
	},

	/**
	 * Display confirmation window for mass-delete and trigger Ajax request in case of User confirmation.
	 *
	 * @param {string} message
	 * @param {string} url
	 * @return void
	 * @private
	 */
	massRemove: function(message, url) {
		bootbox.dialog(message, [
			{
				'label': Vidi.translate('cancel')
			},
			{
				'label': Vidi.translate('delete'),
				'class': "btn-danger",
				'callback': function() {
					$.get(
						url,
						function(response) {
							Vidi.Response.processResponse(response, 'delete');
						}
					);
				}
			}
		], {
			onEscape: function () {
				// Empty but required function to have escape keystroke hiding the modal window.
			}
		});
	}
};
