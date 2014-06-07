$(document).ready(function () {
	"use strict";

	/**
	 * Load the generic "edit relation" form for mm relations.
	 */
	$(document).on('click', '.dataTable tbody .btn-edit-relation', function (e) {

		e.preventDefault();

		var contentIdentifier = $(this).data('uid');
		var $currentCell = $(this).closest('td');

		// Load content by ajax for the modal window.
		$.ajax(
			{
				type: 'get',
				url: $(this).attr('href')
			})
			.done(function (data) {
				$('.modal-body').html(data);

				// bind submit handler to form.
				$('#form-create-relation').on('submit', function (e) {

					// Prevent native submit
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
						beforeSubmit: function (arr, $form, options) {

							// Only submit if button is not disabled
							if ($('.btn-save-relation').hasClass('disabled')) {
								return false;
							}

							// Else submit form
							$('.btn-save-relation').text('Saving...').addClass('disabled');
						},

						/**
						 * On success call back
						 * @param data
						 */
						success: function (data) {

							// Hide the modal window
							bootbox.hideAll();

							// Take the first element of the response as THE response since we are not in a multi editing context.
							var response = data[0];

							if (response.status === false) {
								Vidi.FlashMessage.add(response.message, 'error');
								var fadeOut = false;
								Vidi.FlashMessage.showAll(fadeOut);
								$(this).html('Something went wrong...');
							} else {

								// Store in session the last edited uid
								Vidi.Session.set('lastEditedUid', contentIdentifier);

								// Reload data table.
								Vidi.grid.fnDraw();
							}
						}
					})
				});

			})
			.fail(function (data) {
				alert('Something went wrong! Check out console log for more detail');
				console.log(data);
			});

		// Display the empty modal box with default loading icon.
		// Its content is going to be replaced by the content of the Ajax request.
		var template = '<div style="text-align: center">' +
			'<img src="' + Vidi.module.publicPath + 'Resources/Public/Images/loading.gif" width="" height="" alt="" />' +
			'</div>';

		bootbox.dialog(template, [
			{
				'label': 'Cancel'
			},
			{
				'label': 'Save relation',
				'class': 'btn-primary btn-save-relation',
				'callback': function () {

					$('#form-create-relation').submit();

					// Show to the User the grid is being refreshed.
					$currentCell.html('<img src="' + Vidi.module.publicPath + 'Resources/Public/Images/loading.gif" width="16" height="" alt="" />');

					// Prevent modal closing ; modal window will be closed after submitting.
					return false;
				}
			}
		], {
			onEscape: function () {
				// Empty but required function to have escape keystroke hiding the modal window.
			}
		});
	});
});
