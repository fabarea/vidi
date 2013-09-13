/** @namespace Vidi */

$(document).ready(function () {
	"use strict";

	/**
	 * Create relation action
	 */
	$(document).on('click', '.dataTable tbody .btn-fe_users', function (e) {

		var contentObjectUid = $(this).data('uid');

		// Get content by ajax for the modal...
		$.ajax(
			{
				type: 'get',
				url: '/typo3/ajax.php',
				data: {
					ajaxID: 'vidiAjaxDispatcher',
					extensionName: 'vidi',
					pluginName: 'Pi1',
					controllerName: 'FrontendUser',
					actionName: 'listFrontendUserGroup',
					arguments: {
						frontendUser: contentObjectUid
					}
				}
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
							if ($('.btn-create-relation').hasClass('disabled')) {
								return false;
							}

							// Else submit form
							$('.btn-create-relation').text('Sending...').addClass('disabled');
						},

						/**
						 * On success call back
						 * @param data
						 */
						success: function (data) {

							// Hide modal.
							bootbox.hideAll();

							// Store in session the last edited uid
							Vidi.Session.set('lastEditedUid', contentObjectUid);

							// Reload data table.
							Vidi.table.fnDraw();
						},
					})
				});

			})
			.fail(function (data) {
				alert('Something went wrong! Check out console log for more detail');
				console.log(data);
			});

		// Display modal box with default loading icon.
		var template = '<div style="text-align: center">' +
			'<img src="' + Vidi.module.publicPath + 'Resources/Public/Images/loading.gif" width="" height="" alt="" />' +
			'</div>';

		bootbox.dialog(template, [
			{
				'label': 'Cancel'
			},
			{
				'label': 'Create relation',
				'class': 'btn-primary btn-create-relation',
				'callback': function () {

					$('#form-create-relation').submit();

					// Prevent modal closing.
					// Modal will be closed after submitting.
					return false;
				}
			}
		], {
			onEscape: function () {
				// required to have escape keystroke hiding modal window.
			}
		});
		e.preventDefault()
	});
});
