"use strict";

/** @namespace Vidi */

$(document).ready(function () {

	/**
	 * Create relation action
	 */
	$(document).on('click', '.dataTable tbody .btn-create-relation', function (e) {

		// Get content by ajax...
		var template = '<label>User Group</label>' +
			'<select>' +
			'<option value="">Select a User group</option>' +
			'<option value="">Foo</option>' +
			'<option value="">Bar</option>' +
			'</select>' +
			'<div style="color: red">@todo 1: content of the box is just static HTML and must be fetched by Ajax.</div>' +
			'<div style="color: red">@todo 2: finish implementing call back. Grid will be refreshed but data not saved.</div>';

		bootbox.dialog(template, [
			{
				'label': 'Cancel'
			},
			{
				'label': 'Create relation',
				'class': 'btn-primary',
				'callback': function () {
					console.log('todo');

					// Reload data table
					Vidi.table.fnDraw();

				}
			}
		]);
		e.preventDefault()
	});
});
