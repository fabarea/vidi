"use strict";

/** @namespace Vidi */
/**
 * Object for handling a response from an Ajax request
 *
 * @type {Object} Vidi.Response
 */
Vidi.Response = {

	/**
	 * Display confirmation window for mass-delete and trigger Ajax request in case of User confirmation.
	 *
	 * @param {object} response
	 * @param {string} action
	 * @return void
	 * @private
	 */
	processResponse: function(response, action) {
		var label, message;

		// Display possible error messages.
		if (response.hasErrors) {
			message = response.errorMessages.join('</li><li>');
			message = '<ul><li>' + message + '</li></ul>';
			Vidi.FlashMessage.add(message, 'error');
		}

		// Display number of records processed.
		if (response.numberOfProcessedObjects > 0) {
			if (response.numberOfProcessedObjects === 1) {
				label = 'success-' + action;
				message = Vidi.format(label, response.processedObject['name']);
			} else if (response.numberOfProcessedObjects > 0) {
				label = 'success-mass-' + action;
				message = Vidi.format(label, response.numberOfProcessedObjects, response.numberOfObjects);
			}
			Vidi.FlashMessage.add(message, 'success');
		}

		// GUI: un-check the top checkbox.
		$('.checkbox-row-top').removeAttr('checked');

		// Reload data table
		Vidi.grid.fnDraw(false); // false = for keeping the pagination.

		// Display flash message
		var fadeOut = !response.hasErrors;
		Vidi.FlashMessage.showAll(fadeOut);
	}
};
