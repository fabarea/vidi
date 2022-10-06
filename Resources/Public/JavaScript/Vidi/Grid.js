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
 * Module: Fab/Vidi/Vidi/Grid
 */
define([
	'jquery',
	'TYPO3/CMS/Backend/Notification',
	'Fab/Vidi/Vidi/Session',
	'Fab/Vidi/Vidi/Edit',
	'Fab/Vidi/Vidi/Delete',
	'Fab/Vidi/Vidi/VisualSearch'
], function($, Notification, Tooltip, Session, VidiEdit, VidiRemove, VisualSearch) {
	'use strict';

	var Grid = {

		/**
		 * Variable for storing various data.
		 *
		 * {Object}
		 */
		storage: {},

		/**
		 * @return object
		 */
		getOptions: function() {

			/**
			 * Table initial options.
			 *
			 * Internal reminder: properties of Datatables have prefix: m, b, s, i, o, a, fn etc...
			 * this corresponds to the variable type e.g. mixed, boolean, string, integer, object, array, function
			 */
			var config = {
				stateSave: true,

				/**
				 * @param settings
				 * @param data
				 */
				stateSaveCallback: function(settings, data) {
					data.stateColumns = [];
					if (typeof settings.aoColumns === 'object') {
						$(settings.aoColumns).each(function(index, column) {

							var state = {};
							state.visible = column.bVisible;
							data.stateColumns.push(state);
						});
					}
					Vidi.Session.set('dataTables', JSON.stringify(data));
				},

				/**
				 * @param settings
				 */
				stateLoadCallback: function(settings) {

					var state = JSON.parse(Vidi.Session.get('dataTables'));

					// Mark or un-mark checkbox corresponding to column visibility.
					if (state) {
						$('.check-visible-toggle').each(function(index) {
							if (state.stateColumns[index + 1] && state.stateColumns[index + 1].visible) {
								$(this).attr('checked', 'checked');
							} else {
								$(this).removeAttr('checked');
							}
						});

						// Set default search by overriding the session data if argument is passed.
						// Override search if given in URL.
						var uri = new Uri(window.location.href);
						if (uri.getQueryParamValue('search')) {
							var search = uri.getQueryParamValue('search');

							state.search.search = search.replace(/'/g, '"');
						}

						// Also stores value to be used in visual search.
						if (uri.getQueryParamValue('query')) {
							Vidi.Session.set('visualSearchQuery', uri.getQueryParamValue('query'));
						}
					}
					return state;
				},

				ajax: {
					url: Vidi.module.moduleUrl,
					data: function(data) {

						// Get the parameter related to filter from the URL and "re-inject" them into the Ajax request
						var uri = new Uri(window.location.href);
						for (var index = 0; index < uri.queryPairs.length; index++) {
							var queryPair = uri.queryPairs[index];
							var parameterName = queryPair[0];
							var parameterValue = queryPair[1];

							// Transmit filter parameter.
							var regularExpression = new RegExp(Vidi.module.parameterPrefix);
							if (regularExpression.test(parameterName)) {
								data[decodeURI(parameterName)] = parameterValue;
							}

							// Transmit a few other parameters as well.
							var transmittedParameters = ['vidiModuleCode', 'id'];
							for (var parameterIndex = 0; parameterIndex < transmittedParameters.length; parameterIndex++) {
								var transmittedParameter = transmittedParameters[parameterIndex];
								if (transmittedParameter === parameterName) {
									data[decodeURI(parameterName)] = parameterValue;
								}
							}
						}

						// Transmit visible columns to the server so to process only necessary stuff.
						data[Vidi.module.parameterPrefix + '[columns]'] = Vidi.Grid.getListOfVisibleColumns();

						// Handle the search term parameter coming from the Visual Search bar.
						if (data.search.value) {

							if (Vidi.module.areFacetSuggestionsLoaded) {
								// Save raw query to be used in Vidi Backend.
								data.search.value = Vidi.VisualSearch.convertExpression(data.search.value);
								data[Vidi.module.parameterPrefix + '[searchTerm]'] = data.search.value;

								Vidi.Session.set('query', data.search.value);
							} else if (Vidi.Session.get('query')) { // retrieve a query from the session.
								data.search.value = Vidi.Session.get('query');
								data[Vidi.module.parameterPrefix + '[searchTerm]'] = Vidi.Session.get('query');
							}
						}

						data = Vidi.Grid.addAjaxAdditionalParameters(data);

						data[Vidi.module.parameterPrefix + '[action]'] = 'list';
						data[Vidi.module.parameterPrefix + '[controller]'] = 'Content';
						data[Vidi.module.parameterPrefix + '[format]'] = 'json';

						// Visual effect
						$('#content-list').css('opacity', 0.3);

						// Not needed in the Ajax request.
						delete data.columns;
						delete data.draw;

						// Store the parameters to be able to reconstruct the URL later on.
						Vidi.Grid.storage.data = data;
					},
					error: function(response) {
						// Avoid error display if request is interrupted before we can get a proper status code.
						if (response.status > 0) {
							var message = 'Oups! Something went wrong with the Ajax request... Investigate the problem in the Network Monitor.';
							Notification.error('Communication error', message);
						}
					}
				},
				autoWidth:false,
				processing: true,
				serverSide: true,
				language: {
					// Commented because bug in IE.
					// processing: '<img src="' + Vidi.module.publicPath + 'Resources/Public/Images/loading.gif" alt="" width="40"/>',
					 processing: '',
					// Override some label.
					search: '',
					lengthMenu: '_MENU_'
				},

				columns: Vidi._columns,
				lengthMenu: [Vidi.module.lengthMenu, Vidi.module.lengthMenu],
				displayLength: Vidi.module.defaultLength,
				initComplete: function() {
					Vidi.VisualSearch.initialize();
					// Vidi.Selection.initialize();

					var query = Vidi.Session.get('visualSearchQuery');
					Vidi.VisualSearch.instance.searchBox.setQuery(query);
				},

				/**
				 * Override the default Ajax call of DataTable.
				 *
				 * @param {object} transaction
				 */
				drawCallback: function(transaction) {

					// Store the transaction parameters for later use.
					Vidi.Grid.storage.lastTransaction = transaction;

					// Restore visual
					$('#content-list').css('opacity', 1);

					// Possibly animate row
					Vidi.Grid.animateRow();

					// Add action for switching visibility of hidden elements when mouse is in table cell.
					$('.dataTable tbody td')
						.hover(function() {
							$('.invisible', this).toggleClass('visible').toggleClass('invisible');
						}, function() {
							$('.visible', this).toggleClass('invisible').toggleClass('visible');
						});

					// Attach event to DOM elements
					Vidi.Edit.attachHandlerInGrid();

					// Update the mass action menu label.
					Vidi.Grid.updateMassActionMenu();

					// Module TYPO3/CMS/Backend/Tooltip does not work here, so use call Bootstrap API directly
					// activate tooltip.
					var options = {
						container: 'body',
						placement: 'auto'
					};
					//$('[data-toggle="tooltip"]').tooltip('destroy').tooltip(options);

					/**
					 * Bind handler for editable content for input.
					 */
					// Vidi.grid.$('.editable-textarea').editable(
					// 	Vidi.EditInline.getUrl(),
					// 	{
					// 		type: 'custom-textarea',
					// 		placeholder: '',
					// 		cancel: TYPO3.l10n.localize('cancel'),
					// 		submit: '<button type="submit" class="btn btn-default">' + TYPO3.l10n.localize('ok') + '</button>',
					// 		indicator: Vidi.EditInline.indicator,
					// 		data: Vidi.EditInline.getParameters,
					// 		submitdata: Vidi.EditInline.submitData,
					// 		callback: Vidi.EditInline.submitDataCallBack
					// 	}
					// );

					/**
					 * Bind handler for editable content for input.
					 */
					// Vidi.grid.$('.editable-textfield').editable(
					// 	Vidi.EditInline.getUrl(),
					// 	{
					// 		type: 'custom-textfield',
					// 		placeholder: '',
					// 		submit: '<button type="submit" class="btn btn-default">' + TYPO3.l10n.localize('ok') + '</button>',
					// 		indicator: Vidi.EditInline.indicator,
					// 		data: Vidi.EditInline.getParameters,
					// 		submitdata: Vidi.EditInline.submitData,
					// 		callback: Vidi.EditInline.submitDataCallBack
					// 	}
					// );
				}
			};

			config = this.initializeDefaultSearch(config);
			return config;
		},

		/**
		 * Add possible additional parameters for Ajax.
		 *
		 * @param {Object} data
		 * @return {Object}
		 */
		addAjaxAdditionalParameters: function(data) {

			var additionalParametersList = $('#ajax-additional-parameters').val();
			if (additionalParametersList) {
				var additionalParameters = additionalParametersList.split('&');
				for (var i = 0; i < additionalParameters.length; i++) {
					var splitValues = additionalParameters[i].split('=');
					if (splitValues.length === 2) {
						var parameterName = splitValues[0];
						var parameterValue = splitValues[1];
						var parameterPrefix = Vidi.module.parameterPrefix;
						data[parameterPrefix + '[' + parameterName + ']'] = parameterValue;
					}
				}
			}
			return data;
		},

		/**
		 * Update the label of the mass action menu.
		 *
		 * @return {void}
		 */
		updateMassActionMenu: function() {
			var massActionLabel, label;
			if (Vidi.Grid.hasSelectedRows()) {
				label = TYPO3.l10n.localize('for_selected_rows');
				massActionLabel = TYPO3.l10n.localize('for_selected_rows', {0: Vidi.Grid.getNumberOfSelectedRows()});
			} else {
				massActionLabel = TYPO3.l10n.localize('for_all_rows', {0: Vidi.Grid.getStoredTransaction().fnRecordsTotal()});
			}

			$('.mass-action-label').html('<span class="caret"></span> ' + massActionLabel);
		},

		/**
		 * Return identifiers corresponding to selected rows in the Grid.
		 *
		 * @return {Array}
		 */
		getSelectedIdentifiers: function() {
			var selectedIdentifiers = [];
			$('#content-list')
				.find('.checkbox-row')
				.filter(':checked')
				.each(function(index) {
					var identifier = $(this).data('uid');
					selectedIdentifiers.push(identifier);
				});

			return selectedIdentifiers;
		},

		/**
		 * Return the selected rows in the Grid.
		 *
		 * @return {object}
		 */
		getSelectedRows: function() {
			return $('#content-list')
				.find('.checkbox-row')
				.filter(':checked')
				.closest('tr');
		},

		/**
		 * Return the row identifier / uid which corresponds at the same time to the content identifier.
		 *
		 * @param {object} element
		 * @return {int}
		 */
		getRowIdentifier: function(element) {
			return $(element).closest('tr').get(0).id.replace('row-', '') - 0;
		},

		/**
		 * Return the number of selected rows.
		 *
		 * @return {int}
		 */
		getNumberOfSelectedRows: function() {
			return Vidi.Grid.getSelectedIdentifiers().length;
		},

		/**
		 * Tells whether the Grid has selected rows.
		 *
		 * @return {boolean}
		 */
		hasSelectedRows: function() {
			return Vidi.Grid.getSelectedIdentifiers().length > 0;
		},

		/**
		 * @return {object}
		 */
		getStoredTransaction: function() {
			return Vidi.Grid.storage.lastTransaction;
		},

		/**
		 * @return {object}
		 */
		getStoredParameters: function() {
			return Vidi.Grid.storage.data;
		},

		/**
		 * Set a default search at the data table configuration level.
		 * This case is needed when there is no data saved in session yet.
		 *
		 * @return {array} config
		 * @private
		 */
		initializeDefaultSearch: function(config) {

			var state = JSON.parse(Vidi.Session.get('dataTables'));

			// special case if no session exists.
			if (!state) {
				// Override search if given in URL.
				var uri = new Uri(window.location.href);
				if (uri.getQueryParamValue('search')) {
					var search = uri.getQueryParamValue('search');
					config.search = {
						search: search.replace(/'/g, '"')
					};
				}

				// Also stores value to be used in visual search.
				if (uri.getQueryParamValue('query')) {
					Vidi.Session.set('visualSearchQuery', uri.getQueryParamValue('query'));
				}
			}
			return config;
		},

		/**
		 * Compute the column position according to an DOM element given as parameter
		 *
		 * @param {Object} element
		 * @return int
		 */
		getColumnPosition: function(element) {
			var cell, columnPosition;

			if (element.nodeName === 'TD') {
				cell = element;
			} else {
				cell = $(element).closest('td').get(0);

			}
			columnPosition = Vidi.grid.fnGetPosition(cell)[2];
			return columnPosition;
		},

		/**
		 * Compute the row position according to an DOM element given as parameter
		 *
		 * @param {Object} element
		 * @return int
		 */
		getRowPosition: function(element) {
			return $(element).closest('tr').index();
		},

		/**
		 * Compute the row position according to an DOM element given as parameter
		 *
		 * @return string
		 */
		getListOfVisibleColumns: function() {
			var columns = $('#content-list').dataTable().fnSettings().aoColumns;
			var separator = '';
			var visibleColumns = '';
			$.each(columns, function(index, column) {
				if (column['bVisible']) {
					visibleColumns += separator + column['columnName'];
					separator = ',';
				}
			});

			return visibleColumns;
		},

		/**
		 * Apply effect telling the User a row was edited.
		 *
		 * @return void
		 * @private
		 */
		animateRow: function(uid) {

			// Only if User has previously edited a record.
			if (!uid && Vidi.Session.has('lastEditedUid')) {
				uid = Vidi.Session.get('lastEditedUid');
			}

			if (uid) {
				// Wait a little bit before applying fade-int class. Look nicer.
				setTimeout(function() {
					$('#row-' + uid).addClass('fade-in');
				}, 100);
				setTimeout(function() {
					$('#row-' + uid).addClass('fade-out').removeClass('fade-in');

					// Reset last edited uid
					Vidi.Session.reset('lastEditedUid');
				}, 500);
			}
		}
	};

	// Expose in Vidi object for compatibility reason.
	Vidi.Grid = Grid;
	return Grid;
});
