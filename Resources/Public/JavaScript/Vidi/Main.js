// jshint ;_;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Module: Fab/Vidi/Vidi/Main
 */
define([
		'jquery',
		'TYPO3/CMS/Backend/Notification',
		'Fab/Vidi/Vidi/Grid',
		'Fab/Vidi/Vidi/Visibility',
		'Fab/Vidi/Vidi/Delete',
		'Fab/Vidi/Vidi/EditInline',
		'Fab/Vidi/Vidi/Edit',
		'Fab/Vidi/Vidi/Export',
		'Fab/Vidi/Vidi/Clipboard',
		'Fab/Vidi/DataTables/dataTables',
		'Fab/Vidi/DataTables/dataTables.rowReordering',
		'Fab/Vidi/DataTables/dataTables.bootstrap'
	],
	function($, Notification, Grid, Visibility, Delete, EditInline) {

		'use strict';

		// Initialize some objects...
		Vidi.Edit.attachHandler();
		Vidi.Clipboard.attachHandler();
		Visibility.attachHandler();
		Delete.attachHandler();
		EditInline.configure();

		// Make sure the top checkbox is unchecked by default.
		$('.checkbox-row-top').removeAttr('checked');

		/**
		 * Add handler when clicking the reload button.
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

			var loadingMessage = '<img src="/' + Vidi.module.publicPath + 'Resources/Public/Images/loading.gif" width="16" height="" alt="" />';
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
					Vidi.Delete.removeSelectedRows();
				} else {
					Vidi.Delete.removeSelection();
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
		Vidi.grid = $('#content-list').dataTable(Grid.getOptions());

		if (Vidi.module.hasSortingByDragAndDrop) {
			Vidi.grid.rowReordering({
					sURL: Vidi.module.moduleUrl + '&' + Vidi.module.parameterPrefix + '[format]=json' + '&' + Vidi.module.parameterPrefix + '[controller]=Content' + '&' + Vidi.module.parameterPrefix + '[action]=sort',
					sRequestType: 'GET'
				}
			);
		}

		return Vidi;
	});
