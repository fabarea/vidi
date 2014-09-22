"use strict";

/** @namespace Vidi */

/**
 * Object for handling "edit-inline" actions.
 *
 * @type {Object} Vidi.EditInline
 */
Vidi.EditInline = {

	/**
	 * Loading indicator
	 */
	indicator: '<img src="' + Vidi.module.publicPath + 'Resources/Public/Images/loading.gif" width="16" height="" alt="" />',

	/**
	 * Get needed values to be added to the Ajax request.
	 *
	 * @param {string} value
	 * @param {object} settings
	 * @returns {object}
	 */
	submitData: function (value, settings) {
		var data = {}; // initialize empty

		var columnPosition = Vidi.Grid.getColumnPosition(this);

		// Compute "matches" parameter...
		var parameterName = '{0}[matches][uid]'.format(Vidi.module.parameterPrefix);
		data[parameterName] = Vidi.Grid.getRowIdentifier(this);

		// Compute "fieldNameAndPath" parameter...
		parameterName= '{0}[fieldNameAndPath]'.format(Vidi.module.parameterPrefix);
		data[parameterName] = Vidi._columns[columnPosition]['columnName'];

		if ($(this).data('language')) {
			parameterName= '{0}[language]'.format(Vidi.module.parameterPrefix);
			data[parameterName] = $(this).data('language');
		}

		return data;
	},

	/**
	 * Define dynamically the name of the field which will be used as POST parameter.
	 *
	 * @param {string} value
	 * @param {object} settings
	 * @returns {string}
	 */
	getParameters: function (value, settings) {
		var contentParameter, fieldName;

		var columnPosition = Vidi.Grid.getColumnPosition(this);

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
	 * @return {void}
	 */
	submitDataCallBack: function(data, settings) {

		// dataType = html is hardcoded in jEditable -> so convert to JSON first
		// And take the first element as THE response since we are not in a multi editing context.
		var response = JSON.parse(data);

		if (!response.hasErrors) {
			$(this).html(response.processedObject['updatedValue']); // re-inject the value in the Cell

			// remove a possible "invisible" or "visible" class.
			if ($(this).closest('div.invisible')) {
				$(this).closest('div.invisible').removeClass('invisible');
			}
			if ($(this).closest('div.visible')) {
				$(this).closest('div.visible').removeClass('visible');
			}
		} else {

			// Display error messages.
			for (var index = 0; index < response.errorMessages.length; index++) {
				var message = response.errorMessages[index];
				Vidi.FlashMessage.add(message, 'error');
			}

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
