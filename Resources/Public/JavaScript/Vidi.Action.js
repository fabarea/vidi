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
	edit: function () {

		// bind the click handler script to the newly created elements held in the table
		$('.btn-edit').bind('click', function (e) {
			Vidi.Session.set('lastEditedUid', $(this).data('uid'));
		});

		// Make a row selectable
		$('.checkbox-row').bind('click', function (e) {
			var checkboxes;

			$(this)
				.closest('tr')
				.toggleClass('active');
			e.stopPropagation(); // we don't want the event to propagate.

			checkboxes = $('#content-list').find('.checkbox-row').filter(':checked');
			if (checkboxes.length > 0) {
				$('.mass-action').removeClass('disabled');
			} else {
				$('.mass-action').addClass('disabled');
			}
		});

		// Add listener on the row as well
		$('.checkbox-row')
			.parent()
			.css('cursor', 'pointer')
			.bind('click', function (e) {
				$(this).find('.checkbox-row').click()
			});
	},

	/**
	 * Bind delete buttons in list view.
	 *
	 * @return void
	 */
	remove: function () {
		$('.btn-delete')
			.click(function () {
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
				onShown: function () {

					// Element corresponds to the click-over box. Keep it accessible in the closure.
					var element = this;

					// bind click on "btn-delete-row"
					$('.btn-delete-row').bind('click', function (e) {
						var row, title, message, url;

						$(this).addClass('disabled').text(Vidi.translate('deleting'));
						url = $(Vidi.Action.scope).attr('href');

						// Compute media title
						row = $(Vidi.Action.scope).closest("tr").get(0);
						title = $('.media-title', row).html();
						message = Vidi.format("confirm-delete", $.trim(title));

						// Send Ajax request to delete media
						$.get(url,
							function (data) {

								// Hide click-over box.
								element.hide();

								// Reload data table
								Vidi.table.fnDeleteRow(Vidi.table.fnGetPosition(row));
								var message = Vidi.format('message-deleted', data.object[Vidi.module.tca.ctrl.label]);
								Vidi.FlashMessage.add(message, 'success');
							}
						);
					});
				}
			});
	}
};

