$(document).ready(function () {
	"use strict";

	/**
	 * Create relation action
	 */
	$(document).on('click', '.dataTable tbody .btn-relation', function (e) {
		var contentUid, contentDataType, relatedDataType, relationProperty, $currentCell;

		contentUid = $(this).data('uid');
		contentDataType = $(this).data('type');
		relatedDataType = $(this).data('related-type');
		relationProperty = $(this).data('relation-property');
		$currentCell = $(this).closest('td');


		// Get content by ajax for the modal...
		$.ajax(
			{
				type: 'get',
				url: '/typo3/ajax.php',
				data: {
					ajaxID: 'vidiAjaxDispatcher',
					extensionName: 'vidi',
					pluginName: 'Pi1',
					controllerName: 'Relation',
					actionName: 'list',
					arguments: {
						content: contentUid,
						dataType: contentDataType,
						relationProperty: relationProperty,
						relatedDataType: relatedDataType
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

							// Hide modal.
							bootbox.hideAll();

							// Store in session the last edited uid
							Vidi.Session.set('lastEditedUid', contentUid);

							// Reload data table.
							Vidi.grid.fnDraw();
						}
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
				// required to have escape keystroke hiding modal window.
			}
		});
		e.preventDefault()
	});
});
