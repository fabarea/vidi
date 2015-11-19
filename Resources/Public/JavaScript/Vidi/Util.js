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
 * Module: Fab/Vidi/Vidi/Util
 */
define(['jquery'], function($) {
	'use strict';

	var Util = {

		/**
		 * @param {object} parameters
		 * @returns string
		 */
		getUrl: function(parameters) {
			var serializedParameters = $.param(parameters);
			var url = Vidi.module.moduleUrl + "&" + serializedParameters;
			return url;
		}
	};

	return Util;
});