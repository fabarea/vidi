"use strict";

/** @namespace Vidi */

/**
 * Object for handling the Visual Search.
 *
 * @type {Object}
 */
Vidi.Selection = {

	/**
	 * @return void
	 */
	initialize: function() {

		$('.btn-selection-edit')
			.click(function(e) {
				e.preventDefault();

				// Open form for saving selection.
				Vidi.Selection.showWindow();

				// Load form
				Vidi.Selection.loadContent($('#link-selection-edit').attr('href'));

			});

		// Move element
		$('.btn-selection-list').detach().appendTo('.visual-search-container');

		$(document).on('submit', '#form-selection-create', function(e) {

			// Prevent native submit.
			e.preventDefault();

			// Save selection
			Vidi.Selection.setSubmittingState(true);

			// Register
			$(this).ajaxSubmit({

				/**
				 * Before submit handler.
				 * @param arr
				 * @param $form
				 * @param options
				 * @returns {boolean}
				 */
				beforeSubmit: function (arr, $form, options) {

					// Make sure name has a value.
					console.log($(this).find('.field-matches').val());

					// Inject the query into the matches field.
					console.log(Vidi.Session.get('visualSearch.query'));


					// Only submit if button is not disabled
					//if ($('.btn-save-relation').hasClass('disabled')) {
					//	return false;
					//}
					//
					//// Else submit form
					//$('.btn-save-relation').addClass('disabled');
				},

				/**
				 * On success call back
				 * @param response
				 */
				success: function (response) {

					// Reload the browser.
					//location.reload();
				}
			});

			// Reload whole window.

			// Call the Edit routine which will pop-up the modal window.
			//Vidi.Edit
			//	.setLabelSave('Save relation')
			//	.setIsMassEditingRelation(false)
			//	.setEditedCells($(this).closest('td'))
			//	.setRowIdentifier(Vidi.Grid.getRowIdentifier(this))
			//	.loadContent($(this).attr('href'))
			//	.showWindow();
		});

	},

	/**
	 * Tell the user the form is being submitted.
	 *
	 * @param {bool} isSubmitting
	 * @return string
	 */
	setSubmittingState: function(isSubmitting) {
		if (isSubmitting) {
			$('.modal-selection-wrapper').css('opacity', 0.6);
			$('.modal-selection-waiting').show();
		} else {
			$('.modal-selection-wrapper').css('opacity', 1);
			$('.modal-selection-waiting').hide();
		}

	},

	/**
	 * Open form for saving selection.
	 *
	 * @param {string} facetLabel
	 * @return string
	 */
	showWindow: function(facetLabel) {

		var template = '<div style="text-align: center">' +
			'<img src="' + Vidi.module.publicPath + 'Resources/Public/Images/loading.gif" width="" height="" alt="" />' +
			'</div>';

		// Configure buttons and callback

		var modalWindowConfiguration = [
			{
				label: 'Close'
			}
		];

		var box = bootbox.dialog(
			template,
			modalWindowConfiguration, {
				onEscape: function() {
					// Empty but required function to have escape keystroke hiding the modal window.
				}
			});

		// Make the bootbox window a little bit bigger.
		$(box).css('width', '50%');

	},

	/**
	 * Load content by ajax.
	 *
	 * @param {string} url
	 * @return Vidi.Edit
	 */
	loadContent: function(url) {
		// Load content by ajax for the modal window.
		$.ajax(
			{
				type: 'get',
				url: url
			})
			.done(function(data) {
				$('.modal-body').html(data);

				// bind submit handler to form.
				$('#form-edit').on('submit', function(e) {

					// Prevent native submit.
					e.preventDefault();

					// Register
					$(this).ajaxSubmit({

						/**
						 * Before submit handler.
						 * @param arr
						 * @param $form
						 * @param options
						 * @returns {boolean}
						 */
						beforeSubmit: function(arr, $form, options) {

							// Only submit if button is not disabled
							if ($('.btn-save-relation').hasClass('disabled')) {
								return false;
							}

							// Else submit form
							$('.btn-save-relation').addClass('disabled');
						},

						/**
						 * On success call back
						 * @param response
						 */
						success: function(response) {

							// Hide the modal window
							bootbox.hideAll();

							Vidi.Response.processResponse(response, 'update');
						}
					})
				});

			})
			.fail(function(data) {
				alert('Something went wrong! Check out console log for more detail');
				console.log(data);
			});

		return Vidi.Edit;
	},

	/**
	 * Display a message error to the User that the Ajax request went wrong.
	 *
	 * @return void
	 * @private
	 */
	showError: function() {
		var message = 'Oups! Something went wrong when retrieving auto-suggestion values... Investigate the problem in the Network Monitor. <br />';
		Vidi.FlashMessage.add(message, 'error');
		var fadeOut = false;
		Vidi.FlashMessage.showAll(fadeOut);
	}
};
