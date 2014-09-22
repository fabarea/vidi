/** @namespace Vidi */
(function($) {
	$(function() {
		"use strict";

		// Initialize some objects...
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
				$('.mass-edit', this).fadeTo(100, 1);
			}, function() {
				$('.mass-edit', this).fadeTo(100, 0);
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
			} else {
				checkboxes.filter(':checked').click();
			}

			// Update the mass action menu label.
			Vidi.Grid.updateMassActionMenu();
		});

		/**
		 * Mass delete action
		 */
		$('.mass-action-items')
			.find('.mass-delete').click(function(e) {
				e.preventDefault();

				if (Vidi.Grid.hasSelectedRows()) {
					var baseUrl = $(this).attr('href');
					Vidi.Remove.removeSelectedRows(baseUrl);
				} else {
					Vidi.Remove.removeSelection();
				}
			})
			.end()
			.find('.export-xml, .export-csv, .export-xls').click(function(e) {
				e.preventDefault();

				var format = $(this).data('format');
				if (Vidi.Grid.hasSelectedRows()) {
					Vidi.Export.exportSelectedRows(format);
				} else {
					Vidi.Export.exportSelection(format);
				}

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
})(jQuery);

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

/*
 * Configure "custom-textfield" type for jEditable.
 */
$.editable.addInputType('custom-textfield', {
	element : function(settings, original) {
		var input = $('<input type="text" style="width: 75%; font-size: 12px; height: 16px"/>');
		$(this).append(input);
		return(input);
	},
	content : function(string, settings, original) {
		if (string === '<i>' + TYPO3.l10n.localize('start_editing') + '</i>') {
			string = '';
		}
		$('input', this).val(string);
	}
});

/*
 * Configure "custom-textarea" type for jEditable.
 */
$.editable.addInputType('custom-textarea', {
	element : function(settings, original) {
		var input = $('<textarea style="width: 80%; height: 40%"></textarea>');
		$(this).append(input);
		return(input);
	},
	content : function(string, settings, original) {
		if (string === '<i>' + TYPO3.l10n.localize('start_editing') + '</i>') {
			string = '';
		}
		$("textarea", this).val(string);
	}
});