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
 * Module: Fab/Vidi/Vidi/Export
 */
define([
	'jquery',
	'Fab/Vidi/Vidi/Util'
], function($, Util) {
	'use strict';

	var Export = {

		/**
		 * Export selected rows.
		 *
		 * @param {string} format
		 * @return void
		 */
		exportSelectedRows: function(format) {

			var parameters = Vidi.Grid.getStoredParameters();
			parameters[Vidi.module.parameterPrefix + '[format]'] = format;
			parameters[Vidi.module.parameterPrefix + '[matches][uid]'] = Vidi.Grid.getSelectedIdentifiers().join(',');

			document.location.href = Util.getUrl(parameters);
		},

		/**
		 * Export the current selection.
		 *
		 * @param {string} format
		 * @return void
		 */
		exportSelection: function(format) {
			var parameters = Vidi.Grid.getStoredParameters();
			parameters[Vidi.module.parameterPrefix + '[format]'] = format;
			parameters['start'] = 0;
			parameters['length'] = 0;

			document.location.href = Util.getUrl(parameters);
		}
	};

	// Expose in Vidi object for compatibility reason.
	Vidi.Export = Export;
	return Export;
});
