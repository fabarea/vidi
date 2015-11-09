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
 * Module: Fab/Vidi/Vidi/Panel
 */
define(['jquery'], function($) {
	'use strict';

	var Panel = {

		/**
		 * Display THE "form" panel
		 *
		 * @return void
		 */
		showForm: function() {

			// Store the loading message
			if (typeof Vidi.icons.loading == 'undefined') {
				Vidi.icons.loading = $('#container-main-sub').html();
			}
			this.togglePanel();
		},

		/**
		 * Display THE "list" panel
		 *
		 * @param {boolean} reloadTable
		 * @return void
		 */
		showList: function(reloadTable) {
			if (typeof (reloadTable) == 'undefined') {
				reloadTable = true;
			}

			// Remove footer and header markup.
			$('#footer > *').remove();
			$('#navbar-sub > *').remove();

			// Add loading message for the next time the panel is displayed
			$('#container-main-sub').html(Vidi.icons.loading);
			this.togglePanel();

			if (reloadTable) {
				Vidi.grid.fnDraw(false); // false = for keeping the pagination.
			}
		},

		/**
		 * Toggle visibility of various panels
		 *
		 * @private
		 * @return void
		 */
		togglePanel: function() {
			// Expand / Collapse widgets
			$(['container-main-top', 'container-main-sub', 'navbar-main', 'navbar-sub']).each(function(index, value) {
				$('#' + value).toggle();
			});

		}
	};

	// Expose in Vidi object for compatibility reason.
	Vidi.Panel = Panel;
	return Panel;
});