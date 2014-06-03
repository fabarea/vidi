"use strict";

/** @namespace Vidi */

/**
 * Object for handling Data Table
 *
 * @type {Object}
 */
Vidi.Editable = {

	/**
	 * Loading indicator
	 */
	indicator: '<img src="' + Vidi.module.publicPath + 'Resources/Public/Images/loading.gif" width="16" height="" alt="" />',

	/**
	 * @param {string} value
	 * @param {object} settings
	 * @returns {object}
	 */
	submitData: function (value, settings) {
		var uidParameter, uid, dataType, columnPosition, classes, expression;
		var data = {};

		// Compute parameter which must be defined at this level.
		uidParameter = '{0}[matches][uid]'.format(Vidi.module.parameterPrefix);

		// Get needed values
		columnPosition = Vidi.grid.fnGetPosition(this)[2];
		dataType = Vidi._columns[columnPosition]['dataType'];

		classes = this.parentNode.getAttribute('class').split(' ');
		expression = new RegExp(dataType + '_[0-9]+', 'ig');
		uid = 0;
		$.each(classes, function(index, value) {

			var match = value.match(expression);
			if (match) {
				var matches = match[0].split('_');
				uid = matches[matches.length - 1];
			}
		});

		if (uid == 0) {
			console.log('Vidi: I could not found a valid uid. Update will not succeed! #1390668840')
		}

		// ... And return as array
		data[uidParameter] = uid;
		return data;
	},

	/**
	 * @param {string} value
	 * @param {object} settings
	 * @returns {string}
	 */
	data: function (value, settings) {
		var contentParameter, columnPosition, fieldName;

		// Define dynamically the name of the field which will be used as POST parameter
		columnPosition = Vidi.grid.fnGetPosition(this)[2];
		fieldName = Vidi._columns[columnPosition]['mData'];
		contentParameter = '{0}[content][{1}]'.format(Vidi.module.parameterPrefix, fieldName);

		settings.name = contentParameter;
		return value;
	},

	/**
	 * Callback after submit action.
	 *
	 * @param {string} data
	 * @param {object} settings
	 * @returns {string}
	 */
	submitCallBack: function(data, settings) {

		// dataType = html is hardcoded in jEditable -> so convert to JSON first
		// And take the first element as THE response since we are not in a multi editing context.
		var response = JSON.parse(data)[0];

		if (response.status === true) {
			var updatedField = response.updatedField;
			$(this).html(response.object[updatedField]); // re-inject the value in the Cell
		} else {
			Vidi.FlashMessage.add(response.message, 'error');
			var fadeOut = false;
			Vidi.FlashMessage.showAll(fadeOut);
			$(this).html('Something went wrong...');
		}
	},

	/**
	 * Computed the URL used for editable content.
	 *
	 * @return {string}
	 * @private
	 */
	getUrl: function() {

		// list of parameters used to call the right controller / action.
		var parameters = {
			format: 'json',
			action: 'update',
			controller: 'Content'
		};

		var urlParts = [Vidi.module.moduleUrl];
		$.each(parameters, function(index, value) {
			var element = '{0}[{1}]={2}'.format(Vidi.module.parameterPrefix, index, value);
			urlParts.push(element);
		});

		return urlParts.join('&');
	}
};
