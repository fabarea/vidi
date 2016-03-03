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
 * Module: Fab/Vidi/Vidi/Selection
 */
define([
	'jquery',
	'TYPO3/CMS/Backend/Modal'
], function($, Modal) {
	'use strict';

	var Selection = {

		/**
		 * @return void
		 */
		initialize: function() {

			/**
			 * Move DOM to their right place element.
			 */
			$('.btn-selection-list').detach().appendTo('.visual-search-container');

			/**
			 * Popup the edit windows.
			 */
			$(document).on('click', '.btn-selection-edit', function(e) {
				e.preventDefault();

				// Open form for saving selection.
				Vidi.Selection.showWindow();
			});

			/**
			 * Change query in the Visual Search bar dynamically.
			 */
			$(document).on('click', '.btn-change-filter', function(e) {

				// Prevent native submit.
				e.preventDefault();

				// Set the query
				var query = $(this).next().html();
				Vidi.VisualSearch.instance.searchBox.setQuery(query);

				// Trigger the event
				var event = $.Event("keydown");
				event.which = 13; // # Some key code value
				Vidi.VisualSearch.instance.searchBox.searchEvent(event);
			});

		},

		/**
		 * Update the list of selections.
		 *
		 * @return string
		 */
		updateList: function() {
			$.ajax(
				{
					url: $('#link-selection-list').attr('href')
				})
				.done(function(content) {
					$('#selection-list').replaceWith(content);
				});
		},

		/**
		 * Tell the user the form is being submitted.
		 *
		 * @param {bool} isSubmitting
		 * @return string
		 */
		updateGuiSubmittingState: function(isSubmitting) {
			if (isSubmitting) {
				$('.modal-selection-wrapper', Vidi.modal).css('opacity', 0.6);
				$('.modal-selection-waiting', Vidi.modal).show();
			} else {
				$('.modal-selection-wrapper', Vidi.modal).css('opacity', 1);
				$('.modal-selection-waiting', Vidi.modal).hide();
			}
		},

		/**
		 * @return array
		 */
		getButtons: function() {

			var buttons = [
				{
					text: TYPO3.l10n.localize('close'),
					btnClass: 'btn btn-primary',
					trigger: function() {
						Modal.dismiss();
					}
				}
			];

			return buttons;
		},

		/**
		 * Open form for saving selection.
		 *
		 * @return string
		 */
		showWindow: function() {

			Vidi.modal = Modal.loadUrl(
				TYPO3.l10n.localize('selections'),
				top.TYPO3.Severity.notice,
				this.getButtons(),
				$('#link-selection-edit').attr('href'),
				function() {

					/**
					 * Delete a selection in the form just opened in the popup.
					 */
					$(Vidi.modal).on('click', '.btn-selection-delete', function(e) {

						// Stop default behaviour.
						e.preventDefault();

						// Ask for confirmation
						var message = TYPO3.l10n.localize('confirm-delete', {0: $(this).data('selection-title')});
						var result = window.confirm(message);

						if (result) {
							var me = this;

							Vidi.Selection.updateGuiSubmittingState(true);

							var $form = $(this).closest('form');

							// Ajax request
							$.ajax({
								url: $(this).attr('href'),
								data: $form.serialize(),

								/**
								 * On success call back
								 *
								 * @param response
								 */
								success: function(response) {
									$(me).closest('form').remove();
									Vidi.Selection.updateGuiSubmittingState(false);

									// Update the list of selections.
									Vidi.Selection.updateList();
								}
							});
						}
					});

					/**
					 * Create a new selection in the form just opened in the popup.
					 */
					$(Vidi.modal).on('click', '.btn-selection-create', function(e) {

						// Prevent native behaviour.
						e.preventDefault();

						// Save selection
						Vidi.Selection.updateGuiSubmittingState(true);

						var $form = $(this).closest('form');

						$('.selection-query', $form).val(Vidi.Session.get('query'));
						$('.selection-speakingQuery', $form).val(Vidi.Session.get('visualSearchQuery'));

						// Ajax request
						$.ajax({
							url: $form.attr('action'),
							data: $form.serialize(),

							/**
							 * On success call back
							 *
							 * @param response
							 */
							success: function(response) {

								$('.modal-body', Vidi.modal).html(response);

								// Update the list of selections.
								Vidi.Selection.updateList();
							}
						});

					});


					/**
					 * In case the User hit "enter", submit the form.
					 */
					$(Vidi.modal).on('keydown', '.form-control-selection', function(e) {
						var $form = $(this).closest('form');
						$('.btn-selection-update', $form).css('visibility', '');

						if (e.keyCode === 13) {
							// One case or another, it doesn't matter.
							$('.btn-selection-update', $form).click();
							$('.btn-selection-create', $form).click();
							e.preventDefault();
						}
					});

					/**
					 * Update or delete a selection in the form just opened in the popup.
					 */
					$(Vidi.modal).on('click', '.btn-selection-update', function(e) {
						e.preventDefault();
						var me = this;

						Vidi.Selection.updateGuiSubmittingState(true);

						var $form = $(this).closest('form');

						// Ajax request
						$.ajax({
							url: $form.attr('action'),
							data: $form.serialize(),

							/**
							 * On success call back
							 *
							 * @param response
							 */
							success: function(response) {

								$(me).closest('form').replaceWith(response);
								Vidi.Selection.updateGuiSubmittingState(false);

								// Update the list of selections.
								Vidi.Selection.updateList();
							}
						});
					});

					/**
					 * Update the selection with the current query.
					 */
					$(Vidi.modal).on('click', '.btn-selection-speakingQuery', function(e) {
						e.preventDefault();
						var $form = $(this).closest('form');

						$('.selection-query', $form).val(Vidi.Session.get('query'));

						var speakingQuery = Vidi.Session.get('visualSearchQuery');
						if (Vidi.Session.get('query') === '[]') {
							speakingQuery = ''; // Mm... there is a problem somewhere else.
						}
						$('.selection-speakingQuery', $form).val(speakingQuery);
						$('.btn-selection-update', $form).click();
					});
				}
			);
		}

	};

	// Expose in Vidi object for compatibility reason.
	Vidi.Selection = Selection;
	return Selection;
});