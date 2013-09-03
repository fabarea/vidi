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
				sessionStorage.setItem('DataTables_' + Vidi.module.dataType, JSON.stringify(oData));
			},
			'fnStateLoad': function (oSettings) {
				var state = JSON.parse(sessionStorage.getItem('DataTables_' + Vidi.module.dataType));

				// Set default search by tampering the session data.
				if (state) {
					// Override search if given in URL.
					var uri = new Uri(window.location.href);
					if (uri.getQueryParamValue('search')) {
						state.oSearch.sSearch = uri.getQueryParamValue('search');
					}
				}
				return state;
			},
			'bProcessing': true,
			'bServerSide': true,
			'sAjaxSource': "mod.php",
			'oLanguage': {
				// remove some label
				"sSearch": '',
				"sLengthMenu": '_MENU_'
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

				aoData.push({ "name": 'M', "value": moduleCode});
				aoData.push({ "name": parameterCode + '[action]', "value": 'listRow' });
				aoData.push({ "name": parameterCode + '[controller]', "value": 'Content' });
				aoData.push({ "name": parameterCode + '[format]', "value": 'json' });
			},
			'aoColumns': Vidi._columns,
			'aLengthMenu': [
				[10, 25, 50, 100, -1],
				[10, 25, 50, 100, "All"]
			],
			'fnInitComplete': function () {
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
			}
		};

		config = this.setDefaultSearch(config);
		return config;
	},

	/**
	 * Set a default search at the data table configuration level.
	 * This case is needed when there is no data saved in session yet.
	 *
	 * @return {array} config
	 * @return array
	 * @private
	 */
	setDefaultSearch: function (config) {

		var state = JSON.parse(sessionStorage.getItem('DataTables_' + Vidi.module.dataType));

		// special case if no session exists.
		if (!state) {
			// Override search if given in URL.
			var uri = new Uri(window.location.href);
			if (uri.getQueryParamValue('search')) {
				config.oSearch = {
					'sSearch': uri.getQueryParamValue('search')
				};
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
		if (Vidi.Session.has('vidi.lastEditedUid')) {
			var uid = Vidi.Session.get('vidi.lastEditedUid');

			// Wait a little bit before applying fade-int class. Look nicer.
			setTimeout(function () {
				$('#row-' + uid).addClass('fade-in');
			}, 100);
			setTimeout(function () {
				$('#row-' + uid).addClass('fade-out').removeClass('fade-in');

				// Reset last edited uid
				Vidi.Session.reset('vidi.lastEditedUid');
			}, 500);
		}
	}
};
