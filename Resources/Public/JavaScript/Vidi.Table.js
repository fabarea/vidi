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
		return {
			'bStateSave': true,
			'fnStateSave': function (oSettings, oData) {
				sessionStorage.setItem('DataTables_' + Vidi.module.dataType, JSON.stringify(oData));
			},
			'fnStateLoad': function (oSettings) {
				return JSON.parse(sessionStorage.getItem('DataTables_' + Vidi.module.dataType));
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
			"fnServerParams": function (aoData) {

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
				Vidi.Table.animateRow();
			},
			'fnDrawCallback': function () {

				// Switch visibility of hidden elements when mouse is in table cell.
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
	},

	/**
	 * Apply effect telling the User a row was edited.
	 *
	 * @return void
	 * @private
	 */
	animateRow: function () {

		// Only if User has previously edited a record.
		if (Vidi.Session.has('media.lastEditedUid')) {
			var uid = Vidi.Session.get('media.lastEditedUid');

			// Wait a little bit before applying fade-int class. Look nicer.
			setTimeout(function () {
				$('#row-' + uid).addClass('fade-in');
			}, 100);
			setTimeout(function () {
				$('#row-' + uid).addClass('fade-out').removeClass('fade-in');

				// Reset last edited uid
				Vidi.Session.reset('media.lastEditedUid');
			}, 500);
		}
	}
};

