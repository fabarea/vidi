"use strict";

/** @namespace Vidi */

$(document).ready(function() {

	// Initialize Session
	Vidi.Session.initialize();
	Vidi.Edit.initialize();

	/**
	 * Add handler when clicking the reload button
	 */
	$('.btn-reload').click(function(e) {
		e.preventDefault();
		Vidi.grid.fnDraw(false); // false = for keeping the pagination.
	});

	/**
	 * Pencil icon on the top of the Grid.
	 */
	$('#content-list').find('th').hover(function() {
			$('.mass-edit', this).fadeTo( 100, 1);
		}, function() {
			$('.mass-edit', this).fadeTo( 100, 0);
		}
	);

	/**
	 * Enable the hide / show column
	 */
	$('.check-visible-toggle').click(function() {
		var iCol = $(this).val();

		/* Get the DataTables object again - this is not a recreation, just a get of the object */
		var oTable = $('#content-list').dataTable();

		var loadingMessage = '<img src="' + Vidi.module.publicPath + 'Resources/Public/Images/loading.gif" width="16" height="" alt="" />';
		var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
		oTable.fnSetColumnVis(iCol, bVis ? false : true);
		if (!bVis) {

			// look for the nth-child which corresponds to a visible column.
			var columnIndex = 1;
			for (var index = 1; index < oTable.fnSettings().aoColumns.length && index <= iCol; index++) {
				var column = oTable.fnSettings().aoColumns[index];
				if (column.bVisible) {
					columnIndex++;
				}
			}
			$('#content-list tbody td:nth-child(' + columnIndex + ')').html(loadingMessage);
		}
	});

	/**
	 * Select or deselect all rows at once.
	 */
	$('.checkbox-row-top').click(function() {
		var checkboxes;
		checkboxes = $('#content-list').find('.checkbox-row');
		if ($(this).is(':checked')) {
			checkboxes.filter(':not(:checked)').click();
			$('.menu-selected-rows').removeClass('disabled');
		} else {
			checkboxes.filter(':checked').click();
			$('.menu-selected-rows').addClass('disabled');
		}
	});

	/**
	 * Export data for current selection.
	 */
	$('.action-all-rows').find('.export-xml, .export-csv, .export-xls').click(function(e) {

		e.preventDefault();

		// Create Uri object which will receive the parameters.
		var baseUrl = window.location.protocol + '//' + window.location.hostname + '/typo3/';
		var uri = new Uri(Vidi.Grid.stored.url);

		// Feed the Uri with parameter
		var formatParameterName = Vidi.module.parameterPrefix + '[format]';
		for (var index in Vidi.Grid.stored.data) {
			var parameter = Vidi.Grid.stored.data[index];

			if (parameter.name === formatParameterName) {
				parameter.value = $(this).data('format');
			} else if (parameter.name === 'iDisplayLength' || parameter.name === 'iDisplayStart') {
				parameter.value = 0;
			}

			uri.addQueryParam(parameter.name, parameter.value);
		}
		var url = baseUrl + uri.toString();
		window.open(url);
	});

	/**
	 * Mass edit action
	 */
	$('.action-all-rows').find('.mass-edit').click(function(e) {
		e.preventDefault();

		// @todo implement me!
	});

	/**
	 * Mass delete action
	 */
	$('.action-all-rows').find('.mass-delete').click(function(e) {
		e.preventDefault();

		var uri = new Uri(Vidi.Grid.stored.url);

		// Add parameters to the Uri being built.
		var actionParameterName = Vidi.module.parameterPrefix + '[action]';
		for (var index in Vidi.Grid.stored.data) {
			var parameter = Vidi.Grid.stored.data[index];

			if (parameter.name === actionParameterName) {
				parameter.value = 'delete';
			} else if (parameter.name === 'iDisplayLength' || parameter.name === 'iDisplayStart') {
				parameter.value = 0;
			}
			uri.addQueryParam(parameter.name, parameter.value);
		}

		var message = Vidi.translate('confirm-mass-delete-current-selection');

		// Trigger mass-delete action against current selection.
		Vidi.Action.massRemove(message, uri.toString());
	});


	/**
	 * Export data for selected rows.
	 */
	$('.action-selected-rows').find('.export-xml, .export-csv, .export-xls').click(function(e) {

		e.preventDefault();

		// Create Uri object which will receive the parameters.
		var baseUrl = window.location.protocol + '//' + window.location.hostname + '/typo3/';
		var uri = new Uri(Vidi.Grid.stored.url);

		// Add parameters to the Uri object.
		uri.addQueryParam(Vidi.module.parameterPrefix + '[action]', 'list');
		uri.addQueryParam(Vidi.module.parameterPrefix + '[controller]', 'Content');
		uri.addQueryParam(Vidi.module.parameterPrefix + '[format]', $(this).data('format'));
		uri.addQueryParam(Vidi.module.parameterPrefix + '[matches][uid]', Vidi.Grid.getSelectedIdentifiers().join(','));

		var url = baseUrl + uri.toString();
		window.open(url);
	});

	/**
	 * Mass edit action
	 */
	$('.action-selected-rows').find('.mass-edit').click(function(e) {
		e.preventDefault();

		// @todo implement me!
	});

	/**
	 * Mass delete action
	 */
	$('.action-selected-rows').find('.mass-delete').click(function(e) {
		var selectedIdentifiers, message, url, uid;

		// Get selected rows.
		selectedIdentifiers = Vidi.Grid.getSelectedIdentifiers();

		e.preventDefault();
		url = $(this).attr('href');
		url = url + '&' + Vidi.module.parameterPrefix + '[matches][uid]=' + selectedIdentifiers.join(',');

		message = Vidi.format('confirm-mass-delete-plural', selectedIdentifiers.length);
		if (selectedIdentifiers.length <= 1) {
			message = Vidi.format('confirm-mass-delete-singular', selectedIdentifiers.length);
		}

		// Trigger mass-delete action against selected rows.
		Vidi.Action.massRemove(message, url);
	});

	/**
	 * Add Access Key for switching back to the Grid with key escape
	 */
	$(document).keyup(function(e) {
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
	Vidi.grid = $('#content-list').dataTable(Vidi.Grid.getOptions());

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
Vidi.format = function(key) {
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
Vidi.translate = function(key) {
	return Vidi.Label.get(key);
};

/**
 * Merge second object into first one
 *
 * @param {object} set1
 * @param {object} set2
 * @return {object}
 */
Vidi.merge = function(set1, set2) {
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
Vidi.computeUrl = function(actionName, controllerName) {

	// list of parameters used to call the right controller / action.
	var parameters = {
		format: 'json',
		action: actionName,
		controller: controllerName
	};

	var urlParts = [Vidi.module.moduleUrl];
	$.each(parameters, function(index, value) {
		var element = '{0}[{1}]={2}'.format(Vidi.module.parameterPrefix, index, value);
		urlParts.push(element);
	});

	return urlParts.join('&');
}