"use strict";

/** @namespace Vidi */
/**
 * Object for handling event and their actions
 *
 * @type {Object} Event
 */
Vidi.Action = {

	/**
	 * Bind edit buttons in list view.
	 *
	 * @return void
	 */
	edit: function() {

		// bind the click handler script to the newly created elements held in the table
		$('.btn-edit').bind('click', function(e) {
			Vidi.Session.set('lastEditedUid', $(this).data('uid'));
		});

		// Make a row selectable
		$('.checkbox-row').bind('click', function(e) {
			var checkboxes;

			$(this)
				.closest('tr')
				.toggleClass('active');
			e.stopPropagation(); // we don't want the event to propagate.

			checkboxes = $('#content-list').find('.checkbox-row').filter(':checked');
			if (checkboxes.length > 0) {
				$('.menu-selected-rows').removeClass('disabled');
			} else {
				$('.menu-selected-rows').addClass('disabled');
			}
		});

		// Add listener on the row as well
		$('.checkbox-row')
			.parent()
			.css('cursor', 'pointer')
			.bind('click', function(e) {
				$(this).find('.checkbox-row').click()
			});
	},

	/**
	 * Bind delete buttons in list view.
	 *
	 * @return void
	 */
	remove: function() {
		$('.btn-delete')
			.click(function() {
				Vidi.Action.scope = this;
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
					var element = this;

					// bind click on "btn-delete-row"
					$('.btn-delete-row').bind('click', function(e) {
						var row, message, url;

						$(this).addClass('disabled').text(Vidi.translate('deleting'));

						url = $(Vidi.Action.scope).attr('href');
						row = $(Vidi.Action.scope).closest("tr").get(0);

						// Send Ajax request to delete media
						$.get(url,
							function(results) {

								// Hide click-over box.
								element.hide();

								// Reload data table
								Vidi.grid.fnDeleteRow(Vidi.grid.fnGetPosition(row));
								var message = Vidi.format('message-deleted',results[0].object[Vidi.module.tca.ctrl.label]);
								Vidi.FlashMessage.add(message, 'success');
							}
						);
					});
				}
			})
			// Reset default title which was stripped by clickover plugin.
			.attr('title', Vidi.translate('delete'));
	},

	/**
	 * Display confirmation window for mass-delete and trigger Ajax request in case of User confirmation.
	 *
	 * @param {string} message
	 * @param {string} url
	 * @return void
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
						function(results) {

							// Check if delete has worked
							var resultOk, messageFadeOut= true;
							for (var index in results) {
								var result = results[index];
								if (result.status === false) {
									resultOk = messageFadeOut = false;
									Vidi.FlashMessage.add(result.message, 'error');
								}
							}

							// Every thing was found OK.
							if (resultOk) {
								message = Vidi.format('message-mass-deleted-plural', results.length);
								if (results.length <= 1) {
									message = Vidi.format('message-mass-deleted-singular', results.length);
								}

								Vidi.FlashMessage.add(message, 'success');

								// Un-check the top checkbox.
								$('.checkbox-row-top').removeAttr('checked');

								// Reload data table
								Vidi.grid.fnDraw(false); // false = for keeping the pagination.
							}

							// Display flash message
							Vidi.FlashMessage.showAll(messageFadeOut);
						}
					);
				}
			}
		]);
	}
};
