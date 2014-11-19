"use strict";

/** @namespace Vidi */

/**
 * Object for handling Data Table
 *
 * @type {Object}
 */
Vidi.Grid = {

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
			'bStateSave': true,
			// stateSaveCallback - rename me after 10.4 migration
			'fnStateSave': function(oSettings, oData) {
				Vidi.Session.set('dataTables', JSON.stringify(oData));
			},
			// fnStateLoadCallback - rename me after 10.4 migration
			'fnStateLoad': function(oSettings) {

				var state = JSON.parse(Vidi.Session.get('dataTables'));

				// Mark or un-mark checkbox corresponding to column visibility.
				if (state) {
					$('.check-visible-toggle').each(function(index) {
						if (state.abVisCols[index + 1]) {
							$(this).attr('checked', 'checked');
						} else {
							$(this).removeAttr('checked')
						}
					});
				}

				// Set default search by overriding the session data if argument is passed.
				if (state) {
					// Override search if given in URL.
					var uri = new Uri(window.location.href);
					if (uri.getQueryParamValue('search')) {
						var search = uri.getQueryParamValue('search');
						state.oSearch.sSearch = search.replace(/'/g, '"');
					}

					// Also stores value to be used in visual search.
					if (uri.getQueryParamValue('query')) {
						Vidi.Session.set('visualSearch.query', uri.getQueryParamValue('query'));
					}
				}
				return state;
			},

			/**
			 * Override the default Ajax call of DataTable.
			 *
			 * @param sSource
			 * @param aoData
			 * @param fnCallback
			 * @param oSettings
			 */
			'fnServerData': function(sSource, aoData, fnCallback, oSettings) {

				// Store the parameters to be able to reconstruct the URL later on.
				Vidi.Grid.storage.data = aoData;
				Vidi.Grid.storage.url = sSource;

				oSettings.jqXHR = $.ajax({
					'dataType': 'json',
					'type': "GET",
					'url': sSource,
					'data': aoData,
					'success': fnCallback,
					'error': function() {
						var message = 'Oups! Something went wrong with the Ajax request... Investigate the problem in the Network Monitor. <br />';
						Vidi.FlashMessage.add(message, 'error');
						var fadeOut = false;
						Vidi.FlashMessage.showAll(fadeOut);
					}
				});
			},
			'bProcessing': true,
			'bServerSide': true,
			'sAjaxSource': Vidi.module.moduleUrl,
			'oLanguage': {
				// Commented because bug in IE.
				//'sProcessing': '<img src="' + Vidi.module.publicPath + 'Resources/Public/Images/loading.gif" alt="" width="20"/>',
				// Override some label.
				'sSearch': '',
				'sLengthMenu': '_MENU_'
			},

			/**
			 * Add Ajax parameters from plug-ins
			 *
			 * @param {object} aoData dataTables settings object
			 * @return void
			 */
			'fnServerParams': function(aoData) {

				// Get the parameter related to filter from the URL and "re-inject" them into the Ajax request
				var uri = new Uri(window.location.href);
				for (var index = 0; index < uri.queryPairs.length; index++) {
					var queryPair = uri.queryPairs[index];
					var parameterName = queryPair[0];
					var parameterValue = queryPair[1];

					// Transmit filter parameter.
					var regularExpression = new RegExp(Vidi.module.parameterPrefix);
					if (regularExpression.test(parameterName)) {
						aoData.push({ 'name': decodeURI(parameterName), 'value': parameterValue });
					}

					// Transmit a few other parameters as well.
					var transmittedParameters = ['vidiModuleCode', 'id'];
					for (var parameterIndex = 0; parameterIndex < transmittedParameters.length; parameterIndex++) {
						var transmittedParameter = transmittedParameters[parameterIndex];
						if (transmittedParameter === parameterName) {
							aoData.push({ 'name': decodeURI(parameterName), 'value': parameterValue });
						}
					}
				}

				// Transmit visible columns to the server so that id does not need to process not displayed stuff.
				var columns = $(this).dataTable().fnSettings().aoColumns;
				$.each(columns, function(index, column) {
					if (column['bVisible']) {
						aoData.push({ 'name': Vidi.module.parameterPrefix + '[columns][]', 'value': column['columnName'] });
					}
				});

				// Handle the search term parameter coming from the Visual Search bar.
				$.each(aoData, function(index, object) {
					if (object['name'] === 'sSearch') {
						object['value'] = Vidi.VisualSearch.convertExpression(object['value']);
						aoData.push({ 'name': Vidi.module.parameterPrefix + '[searchTerm]', 'value': object['value'] });
					}
				});

				// Get the parameter related to filter from the URL and "re-inject" them into the Ajax request
				var parameterPrefix = 'tx_vidi_' + Vidi.module.moduleCode.toLowerCase();

				aoData.push({ 'name': parameterPrefix + '[action]', 'value': 'list' });
				aoData.push({ 'name': parameterPrefix + '[controller]', 'value': 'Content' });
				aoData.push({ 'name': parameterPrefix + '[format]', 'value': 'json' });

				// Visual effect
				$('#content-list').css('opacity', 0.3);
			},
			'aoColumns': Vidi._columns,
			'aLengthMenu': [
				[10, 25, 50, 100, 200, 500],
				[10, 25, 50, 100, 200, 500]
			],
			'fnInitComplete': function() {
				Vidi.VisualSearch.initialize();

				var query = Vidi.Session.get('visualSearch.query');
				Vidi.VisualSearch.instance.searchBox.setQuery(query);
			},
			/**
			 * Override the default Ajax call of DataTable.
			 *
			 * @param {object} transaction
			 */
			'fnDrawCallback': function(transaction) {

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
				Vidi.Remove.attachHandlerInGrid();

				// Handle flash message
				Vidi.FlashMessage.showAll();

				// Update the mass action menu label.
				Vidi.Grid.updateMassActionMenu();

				/**
				 * Bind handler for editable content for input.
				 */
				Vidi.grid.$('.editable-textarea').editable(
					Vidi.EditInline.getUrl(),
					{
						type: 'custom-textarea',
						placeholder: '',
						cancel: 'Cancel',
						submit: 'OK',
						indicator: Vidi.EditInline.indicator,
						data: Vidi.EditInline.getParameters,
						submitdata: Vidi.EditInline.submitData,
						callback: Vidi.EditInline.submitDataCallBack
					}
				);

				/**
				 * Bind handler for editable content for input.
				 */
				Vidi.grid.$('.editable-textfield').editable(
					Vidi.EditInline.getUrl(),
					{
						type: 'custom-textfield',
						placeholder: '',
						submit: 'OK',
						indicator: Vidi.EditInline.indicator,
						data: Vidi.EditInline.getParameters,
						submitdata: Vidi.EditInline.submitData,
						callback: Vidi.EditInline.submitDataCallBack
					}
				);
			}
		};

		config = this.initializeDefaultSearch(config);
		return config;
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
			massActionLabel = label.format(Vidi.Grid.getNumberOfSelectedRows());
		} else {
			label = TYPO3.l10n.localize('for_all_rows');
			massActionLabel = label.format(Vidi.Grid.getStoredTransaction().fnRecordsTotal());
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
	 * @return {string}
	 */
	getStoredUrl: function() {
		return Vidi.Grid.storage.url;
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
				config.oSearch = {
					'sSearch': search.replace(/'/g, '"')
				};
			}

			// Also stores value to be used in visual search.
			if (uri.getQueryParamValue('query')) {
				Vidi.Session.set('visualSearch.query', uri.getQueryParamValue('query'));
			}
		}
		return config;
	},

	/**
	 * Compute the cell position according to an DOM element given as parameter
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
	 * Apply effect telling the User a row was edited.
	 *
	 * @return void
	 * @private
	 */
	animateRow: function() {

		// Only if User has previously edited a record.
		if (Vidi.Session.has('lastEditedUid')) {
			var uid = Vidi.Session.get('lastEditedUid');

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
