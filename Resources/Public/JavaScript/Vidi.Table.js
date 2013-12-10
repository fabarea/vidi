"use strict";

/** @namespace Vidi */

/**
 * Object for handling Data Table
 *
 * @type {Object}
 */
Vidi.Table = {

	/**
	 * @return object
	 */
	getOptions: function () {

		/**
		 * Table initial options.
		 *
		 * Internal reminder: properties of Datatables have prefix: m, b, s, i, o, a, fn etc...
		 * this corresponds to the variable type e.g. mixed, boolean, string, integer, object, array, function
		 */
		var config = {
			'bStateSave': true,
			'fnStateSave': function (oSettings, oData) {
				Vidi.Session.set('dataTables', JSON.stringify(oData));
			},
			'fnStateLoad': function (oSettings) {

				var state = JSON.parse(Vidi.Session.get('dataTables'));

				// Mark or un-mark checkbox corresponding to column visibility.
				if (state) {
					$('.check-visible-toggle').each(function (index) {
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
			'bProcessing': true,
			'bServerSide': true,
			'sAjaxSource': "mod.php",
			'oLanguage': {
				// remove some label
				'sSearch': '',
				'sLengthMenu': '_MENU_',
                'sProcessing': '<img src="' + Vidi.module.publicPath + 'Resources/Public/Images/loading.gif" alt="" width="20"/>'
			},
			/**
			 * Add Ajax parameters from plug-ins
			 *
			 * @param {object} aoData dataTables settings object
			 * @return void
			 */
			'fnServerParams': function (aoData) {
				var uri, moduleCode, parameterPrefix;
				parameterPrefix = Vidi.module.parameterPrefix;

				// Get the parameter related to filter from the URL and "re-inject" them into the Ajax request
				uri = new Uri(window.location.href);
				for (var index = 0; index < uri.queryPairs.length; index++) {
					var queryPair = uri.queryPairs[index];
					var parameterName = queryPair[0];
					var parameterValue = queryPair[1];
					var regularExpression = new RegExp(parameterPrefix);
					if (regularExpression.test(parameterName)) {
						aoData.push({ 'name': decodeURI(parameterName), 'value': parameterValue });
					}
				}

				// Transmit visible columns to the server so that id does not need to process not displayed stuff.
				var columns = $(this).dataTable().fnSettings().aoColumns;
				$.each(columns, function(index, column) {
					if (column['bVisible']) {
						aoData.push({ 'name': parameterPrefix + '[columns][]', 'value': column['mData'] });
					}
				});

				// Handle the search term parameter
				$.each(aoData, function (index, object) {
					if (object['name'] === 'sSearch') {
						aoData.push({ 'name': parameterPrefix + '[searchTerm]', 'value': object['value'] });
					}
				});

				// Get the parameter related to filter from the URL and "re-inject" them into the Ajax request
				moduleCode = uri.getQueryParamValue('M');
				parameterPrefix = 'tx_vidi_' + moduleCode.toLowerCase();

				aoData.push({ 'name': 'M', 'value': moduleCode});
				aoData.push({ 'name': parameterPrefix + '[action]', 'value': 'listRow' });
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
			'fnInitComplete': function () {
				Vidi.VisualSearch.initialize();

				var query = Vidi.Session.get('visualSearch.query');
				Vidi.VisualSearch.instance.searchBox.setQuery(query);
			},
			'fnDrawCallback': function () {

				// Restore visual
				$('#content-list').css('opacity', 1);

				// Possibly animate row
				Vidi.Table.animateRow();

				// Add action for switching visibility of hidden elements when mouse is in table cell.
				$('.dataTable tbody td')
					.hover(function () {
						$('.invisible', this).toggleClass('visible').toggleClass('invisible');
					}, function () {
						$('.visible', this).toggleClass('invisible').toggleClass('visible');
					});

				// Attach event to DOM elements
				Vidi.Action.edit();
				Vidi.Action.remove();

				// Handle flash message
				Vidi.FlashMessage.showAll();

				/**
				 * Bind handler for editable content for input.
				 */
				Vidi.table.$('td.editable-textarea').editable(
					Vidi.Table.computeEditableUrl(),
					{
						type: 'textarea',
						placeholder: '',
						cancel: 'Cancel',
						submit: 'OK',
						indicator: Vidi.Editable.indicator,
						data: Vidi.Editable.data,
						submitdata: Vidi.Editable.submitData
						//callback: function (sValue, settings) {
						// could be the reload of the whole grid.
						//},
					}
				);

				/**
				 * Bind handler for editable content for input.
				 */
				Vidi.table.$('td.editable-textfield').editable(
					Vidi.Table.computeEditableUrl(),
					{
						placeholder: '',
						height: '20px',
						indicator: Vidi.Editable.indicator,
						data: Vidi.Editable.data,
						submitdata: Vidi.Editable.submitData
					}
				);
			}
		};

		config = this.setDefaultSearch(config);
		return config;
	},

	/**
	 * Computed the URL used for editable content.
	 *
	 * @return {string}
	 * @private
	 */
	computeEditableUrl: function () {

		// list of parameters used to call the right controller / action.
		var parameters = {
			format: 'json',
			action: 'update',
			controller: 'Content'
		};

		var urlParts = ['M=' + Vidi.module.codeName];
		$.each(parameters, function (index, value) {
			var element = '{0}[{1}]={2}'.format(Vidi.module.parameterPrefix, index, value);
			urlParts.push(element);
		});

		return '/typo3/mod.php?' + urlParts.join('&');
	},

	/**
	 * Set a default search at the data table configuration level.
	 * This case is needed when there is no data saved in session yet.
	 *
	 * @return {array} config
	 * @private
	 */
	setDefaultSearch: function (config) {

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
	 * Apply effect telling the User a row was edited.
	 *
	 * @return void
	 * @private
	 */
	animateRow: function () {

		// Only if User has previously edited a record.
		if (Vidi.Session.has('lastEditedUid')) {
			var uid = Vidi.Session.get('lastEditedUid');

			// Wait a little bit before applying fade-int class. Look nicer.
			setTimeout(function () {
				$('#row-' + uid).addClass('fade-in');
			}, 100);
			setTimeout(function () {
				$('#row-' + uid).addClass('fade-out').removeClass('fade-in');

				// Reset last edited uid
				Vidi.Session.reset('lastEditedUid');
			}, 500);
		}
	}
};
