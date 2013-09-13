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
				'sLengthMenu': '_MENU_'
			},

			/**
			 * Add Ajax parameters from plug-ins
			 *
			 * @param {object} aoData dataTables settings object
			 * @return void
			 */
			'fnServerParams': function (aoData) {

				// Get the parameter related to filter from the URL and "re-inject" them into the Ajax request
				var uri, moduleCode, parameterCode;
				uri = new Uri(window.location.href);
				moduleCode = uri.getQueryParamValue('M');
				parameterCode = 'tx_vidi_' + moduleCode.toLowerCase();

				aoData.push({ 'name': 'M', 'value': moduleCode});
				aoData.push({ 'name': parameterCode + '[action]', 'value': 'listRow' });
				aoData.push({ 'name': parameterCode + '[controller]', 'value': 'Content' });
				aoData.push({ 'name': parameterCode + '[format]', 'value': 'json' });
			},
			'aoColumns': Vidi._columns,
			'aLengthMenu': [
				[10, 25, 50, 100, -1],
				[10, 25, 50, 100, 'All']
			],
			'fnInitComplete': function () {
				Vidi.VisualSearch.initialize();

				var query = Vidi.Session.get('visualSearch.query');
				Vidi.VisualSearch.instance.searchBox.setQuery(query);
			},
			'fnDrawCallback': function () {

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
				Vidi.Action.delete();

				// Handle flash message
				Vidi.FlashMessage.showAll();

				/**
				 * Bind handler for editable content.
				 */
				Vidi.table.$('td.editable').editable(
					Vidi.Table.computeEditableUrl(),
					{
						placeholder: '',
						indicator: '<img src="' + Vidi.module.publicPath + 'Resources/Public/Images/loading.gif" width="16" height="" alt="" />',
						//callback: function (sValue, settings) {
							// could be the reload of the whole grid.
						//},
						data: function (value, settings) {

							// Define dynamically the name of the field which will be used as POST parameter
							var columnPosition = Vidi.table.fnGetPosition(this)[2];
							var fieldName = Vidi._columns[columnPosition]['mData'];
							var contentParameter = '{0}[content][{1}]'.format(Vidi.module.parameterPrefix, fieldName);
							settings.name = contentParameter;

							return value;
						},
						submitdata: function (value, settings) {

							var data = {};

							// Set uid parameter which must be defined at this level.
							var uidParameter = '{0}[content][uid]'.format(Vidi.module.parameterPrefix);
							data[uidParameter] = this.parentNode.getAttribute('id');

							return data;
						},
						'height': '20px'
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
