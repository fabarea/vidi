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

		var data = {};

		// Set uid parameter which must be defined at this level.
		var uidParameter = '{0}[content][uid]'.format(Vidi.module.parameterPrefix);
		var uid = this.parentNode.getAttribute('id').replace('row-', '');
		data[uidParameter] = uid;
		return data;
	},

	/**
	 * @param {string} value
	 * @param {object} settings
	 * @returns {string}
	 */
	data: function (value, settings) {

		// Define dynamically the name of the field which will be used as POST parameter
		var columnPosition = Vidi.table.fnGetPosition(this)[2];
		var fieldName = Vidi._columns[columnPosition]['mData'];
		var contentParameter = '{0}[content][{1}]'.format(Vidi.module.parameterPrefix, fieldName);
		settings.name = contentParameter;

		return value;
	}
};
