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
 * Module: Fab/Vidi/Vidi/Delete
 */
define([
	'jquery',
	'TYPO3/CMS/Backend/Modal',
	'Fab/Vidi/Vidi/Util'
], function($, Modal, Util) {
	'use strict';

	var Delete = {

		/**
		 * Bind delete buttons in list view.
		 *
		 * @return void
		 */
		attachHandler: function() {
			$(document).on('click', '.btn-delete', function(e) {

				e.preventDefault();

				var me = this;

				Vidi.modal = Modal.confirm(
					TYPO3.l10n.localize('delete'),
					TYPO3.l10n.localize('confirm-delete', {0: $(this).data('label')}),
					top.TYPO3.Severity.warning,
					[
						{
							text: TYPO3.l10n.localize('cancel'),
							btnClass: 'btn btn-default',
							trigger: function() {
								Modal.dismiss();
							}
						},
						{
							text: TYPO3.l10n.localize('delete'),
							btnClass: 'btn btn-warning',
							trigger: function() {

								// Avoid double sumbit.
								$('.modal-dialog .btn-warning', Vidi.modal).addClass('disabled');

								$.ajax({
									url: $(me).attr('href'),
									success: function(response) {
										Modal.dismiss();
										Vidi.Response.processResponse(response, 'delete');
									}
								});
							}
						}
					]
				);
			});
		},

		/**
		 * Remove selected rows.
		 *
		 * @param {string} baseUrl
		 * @return void
		 */
		removeSelectedRows: function(baseUrl) {

			// Get selected rows.
			var selectedIdentifiers = Vidi.Grid.getSelectedIdentifiers();

			// Build the URL.
			var parameters = Vidi.Grid.getStoredParameters();
			parameters[Vidi.module.parameterPrefix + '[action]'] = 'delete';
			parameters[Vidi.module.parameterPrefix + '[matches][uid]'] = selectedIdentifiers.join(',');
			var url = Util.getUrl(parameters);

			var message;
			var numberOfSelectedRows = Vidi.Grid.getNumberOfSelectedRows();
			if (numberOfSelectedRows > 0) {
				message = TYPO3.l10n.localize('confirm-mass-delete-plural', {0: numberOfSelectedRows});
			} else {
				message = TYPO3.l10n.localize('confirm-mass-delete-singular', {0: numberOfSelectedRows});
			}

			// Trigger mass-delete action against selected rows.
			Vidi.Delete.massRemove(message, url);
		},

		/**
		 * Remove the current selection.
		 *
		 * @return void
		 */
		removeSelection: function() {

			// Build the URL.
			var parameters = Vidi.Grid.getStoredParameters();
			parameters[Vidi.module.parameterPrefix + '[action]'] = 'delete';
			parameters['start'] = 0;
			parameters['length'] = 0;
			var url = Util.getUrl(parameters);

			console.log(url);

			var message;
			var numberOfSelectedRows = Vidi.Grid.getStoredTransaction().fnRecordsTotal();
			if (numberOfSelectedRows > 0) {
				message = TYPO3.l10n.localize('confirm-mass-delete-plural', {0: numberOfSelectedRows});
			} else {
				message = TYPO3.l10n.localize('confirm-mass-delete-singular', {0: numberOfSelectedRows});
			}

			// Trigger mass-delete action against current selection.
			Vidi.Delete.massRemove(message, url);
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

			Vidi.modal = Modal.show(
				TYPO3.l10n.localize('delete'),
				message,
				top.TYPO3.Severity.warning,
				[
					{
						text: TYPO3.l10n.localize('cancel'),
						btnClass: 'btn btn-default',
						trigger: function() {
							Modal.dismiss();
						}
					},
					{
						text: TYPO3.l10n.localize('delete'),
						btnClass: 'btn btn-warning',
						trigger: function() {

							// Avoid double sumbit.
							$('.modal-dialog .btn-warning', Vidi.modal).addClass('disabled');


							$.ajax({
								url: url,
								success: function(response) {
									Modal.dismiss();
									Vidi.Response.processResponse(response, 'delete');
								}
							});
						}
					}
				]
			);

		}
	};

	// Expose in Vidi object for compatibility reason.
	Vidi.Delete = Delete;
	return Delete;
});
