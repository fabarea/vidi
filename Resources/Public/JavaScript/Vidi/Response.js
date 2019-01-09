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
 * Module: Fab/Vidi/Vidi/Response
 */
define([
	'jquery',
	'TYPO3/CMS/Backend/Notification',
	'Fab/Vidi/Vidi/Grid'
], function($, Notification) {
	'use strict';

	var Response = {

		/**
		 * Display confirmation.
		 *
		 * @param {object} response
		 * @param {string} action
		 * @return void
		 * @private
		 */
		processResponse: function(response, action) {
			var label, message, title = '';

			// Display possible error messages.
			if (response.hasErrors) {
				message = response.errorMessages.join('</li><li>');
				message = '<ul><li>' + message + '</li></ul>';
				Notification.error(title, message);
			}

			// Display number of records processed.
			if (response.numberOfProcessedObjects > 0) {
				if (response.numberOfProcessedObjects === 1 && 'processedObject' in response) {
					label = 'success-' + action;
					message = TYPO3.l10n.localize(label, {0: response.processedObject['name']});
				} else if (response.numberOfProcessedObjects > 0) {
					label = 'success-mass-' + action;
					message = TYPO3.l10n.localize(label, {0: response.numberOfProcessedObjects, 1: response.numberOfObjects});
				}

				//top.Notification.success(title, message, 3);
            }

			// GUI: un-check the top checkbox.
			$('.checkbox-row-top').removeAttr('checked');

			// Reload the grid.
			Vidi.grid.fnDraw(false); // false = for keeping the pagination.

			if (response.processedObject) {
				Vidi.Grid.animateRow(response.processedObject.uid);
			}
		}
	};

	// Expose in Vidi object for compatibility reason.
	Vidi.Response = Response;
	return Response;
});
