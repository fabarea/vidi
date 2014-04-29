"use strict";

/** @namespace Vidi */

$(document).ready(function () {

	// Initialize Session
	Vidi.Session.initialize();

	/**
	 * Enable the hide / show column
	 */
	$('.check-visible-toggle').click(function () {
		var iCol = $(this).val();

		/* Get the DataTables object again - this is not a recreation, just a get of the object */
		var oTable = $('#content-list').dataTable();

		var loadingMessage = '<img src="' + Vidi.module.publicPath + 'Resources/Public/Images/loading.gif" width="16" height="" alt="" />';
		var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
		oTable.fnSetColumnVis(iCol, bVis ? false : true);
		if (! bVis) {

			// look for the nth-child which corresponds to a visible column
			var columnIndex = 1;
			for(var index = 1; index < oTable.fnSettings().aoColumns.length && index <= iCol; index ++) {
				var column = oTable.fnSettings().aoColumns[index];
				if (column.bVisible) {
					columnIndex ++;
				}
			}
			$('#content-list tbody td:nth-child(' + columnIndex + ')').html(loadingMessage);
		}
	});

	/**
	 * Select or deselect all rows at once.
	 */
	$('.checkbox-row-top').click(function () {
		var checkboxes;
		checkboxes = $('#content-list').find('.checkbox-row');
		if ($(this).is(':checked')) {
			checkboxes.filter(':not(:checked)').click();
			$('.mass-action').removeClass('disabled');
		} else {
			checkboxes.filter(':checked').click();
			$('.mass-action').addClass('disabled');
		}
	});

	/**
	 * Mass delete action
	 */
	$('.mass-delete').click(function (e) {
		var selectedRows, message, url, uid;

		e.preventDefault();
		url = $(this).attr('href');

		selectedRows = [];
		$('#content-list')
			.find('.checkbox-row')
			.filter(':checked')
			.each(function (index) {
				uid = $(this).data('uid');
				selectedRows.push(uid);
				url += '&{0}[contents][{1}]={2}'.format(Vidi.module.parameterPrefix, index, uid);
			});


		message = Vidi.format("confirm-mass-delete-plural", selectedRows.length);
		if (selectedRows.length <= 1) {
			message = Vidi.format("confirm-mass-delete-singular", selectedRows.length);
		}

		bootbox.dialog(message, [
			{
				'label': Vidi.translate('cancel')
			},
			{
				'label': Vidi.translate('delete'),
				'class': "btn-danger",
				'callback': function () {
					$.get(url,
						function (data) {
							message = Vidi.format('message-mass-deleted-plural', selectedRows.length);
							if (selectedRows.length <= 1) {
								message = Vidi.format('message-mass-deleted-singular', selectedRows.length);
							}
							Vidi.FlashMessage.add(message, 'success');
							Vidi.FlashMessage.showAll();
							$('.checkbox-row-top').removeAttr('checked'); // un-check the top checkbox.
							
							// Reload data table
							Vidi.table.fnDraw();
						}
					);
				}
			}
		]);
	});

	/**
	 * Add Access Key for switching back to the Grid with key escape
	 */
	$(document).keyup(function (e) {
		// escape
		var ESCAPE_KEY = 27;
		if (e.keyCode == ESCAPE_KEY) {

			// True means the main panel is not currently displayed.
			if ($('#navbar-sub > *').length > 0) {
				var noRedraw = false;
				Vidi.Panel.showList(noRedraw);
			}
		}
	});

	/**
	 * Initialize Grid
	 */
	Vidi.table = $('#content-list').dataTable(Vidi.Table.getOptions());

	// Add place holder for the search
	$('.dataTables_filter input').attr('placeholder', Vidi.translate('search'));
});

/**
 * Format a string give a place holder. Acts as the "sprintf" function in PHP
 *
 * Example:
 *
 * "Foo {0}".format('Bar') will return "Foo Bar"
 *
 * @param {string} key
 */
Vidi.format = function (key) {
	var s = Vidi.translate(key),
		i = arguments.length + 1;

	while (i--) {
		s = s.replace(new RegExp('\\{' + i + '\\}', 'gm'), arguments[i + 1]);
	}
	return s;
};

/**
 * Shorthand method for getting a label.
 *
 * @param {string} key
 */
Vidi.translate = function (key) {
	return Vidi.Label.get(key);
};

/**
 * Merge second object into first one
 *
 * @param {object} set1
 * @param {object} set2
 * @return {object}
 */
Vidi.merge = function (set1, set2) {
	for (var key in set2) {
		if (set2.hasOwnProperty(key))
			set1[key] = set2[key]
	}
	return set1
};


/**
 * Computed the URL with a parameter-able action.
 *
 * @param {string} actionName
 * @param {string} controllerName
 * @return {string}
 * @private
 */
Vidi.computeUrl = function (actionName, controllerName) {

	// list of parameters used to call the right controller / action.
	var parameters = {
		format: 'json',
		action: actionName,
		controller: controllerName
	};

	var urlParts = [Vidi.module.moduleUrl];
	$.each(parameters, function (index, value) {
		var element = '{0}[{1}]={2}'.format(Vidi.module.parameterPrefix, index, value);
		urlParts.push(element);
	});

	return urlParts.join('&');
}