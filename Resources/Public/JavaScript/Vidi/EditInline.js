// jshint ;_;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Module: Fab/Vidi/Vidi/EditInline
 * @deprecated. This module is not loaded anymore and can be removed if not restored
 */
define([
	'jquery',
	'TYPO3/CMS/Backend/Notification'
], function($, Notification) {
	'use strict';

	var EditInline = {

		/**
		 * Loading indicator
		 */
		indicator: '<img src="/' + Vidi.module.publicPath + 'Resources/Public/Images/loading.gif" width="16" alt="" />',

		/**
		 * @returns void
		 */
		configure: function() {

			/*
			 * Configure "custom-textfield" type for jEditable.
			 */
			$.editable.addInputType(
				'custom-textfield',
				{
					element: function(settings, original) {
						var input = $('<input type="text" style="width: 75%;" class="form-control"/>');
						$(this).append(input);
						return (input);
					},
					content: function(string, settings, original) {
						if (string === '<i>' + TYPO3.l10n.localize('start_editing') + '</i>') {
							string = '';
						}
						$('input', this).val(string);
					}
				});

			/*
			 * Configure "custom-textarea" type for jEditable.
			 */
			$.editable.addInputType(
				'custom-textarea',
				{
					element: function(settings, original) {
						var input = $('<textarea style="width: 80%; height: 40%" class="form-control"></textarea>');
						$(this).append(input);
						return (input);
					},
					content: function(string, settings, original) {
						if (string === '<i>' + TYPO3.l10n.localize('start_editing') + '</i>') {
							string = '';
						}
						$("textarea", this).val(string);
					}
				});
		},

		/**
		 * Get needed values to be added to the Ajax request.
		 *
		 * @param {string} value
		 * @param {object} settings
		 * @returns {object}
		 */
		submitData: function(value, settings) {
			var data = {}; // initialize empty

			var columnPosition = Vidi.Grid.getColumnPosition(this);

			// Compute "matches" parameter...
			var parameterName = '{0}[matches][uid]'.format(Vidi.module.parameterPrefix);
			data[parameterName] = Vidi.Grid.getRowIdentifier(this);

			// Compute "fieldNameAndPath" parameter...
			parameterName = '{0}[fieldNameAndPath]'.format(Vidi.module.parameterPrefix);
			data[parameterName] = Vidi._columns[columnPosition]['columnName'];

			if ($(this).data('language')) {
				parameterName = '{0}[language]'.format(Vidi.module.parameterPrefix);
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
		getParameters: function(value, settings) {
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

				var title = TYPO3.l10n.localize('general.error');

				var message = response.errorMessages.join('</li><li>');
				message = '<ul><li>' + message + '</li></ul>';
				Notification.error(title, message);

				$(this).html(TYPO3.l10n.localize('general.error'));
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

	// Expose in Vidi object for compatibility reason.
	Vidi.EditInline = EditInline;
	return EditInline;
});
