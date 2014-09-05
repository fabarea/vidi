"use strict";

/** @namespace Vidi */
/**
 * Object for handling "export" actions.
 *
 * @type {Object} Vidi.Export
 */
Vidi.Export = {

	/**
	 * Export selected rows.
	 *
	 * @param {string} format
	 * @return void
	 */
	exportSelectedRows: function(format) {

		// Create Uri object which will receive the parameters.
		var baseUrl = window.location.protocol + '//' + window.location.hostname + '/typo3/';
		var uri = new Uri(Vidi.Grid.getStoredUrl());

		// Add parameters to the Uri object.
		uri.addQueryParam(Vidi.module.parameterPrefix + '[action]', 'list');
		uri.addQueryParam(Vidi.module.parameterPrefix + '[controller]', 'Content');
		uri.addQueryParam(Vidi.module.parameterPrefix + '[format]', format);
		uri.addQueryParam(Vidi.module.parameterPrefix + '[matches][uid]', Vidi.Grid.getSelectedIdentifiers().join(','));

		var url = baseUrl + uri.toString();
		window.open(url);
	},

	/**
	 * Export the current selection.
	 *
	 * @param {string} format
	 * @return void
	 */
	exportSelection: function(format) {

		// Create Uri object which will receive the parameters.
		var baseUrl = window.location.protocol + '//' + window.location.hostname + '/typo3/';
		var uri = new Uri(Vidi.Grid.getStoredUrl());

		// Feed the Uri with parameter
		var formatParameterName = Vidi.module.parameterPrefix + '[format]';
		for (var index in Vidi.Grid.getStoredParameters()) {
			var parameter = Vidi.Grid.getStoredParameters()[index];

			if (parameter.name === formatParameterName) {
				parameter.value = format;
			} else if (parameter.name === 'iDisplayLength' || parameter.name === 'iDisplayStart') {
				parameter.value = 0;
			}

			uri.addQueryParam(parameter.name, parameter.value);
		}
		var url = baseUrl + uri.toString();
		window.open(url);
	}
};
