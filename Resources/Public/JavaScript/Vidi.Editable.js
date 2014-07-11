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
		var data = {}; // initialize empty
		var uid = 0;

		// Get needed values to be added to the Ajax request.
		var columnPosition = Vidi.grid.fnGetPosition(this)[2];
		var dataType = Vidi._columns[columnPosition]['dataType'];

		// Retrieves the identifier (AKA uid) of the record from the row.
		var classes = this.parentNode.getAttribute('class').split(' ');
		var expression = new RegExp(dataType + '_[0-9]+', 'ig');
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

		// Compute parameter names...
		var parameterNameUid = '{0}[matches][uid]'.format(Vidi.module.parameterPrefix);
		var parameterNameDataType = '{0}[dataType]'.format(Vidi.module.parameterPrefix);

		// ... and return the data array.
		data[parameterNameUid] = uid;
		data[parameterNameDataType] = dataType;
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
