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

			// Load form
			Vidi.Selection.loadContent($('#link-selection-edit').attr('href'));
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
			var event = jQuery.Event("keydown");
			event.which = 13; // # Some key code value
			Vidi.VisualSearch.instance.searchBox.searchEvent(event);
		});

		/**
		 * Display visibility icon if something changed content wise.
		 */
		$(document).on('keydown', '.form-control-selection', function(e) {
			var $form = $(this).closest('form');
			$('.btn-selection-update', $form).css('visibility', '');

			if (e.keyCode === 13) {
				$('.btn-selection-update', $form).click();
				e.preventDefault();
			}
		});
		$(document).on('change', '.form-control-selection', function(e) {
			var $form = $(this).closest('form');
			$('.btn-selection-update', $form).css('visibility', '');
		});

		/**
		 * Delete a selection in the form just opened in the popup.
		 */
		$(document).on('click', '.btn-selection-delete', function(e) {
			var me = this;

			window.e = $(this).closest('form');
			e.preventDefault();
			Vidi.Selection.updateGuiSubmittingState(true);

			$(this)
				.closest('form')
				.attr('action', $(this).attr('href'))
				.ajaxSubmit({

					/**
					 * On success call back
					 * @param response
					 */
					success: function(response) {
						$(me).closest('form').remove();
						Vidi.Selection.updateGuiSubmittingState(false);

						// Update the list of selections.
						Vidi.Selection.updateList();
					}
				});
		});

		/**
		 * Update the selection with the current query
		 */
		$(document).on('click', '.btn-selection-query', function(e) {
			e.preventDefault();
			var $form = $(this).closest('form');
			$('.selection-query', $form).val(Vidi.Session.get('visualSearchQuery'));
			$('.btn-selection-update', $form).click();
		});

		/**
		 * Update or delete a selection in the form just opened in the popup.
		 */
		$(document).on('click', '.btn-selection-update', function(e) {
			e.preventDefault();
			var me = this;

			window.e = $(this).closest('form');
			Vidi.Selection.updateGuiSubmittingState(true);

			$(this)
				.closest('form')
				.attr('action', $(this).attr('href'))
				.ajaxSubmit({

					/**
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
		 * Create a new selection in the form just opened in the popup.
		 */
		$(document).on('submit', '#form-selection-create', function(e) {

			// Prevent native submit.
			e.preventDefault();

			// Save selection
			Vidi.Selection.updateGuiSubmittingState(true);

			$('.selection-query', this).val(Vidi.Session.get('visualSearchQuery'));

			// Register
			$(this).ajaxSubmit({

				/**
				 * On success call back
				 * @param response
				 */
				success: function(response) {
					$('.modal-body').html(response);

					// Update the list of selections.
					Vidi.Selection.updateList();
				}
			});
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
		$(box).css('width', '600px');

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
	}
};